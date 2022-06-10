<?php

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostSaving
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
}
