<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Parser;

use Composer\Autoload\ClassMapGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Korridor\LaravelComputedAttributes\ComputedAttributes;
use ReflectionClass;
use ReflectionException;

class ModelAttributeParser
{
    /**
     * @return string
     */
    public function getAbsolutePathOfModelFolder(): string
    {
        return base_path(Config::get('computed-attributes.model_path'));
    }

    /**
     * @return string
     */
    public function getModelNamespaceBase(): string
    {
        return Config::get('computed-attributes.model_namespace') . '\\';
    }

    /**
     * Get all models classes that use the ComputedAttributes trait.
     *
     * @return array
     *
     * @throws ReflectionException
     */
    public function getAllModelClasses(): array
    {
        // Get all models with trait
        $classmap = ClassMapGenerator::createMap($this->getAbsolutePathOfModelFolder());
        $models = [];
        foreach ($classmap as $class => $filepath) {
            $reflection = new ReflectionClass($class);
            $traits = $reflection->getTraitNames();
            foreach ($traits as $trait) {
                if ('Korridor\\LaravelComputedAttributes\\ComputedAttributes' === $trait) {
                    $models[] = $class;
                }
            }
        }

        return $models;
    }

    /**
     * @param  string|null  $modelsWithAttributes
     * @return ModelAttributesEntry[]
     *
     * @throws ParsingException
     * @throws ReflectionException
     */
    public function getModelAttributeEntries(?string $modelsWithAttributes = null): array
    {
        $modelAttributesToProcess = [];
        $models = $this->getAllModelClasses();
        if (null === $modelsWithAttributes) {
            foreach ($models as $model) {
                /** @var Model|ComputedAttributes $modelInstance */
                $modelInstance = new $model();
                $attributes = $modelInstance->getComputedAttributeConfiguration();
                $modelAttributesToProcess[] = new ModelAttributesEntry($model, $attributes);
            }
        } else {
            $modelsInAttribute = explode(';', $modelsWithAttributes);
            foreach ($modelsInAttribute as $modelInAttribute) {
                $modelInAttributeExploded = explode(':', $modelInAttribute);
                if (1 !== sizeof($modelInAttributeExploded) && 2 !== sizeof($modelInAttributeExploded)) {
                    throw new ParsingException('Parsing error');
                }
                $model = $this->getModelNamespaceBase() . str_replace('/', '\\', $modelInAttributeExploded[0]);
                if (in_array($model, $models)) {
                    /** @var Model|ComputedAttributes $modelInstance */
                    $modelInstance = new $model();
                } else {
                    throw new ParsingException('Model "' . $model . '" not found ' .
                        '(don\'t forget to add the ComputedAttributes trait to the model)');
                }
                $attributes = $modelInstance->getComputedAttributeConfiguration();
                if (2 === sizeof($modelInAttributeExploded)) {
                    $attributeWhitelistItems = explode(',', $modelInAttributeExploded[1]);
                    foreach ($attributeWhitelistItems as $attributeWhitelistItem) {
                        if (! in_array($attributeWhitelistItem, $attributes)) {
                            throw new ParsingException('Attribute "' . $attributeWhitelistItem .
                                '" does not exist in model ' . $model);
                        }
                    }
                    $attributes = $attributeWhitelistItems;
                }
                $modelAttributesToProcess[] = new ModelAttributesEntry($model, $attributes);
            }
        }

        return $modelAttributesToProcess;
    }
}
