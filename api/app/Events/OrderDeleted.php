<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class OrderDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public array $order;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Order $order
    ) {
        $this->order = $order->toArray();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('restaurants.'.$this->order['restaurant_id'].'.orders')];
    }
}
