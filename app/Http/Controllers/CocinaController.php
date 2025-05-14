<?php

namespace App\Http\Controllers;

use App\Models\PedidoProducto;
use App\Models\Pedidos;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CocinaController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Filtrar pedidos y sus productos con estado pendiente
        $pedidos = Pedidos::with('productos.producto') // Asegúrate de cargar las relaciones
            ->join('pedido_productos', 'pedidos.id', '=', 'pedido_productos.pedido_id')
            ->with('mesa')
            ->where('pedido_productos.estado', 'pendiente')
            ->where('pedidos.sucursal_id', $sucursalId)
            ->select('pedidos.*')
            ->orderBy('pedidos.prioridad', 'desc')
            ->distinct()
            ->get();


        $productosPendientes = PedidoProducto::join('pedidos', 'pedido_productos.pedido_id', '=', 'pedidos.id')
            ->where('pedido_productos.estado', 'pendiente')
            //->where('pedidos.estado', 'pendiente')
            ->select('pedido_productos.*')
            ->get();

        return Inertia::render('Cocina/index', [
            'pedidos' => $pedidos,
            'productosPendientes' => $productosPendientes,
        ]);
    }

}
