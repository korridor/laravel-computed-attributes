<?php

namespace Korridor\LaravelComputedAttributes\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Korridor\LaravelComputedAttributes\ComputedAttributes;
use Korridor\LaravelComputedAttributes\Parser\ModelAttributeParser;
use Korridor\LaravelComputedAttributes\Parser\ParsingException;
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
    '{--chunkSize= : Size of the model chunk}';

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
     * @return int
     * @throws ReflectionException
     */
    public function handle()
    {
        $modelsWithAttributes = $this->argument('modelsAttributes');

        $this->info('Parsing arguments...');

        // Validate and parse modelsAttributes argument
        $modelAttributeParser = app(ModelAttributeParser::class);
        try {
            $modelAttributesEntries = $modelAttributeParser->getModelAttributeEntries($modelsWithAttributes);
        } catch (ParsingException $exception) {
            var_dump($exception->getMessage());
            $this->error($exception->getMessage());

            return 1;
        }

        // Validate chunkSize option
        $chunkSizeRaw = $this->option('chunkSize');
        if ($chunkSizeRaw !== null) {
            if (preg_match('/^\d+$/', $chunkSizeRaw)) {
                $chunkSize = intval($chunkSizeRaw);
                if ($chunkSize < 1) {
                    $this->error('Option chunkSize needs to be greater than zero');

                    return 1;
                }
            } else {
                $this->error('Option chunkSize needs to be an integer');

                return 1;
            }
        }

        // Calculate
        foreach ($modelAttributesEntries as $modelAttributesEntry) {
            $model = $modelAttributesEntry->getModel();
            $this->info('Start calculating for following attributes of model "'.$model.'":');

            /** @var Builder|ComputedAttributes $modelInstance */
            $modelInstance = new $model();
            $attributes = $modelAttributesEntry->getAttributes();
            $this->info('['.implode(',', $attributes).']');
            if (sizeof($attributes) > 0) {
                $modelInstance->chunk($chunkSize, function ($modelResults) use ($attributes) {
                    /* @var Model|ComputedAttributes $modelResult */
                    foreach ($modelResults as $modelResult) {
                        foreach ($attributes as $attribute) {
                            $modelResult->setComputedAttributeValue($attribute);
                        }
                        $modelResult->save();
                    }
                });
            }
        }

        return 0;
    }
}
