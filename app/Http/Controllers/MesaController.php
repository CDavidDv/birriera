<?php

namespace App\Http\Controllers;

use App\Events\UpdateMesasPedidoEvent;
use App\Models\Inventario;
use App\Models\Mesa;
use App\Models\Pedidos;
use App\Models\PersonaEnMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MesaController extends Controller
{
    public function index()
    {
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
        
        $mesas = Mesa::where('sucursal_id', $sucursalId)->orderBy('posicion', 'asc')->get();

        $ordenes = Pedidos::with(['productos.producto'])
            ->with('mesa')
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->where('sucursal_id', $sucursalId)
            ->get();

        return Inertia::render('Dashboard/index', [
            'inventario' => $inventario,
            'mesas' => $mesas,
            'ordenes' => $ordenes || []
        ]);
    }

    public function store(Request $request) {
        $user = Auth::user();
        $sucursalId = $user->sucursal_id;
    
        
        $data = $request->validate([
            'nuevasMesas' => 'array',
            'nuevasMesas.*.nombre' => 'required|string',
            'nuevasMesas.*.tipo' => 'required|string',
            'nuevasMesas.*.posicion' => 'required|integer|min:1',
    
            'mesasActualizadas' => 'array',
            'mesasActualizadas.*.id' => 'required|integer',
            'mesasActualizadas.*.nombre' => 'required|string',
            'mesasActualizadas.*.tipo' => 'required|string',
            'mesasActualizadas.*.posicion' => 'required|integer|min:1',
    
            'mesasEliminadas' => 'array',
            'mesasEliminadas.*.id' => 'required|integer',
        ]);
    
        // Crear nuevas mesas
        if (!empty($data['nuevasMesas'])) {
            foreach ($data['nuevasMesas'] as &$mesa) {
                $mesa['sucursal_id'] = $sucursalId;
            }
            Mesa::insert($data['nuevasMesas']);
        }
    
        // Actualizar mesas existentes
        foreach ($data['mesasActualizadas'] as $mesa) {
            Mesa::where('id', $mesa['id'])
                ->where('sucursal_id', $sucursalId) // Validar sucursal
                ->update([
                    'nombre' => $mesa['nombre'],
                    'tipo' => $mesa['tipo'],
                    'posicion' => $mesa['posicion'],
                ]);
        }
    
        // Eliminar mesas
        if (!empty($data['mesasEliminadas'])) {
            Mesa::where('sucursal_id', $sucursalId) // Validar sucursal
                ->whereIn('id', array_column($data['mesasEliminadas'], 'id'))
                ->delete();
        }
    
        // Verificar y ajustar las posiciones para evitar conflictos
        $this->reordenarPosiciones($sucursalId);
    
        // Regresar a la ruta dashboard
        return redirect()->route('dashboard')->with('success', 'Configuración de mesas guardada correctamente.');
    }
    
    /**
     * Reordena las posiciones de las mesas para asegurar que sean únicas y consecutivas.
     */
    private function reordenarPosiciones($sucursalId) {
        $mesas = Mesa::where('sucursal_id', $sucursalId)
            ->orderBy('posicion')
            ->get();
    
        $posicion = 1;
        foreach ($mesas as $mesa) {
            $mesa->update(['posicion' => $posicion]);
            $posicion++;
        }
    }
    
    
    

    public function update(Request $request, $id)
    {
        $table = Mesa::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'required|string|min:3|max:255|unique:mesas,nombre,' . $table->id,
            'tipo' => 'required|in:mesa,barra',
            'estado' => 'in:libre,ocupada',
        ]);

        $table->update($validated);
        return redirect()->route('dashboard')->with('success', 'Mesa actualizada exitosamente');
    }

    public function destroy($id)
    {
        $table = Mesa::findOrFail($id);

        if ($table->estado !== 'libre') {
            return back()->with('error', 'No se puede eliminar una mesa ocupada.');
        }

        try{
            $table->delete();
            return redirect()->route('dashboard')->with('success', 'Mesa eliminada exitosamente');
            
        }catch(\Exception $e){
            return back()->with('error', 'No se puede eliminar una mesa con pedidos pendientes.');
        }

    }
    public function asignarPersonas(Request $request, $mesaId)
    {
        $mesa = Mesa::findOrFail($mesaId);

        // Crear registros para cada persona
        foreach (range(1, $request->numero_personas) as $indice) {
            PersonaEnMesa::create([
                'mesa_id' => $mesa->id,
                'nombre' => $request->nombres[$indice - 1] ?? "Persona $indice",
                'orden' => $indice,
            ]);
        }

        $mesa->estado = 'ocupada';
        $mesa->save();

        return response()->json(['message' => 'Personas asignadas correctamente']);
    }

    public function updateMesa(Request $request)
    {
        $validatedData = $request->validate([
            'mesa' => 'required|exists:mesas,id',
            'nuevaMesa' => 'required|exists:mesas,id',
        ]);

        $mesa = Mesa::find($validatedData['mesa']);
        $nuevaMesa = Mesa::find($validatedData['nuevaMesa']);

        broadcast(new UpdateMesasPedidoEvent($mesa, $nuevaMesa));
        // Verificar si ambas mesas existen
        if (!$mesa || !$nuevaMesa) {
            return redirect()->route('dashboard')->with('error', 'Alguna de las mesas no existe');
        }

        // Verificar si la nueva mesa está libre
        if ($nuevaMesa->estado !== 'libre') {
            return redirect()->route('dashboard')->with('error', 'La mesa seleccionada está ocupada');
        }

        // Obtener pedidos de la mesa actual en estado pendiente
        $pedidosPendientes = Pedidos::where('mesa_id', $mesa->id)
            ->whereNotIn('estado', ['finalizado', 'cancelado'])
            ->get();

        if ($pedidosPendientes->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No hay pedidos pendientes para transferir');
        }

        // Transferir los pedidos a la nueva mesa
        foreach ($pedidosPendientes as $pedido) {
            $pedido->mesa_id = $nuevaMesa->id;
            $pedido->save();
        }

        //MOVER MESA
        // Actualizar estados de las mesas
        $nuevaMesa->estado = $mesa->estado; // Transferir el estado, o establecer "ocupada"
        $mesa->estado = 'libre';
        
        // Guardar cambios en las mesas
        $mesa->save();
        $nuevaMesa->save();
        
        
        return redirect()->route('dashboard')->with('success', 'Mesa cambiada exitosamente');
    }

}
