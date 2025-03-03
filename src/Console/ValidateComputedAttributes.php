<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Korridor\LaravelComputedAttributes\ComputedAttributesInterface;
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
    protected $signature = 'computed-attributes:validate ' .
        '{ modelsAttributes? : List of models and optionally their attributes, ' .
        'if not given all models that use the ComputedAttributes trait ' .
        '(example: "FullModel;PartModel:attribute_1,attribute_2" or "OtherNamespace/OtherModel")} ' .
        '{ --chunkSize=500 : Size of the model chunk }' .
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
        $this->info('Parsing arguments...');

        // Validate modelsAttributes argument
        $modelsWithAttributes = $this->argument('modelsAttributes');
        if ($modelsWithAttributes !== null && !is_string($modelsWithAttributes)) {
            $this->error('Argument modelsAttributes needs to be a string');

            return self::FAILURE;
        }

        // Validate chunkSize option
        $chunkSizeRaw = $this->option('chunkSize');
        if (is_string($chunkSizeRaw) && preg_match('/^\d+$/', $chunkSizeRaw)) {
            $chunkSize = (int) $chunkSizeRaw;
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
            if (is_string($chunkRaw) && preg_match('/^\d+$/', $chunkRaw)) {
                $chunk = (int) $chunkRaw;
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
            /** @var Model&ComputedAttributesInterface $modelInstance */
            $modelInstance = new $model();
            $attributes = $modelAttributesEntry->getAttributes();

            $this->info('Start validating following attributes of model "' . $model . '":');
            $this->info('[' . implode(',', $attributes) . ']');
            if (sizeof($attributes) > 0) {
                $query = $modelInstance->computedAttributesValidate($attributes);

                if ($chunk !== null) {
                    $modelResults = $query->offset($chunk * $chunkSize)
                        ->limit($chunkSize)
                        ->get();
                    $this->validateModels($modelResults, $attributes, $modelAttributesEntry);
                } else {
                    $query->chunk($chunkSize, function ($modelResults) use ($attributes, $modelAttributesEntry): void {
                        $this->validateModels($modelResults, $attributes, $modelAttributesEntry);
                    });
                }
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param EloquentCollection<array-key, Model&ComputedAttributesInterface> $models
     * @param array<string> $attributes
     * @param ModelAttributesEntry $modelAttributesEntry
     * @return void
     */
    private function validateModels(EloquentCollection $models, array $attributes, ModelAttributesEntry $modelAttributesEntry): void
    {
        foreach ($models as $modelResult) {
            foreach ($attributes as $attribute) {
                if ($modelResult->getComputedAttributeValue($attribute) !== $modelResult->{$attribute}) {
                    $this->info($modelAttributesEntry->getModel() .
                        '[' . $modelResult->getKeyName() . '=' . $modelResult->getKey() . '][' . $attribute . ']');
                    $this->info('Current value: ' . $this->varToString($modelResult->{$attribute}));
                    $this->info('Calculated value: ' .
                        $this->varToString($modelResult->getComputedAttributeValue($attribute)));
                }
            }
        }
    }

    /**
     * @param mixed $var
     * @return string
     */
    private function varToString(mixed $var): string
    {
        if ($var === null) {
            return 'null';
        }

        return gettype($var) . '(' . $var . ')';
    }
}
