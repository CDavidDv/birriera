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
use Illuminate\Support\Facades\Storage;
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
                'observaciones' => $request->observaciones,
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
                    'tipo_servicio' => 'para_llevar',
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


            $pedido = null;
            if($request->id_pedido_llevar){
                $pedido = Pedidos::where('id', $request->id_pedido_llevar)
                ->whereNotIn('estado', ['cancelado', 'finalizado'])
                ->first();
            }else{
                $pedido = Pedidos::where('mesa_id', $request->mesa)
                    ->whereNotIn('estado', ['cancelado', 'finalizado'])
                    ->first();
                
                if($request->para_llevar && $request->mesa){

                    $mesa = Mesa::findOrFail($request->mesa);
                    if($mesa->estado !== 'pendiente'){
                        $mesa->estado = 'para_llevar';
                        $mesa->save();
                    }
                    

                    $pedido = Pedidos::where('mesa_id', $request->mesa)
                    ->whereNotIn('estado', ['finalizado', 'cancelado'])
                    ->firstOrFail();

                    if($mesa->estado != 'pendiente' || $mesa->estado != 'espera_entrega' || $mesa->estado != 'espera'){
                        $pedido->estado = 'espera_empacar';
                    }

                    $pedido->tipo_pedido = 'mixto';   
                    if($request->observaciones){
                        $pedido->observaciones = $request->observaciones;
                    }

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
                        }
                        // }else if($pedidoProducto->estado == 'espera_entrega'){
                        //     $pedidoProducto->estado = 'espera_empacar';
                        //     $pedidoProducto->save();
                        // }
                    }

                    //PONER PARA LLEVAR 
                    broadcast(new ParaLlevarPedidoEvent($pedido));    
                }
            }
            if (!$pedido) {
                return redirect()->back()->with('error', 'No hay un pedido activo para esta mesa.');
            }
        
            $pedido->prioridad = 'urgente';            
            $pedido->nombre_cliente = $request->nombre_cliente;
            $pedido->observaciones = $request->observaciones;
            $pedido->save();
            $total = $pedido->total;
        
            
            // Asegurar agregar correctamente productos con persona_id
            if($request->productos){
                $this->agregarProductosAlPedido($pedido, $request->productos, $sucursalId, $total, $request->para_llevar);
            }
        
            // Actualizar el pedido
            $pedidoConProductos = Pedidos::with(['productos.producto'])->findOrFail($pedido->id);
        
            if (!$request->para_llevar && $request->mesa) {
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
                'observaciones' => $request->observaciones,
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
                    'tipo_servicio' => $request->para_llevar ? 'para_llevar' : 'para_comer',
                ]);
                $inventario = Inventario::find($producto['id']);
                $inventario->update(['cantidad' => $inventario->cantidad - $producto['cantidad']]);
                $inventario->save();
                $total += $producto['subtotal'];
            }

            //acutalizar estado de la mesa
            if($request->mesa){
                $mesa = Mesa::find($request->mesa);
                $mesa->update(['estado' => 'pendiente']);
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

    private function agregarProductosAlPedido($pedido, $productos, $sucursalId, &$total, $para_llevar)
    {   
        foreach ($productos as $producto) {
            // Buscar si el producto ya existe en el pedido con el mismo estado y persona
            $pedidoProductoExistente = PedidoProducto::where('pedido_id', $pedido->id)
                ->where('inventario_id', $producto['id'])
                ->where('estado', 'pendiente')
                ->where('persona_id', $producto['persona_id'] ?? null)
                ->where('tipo_servicio', $para_llevar ? 'para_llevar' : 'para_comer')
                ->first();

            if ($pedidoProductoExistente) {
                // Actualizar cantidad y subtotal del producto existente
                $pedidoProductoExistente->cantidad += $producto['cantidad'];
                $pedidoProductoExistente->subtotal += $producto['subtotal'];
                $pedidoProductoExistente->save();
            } else {
                // Crear nuevo registro del producto
                PedidoProducto::create([
                    'pedido_id' => $pedido->id,
                    'inventario_id' => $producto['id'],
                    'persona_id' => $producto['persona_id'] ?? null,
                    'sucursal_id' => $sucursalId,
                    'cantidad' => $producto['cantidad'],
                    'subtotal' => $producto['subtotal'],
                    'personalizacion' => $producto['personalizacion'] ?? '',
                    'tipo_servicio' => $para_llevar ? 'para_llevar' : 'para_comer',
                    'estado' => 'pendiente'
                ]);

                // Actualizar inventario
                $inventario = Inventario::find($producto['id']);
                $inventario->update(['cantidad' => $inventario->cantidad - $producto['cantidad']]);
                $inventario->save();
            }

            // Actualizar total
            $total += $producto['subtotal'];
        }

        // Actualizar total del pedido
        $pedido->update(['total' => $total]);
        $pedido->save();
    }


    public function datos(Request $request){
        // Obtén el usuario autenticado
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get()->map(function ($item) {
            if($item->imagen !== 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg=='){
                $item->imagen = Storage::url($item->imagen);
            }
            return $item;
        });
        
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
                if($pedidoProducto->estado == 'espera_entrega' && $pedidoProducto->tipo_servicio == 'para_comer'){
                    $pedidoProducto->estado = 'entregado';
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
            $pedido = Pedidos::with('mesa')->findOrFail($validated['id']);
            $mesa = $pedido->mesa;
            
            // Actualizar estado del pedido y mesa según tipo
            if ($pedido->tipo_pedido === 'normal' || $pedido->tipo_pedido === 'mixto') {
                $pedido->estado = 'espera_entrega';
                if ($mesa) {
                    $mesa->estado = 'espera_entrega';
                    $mesa->save();
                }
            } else {
                $pedido->estado = 'espera_empacar';
            }
            
            $pedido->save();

            // Actualizar estado de productos
            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {
                $nuevoEstado = $this->determinarNuevoEstadoProducto($pedido->tipo_pedido, $pedidoProducto->estado);
                if ($nuevoEstado) {
                    $pedidoProducto->estado = $nuevoEstado;
                    $pedidoProducto->save();
                }
            }

            // Actualizar seguimiento
            $user = Auth::user();
            $seguimientoOrden = SeguimientoOrden::where('pedido_id', $pedido->id)
                ->where('sucursal_id', $user->sucursal_id)
                ->first();

            // Determinar tipos de servicio y enviar eventos
            $tiposServicio = $this->determinarTiposServicio($pedidoProductos);
            $pedidoConProductos = $pedido->load('productos.producto');

            if ($tiposServicio['para_llevar']) {
                broadcast(new CocinarDeliveryPedidoEvent($pedidoConProductos));
            }
            if ($tiposServicio['para_comer']) {
                broadcast(new CocinarPedidoEvent($pedidoConProductos));
            }
            
            // Actualizar seguimiento de orden
            $seguimientoOrden->update([
                'cocino' => $user->id,
                'hora_cocino' => now(),
                'estado' => 'cocinado'
            ]);

            return back()->with('success', 'Pedido entregado exitosamente.');
            
        } catch (\Exception $e) {
            return redirect()->route('cocina')->with('error', 'Pedido no pudo ser completado');
        }
    }

    private function determinarNuevoEstadoProducto($tipoPedido, $estadoActual)
    {
        if ($tipoPedido === 'normal' && $estadoActual === 'pendiente' && $estadoActual !== 'entregado') {
            return 'espera_entrega';
        }
        
        if ($tipoPedido !== 'normal') {
            if (in_array($estadoActual, ['espera', 'finalizar', 'entregado'])) {
                return 'entregado';
            }
            return 'espera_entrega';
        }

        return null;
    }

    private function determinarTiposServicio($productos)
    {
        $tipos = [
            'para_llevar' => false,
            'para_comer' => false
        ];

        foreach ($productos as $producto) {
            if ($producto->tipo_servicio === 'para_llevar') {
                $tipos['para_llevar'] = true;
            } else if ($producto->tipo_servicio === 'para_comer') {
                $tipos['para_comer'] = true;
            }
        }

        return $tipos;
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

        $ordenesPendientesParaLlevar = Pedidos::with(['productos.producto', 'mesa'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['espera_empacar', 'para_llevar', 'espera_entrega', 'espera'])
            
            ->where('para_llevar', 1)
            ->get();
    

        
        $ordenesPendientesEntrega = Pedidos::with(['productos.producto', 'mesa'])
            ->where('sucursal_id', $sucursalId)
            ->whereIn('estado', ['espera_empacar', 'para_llevar', 'espera_entrega', 'espera'])
            ->where('para_llevar', 0)
            ->whereHas('productos', function($query) {
                $query->where('tipo_servicio', 'para_llevar');
                $query->where('estado', '!=', 'entregado');
            })
            ->get();


        $ordenes = $ordenesPendientesEntrega->merge($ordenesPendientesParaLlevar);

        return Inertia::render('Empacadores/index', [
            'ordenes' => $ordenes,
            'ordenesPendientesEntrega' => $ordenesPendientesEntrega,
            'ordenesPendientesParaLlevar' => $ordenesPendientesParaLlevar
        ]);
    }

    public function completar_pedido(Request $request) {
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
        ]);

        try {
            $user = Auth::user();
            $sucursalId = $user->sucursal_id;
            $pedido = Pedidos::findOrFail($validated['id']);
            
            $seguimientoOrden = SeguimientoOrden::where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();

            
            $seguimientoOrden->empaco = $user->id;
            $seguimientoOrden->hora_empaco = now();
            $seguimientoOrden->estado = 'empacado';
            $seguimientoOrden->save();

            if($pedido->pagado === 1) {
                $pedido->estado = 'finalizado';
                $mesa = Mesa::find($pedido->mesa_id);
                if($mesa) {
                    $mesa->estado = 'libre';
                    $mesa->save();
                }
                broadcast(new TerminarPedidoEvent($pedido));
            }
        

            $pedido->save();

            $pedidoProductos = PedidoProducto::where('pedido_id', $pedido->id)->get();
            foreach ($pedidoProductos as $pedidoProducto) {
                if($pedido->pagado === 1 && $pedidoProducto->estado === 'espera_empacar' && $pedidoProducto->tipo_servicio === 'para_llevar') {
                    $pedidoProducto->estado = 'finalizado';
                } else if(($pedidoProducto->estado === 'pendiente' || $pedidoProducto->estado === 'espera_empacar' || $pedidoProducto->estado === 'espera_entrega') && $pedidoProducto->tipo_servicio === 'para_llevar') {
                    $pedidoProducto->estado = 'entregado';
                }
                $pedidoProducto->save();
            }

            return back()->with('success', 'Pedido completado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Pedido no pudo ser completado');
        }
    }

    public function pagar_pedido(Request $request) {
        $validated = $request->validate([
            'id' => 'required|exists:pedidos,id',
            'paymentMethod' => 'required|string',
            'discount' => 'nullable|numeric',
            'tip' => 'nullable|numeric', 
            'cashReceived' => 'nullable|numeric'
        ]);

        try {
            $user = Auth::user();
            $sucursalId = $user->sucursal_id;
            $pedido = Pedidos::findOrFail($validated['id']);

            $seguimientoOrden = SeguimientoOrden::where('pedido_id', $pedido->id)
                ->where('sucursal_id', $sucursalId)->first();

            $pedido->metodo_pago = $request->paymentMethod;
            $pedido->descuento = $request->discount;
            $pedido->propina = $request->tip;
            $pedido->dinero_recibido = $request->cashReceived;
            $pedido->pagado = 1;

            if($pedido->pagado === 1) {
                $pedido->estado = 'finalizado';
                $seguimientoOrden->cobro = $user->id;
                $seguimientoOrden->hora_cobro = now();
                $seguimientoOrden->estado = 'cobro';
                $seguimientoOrden->save();

                $mesa = Mesa::find($pedido->mesa_id);
                if($mesa) {
                    $mesa->estado = 'libre';
                    $mesa->save();
                }
                broadcast(new TerminarPedidoEvent($pedido));
            }

            $pedido->save();

            // Actualizar corte de caja
            $corteCajaController = new CorteCajaController();
            $fecha = Carbon::now()->format('Y-m-d');
            
            $dinerototal = $pedido->descuento ? 
                $pedido->total - $pedido->descuento : 
                $pedido->total;

            $corteCaja = CorteCaja::where('sucursal_id', $user->sucursal_id)
                ->where('fecha', $fecha)
                ->where('status', true)
                ->first();

            if($corteCaja) {
                if($pedido->metodo_pago === 'cash') {
                    $corteCaja->dinero_total += $dinerototal;
                    $corteCaja->total_entradas += $dinerototal;
                    $corteCaja->ventas_total += $dinerototal;
                    $corteCaja->dinero_en_efectivo += $dinerototal;
                    $corteCaja->save();
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro');
                } else if($pedido->metodo_pago === 'card') {
                    $corteCaja->dinero_total += $dinerototal;
                    $corteCaja->total_entradas += $dinerototal;
                    $corteCaja->ventas_total += $dinerototal;
                    $corteCaja->dinero_tarjeta += $dinerototal;
                    $corteCaja->save();
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro', 'tarjeta');
                } else {
                    $corteCaja->dinero_total += $dinerototal;
                    $corteCaja->total_entradas += $dinerototal;
                    $corteCaja->ventas_total += $dinerototal;
                    $corteCaja->dinero_tarjeta += $dinerototal;
                    $corteCaja->save();
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro', 'transferencia');
                }
            } else {
                if($pedido->metodo_pago === 'cash') {
                    CorteCaja::create([
                        'sucursal_id' => $user->sucursal_id,
                        'usuario_id' => $user->id,
                        'fecha' => $fecha,
                        'dinero_total' => $dinerototal,
                        'total_entradas' => $dinerototal,
                        'ventas_total' => $dinerototal,
                        'dinero_en_efectivo' => $dinerototal
                    ]);
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro');
                } else if($pedido->metodo_pago === 'card') {
                    CorteCaja::create([
                        'sucursal_id' => $user->sucursal_id,
                        'usuario_id' => $user->id,
                        'fecha' => $fecha,
                        'dinero_total' => 0,
                        'total_entradas' => $dinerototal,
                        'ventas_total' => $dinerototal,
                        'dinero_tarjeta' => $dinerototal
                    ]);
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro', 'tarjeta');
                } else {
                    CorteCaja::create([
                        'sucursal_id' => $user->sucursal_id,
                        'usuario_id' => $user->id,
                        'fecha' => $fecha,
                        'dinero_total' => 0,
                        'total_entradas' => $dinerototal,
                        'ventas_total' => $dinerototal,
                        'dinero_tarjeta' => $dinerototal
                    ]);
                    $corteCajaController->crearLogCaja($sucursalId, $user->id, $dinerototal, 'venta', 'Primer registro', 'transferencia');
                }
            }

            return back()->with('success', 'Pago procesado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el pago');
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
        
        //regresar  a la pagina anterior
        return redirect()->back()->with('success', 'Pedido eliminado correctamente.');
        
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
