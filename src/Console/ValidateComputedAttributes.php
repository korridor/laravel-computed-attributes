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
class ValidateComputedAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'computed-attributes:validate '.
        '{modelsAttributes? : List of models and optionally their attributes, '.
        'if not given all models that use the ComputedAttributes trait '.
        '(example: "FullModel;PartModel:attribute_1,attribute_2" or "OtherNamespace/OtherModel")} '.
        '{--chunkSize=500 : Size of the model chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validates the current values of the given computed attributes.';

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
            $this->error($exception->getMessage());

            return 1;
        }

        // Validate chunkSize option
        $chunkSizeRaw = $this->option('chunkSize');
        if (preg_match('/^\d+$/', $chunkSizeRaw)) {
            $chunkSize = intval($chunkSizeRaw);
            if ($chunkSize < 1) {
                $this->error('Option chunkSize needs to be greater than zero');

                return 1;
            }
        } else {
            $this->error('Option chunkSize needs to be an integer greater than zero');

            return 1;
        }

        // Validate
        foreach ($modelAttributesEntries as $modelAttributesEntry) {
            $model = $modelAttributesEntry->getModel();
            /** @var Builder|ComputedAttributes $modelInstance */
            $modelInstance = new $model();
            $attributes = $modelAttributesEntry->getAttributes();

            $this->info('Start validating following attributes of model "'.$model.'":');
            $this->info('['.implode(',', $attributes).']');
            if (sizeof($attributes) > 0) {
                $modelInstance->computedAttributesValidate($attributes)
                    ->chunk($chunkSize, function ($modelResults) use ($attributes, $modelAttributesEntry) {
                        /* @var Model|ComputedAttributes $modelResult */
                        foreach ($modelResults as $modelResult) {
                            foreach ($attributes as $attribute) {
                                if ($modelResult->getComputedAttributeValue($attribute) !== $modelResult->{$attribute}) {
                                    $this->info($modelAttributesEntry->getModel().
                                    '['.$modelResult->getKeyName().'='.$modelResult->getKey().']['.$attribute.']');
                                    $this->info('Current value: '.$this->varToString($modelResult->{$attribute}));
                                    $this->info('Calculated value: '.
                                    $this->varToString($modelResult->getComputedAttributeValue($attribute)));
                                }
                            }
                        }
                    });
            }
        }

        return true;
    }

    /**
     * @param $var
     * @return false|string
     */
    private function varToString($var)
    {
        if ($var === null) {
            return 'null';
        }

        return gettype($var).'('.$var.')';
    }
}
