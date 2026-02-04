<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use App\Models\Drivers;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public Drivers $driver;

    public function __construct(Drivers $driver)
    {
        $this->driver = $driver;
    }

    public function broadcastOn()
    {
        return new Channel('drivers');
    }
}
