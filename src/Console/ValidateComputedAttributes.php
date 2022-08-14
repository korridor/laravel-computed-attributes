<?php

namespace Korridor\LaravelComputedAttributes\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Korridor\LaravelComputedAttributes\ComputedAttributes;
use Korridor\LaravelComputedAttributes\Parser\ModelAttributeParser;
use Korridor\LaravelComputedAttributes\Parser\ModelAttributesEntry;
use Korridor\LaravelComputedAttributes\Parser\ParsingException;
use ReflectionException;

/**
 * Class GenerateComputedAttributes.
 */
class ValidateComputedAttributes extends Command
{

    // Note: Fix for Laravel 6
    public const SUCCESS = 0;
    public const FAILURE = 1;
    public const INVALID = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'computed-attributes:validate '.
        '{ modelsAttributes? : List of models and optionally their attributes, '.
        'if not given all models that use the ComputedAttributes trait '.
        '(example: "FullModel;PartModel:attribute_1,attribute_2" or "OtherNamespace/OtherModel")} '.
        '{ --chunkSize=500 : Size of the model chunk }'.
        '{ --chunk= : Process only one chunk. If the argument is missing all model entries are being processed }';

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
     *
     * @throws ReflectionException
     */
    public function handle(): int
    {
        $modelsWithAttributes = $this->argument('modelsAttributes');

        $this->info('Parsing arguments...');

        // Validate chunkSize option
        $chunkSizeRaw = $this->option('chunkSize');
        if (preg_match('/^\d+$/', $chunkSizeRaw)) {
            $chunkSize = intval($chunkSizeRaw);
            if ($chunkSize < 1) {
                $this->error('Option chunkSize needs to be greater than zero');

                return self::FAILURE;
            }
        } else {
            $this->error('Option chunkSize needs to be an integer greater than zero');

            return self::FAILURE;
        }

        // Validate block option
        $chunkRaw = $this->option('chunk');
        if ($chunkRaw !== null) {
            if (preg_match('/^\d+$/', $chunkRaw)) {
                $chunk = intval($chunkRaw);
                if ($chunk < 0) {
                    $this->error('Option chunk needs to be greater or equal than zero');

                    return self::FAILURE;
                }
            } else {
                $this->error('Option chunk needs to be an integer or equal greater than zero');

                return self::FAILURE;
            }
        } else {
            $chunk = null;
        }

        // Validate and parse modelsAttributes argument
        /** @var ModelAttributeParser $modelAttributeParser */
        $modelAttributeParser = app(ModelAttributeParser::class);
        try {
            $modelAttributesEntries = $modelAttributeParser->getModelAttributeEntries($modelsWithAttributes);
        } catch (ParsingException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
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
                $query = $modelInstance->computedAttributesValidate($attributes);

                if ($chunk !== null) {
                    $modelResults = $query->offset($chunk * $chunkSize)
                        ->limit($chunkSize)
                        ->get();
                    $this->validateModels($modelResults, $attributes, $modelAttributesEntry);
                } else {
                    $query->chunk($chunkSize, function ($modelResults) use ($attributes, $modelAttributesEntry) {
                        $this->validateModels($modelResults, $attributes, $modelAttributesEntry);
                    });
                }
            }
        }

        return self::SUCCESS;
    }

    private function validateModels(Collection $models, array $attributes, ModelAttributesEntry $modelAttributesEntry): void
    {
        /* @var Model|ComputedAttributes $modelResult */
        foreach ($models as $modelResult) {
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
    }

    /**
     * @param $var
     * @return string
     */
    private function varToString($var): string
    {
        if ($var === null) {
            return 'null';
        }

        return gettype($var).'('.$var.')';
    }
}
