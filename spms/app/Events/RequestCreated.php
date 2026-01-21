<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;


class RequestCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('requests');
    }

    public function broadcastAs(): string
    {
        return 'request.created';
    }
}
