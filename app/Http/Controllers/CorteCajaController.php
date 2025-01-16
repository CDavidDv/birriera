<?php

namespace App\Http\Controllers;

use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\LogsCaja;
use App\Models\Pedidos;
use App\Models\Venta;

use App\Models\VentaProducto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CorteCajaController extends Controller
{
    public function filtro(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $filter = $request->input('filter');
        $value = $request->input('value');

        if ($filter === 'day') {
            $ventas = Pedidos::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $value)
                ->where('estado', 'finalizado')
                ->get();
            $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $value)
                ->whereNotIn('tipo', ['venta'])
                ->get();
            $corte = CorteCaja::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', $value)
                ->first();
        } elseif ($filter === 'week') {
            $ventas = Pedidos::where('sucursal_id', $sucursalId)
                ->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()])
                ->where('estado', 'finalizado')
                ->get();
            $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
                ->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()])
                ->whereNotIn('tipo', ['venta'])
                ->get();
            $corte = CorteCaja::where('sucursal_id', $sucursalId)
                ->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()])
                ->first();
        } elseif ($filter === 'month') {
            $ventas = Pedidos::where('sucursal_id', $sucursalId)
                ->whereMonth('created_at', Carbon::parse($value)->month)
                ->where('estado', 'finalizado')
                ->get();
            $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
                ->whereMonth('created_at', Carbon::parse($value)->month)
                ->whereNotIn('tipo', ['venta'])
                ->get();
            $corte = CorteCaja::where('sucursal_id', $sucursalId)
                ->whereMonth('created_at', Carbon::parse($value)->month)
                ->first();
        } else {
            return response()->json(['error' => 'Filtro no válido.'], 400);
        }

        // Ajustar las horas en los resultados
        $ventas->transform(function ($venta) {
            $venta->created_at = Carbon::parse($venta->created_at)->subHours(6); // Suma 6 horas
            $venta->updated_at = Carbon::parse($venta->updated_at)->subHours(6); // Suma 6 horas
            return $venta;
        });

        $logscaja->transform(function ($log) {
            $log->created_at = Carbon::parse($log->created_at)->subHours(6); // Suma 6 horas
            $log->updated_at = Carbon::parse($log->updated_at)->subHours(6); // Suma 6 horas
            return $log;
        });

        if ($corte) {
            $corte->created_at = Carbon::parse($corte->created_at)->subHours(6); // Suma 6 horas
            $corte->updated_at = Carbon::parse($corte->updated_at)->subHours(6); // Suma 6 horas
        }

        return response()->json([
            'ventas' => $ventas,
            'corte' => $corte,
            'logscaja' => $logscaja
        ]);
    }


    public function corte()
    {   
        // Obtener usuario autenticado
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
    
        // Obtener las ventas
        $ventas = Pedidos::where('sucursal_id', $sucursalId)
            ->where('estado', 'finalizado')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc') // Ordenar por la fecha más reciente
            ->limit(20)
            ->get();
    
        // Obtener el corte
        $corte = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->first();
    
        // Obtener los logs de caja
        $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->whereNotIn('tipo', ['venta'])
            ->get();
    
        // Ajustar las horas en los resultados
        $ventas->transform(function ($venta) {
            $venta->created_at = Carbon::parse($venta->created_at)->subHours(6); // Suma 6 horas
            $venta->updated_at = Carbon::parse($venta->updated_at)->subHours(6); // Suma 6 horas
            return $venta;
        });
    
        $logscaja->transform(function ($log) {
            $log->created_at = Carbon::parse($log->created_at)->subHours(6); // Suma 6 horas
            $log->updated_at = Carbon::parse($log->updated_at)->subHours(6); // Suma 6 horas
            return $log;
        });
    
        if ($corte) {
            $corte->created_at = Carbon::parse($corte->created_at)->subHours(6); // Suma 6 horas
            $corte->updated_at = Carbon::parse($corte->updated_at)->subHours(6); // Suma 6 horas
        }
    
        // Retornar los datos ajustados
        return Inertia::render('Corte/index', [
            'ventas' => $ventas,
            'corte' => $corte,
            'logscaja' => $logscaja 
        ]);
    }
    

    public function caja(){ 
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $ordenesParaLlevarPendientesPago = Pedidos::with(['productos.producto'])
        ->with('mesa')
        ->where('sucursal_id', $sucursalId)
        ->whereIn('estado', ['espera_empacar', 'pendiente', 'para_llevar', 'espera_entrega'])
        ->whereIn('tipo_pedido', ['mixto', 'para_llevar'])
        ->where('pagado', 0)
        ->get();
        
        $ordenesLocalesPendientesPeago = Pedidos::with(['productos.producto'])
        ->with('mesa')
        ->where('sucursal_id', $sucursalId)
        ->whereIn('estado', ['espera_pago'])
        ->whereIn('tipo_pedido', ['normal','mixto'])
        ->where('pagado', 0)
        ->get();

        $ordenesPendientesPago = $ordenesParaLlevarPendientesPago->merge($ordenesLocalesPendientesPeago);

        $ordenesRecientes = Pedidos::with(['productos.producto', 'mesa'])
            ->where('sucursal_id', $sucursalId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Caja/index', [
            'ordenes' => $ordenesPendientesPago,
            'ordenesRecientes' => $ordenesRecientes

        ]);
    }

    public function entregar()
    {
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $ordenesPendientesEntrega = Pedidos::with(['productos.producto'])
            ->with('mesa')
            ->where('sucursal_id', $sucursalId)
            ->where('estado', 'espera_entrega')
            ->where('para_mesa', 1)
            ->get();

       

        return Inertia::render('Entregar/index', [
            'ordenes' => $ordenesPendientesEntrega
        ]);
    }

    public function guardarOperacion(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
    
        // Validación
        $request->validate([
            'tipo' => 'required|string',
            'monto' => 'nullable|numeric',
            'motivo' => 'nullable|string',
            'cantidad' => 'nullable|numeric',
            'descripcion' => 'nullable|string',
        ]);
    
        switch ($request->tipo) {
            case 'initialCash':
                return $this->guardarInicial($request->monto);
    
            case 'finalCash':
                return $this->guardarFinal($request->monto);
    
            case 'withdraw':
            case 'deposit':
            case 'addGastos':
                return $this->guardar($request);
    
            default:
                // Guardar operación genérica en inventario
                $operacion = new Inventario();
                $operacion->sucursal_id = $sucursalId;
                $operacion->tipo = $request->tipo;
                $operacion->cantidad = $request->cantidad ?? 0;
                $operacion->descripcion = $request->descripcion ?? '';
                $operacion->save();
    
                return back()->with('success', 'Operación guardada correctamente.');
        }
    }
    
    public function guardarInicial($dinero_inicio)
    {
        $usuario = auth()->user();
        $fecha = Carbon::now()->format('Y-m-d');
    
        if (CorteCaja::where('sucursal_id', $usuario->sucursal_id)->where('fecha', $fecha)->exists()) {
            return back()->with('error', 'Ya existe un corte de caja para esta fecha.');
        }
    
        CorteCaja::create([
            'sucursal_id' => $usuario->sucursal_id,
            'fecha' => $fecha,
            'dinero_en_efectivo' => $dinero_inicio,
            'saldo_inicial' => $dinero_inicio || 0,
            'dinero_total' => $dinero_inicio,
            'usuario_id' => $usuario->id,
        ]);
        $this->crearLogCaja($usuario->sucursal_id, $usuario->id, $dinero_inicio, 'corte-entrada');
        return redirect()->route('corte-caja')->with('success', 'Cantidad inicial guardada correctamente.');
    }
    
    public function guardarFinal($dinero_final)
    {
        $usuario = auth()->user();
        $fecha = Carbon::now()->format('Y-m-d');
        $corteCaja = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
                              ->where('fecha', $fecha)
                              ->first();
    
        if (!$corteCaja) {
            return back()->with('error', 'No existe un corte de caja para esta fecha. Debe guardar la cantidad inicial primero.');
        }
    
        if (!is_null($corteCaja->dinero_final)) {
            return back()->with('error', 'Ya existe un corte final registrado para esta fecha.');
        }
    
        $corteCaja->saldo_final = $dinero_final;
        $corteCaja->usuario_id = $usuario->id;
        $corteCaja->save();
        $this->crearLogCaja($usuario->sucursal_id, $usuario->id, $dinero_final, 'corte-salida');
    
        return back()->with('success', 'Cantidad final guardada correctamente.');
    }
    
    public function guardar(Request $request)
    {
        $usuario = auth()->user();
        $fecha = Carbon::now()->format('Y-m-d');
        $corteCaja = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
                              ->where('fecha', $fecha)
                              ->first();
    
        if (!$corteCaja) {
            return back()->with('error', 'Debe iniciar un corte de caja antes de realizar esta operación.');
        }
    
        $sucursalId = $usuario->sucursal_id;
        // Manejo de tipos
        switch ($request->tipo) {
            case 'withdraw':
                $corteCaja->total_salidas += $request->monto;
                $corteCaja->dinero_total -= $request->monto;
                $corteCaja->dinero_en_efectivo -= $request->monto;
                $this->crearLogCaja($sucursalId, $usuario->id, $request->monto, 'salida', $request->motivo);
                break;
    
            case 'deposit':
                $corteCaja->total_entradas += $request->monto;
                $corteCaja->dinero_total += $request->monto;
                $corteCaja->dinero_en_efectivo += $request->monto;
                $this->crearLogCaja($sucursalId, $usuario->id, $request->monto, 'entrada', $request->motivo);
                break;
    
            case 'addGastos':
                $corteCaja->total_salidas += $request->monto;
                $corteCaja->dinero_en_efectivo -= $request->monto;
                $corteCaja->dinero_total -= $request->monto;
                $corteCaja->gastos += $request->monto;
                $this->crearLogCaja($sucursalId, $usuario->id, $request->monto, 'salida', $request->motivo);
                break;
    
            default:
                return back()->with('error', 'Tipo de operación no válida.');
        }
    
        $corteCaja->save();
    
        return back()->with('success', 'Operación realizada correctamente.');
    }
    

    public function crearLogCaja($sucursalId, $usuarioId, $cantidad, $tipo, $descripcion = null, $metodoPago = 'efectivo')
    {
        // Validar los datos antes de procesar
        $validatedData = [
            'sucursal_id' => $sucursalId,
            'usuario_id' => $usuarioId,
            'cantidad' => $cantidad,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
            'metodo_pago' => $metodoPago,
        ];

        // Obtener el dinero en caja antes del movimiento
        $dineroAntes = CorteCaja::where('sucursal_id', $sucursalId)
            ->latest('created_at')
            ->value('dinero_total');

        if (is_null($dineroAntes)) {
            return back()->with('error', 'No se encontró un corte de caja para la sucursal.');
        }

        // Calcular el dinero después del movimiento
        $dineroDespues = $tipo === 'entrada' ? $dineroAntes + $cantidad : $dineroAntes - $cantidad;

        if ($dineroDespues < 0) {
            return back()->with('error', 'El monto en caja no puede ser negativo.');
        }

        // Crear el log de caja
        $logCaja = LogsCaja::create([
            'sucursal_id' => $validatedData['sucursal_id'],
            'usuario_id' => $validatedData['usuario_id'],
            'cantidad' => $validatedData['cantidad'],
            'tipo' => $validatedData['tipo'],
            'descripcion' => $validatedData['descripcion'],
            'metodo_pago' => $validatedData['metodo_pago'],
            'dinero_antes' => $dineroAntes,
            'dinero_despues' => $dineroDespues,
        ]);

        return $logCaja;
    }


    public function obtenerDatos()
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        
        $ventas = Pedidos::where('sucursal_id', $sucursalId)
            ->where('estado', 'finalizado')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'desc') 
            ->limit(20)
            ->get();
        
        $logscaja =  LogsCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->whereNotIn('tipo', ['venta'])
            ->get();

        
        return response()->json([
            'ventas' => $ventas,
            'logscaja' => $logscaja
        ]);
    }

}
