<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pedidos;

class Sucursal extends Model
{
    use HasFactory;
    protected $table = 'sucursales';
    protected $fillable = ['nombre', 'direccion', 'telefono'];

    // Relación Muchos a Muchos con usuarios
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_sucursales')
                    ->withTimestamps();
    }

    public function inventario()
    {
        return $this->hasMany(Inventario::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class);
    }

    public function checkInCheckOuts()
    {
        return $this->hasMany(CheckInCheckOut::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function cocina()
    {
        return $this->hasMany(Cocina::class);
    }

    public function cortesDeCaja()
    {
        return $this->hasMany(CorteCaja::class);
    }

}
