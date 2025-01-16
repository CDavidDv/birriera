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

class CancelarPedidoEvent implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $pedido, $mesa;

    public function __construct(Pedidos $pedido, ?Mesa $mesa = null)
    {
        $this->pedido = $pedido;
        $this->mesa = $mesa;
    }
    

    public function broadcastOn()
    {
        return new Channel('pedidos_sucursal_' . $this->pedido->sucursal_id);
    }

    public function broadcastWith()
    {   
        return [
            'pedido' => $this->pedido,
            'mesa' => $this->mesa ? $this->mesa->toArray() : null,
        ];
    }

    public function broadcastAs()
    {
        return 'cancelar-pedido'; // Nombre del evento
    }
}
