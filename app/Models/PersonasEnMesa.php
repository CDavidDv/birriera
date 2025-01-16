<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pedidos;

class PersonaEnMesa extends Model
{
    protected $fillable = ['mesa_id', 'nombre', 'orden'];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class);
    }
    
}
