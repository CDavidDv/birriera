<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeguimientoOrden extends Model
{
    use HasFactory;

    protected $table = 'seguimiento_orden';

    // Campos que se pueden asignar en masa.
    protected $fillable = [
        'sucursal_id',
        'pedido_id',
        'tipo_pedido',
        'creo',
        'cocino',
        'mandar_caja',
        'entrego',
        'cobro',
        'empaco',
        'estado',
    ];

    /**
     * Relaciones con otras tablas
     */

    // Relación con la tabla sucursales.
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    // Relación con la tabla pedidos.
    public function pedido()
    {
        return $this->belongsTo(Pedidos::class, 'pedido_id');
    }

    // Relación con el modelo Usuario para cada acción.
    public function creador()
    {
        return $this->belongsTo(User::class, 'creo');
    }

    public function cocinero()
    {
        return $this->belongsTo(User::class, 'cocino');
    }

    public function cajero()
    {
        return $this->belongsTo(User::class, 'mandar_caja');
    }

    public function entregador()
    {
        return $this->belongsTo(User::class, 'entrego');
    }

    public function cobrador()
    {
        return $this->belongsTo(User::class, 'cobro');
    }

    public function empacador()
    {
        return $this->belongsTo(User::class, 'empaco');
    }
}
