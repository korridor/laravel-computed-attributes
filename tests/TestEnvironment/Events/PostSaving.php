<?php

declare(strict_types=1);

namespace Korridor\LaravelComputedAttributes\Tests\TestEnvironment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostSaving
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;
}
