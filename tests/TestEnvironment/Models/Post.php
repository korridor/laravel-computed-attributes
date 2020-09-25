<?php

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Korridor\LaravelComputedAttributes\ComputedAttributes;

class Post extends Model
{
    use ComputedAttributes;

    /**
     * @var array
     */
    protected $computed = [
        'complex_calculation',
        'sum_of_votes',
    ];

    /**
     * @return int
     */
    public function getComplexCalculationComputed()
    {
        return 1 + 2;
    }

    /**
     * @return int
     */
    public function getSumOfVotesComputed()
    {
        return $this->votes()->sum('rating');
    }

    /*
     * Relations
     */

    /**
     * @return HasMany|Vote
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        static::saving(function (Post $model) {
            $model->setComputedAttributeValue('sum_of_votes');
        });
        parent::boot();
    }
}
