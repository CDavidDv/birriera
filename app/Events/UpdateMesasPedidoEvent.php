<?php

namespace App\Events;

use App\Models\Mesa;
use App\Models\Pedidos;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateMesasPedidoEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $mesaOld, $mesaNew;

    public function __construct(Mesa $mesaOld, Mesa $mesaNew)
    {
        
        $this->mesaOld = $mesaOld;
        $this->mesaNew = $mesaNew;
    }

    public function broadcastOn()
    {
        return new Channel('pedidos_sucursal_' . $this->mesaOld->sucursal_id);
    }

    public function broadcastWith()
    {   
        return [
            'mesaNew' => $this->mesaNew,
            'mesaOld' => $this->mesaOld
        ];
    }
    public function broadcastAs()
    {
        return 'update-mesas';
    }
}
