<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehiclePriceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $price;
    public $type;

    public function __construct($price, $type)
    {
        $this->price = $price;
        $this->type = $type;
        \Log::info('Evento VehiclePriceUpdated criado:', ['price' => $price, 'type' => $type]);
    }

    public function broadcastOn()
    {
        \Log::info('Evento VehiclePriceUpdated transmitido no canal vehicle-prices');
        return new Channel('vehicle-prices');
    }

    public function broadcastAs()
    {
        return 'price.updated';
    }
} 