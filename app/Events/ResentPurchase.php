<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResentPurchase
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $purchase;
    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }
}
