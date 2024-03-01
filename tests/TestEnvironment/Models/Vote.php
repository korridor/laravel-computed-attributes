<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $rating
 */
class Vote extends Model
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'int',
    ];

    /*
     * Relations
     */

    /**
     * @return BelongsTo<Post, Vote>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Boot function from laravel.
     */
    protected static function boot(): void
    {
        /*
        Note: This listener is only commented out to test the commands on incorrect data.
        static::saved(function (Vote $model) {
            $model->post->setComputedAttributeValue('sum_of_votes');
        });
        */
        parent::boot();
    }
}
