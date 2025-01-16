<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Mesa;
use App\Models\Pedidos;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaProducto;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function dashboard()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        if ($user->hasRole('cocina')) return redirect()->route('cocina');
        if ($user->hasRole('caja')) return redirect()->route('corte-caja');
        if ($user->hasRole('empacador')) return redirect()->route('empacar');

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        
        $ordenes = Pedidos::with(['productos.producto'])
            ->with('mesa')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->whereIn('tipo_pedido', ['normal', 'mixto'])
            ->where('sucursal_id', $sucursalId)
            ->get();

        $ordenesParaLlevar = Pedidos::with(['productos.producto'])
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->where('tipo_pedido', 'para_llevar')
            ->where('sucursal_id', $sucursalId)
            ->get();
        
        
        $mesas = Mesa::where('sucursal_id', $sucursalId)->get();

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'mesas' => $mesas,
            'ordenes' => $ordenes,
            'ordenesParaLlevar' => $ordenesParaLlevar
        ]);
    }
    public function home()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();


        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        
        $ordenes = Pedidos::with(['productos.producto'])
            ->with('mesa')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->whereIn('tipo_pedido', ['normal', 'mixto'])
            ->where('sucursal_id', $sucursalId)
            ->get();

        $ordenesParaLlevar = Pedidos::with(['productos.producto'])
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->where('tipo_pedido', 'para_llevar')
            ->where('sucursal_id', $sucursalId)
            ->get();
        
        
        $mesas = Mesa::where('sucursal_id', $sucursalId)->get();

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'mesas' => $mesas,
            'ordenes' => $ordenes,
            'ordenesParaLlevar' => $ordenesParaLlevar
        ]);
    }

    



    

    public function entregar()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();

        return Inertia::render('Hornear/index', [
            'inventario' => $inventario
        ]);
    }

    
    public function procesarPastesHorneados(Request $request)
    {
        // Obtén el usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener los pastes horneados desde la solicitud
        $pastesHorneados = $request->input('pastes'); // Array de pastes que contiene nombre, cantidad, masa y relleno

        foreach ($pastesHorneados as $paste) {
            // 1. Aumentar la cantidad de pastes en el inventario
            $inventarioPaste = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'pastes')
                ->where('sucursal_id', $sucursalId)
                ->first();

            $inventarioEmpanadaSalada = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'empanadas saladas')
                ->where('sucursal_id', $sucursalId)
                ->first();

            $inventarioEmpanadaDulce = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'empanadas dulces')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioPaste) {
                $inventarioPaste->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioPaste->save();
            } else if ($inventarioEmpanadaSalada){
                $inventarioEmpanadaSalada->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioEmpanadaSalada->save();
            } else if ($inventarioEmpanadaDulce){
                $inventarioEmpanadaDulce->cantidad += $paste['cantidad']; // Aumenta la cantidad de pastes horneados
                $inventarioEmpanadaDulce->save();
            }else{
                Inventario::create([
                    'sucursal_id' => $sucursalId,
                    'nombre' => $paste['nombre'],
                    'tipo' => 'pastes',
                    'cantidad' => $paste['cantidad'],
                ]);
            }

            // 2. Restar la cantidad de masa utilizada
            $inventarioMasa = Inventario::where('nombre', $paste['masa'])
                ->where('tipo', 'masa')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioMasa) {
                $inventarioMasa->cantidad -= ($paste['cantidad']); // Suponemos que cada paste usa 0.1 kg de masa
                if ($inventarioMasa->cantidad < 0) {
                    $inventarioMasa->cantidad = 0; // Evitar cantidades negativas
                }
                $inventarioMasa->save();
            }

            // 3. Restar la cantidad de relleno utilizado
            $inventarioRelleno = Inventario::where('nombre', $paste['nombre'])
                ->where('tipo', 'relleno')
                ->where('sucursal_id', $sucursalId)
                ->first();

            if ($inventarioRelleno) {
                $inventarioRelleno->cantidad -= ($paste['cantidad']); // Suponemos que cada paste usa 0.2 kg de relleno
                if ($inventarioRelleno->cantidad < 0) {
                    $inventarioRelleno->cantidad = 0; // Evitar cantidades negativas
                }
                $inventarioRelleno->save();
            }
        }

        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();


        return Inertia::render('Hornear/index', [
            'inventario' => $inventario,
            'message' => 'Pastes horneados procesados con éxito.'
        ]);
    }


}
