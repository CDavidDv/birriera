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
use App\Exports\VentasExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelType;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class CorteCajaController extends Controller
{
    public function filtro(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        $filter = $request->input('filter');
        $value = $request->input('value');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');
        $metodoPago = $request->input('metodo_pago');
        $tipoOperacion = $request->input('tipo_operacion');
        $montoMinimo = $request->input('monto_minimo');
        $montoMaximo = $request->input('monto_maximo');
        $ordenarPor = $request->input('ordenar_por', 'fecha_desc');

        // Construir la consulta base para ventas
        $ventasQuery = Pedidos::where('sucursal_id', $sucursalId)
            ->where('estado', 'finalizado');

        // Construir la consulta base para logs de caja
        $logsQuery = LogsCaja::where('sucursal_id', $sucursalId)
            ->whereNotIn('tipo', ['venta']);

        // Aplicar filtros de fecha según el tipo de filtro
        if ($filter === 'custom' && $fechaInicio && $fechaFin) {
            $ventasQuery->whereBetween('created_at', [$fechaInicio, $fechaFin]);
            $logsQuery->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        } elseif ($filter === 'day') {
            $ventasQuery->whereDate('created_at', $value);
            $logsQuery->whereDate('created_at', $value);
        } elseif ($filter === 'week') {
            $ventasQuery->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()]);
            $logsQuery->whereBetween('created_at', [Carbon::parse($value)->startOfWeek(), Carbon::parse($value)->endOfWeek()]);
        } elseif ($filter === 'month') {
            $ventasQuery->whereMonth('created_at', Carbon::parse($value)->month);
            $logsQuery->whereMonth('created_at', Carbon::parse($value)->month);
        }

        // Aplicar filtros adicionales para ventas
        if ($metodoPago) {
            $ventasQuery->where('metodo_pago', $metodoPago);
        }

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
    
        // Obtener el corte
        $corte = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('status', true)
            ->first();
    
        if ($corte) {
            // Obtener las ventas
            $ventas = Pedidos::where('sucursal_id', $sucursalId)
                ->where('estado', 'finalizado')
                ->where('updated_at', '>=', $corte->created_at)
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener los logs de caja
            $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
                ->where('updated_at', '>=', $corte->created_at)
                ->whereNotIn('tipo', ['venta'])
                ->orderBy('created_at', 'desc')
                ->get();
        }else{
            $ventas = Pedidos::where('sucursal_id', $sucursalId)
                ->where('estado', 'finalizado')
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at', 'desc')
                ->get();

            // Obtener los logs de caja
            $logscaja = LogsCaja::where('sucursal_id', $sucursalId)
                ->whereDate('created_at', Carbon::today())
                ->whereNotIn('tipo', ['venta'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
    
        // Ajustar las horas en los resultados
        $ventas->transform(function ($venta) {
            $venta->created_at = Carbon::parse($venta->created_at)->subHours(6); // Suma 6 horas
            $venta->updated_at = Carbon::parse($venta->updated_at)->subHours(6); // Suma 6 horas
            return $venta;
        });
        //dd($ventas);
    
        $logscaja->transform(function ($log) {
            $log->created_at = Carbon::parse($log->created_at)->subHours(6); // Suma 6 horas
            $log->updated_at = Carbon::parse($log->updated_at)->subHours(6); // Suma 6 horas
            return $log;
        });
    
        if ($corte) {
            $corte->created_at = Carbon::parse($corte->created_at)->subHours(6); // Suma 6 horas
            $corte->updated_at = Carbon::parse($corte->updated_at)->subHours(6); // Suma 6 horas
        }

        $cortesDelDia = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->get();
    
        // Retornar los datos ajustados
        return Inertia::render('Corte/index', [
            'ventas' => $ventas,
            'corte' => $corte,
            'logscaja' => $logscaja,
            'cortesDelDia' => $cortesDelDia
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
            ->where('estado', 'finalizado')
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
                return $this->guardar($request);

            case 'deposit':
                return $this->guardar($request);

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
    
        $corte = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
            ->where('fecha', $fecha)
            ->where('status', true)
            ->first();

            
        if ($corte && $corte->saldo_inicial) {
            return back()->with('error', 'Ya existe un corte de caja para esta fecha.');
        }
        
        
        if(!$corte){
            CorteCaja::create([
                'sucursal_id' => $usuario->sucursal_id,
                'fecha' => $fecha,
                'dinero_en_efectivo' => $dinero_inicio,
                'dinero_total' => $dinero_inicio,
                'total_entradas' => $dinero_inicio,
                'saldo_inicial' => $dinero_inicio,
                'usuario_id' => $usuario->id,
            ]);
        }else{
            $corte->saldo_inicial = $dinero_inicio;
            $corte->dinero_en_efectivo += $dinero_inicio;
            $corte->dinero_total += $dinero_inicio;
            $corte->save();
        }
        $this->crearLogCaja($usuario->sucursal_id, $usuario->id, $dinero_inicio, 'corte-entrada');
        return redirect()->route('corte-caja')->with('success', 'Cantidad inicial guardada correctamente.');
    }
    
    public function guardarFinal($dinero_final)
    {
        $usuario = auth()->user();
        $fecha = Carbon::now()->format('Y-m-d');
        $corteCaja = CorteCaja::where('sucursal_id', $usuario->sucursal_id)
                              ->where('fecha', $fecha)
                              ->where('status', true)
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
            ->where('status', true)
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
        return response()->json([
            'ventas' => $ventas,
            'corte' => $corte,
            'logscaja' => $logscaja 
        ]);
    }

    //cerrarCorte
    public function cerrarCorte(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Obtener las ventas del día
        $ventas = Pedidos::where('sucursal_id', $sucursalId)
            ->where('estado', 'finalizado')
            ->whereDate('created_at', Carbon::today())
            ->get();

        // Calcular totales
        $ventasEfectivo = $ventas->where('metodo_pago', 'cash')->sum('total');
        $ventasTarjeta = $ventas->where('metodo_pago', 'card')->sum('total');
        $ventasTransferencia = $ventas->where('metodo_pago', 'transfer')->sum('total');
        $ventasTotal = $ventas->sum('total');

        // Obtener gastos del día
        $gastos = LogsCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('tipo', 'gasto')
            ->sum('cantidad');

        // Obtener otros ingresos
        $otrosIngresos = LogsCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', Carbon::today())
            ->where('tipo', 'ingreso')
            ->sum('cantidad');

        // Obtener el corte anterior para el saldo inicial
        $corteAnterior = CorteCaja::where('sucursal_id', $sucursalId)
            ->whereDate('created_at', '<', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();

        $saldoInicial = $corteAnterior ? $corteAnterior->saldo_siguiente_corte : 0;

        // Calcular efectivo en caja
        $efectivoEnCaja = $saldoInicial + $ventasEfectivo + $otrosIngresos - $gastos;

        // Calcular venta real
        $ventaReal = $ventasTotal - $gastos;

        // Calcular saldo para el siguiente corte
        $saldoSiguienteCorte = $saldoInicial + $ventasTotal + $otrosIngresos - $gastos - $request->efectivo_entregado;

        // Crear nuevo registro de corte
        $corte = new CorteCaja();
        $corte->sucursal_id = $sucursalId;
        $corte->usuario_id = $user->id;
        $corte->saldo_inicial = $saldoInicial;
        $corte->fecha = Carbon::today();
        $corte->gastos = $gastos;
        $corte->dinero_total = $efectivoEnCaja;
        $corte->saldo_actual = $efectivoEnCaja;
        $corte->total_entradas = $ventasTotal + $otrosIngresos;
        $corte->total_salidas = $gastos;
        $corte->dinero_inicio = $saldoInicial;
        $corte->dinero_final = $efectivoEnCaja;
        $corte->ventas_total = $ventasTotal;
        $corte->dinero_en_efectivo = $ventasEfectivo;
        $corte->dinero_tarjeta = $ventasTarjeta + $ventasTransferencia;
        $corte->otros_ingresos = $otrosIngresos;
        $corte->venta_real = $ventaReal;
        $corte->efectivo_entregado = $request->efectivo_entregado;
        $corte->saldo_siguiente_corte = $saldoSiguienteCorte;
            $corte->save();

        // Registrar el efectivo entregado como un retiro
        $this->crearLogCaja(
            $sucursalId,
            $user->id,
            $request->efectivo_entregado,
            'retiro',
            'Efectivo entregado en corte de caja',
            'efectivo'
        );

        return response()->json([
            'success' => true,
            'message' => 'Corte de caja realizado con éxito',
            'data' => $corte
        ]);
    }

}
