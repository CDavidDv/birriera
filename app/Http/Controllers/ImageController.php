<?php

namespace App\Http\Controllers;

use App\Models\Imagenes;
use App\Models\PedidoProducto;
use App\Models\Pedidos;
use App\Models\Sucursal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // máx 5MB
            'name' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');

            // CORREGIDO: Guardar en storage/app/public/images
            $path = $file->store('images', 'public');

            // Guardar ruta y nombre en base de datos
            Imagenes::create([
                'nombre' => $request->name,
                'ruta' => $path, // guardar solo 'images/archivo.webp'
            ]);

            return response()->json([
                'name' => $request->name,
                'url' => Storage::url($path), // genera '/storage/images/archivo.webp'
                'path' => $path, // 'images/archivo.webp'
            ]);
        }

        return response()->json(['error' => 'No se envió imagen'], 400);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        // Eliminar imagen del sistema de archivos
        Storage::disk('public')->delete($request->path);

        // Eliminar registro de la base de datos
        Imagenes::where('ruta', $request->path)->delete();

        return response()->json(['message' => 'Imagen eliminada']);
    }

    public function getImages()
    {
        $imagenes = Imagenes::all()->map(function($imagen) {
            $imagen->url = Storage::url($imagen->ruta);
            return $imagen;
        });
        return response()->json([
                'imagenes' => $imagenes
            ]);
    }
}
