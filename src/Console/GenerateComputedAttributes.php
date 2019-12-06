<?php

namespace Korridor\LaravelComputedAttributes\Console;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Korridor\LaravelComputedAttributes\ComputedAttributes;
use ReflectionClass;
use ReflectionException;

/**
 * Class GenerateComputedAttributes.
 */
class GenerateComputedAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'computed-attributes:generate '.
    '{modelsAttributes? : List of models and optionally their attributes (example: "FullModel;PartModel:attribute_1,attribute_2" or "OtherNamespace\OtherModel")} '.
    '{--chunkSize=100 : Size of the model chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws ReflectionException
     */
    public function handle()
    {
        $modelsWithAttributes = $this->argument('modelsAttributes');
        $modelPath = app_path('Models');
        $modelNamespace = 'App\\Models\\';
        $chunkSizeRaw = $this->option('chunkSize');
        if (preg_match('/^\d+$/', $chunkSizeRaw)) {
            $chunkSize = intval($chunkSizeRaw);
            if ($chunkSize < 1) {
                $this->error('Option chunkSize needs to be greater than zero');

                return false;
            }
        } else {
            $this->error('Option chunkSize needs to be an integer');

            return false;
        }

        // Get all models with trait
        $classmap = ClassMapGenerator::createMap($modelPath);
        $models = [];
        foreach ($classmap as $class => $filepath) {
            $reflection = new ReflectionClass($class);
            $traits = $reflection->getTraitNames();
            foreach ($traits as $trait) {
                if ('Korridor\\LaravelComputedAttributes\\ComputedAttributes' === $trait) {
                    array_push($models, $class);
                }
            }
        }

        // Get all class/attribute combinations
        $modelAttributesToProcess = [];
        if (null === $modelsWithAttributes) {
            $this->info('Start calculating for all models with trait...');
            foreach ($models as $model) {
                /** @var Model|ComputedAttributes $modelInstance */
                $modelInstance = new $model();
                $attributes = $modelInstance->getComputedAttributeConfiguration();
                array_push($modelAttributesToProcess, [
                    'model' => $model,
                    'modelInstance' => $modelInstance,
                    'attributes' => $attributes,
                ]);
            }
        } else {
            $this->info('Start calculating for given models...');
            $modelsInAttribute = explode(';', $modelsWithAttributes);
            foreach ($modelsInAttribute as $modelInAttribute) {
                $modelInAttributeExploded = explode(':', $modelInAttribute);
                if (1 !== sizeof($modelInAttributeExploded) && 2 !== sizeof($modelInAttributeExploded)) {
                    $this->error('Parsing error');

                    return false;
                }
                $model = $modelNamespace.$modelInAttributeExploded[0];
                if (in_array($model, $models)) {
                    /** @var Model|ComputedAttributes $modelInstance */
                    $modelInstance = new $model();
                } else {
                    $this->error('Model "'.$model.'" not found');

                    return false;
                }
                $attributes = $modelInstance->getComputedAttributeConfiguration();
                if (2 === sizeof($modelInAttributeExploded)) {
                    $attributeWhitelistItems = explode(',', $modelInAttributeExploded[1]);
                    foreach ($attributeWhitelistItems as $attributeWhitelistItem) {
                        if (in_array($attributeWhitelistItem, $attributes)) {
                        } else {
                            $this->error('Attribute "'.$attributeWhitelistItem.'" does not exist in model '.$model);

                            return false;
                        }
                    }
                }
                array_push($modelAttributesToProcess, [
                    'model' => $model,
                    'modelInstance' => $modelInstance,
                    'attributes' => $attributes,
                ]);
            }
        }

        // Calculate
        foreach ($modelAttributesToProcess as $modelAttributeToProcess) {
            $this->info('Start calculating for following attributes of model "'.$modelAttributeToProcess['model'].'":');
            /** @var Model|ComputedAttributes $modelInstance */
            $modelInstance = $modelAttributeToProcess['modelInstance'];
            $attributes = $modelAttributeToProcess['attributes'];
            $this->info('['.implode(',', $attributes).']');
            if (sizeof($attributes) > 0) {
                $modelInstance->chunk($chunkSize, function ($modelResults) use ($attributes) {
                    /* @var Model|ComputedAttributes $modelInstance */
                    foreach ($modelResults as $modelResult) {
                        foreach ($attributes as $attribute) {
                            $modelResult->setComputedAttributeValue($attribute);
                        }
                        $modelResult->save();
                    }
                });
            }
        }

        return true;
    }
}
