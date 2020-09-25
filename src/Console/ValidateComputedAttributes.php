<?php

namespace Korridor\LaravelComputedAttributes\Console;

use Illuminate\Console\Command;
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
    '{modelsAttributes? : List of models and optionally their attributes (example: "FullModel;PartModel:attribute_1,attribute_2" or "OtherNamespace/OtherModel")} '.
    '{--chunkSize= : Size of the model chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * @var ModelAttributeParser
     */
    private $modelAttributeParser;

    /**
     * Create a new command instance.
     * @param ModelAttributeParser $modelAttributeParser
     */
    public function __construct(ModelAttributeParser $modelAttributeParser)
    {
        $this->modelAttributeParser = $modelAttributeParser;
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
        try {
            $modelAttributesEntries = $this->modelAttributeParser->getModelAttributeEntries($modelsWithAttributes);
        } catch (ParsingException $exception) {
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

        // Validate
        foreach ($modelAttributesEntries as $modelAttributesEntry) {
            /** @var Model|ComputedAttributes $modelInstance */
            $modelInstance = ($modelAttributesEntry->getModel())();
            $attributes = $modelAttributesEntry->getAttributes();

            $this->info('Start validating for following attributes of model "'.$modelAttributesEntry->getModel().'": '.implode(',', $attributes).'');
            if (sizeof($attributes) > 0) {
                $modelInstance->chunk($chunkSize, function ($modelResults) use ($attributes, $modelAttributesEntry) {
                    /* @var Model|ComputedAttributes $modelResult */
                    foreach ($modelResults as $modelResult) {
                        foreach ($attributes as $attribute) {
                            if ($modelResult->getComputedAttributeValue($attribute) !== $modelResult->{$attribute}) {
                                $this->info($modelAttributesEntry->getModel().'['.$modelResult->getKeyName().'='.$modelResult->getKey().']['.$attribute.']');
                                $this->info('Current value: '.$modelResult->{$attribute});
                                $this->info('Calculated value: '.$modelResult->getComputedAttributeValue($attribute));
                            }
                        }
                    }
                });
            }
        }

        return true;
    }
}
