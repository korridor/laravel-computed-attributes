<?php

namespace Korridor\LaravelComputedAttributes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method static Builder|Model computedAttributesValidate(array $attributes)
 * @method static Builder|Model computedAttributesGenerate(array $attributes)
 */
trait ComputedAttributes
{
    /**
     * Compute the given attribute and return the result.
     *
     * @param  string  $attributeName
     * @return mixed
     */
    public function getComputedAttributeValue(string $attributeName)
    {
        $functionName = 'get'.Str::studly($attributeName).'Computed';

        return $this->{$functionName}();
    }

    /**
     * Compute the given attribute and assign the result in the model.
     *
     * @param  string  $attributeName
     */
    public function setComputedAttributeValue(string $attributeName): void
    {
        $computed = $this->getComputedAttributeValue($attributeName);
        $this->{$attributeName} = $computed;
    }

    /**
     * This scope will be applied during the computed property generation with artisan computed-attributes:generate.
     *
     * @param  Builder  $builder
     * @param  array  $attributes  Attributes that will be generated.
     * @return Builder
     */
    public function scopeComputedAttributesGenerate(Builder $builder, array $attributes): Builder
    {
        return $builder;
    }

    /**
     * This scope will be applied during the computed property validation with artisan computed-attributes:validate.
     *
     * @param  Builder  $builder
     * @param  array  $attributes  Attributes that will be validated.
     * @return Builder
     */
    public function scopeComputedAttributesValidate(Builder $builder, array $attributes): Builder
    {
        return $builder;
    }

    /**
     * Return the configuration array for this model.
     * If the configuration array does not exist the function will return an empty array.
     *
     * @return array
     */
    public function getComputedAttributeConfiguration(): array
    {
        if (isset($this->computed)) {
            return $this->computed;
        } else {
            return [];
        }
    }
}
