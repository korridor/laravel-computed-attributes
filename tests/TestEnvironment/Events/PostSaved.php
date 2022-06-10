<?php

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
