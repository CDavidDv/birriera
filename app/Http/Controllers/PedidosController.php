<?php
namespace App\Http\Controllers;

use App\Events\AddPedidoEvent;
use App\Events\CancelarPedidoEvent;
use App\Events\CocinarDeliveryPedidoEvent;
use App\Events\CocinarPedidoEvent;
use App\Events\CrearPedidoEvent;
use App\Events\EmpacarPedidoEvent;
use App\Events\EntregarPedidoEvent;
use App\Events\EnviarCajaPedidoEvent;
use App\Events\PagarPedidoEvent;
use App\Events\ParaLlevarPedidoEvent;
use App\Events\SubstrackPedidoEvent;
use App\Events\TerminarPedidoEvent;
use App\Models\CorteCaja;
use App\Models\Inventario;
use App\Models\Mesa;
use Illuminate\Http\Request;
use App\Models\Pedidos;
use App\Models\PedidoProducto;
use App\Models\SeguimientoOrden;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PedidosController extends Controller
{
    public function crearPedido(Request $request)
    {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // Validar los datos de entrada
        $request->validate([
            'productos' => 'nullable|array',
            'productos.*.id' => 'nullable|exists:inventarios,id',
            'productos.*.cantidad' => 'nullable|integer',
            'productos.*.subtotal' => 'nullable|numeric|min:0',
        ]);

        // Determinar la prioridad del pedido
        $prioridad = $request->reConsumo ? 'urgente' : 'normal';

        
        if($request->delivery){
            $pedido = Pedidos::create([
                'sucursal_id' => $sucursalId,
                'nombre_cliente' => $request->nombre_cliente,
                'mesa_id' => null,
                'total' => 0,
                'estado' => 'pendiente',
                'tipo_pedido' => 'para_llevar',
                'para_mesa' => 0,
                'para_llevar' => 1,
                'prioridad' => $prioridad,
            ]);

            $total = 0;

            foreach ($request->productos as $producto) {
                PedidoProducto::create([
                    'pedido_id' => $pedido->id,
                    'inventario_id' => $producto['id'],
                    'persona_id' => $producto['persona_id'],
                    'sucursal_id' => $sucursalId,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal'],
                    'personalizacion' => $producto['personalizacion'] ?? '',
                ]);
                $inventario = Inventario::find($producto['id']);
                $inventario->update(['cantidad' => $inventario->cantidad - $producto['cantidad']]);
                $inventario->save();
                $total += $producto['subtotal'];
            }

            $pedido->update(['total' => $total]);

            $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
            //CREAR PEDIDO NUEVO LOCAL
            broadcast(new CrearPedidoEvent($pedidoConProductos));


            SeguimientoOrden::create([
                'sucursal_id' => $sucursalId,
                'pedido_id' => $pedido->id,
                'creo' => $user->id,
                'estado' => 'pendiente',
            ]);

        }else if ($request->reConsumo) {
            $pedido = Pedidos::where('mesa_id', $request->mesa)
                ->whereNotIn('estado', ['cancelado', 'finalizado'])
                ->first();
        
            if (!$pedido) {
                return redirect()->back()->with('error', 'No hay un pedido activo para esta mesa.');
            }
        
            $pedido->prioridad = 'urgente';            
            $pedido->nombre_cliente = $request->nombre_cliente;
            $total = $pedido->total;
        
            // Asegurar agregar correctamente productos con persona_id
            if($request->productos){
                $this->agregarProductosAlPedido($pedido, $request->productos, $sucursalId, $total);
            }
        
            // Actualizar el pedido
            $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
        
            if (!$request->para_llevar) {
                $mesa = Mesa::find($request->mesa);
                $mesa->update(['estado' => 'pendiente']);
                broadcast(new AddPedidoEvent($pedidoConProductos, $mesa));
            } else {
                broadcast(new AddPedidoEvent($pedidoConProductos));
            }
        
            SeguimientoOrden::updateOrCreate(
                ['pedido_id' => $pedido->id, 'sucursal_id' => $sucursalId],
                [
                    'reconsumo' => $user->id,    
                    'hora_reconsumo' => now(),
                    'estado' => 'reconsumo',
                    'tipo_pedido' => 'mixto',
                ]
            );
        
        
        } else if(!$request->delivery) {
            // Crear un nuevo pedido si no es para reconsumo
            $pedido = Pedidos::create([
                'sucursal_id' => $sucursalId,
                'nombre_cliente' => $request->nombre_cliente,
                'mesa_id' => $request->mesa ?? null,
                'total' => 0,
                'estado' => 'pendiente',
                'prioridad' => $prioridad,
                'para_llevar' => $request->para_llevar ?? 0,
            ]);
            

            if (!$request->para_llevar) {
                $mesa = Mesa::find($request->mesa);
                $mesa->update(['estado' => 'pendiente']);
            }

            $total = 0;

            foreach ($request->productos as $producto) {
                PedidoProducto::create([
                    'pedido_id' => $pedido->id,
                    'inventario_id' => $producto['id'],
                    'persona_id' => $producto['persona_id'],
                    'sucursal_id' => $sucursalId,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal'],
                    'personalizacion' => $producto['personalizacion'] ?? '',
                ]);
                $inventario = Inventario::find($producto['id']);
                $inventario->update(['cantidad' => $inventario->cantidad - $producto['cantidad']]);
                $inventario->save();
                $total += $producto['subtotal'];
            }

            $pedido->update(['total' => $total]);

            $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);

            //CREAR PEDIDO NUEVO LOCAL
            broadcast(new CrearPedidoEvent($pedidoConProductos));

            SeguimientoOrden::create([
                'sucursal_id' => $sucursalId,
                'pedido_id' => $pedido->id,
                'creo' => $user->id,
                'hora_creo' => now(),
                'estado' => 'pendiente',
            ]);
        }

        
        return redirect()->route('dashboard')->with('success', 'Pedido creado correctamente.');
    }

    private function agregarProductosAlPedido($pedido, $productos, $sucursalId, &$total)
    {
        foreach ($productos as $producto) {
            // Validar si el producto ya existe en el pedido para la misma persona
            $pedidoProducto = PedidoProducto::where('pedido_id', $pedido->id)
                ->where('inventario_id', $producto['id'])
                ->where('estado', 'pendiente')
                ->where('persona_id', $producto['persona_id'] ?? null) // Aseguramos considerar persona
                ->first();
    
            if ($pedidoProducto) {
                // Actualizar cantidad y subtotal si ya existe
                $pedidoProducto->cantidad += $producto['cantidad'];
                $pedidoProducto->subtotal += $producto['subtotal'];
                $pedidoProducto->estado = 'pendiente';
                $pedidoProducto->save();
            } else {
                // Crear un nuevo registro del producto en el pedido
                PedidoProducto::create([
                    'pedido_id' => $pedido->id,
                    'inventario_id' => $producto['id'],
                    'persona_id' => $producto['persona_id'] ?? null, // Por si no se envía
                    'sucursal_id' => $sucursalId,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal'],
                    'personalizacion' => $producto['personalizacion'] ?? '',
                ]);
                $inventario = Inventario::find($producto['id']);
                $inventario->update(['cantidad' => $inventario->cantidad - $producto['cantidad']]);
                $inventario->save();
            }
    
            // Actualizar el total del pedido
            $total += $producto['subtotal'];
        }
    
        // Actualizar el total en el pedido
        $pedido->update(['total' => $total]);
    }
    


    public function datos(Request $request){
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

        return back()->with([
            'inventario' => $inventario,
            'mesas' => $mesas,
            'ordenes' => $ordenes,
            'ordenesParaLlevar' => $ordenesParaLlevar
        ]);
    }

    public function completarEntregar(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
        ]);

        try {
            $pedido = Pedidos::findOrFail($validated['id']);
            $pedido->estado = 'espera'; // O el estado que necesites

            $mesa = Mesa::find($pedido->mesa_id);
            $mesa->update(['estado' => 'ocupada']);
            $pedido->save();

            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {             
                if($pedidoProducto->estado == 'espera_entrega'){
                    $pedidoProducto->estado = 'espera';
                }
                $pedidoProducto->save();
            }

            $user = Auth::user();
            $sucursalId = $user->sucursal_id;

            $seguimientoOrden = SeguimientoOrden::
                  where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();
            
            $seguimientoOrden->entrego = $user->id;
            $seguimientoOrden->hora_entrego = now();
            $seguimientoOrden->estado = 'entrego';
            $seguimientoOrden->save();

            if($pedido->para_llevar){
                $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
                broadcast(new EmpacarPedidoEvent($pedidoConProductos));
            }else{
                $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
                broadcast(new EntregarPedidoEvent($pedidoConProductos));
            }
            

            return back()->with('success', 'Pedido completado exitosamente.');
            
        } catch (\Exception $e) {
            return redirect()->route('cocina')->with('error', 'Pedido no pudo ser completado');
            
        }
    }

    public function productosEliminados(Request $request)
    {
        
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
            'productos' => 'array',
            'productos.*.id' => 'integer|exists:inventarios,id',
            'productosEliminados' => 'nullable|array',
            'productosEliminados.*.id' => 'required|integer|exists:pedido_productos,inventario_id',
            'productosEliminados.*.persona_id' => 'required|integer',
            'productosEliminados.*.price' => 'required|numeric',
            'productosEliminados.*.quantity' => 'required|integer',
            'productosModificados' => 'nullable|array',
            'productosModificados.*.id' => 'required|integer|exists:pedido_productos,inventario_id',
            'productosModificados.*.quantity' => 'required|integer',
            'productosModificados.*.price' => 'required|numeric',
            'total' => 'required|numeric',
        ]);

        try {
            // Manejar productos eliminados
            if (!empty($validated['productosEliminados'])) {
                foreach ($validated['productosEliminados'] as $producto) {
                    PedidoProducto::where('inventario_id', $producto['id'])
                        ->where('estado', 'pendiente')
                        ->where('persona_id', $producto['persona_id'])
                        ->delete();

                    $pedido = Pedidos::findOrFail($validated['id']);
                    $totalActual = $pedido->total;
                    $totalNuevo = $totalActual - ($producto['quantity'] * $producto['price']);
                    $pedido->update(['total' => $totalNuevo]);

                    $inventario = Inventario::find($producto['id']);
                    $inventario->update(['cantidad' => $inventario->cantidad + $producto['quantity']]);
                    $inventario->save();
                }
            }

            // Manejar productos modificados
            if (!empty($validated['productosModificados'])) {
                foreach ($validated['productosModificados'] as $producto) {
                    // Buscar el producto en el pedido actual
                    $pedidoProducto = PedidoProducto::where('inventario_id', $producto['id'])->where('estado', 'pendiente')->first();
            
                    // Verificar si pertenece al pedido indicado
                    if ($pedidoProducto->pedido_id === $validated['id']) {
                        // Actualizar la cantidad y recalcular el subtotal
                        
                        $subtotal = ($pedidoProducto->cantidad + $producto['quantity']) * $producto['price'];
                        $pedidoProducto->update([
                            'cantidad' => $pedidoProducto->cantidad + $producto['quantity'],
                            'subtotal' => $subtotal,
                        ]);
                        $inventario = Inventario::find($producto['id']);
                        $inventario->update(['cantidad' => $inventario->cantidad + $producto['quantity']]);
                        $inventario->save();

                    }
                }
            
                // Actualizar el total del pedido
                $pedido = Pedidos::findOrFail($validated['id']);
                $nuevoTotal = PedidoProducto::where('pedido_id', $pedido->id)
                    ->sum('subtotal');
            
                $pedido->update(['total' => $nuevoTotal]);

                
                $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
                //CREAR PEDIDO NUEVO LOCAL
                broadcast(new SubstrackPedidoEvent($pedidoConProductos));
            }

            

            

            return back()->with('success', 'Pedido modificado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Hubo un problema: ' . $e->getMessage()]);
        }
    }



    public function enviaraEntregar(Request $request)
    {
        
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
        ]);

        try {
            $pedido = Pedidos::findOrFail($validated['id']);
            $mesa = Mesa::find($pedido->mesa_id);
            
            
            if($pedido->tipo_pedido === 'normal' || $pedido->tipo_pedido === 'mixto'){
                $pedido->estado = 'espera_entrega'; 
                $mesa->estado = 'espera_entrega';
                $mesa->save();
            }else{
                $pedido->estado = 'espera_empacar';
            }
            
            
            $pedido->save();

            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {
                if($pedido->tipo_pedido === 'normal'){
                    if($pedidoProducto->estado === 'pendiente'){
                        $pedidoProducto->estado = 'espera_entrega';
                    }
                }else{
                    if($pedidoProducto->estado === 'espera' || $pedidoProducto->estado === 'finalizar'){
                        $pedidoProducto->estado = 'finalizar';
                    }else{
                        $pedidoProducto->estado = 'espera_entrega';
                    }
                }
                
                $pedidoProducto->save();
            }

            $user = Auth::user();
            $sucursalId = $user->sucursal_id;

            $seguimientoOrden = SeguimientoOrden::
                  where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();

            //COCINAR PEDIDO
            if($pedido->para_llevar){
                $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
                broadcast(new CocinarDeliveryPedidoEvent($pedidoConProductos));
            }else{
                $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
                broadcast(new CocinarPedidoEvent($pedidoConProductos));
            }
            
            
            $seguimientoOrden->cocino = $user->id;
            $seguimientoOrden->hora_cocino = now();
            $seguimientoOrden->estado = 'cocinado';
            $seguimientoOrden->save();

            return back()->with('success', 'Pedido entregado exitosamente.');
            
        } catch (\Exception $e) {
            return redirect()->route('cocina')->with('error', 'Pedido no pudo ser completado');
            
        }
    }

    public function enviaraCaja(Request $request)
    {
        // Validar el ID de la mesa recibida
        $validated = $request->validate([
            'id' => 'required|exists:mesas,id',
        ]);
        
        try {
            // Buscar el pedido asociado a la mesa
            $pedido = Pedidos::where('mesa_id', $validated['id'])
                ->whereNotIn('estado', ['finalizado', 'cancelado']) // Evitar pedidos ya cerrados
                ->firstOrFail();

            // Actualizar el estado del pedido
            $pedido->estado = 'espera_pago'; // Cambiar al estado de espera de pago
            $pedido->save();

            // Actualizar el estado de la mesa
            $mesa = Mesa::findOrFail($validated['id']);
            $mesa->estado = 'espera_pago';
            $mesa->save();

            // Actualizar el estado de los productos asociados al pedido
            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {
                if ($pedidoProducto->estado === 'pendiente') {
                    $pedidoProducto->estado = 'espera_pago';
                    $pedidoProducto->save();
                }
            }

            $user = Auth::user();
            $sucursalId = $user->sucursal_id;

            $seguimientoOrden = SeguimientoOrden::
                  where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();

            //ENVIAR A CAJA 
            broadcast(new EnviarCajaPedidoEvent($pedido));

            
            $seguimientoOrden->mandar_caja = $user->id;
            $seguimientoOrden->hora_mandar_caja = now();
            $seguimientoOrden->estado = 'mandar_caja';
            $seguimientoOrden->save();

            // Retornar con un mensaje de éxito
            return back()->with('success', 'Mesa y pedido enviados correctamente a caja.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Si no se encuentra el pedido o la mesa
            return back()->with('error', 'No se encontró un pedido activo para esta mesa.');
        } catch (\Exception $e) {
            // Capturar cualquier otro error
            return back()->with('error', 'Ocurrió un error al enviar la mesa a caja. Por favor, intenta nuevamente.');
        }
    }

    public function empacar(){
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;

        // $ordenesPendientesEntrega = Pedidos::with(['productos' => function ($query) {
        //     $query->where('estado', 'para_llevar'); 
        // }, 'productos.producto', 'mesa'])
        // ->where('sucursal_id', $sucursalId)
        // ->where('estado', 'espera_entrega')
        // ->where('para_mesa', 1)
        // ->get();

        $ordenesPendientesParaLlevar = Pedidos::with(['productos.producto', 'mesa'])
            
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['espera_empacar', 'para_llevar', 'espera_entrega'])
            ->where('tipo_pedido', 'mixto')
            ->where('para_llevar', 1)
            ->get();
    

        $ordenesPendientesEntrega = Pedidos::with(['productos.producto', 'mesa'])
            
            ->with('mesa')
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['espera_empacar', 'para_llevar'])
            ->whereIn('tipo_pedido', ['para_llevar', 'mixto'])
            ->where('para_llevar', 1)
            ->get();
        $ordenes = $ordenesPendientesEntrega->merge($ordenesPendientesParaLlevar);

        return Inertia::render('Empacadores/index', [
            'ordenes' => $ordenes
        ]);
    }

    public function terminar_pedido(Request $request){
        
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
        ]);
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
        $pedido = Pedidos::findOrFail($validated['id']);
        
        $seguimientoOrden = SeguimientoOrden::
                where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();

        try {

            if($pedido->pagado === 1 && $pedido->estado == 'espera_empacar'){
                $seguimientoOrden->empaco = $user->id;
                $seguimientoOrden->hora_empaco = now();
                $seguimientoOrden->estado = 'empacado';
                $seguimientoOrden->save();
                $pedido->estado = 'finalizado'; 
            }else if($pedido->estado === 'pendiente' || $pedido->estado === 'espera_empacar'){
                $seguimientoOrden->empaco = $user->id;
                $seguimientoOrden->hora_empaco = now();
                $seguimientoOrden->estado = 'empacado';
                $seguimientoOrden->save();
                $pedido->pagado = 1; 
                broadcast(new PagarPedidoEvent($pedido));
            }else if($pedido->estado === 'espera_pago'){
                $pedido->estado = 'finalizado';
                $seguimientoOrden->cobro = $user->id;
                $seguimientoOrden->hora_cobro = now();
                $seguimientoOrden->estado = 'cobro';
                $seguimientoOrden->save();
            }
                

            $pedido->metodo_pago = $request->paymentMethod; 
            $pedido->descuento = $request->discount; 
            $pedido->propina = $request->tip; 
            $pedido->dinero_recibido = $request->cashReceived; 
            
            

            if(($pedido->estado === 'para_llevar' || $pedido->estado === 'espera_entrega') && $pedido->tipo_pedido === 'mixto' && $pedido->pagado){
                $pedido->estado = 'finalizado';
                $mesa = Mesa::find($pedido->mesa_id);
                $mesa->estado = 'libre';
                $mesa->save();

            }

            if(($pedido->estado === 'para_llevar' || $pedido->estado === 'espera_entrega') && $pedido->tipo_pedido === 'mixto'){
                $pedido->estado = 'espera_empacar';
                $pedido->pagado = 1;
                $mesa = Mesa::find($pedido->mesa_id);
                $mesa->estado = 'espera_empacar';
                $mesa->save();
                broadcast(new PagarPedidoEvent($pedido));
            }
            
            if($pedido->estado === 'finalizado'){
                $mesa = Mesa::find($pedido->mesa_id);
                if($mesa){
                    $mesa->estado = 'libre';
                    $mesa->save();
                }
                broadcast(new TerminarPedidoEvent($pedido));
            }
            $pedido->save();
                
            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {

                if($pedido->pagado === 1 && $pedidoProducto->estado === 'espera_empacar'){
                    $pedidoProducto->estado = 'finalizado';
                    $pedido->estado = 'finalizado';
                    $pedidoProducto->save();
                }else if($pedidoProducto->estado === 'pendiente' || $pedidoProducto->estado === 'espera_empacar'){
                    $pedidoProducto->estado = 'pendiente';
                    $pedidoProducto->save();
                }else if($pedidoProducto->estado === 'espera_pago'){
                    $pedidoProducto->estado = 'finalizado';
                    $pedidoProducto->save();
                }
                
            }

            $user = Auth::user();
            $sucursalId = $user->sucursal_id;          
            $dinerototal = $pedido->total - $pedido->descuento;
            
            
            $corteCajaController = new CorteCajaController();
            
            $fecha = Carbon::now()->format('Y-m-d');

            $corteCaja = $corteCaja = CorteCaja::where('sucursal_id', $user->sucursal_id)
                ->where('fecha', $fecha)
                ->first();
                
            if($corteCaja){
                $corteCaja->dinero_total += $dinerototal;
                $corteCaja->save();
            }

            $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta');
            
             
            return back()->with('success', 'Pedido entregado exitosamente.');
            
        } catch (\Exception $e) {
            return redirect()->route('cocina')->with('error', 'Pedido no pudo ser completado');
        }
    }

    public function paraLlevar(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:mesas,id',
        ]);

        try {
            $mesa = Mesa::findOrFail($validated['id']);
            if($mesa->estado !== 'pendiente'){
                $mesa->estado = 'para_llevar';
                $mesa->save();
            }
            

            $pedido = Pedidos::where('mesa_id', $validated['id'])
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->firstOrFail();

            $pedido->para_mesa = 0;
            $pedido->para_llevar = 1;
            if($mesa->estado != 'pendiente' || $mesa->estado != 'espera_entrega' || $mesa->estado != 'espera'){
                $pedido->estado = 'espera_empacar';
            }

            $pedido->tipo_pedido = 'mixto';        

            $pedido->save();

            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {
                if($pedidoProducto->estado == 'pendiente'){
                    $pedidoProducto->estado = $mesa->estado;
                    $pedidoProducto->save();
                }else if($pedidoProducto->estado == 'espera'){
                    $pedidoProducto->estado = 'finalizar';
                    $pedidoProducto->save();
                }else if($pedidoProducto->estado == 'finalizado'){
                    $pedidoProducto->estado = 'finalizado';
                    $pedidoProducto->save();
                }else if($pedidoProducto->estado == 'espera_entrega'){
                    $pedidoProducto->estado = 'espera_empacar';
                    $pedidoProducto->save();
                }
            }

            //PONER PARA LLEVAR 
            broadcast(new ParaLlevarPedidoEvent($pedido));
            

            return back()->with('success', 'Pedido completado exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
            
        }
    }

    //destroy 
    public function cancelar(Request $request)
    {
        if ($request->mesa) {
            $this->cancelarPorMesa($request->id);
        } else {
            $this->cancelarPorOrden($request->id);
        }
        return redirect()->route('dashboard')->with('success', 'Pedido eliminado correctamente.');
    }

    private function cancelarPorMesa($mesaId)
    {
        $auth = Auth::user();
        $sucursalId = $auth->sucursal_id;
        $mesa = Mesa::where('id', $mesaId)->where('sucursal_id', $sucursalId)->whereNotIn('estado', ['finalizado', 'cancelado'])->first();
        if ($mesa) {
            $mesa->estado = 'libre';
            $mesa->save();
        }
         
        $pedido = Pedidos::where('mesa_id', $mesaId)->where('sucursal_id', $sucursalId)->whereNotIn('estado', ['finalizado', 'cancelado'])->firstOrFail();
        $this->cancelarPedido($pedido);
    }

    private function cancelarPorOrden($pedidoId)
    {
        $pedido = Pedidos::findOrFail($pedidoId);
        $this->cancelarPedido($pedido);
    }

    private function cancelarPedido($pedido)
    {
        
        $pedido->estado = 'cancelado';
        $pedidoProducto = PedidoProducto::where('pedido_id', $pedido->id)->get();
        foreach ($pedidoProducto as $producto) {
            $producto->estado = 'cancelado';
            $producto->save();
        }

        $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
        foreach ($pedidoProductos as $pedidoProducto) {
            $pedidoProducto->estado = 'cancelado';
            $pedidoProducto->save();
        }

        $mesa = Mesa::find($pedido->mesa_id);
        if ($mesa) {
            $mesa->estado = 'libre';
            $mesa->save();
        }

        //CANCELAR PEDIDO
        if (!$mesa) {
            // Si mesa es nula, realizar lógica alternativa
            broadcast(new CancelarPedidoEvent($pedido, null));
        } else {
            broadcast(new CancelarPedidoEvent($pedido, $mesa));
        }
        

        $auth = Auth::user();
        $sucursalId = $auth->sucursal_id;
        $seguimientoOrden = SeguimientoOrden::
            where('pedido_id', $pedido->id)
            ->where('sucursal_id', $sucursalId)->first();
            
            
        $seguimientoOrden->cancelo = $auth->id || 0;
        
        $seguimientoOrden->hora_cancelo = now();
        $seguimientoOrden->estado = 'cancelo';
        $seguimientoOrden->save();
        $pedido->save();
    }


    function getPedido($id)
    {
        $pedido = Pedidos::with(['productos.producto', 'mesa'])->where('id', $id)->first();

        if (!$pedido) {
            // Manejar el caso donde no se encuentra el pedido
            return response()->json(['error' => 'Pedido no encontrado'], 404);
        }

        return response()->json($pedido);
    }
}
