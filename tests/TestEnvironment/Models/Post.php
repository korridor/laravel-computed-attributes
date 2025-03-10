<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Korridor\LaravelComputedAttributes\ComputedAttributes;
use Korridor\LaravelComputedAttributes\ComputedAttributesInterface;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events\PostSaved;
use Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events\PostSaving;

/**
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $complex_calculation
 * @property int $sum_of_votes
 * @property-read Collection<int, Vote> $votes
 */
class Post extends Model implements ComputedAttributesInterface
{
    use ComputedAttributes;

    /**
     * The attributes that are computed. (f.e. for performance reasons)
     * These attributes can be regenerated at any time.
     *
     * @var array<int, string>
     */
    protected array $computed = [
        'complex_calculation',
        'sum_of_votes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'complex_calculation' => 'int',
        'sum_of_votes' => 'int',
    ];

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'saved' => PostSaved::class,
        'saving' => PostSaving::class,
    ];

    /*
     * Computed attributes.
     */

    /**
     * @return int
     */
    public function getComplexCalculationComputed(): int
    {
        return 1 + 2;
    }

    /**
     * @return int
     */
    public function getSumOfVotesComputed(): int
    {
        return (int) $this->votes()->sum('rating');
    }

    /*
     * Scopes.
     */

    /**
     * This scope will be applied during the computed property generation with artisan computed-attributes:generate.
     *
     * @param  Builder<static>  $builder
     * @param  array<string>  $attributes  Attributes that will be generated.
     * @return Builder<static>
     */
    public function scopeComputedAttributesGenerate(Builder $builder, array $attributes): Builder
    {
        if (in_array('sum_of_votes', $attributes)) {
            return $builder->with('votes');
        }

        return $builder;
    }

    /**
     * This scope will be applied during the computed property validation with artisan computed-attributes:validate.
     *
     * @param  Builder<static>  $builder
     * @param  array<string>  $attributes  Attributes that will be validated.
     * @return Builder<static>
     */
    public function scopeComputedAttributesValidate(Builder $builder, array $attributes): Builder
    {
        if (in_array('sum_of_votes', $attributes)) {
            return $builder->with('votes');
        }

        return $builder;
    }

    /*
     * Relations
     */

    /**
     * @return HasMany<Vote, $this>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot(): void
    {
        static::saving(function (Post $model): void {
            $model->setComputedAttributeValue('sum_of_votes');
        });
        parent::boot();
    }
}
