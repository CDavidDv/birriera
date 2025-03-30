<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    use HasFactory;
    protected $table = 'pedidos';
    protected $fillable = ['sucursal_id', 'total', 'estado', 'para_mesa', 'para_llevar', 'tipo_pedido', 'prioridad',
        'mesa_id',  'total', 'estado', 'personalizacion', 'sucursal_id', 'nombre_cliente', 'observaciones'];
     
    public function persona()
    {
        return $this->belongsTo(PersonaEnMesa::class);
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function productos()
    {
        return $this->hasMany(PedidoProducto::class, 'pedido_id');
    }
    
}
