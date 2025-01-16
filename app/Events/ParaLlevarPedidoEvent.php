<?php

namespace App\Events;

use App\Models\Pedidos;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParaLlevarPedidoEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $pedido;

    public function __construct(Pedidos $pedido)
    {
        $this->pedido = $pedido;
    }

    public function broadcastOn()
    {
        return new Channel('pedidos_sucursal_' . $this->pedido->sucursal_id);
    }

    public function broadcastWith()
    {   
        return ['id' => $this->pedido->id];
    }
    public function broadcastAs()
    {
        return 'para-llevar-pedido'; // Nombre del evento
    }
}
