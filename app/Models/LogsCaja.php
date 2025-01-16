<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogsCaja extends Model
{
    use HasFactory;

    // Nombre de la tabla (opcional si sigue la convención de nombres)
    protected $table = 'logs_caja';

    protected $fillable = [
        'sucursal_id',
        'usuario_id',
        'cantidad',
        'tipo',
        'descripcion',
        'metodo_pago',
        'dinero_antes',
        'dinero_despues',
    ];

    // Relación con la tabla Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    // Relación con la tabla Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
