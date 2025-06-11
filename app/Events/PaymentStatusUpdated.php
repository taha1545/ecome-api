<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $payment;

   
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

   
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('payments.' . $this->payment->id),
            new PrivateChannel('admin.payments'),
        ];
    }

 
    public function broadcastWith(): array
    {
        return [
            'id' => $this->payment->id,
            'status' => $this->payment->status,
            'amount' => $this->payment->amount,
            'order_id' => $this->payment->order_id,
            'updated_at' => $this->payment->updated_at
        ];
    }
} 