<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @method Builder<static> computedAttributesValidate(array<string> $attributes)
 * @method Builder<static> computedAttributesGenerate(array<string> $attributes)
 * @phpstan-require-extends Model
 */
interface ComputedAttributesInterface
{
    /**
     * Compute the given attribute and return the result.
     *
     * @param  string  $attributeName
     * @return mixed
     */
    public function getComputedAttributeValue(string $attributeName): mixed;

    /**
     * Compute the given attribute and assign the result in the model.
     *
     * @param  string  $attributeName
     */
    public function setComputedAttributeValue(string $attributeName): void;

    /**
     * This scope will be applied during the computed property generation with artisan computed-attributes:generate.
     *
     * @template TModel of Model
     * @param  Builder<TModel>  $builder
     * @param  array<string>  $attributes  Attributes that will be generated.
     * @return Builder<TModel>
     */
    public function scopeComputedAttributesGenerate(Builder $builder, array $attributes): Builder;

    /**
     * This scope will be applied during the computed property validation with artisan computed-attributes:validate.
     *
     * @template TModel of Model
     * @param  Builder<TModel>  $builder
     * @param  array<string>  $attributes  Attributes that will be validated.
     * @return Builder<TModel>
     */
    public function scopeComputedAttributesValidate(Builder $builder, array $attributes): Builder;

    /**
     * Return the configuration array for this model.
     * If the configuration array does not exist the function will return an empty array.
     *
     * @return array<int, string>
     */
    public function getComputedAttributeConfiguration(): array;
}
