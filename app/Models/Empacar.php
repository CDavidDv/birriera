<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empacar extends Model
{
    use HasFactory;
    
    protected $table = 'empacadores';
    
   
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
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

}
