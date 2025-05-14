<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedidos;

class PedidoProducto extends Model
{
    protected $fillable = ['pedido_id', 'inventario_id', 'persona_id', 'cantidad', 'subtotal', 'personalizacion', 'tipo_servicio'];

    public function pedido()
    {
        return $this->belongsTo(Pedidos::class);
    }

    public function producto()
    {
        return $this->belongsTo(Inventario::class, 'inventario_id');
    }
}
