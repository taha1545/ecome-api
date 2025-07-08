<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewOrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;


    public function __construct(Order $order)
    {
        $this->order = $order;
    }


    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.orders'),
        ];
    }


    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'total' => $this->order->total,
                'status' => $this->order->status,
                'created_at' => $this->order->created_at,
                'user' => [
                    'id' => $this->order->user->id,
                    'name' => $this->order->user->name,
                    'email' => $this->order->user->email,
                ],
            ],
        ];
    }
}
