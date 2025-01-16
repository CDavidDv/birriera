<?php

namespace App\Http\Controllers;

use App\Events\UpdateMesasPedidoEvent;
use App\Models\Inventario;
use App\Models\Mesa;
use App\Models\Pedidos;
use App\Models\PersonaEnMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MesaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Asume que el usuario tiene una sucursal_id
        $sucursalId = $user->sucursal_id;

        // Filtra el inventario por sucursal_id
        $inventario = Inventario::where('sucursal_id', $sucursalId)->get();
        
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

        $table->delete();
        return redirect()->route('dashboard')->with('success', 'Mesa eliminada exitosamente');
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
