<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedidos;

class Mesa extends Model
{
    
    protected $fillable = ['sucursal_id', 'nombre', 'capacidad', 'estado'];
    protected $table = 'mesas';
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function personas()
    {
        return $this->hasMany(PersonaEnMesa::class);
    }

    public function pedidos()
    {
        return $this->hasManyThrough(Pedidos::class, PersonaEnMesa::class);
    }

    public function personasEnMesa()
    {
        return $this->hasMany(PersonaEnMesa::class);
    }
}
