<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Parser;

class ModelAttributesEntry
{
    /**
     * @var string
     */
    private $model;

    /**
     * @var string[]
     */
    private $attributes;

    /**
     * ModelAttributesEntry constructor.
     *
     * @param  string  $model
     * @param  string[]  $attributes
     */
    public function __construct(string $model, array $attributes)
    {
        $this->model = $model;
        $this->attributes = $attributes;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
