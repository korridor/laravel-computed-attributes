<?php

use Illuminate\Database\Eloquent\Model;
use Korridor\LaravelComputedAttributes\ComputedAttributes;

class Post extends Model
{
    use ComputedAttributes;

    /**
     * @var array
     */
    protected $computed = [
        'complex_calculation',
    ];

    /**
     * @return int
     */
    public function getComplexCalculationComputed()
    {
        return 1 + 2;
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        static::saving(function (Post $model) {
            $model->setComputedAttributeValue('complex_calculation');
        });
        parent::boot();
    }
}
