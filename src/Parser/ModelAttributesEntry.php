<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Parser;

use Illuminate\Database\Eloquent\Model;
use Korridor\LaravelComputedAttributes\ComputedAttributes;

class ModelAttributesEntry
{
    /**
     * @var class-string<Model>
     */
    private string $model;

    /**
     * @var array<int, string>
     */
    private array $attributes;

    /**
     * ModelAttributesEntry constructor.
     *
     * @param  class-string<Model>  $model
     * @param  array<int, string>  $attributes
     */
    public function __construct(string $model, array $attributes)
    {
        $this->model = $model;
        $this->attributes = $attributes;
    }

    /**
     * @return class-string<Model|ComputedAttributes>
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @return array<int, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
