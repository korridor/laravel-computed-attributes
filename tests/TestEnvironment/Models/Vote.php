<?php

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{

    /*
     * Relations
     */

    /**
     * @return BelongsTo
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        /*
        static::saved(function (Vote $model) {
            $model->post->setComputedAttributeValue('sum_of_votes');
        });
    	*/
        parent::boot();
    }
}
