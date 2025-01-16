<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inventario;

class InventariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $img = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAAXNSR0IArs4c6QAAAohJREFUeF7t1dunAlEYBfBvIpGSrtJVL5H+//+i10Qv6Sq6kHroIh3fxz7mjHSqZZOsIWZq1jT7N2vvCXq93k24vS0QEPBtOwsSEPMjIOhHQAKiAmCeayABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMO69gZVKRcrlsgRBIMfjUfr9vt1ys9mUQqFg+/v9XobD4UtDuZdPp9PSarUkHo/L9XqV6XQqm83mpeu+erJXQB2QDnS73cpisZButyuHw8E+1WpV5vO5nM/nP+c8M4B8Pn83n0qlLK4PQ/9Xj90De+a675zjFVBvqNPpyG63M0C3r99nMhkZDAZ2z+12W06nk4zHYzvW1haLRZnNZnZcq9VktVrZNXTTB9NoNGS5XNoDcPulUun3vxRZmz+ZTKzhvjbvgOFptV6vDUmBooCuOW6gek42m7Wp7xocRlCger0usVjMIMMPSPfDLfU5jb0C6iDC7dEpfLlcbAo/aqCD0mZGYaMN1QbquqctTyaT39XAaAsciDbivzXQrWEKpuBuejvAXC5n3+n0dGtrIpH4vjUw/LbU9o1GIxv0o7ew/qbTV9+i99ZAt27q8qCbe7t/3VvY18L9Sdf1ugZ+0kB93QsBQVkCEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA42wgAUEBMM4GEhAUAONsIAFBATDOBhIQFADjbCABQQEwzgYSEBQA4z92vyNfXU0apAAAAABJRU5ErkJggg==';
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Envase (Botella)',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta Fresa',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Delawer',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Limonada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Naranjada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Valle frut guayaba',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Valle frut naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Generosa mango',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Generosa durazno',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua 1.5L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua 1L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
       

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Dorado',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Con queso',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 20,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => '1/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 140,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => '1/2',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 260,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => '3/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 360,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => '1 kg',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 450,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Quesabirria',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 50,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Refresco',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua de horchata 1/2L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Agua de horchata 1L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 35,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Plato Chico',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 95,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 1,
            'nombre' => 'Plato Grande',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 120,
            'imagen' => $img 
         ]);
         Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Envase (Botella)',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Fanta Fresa',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Delawer',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Limonada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Naranjada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Valle frut guayaba',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Valle frut naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Generosa mango',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Generosa durazno',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Agua 1.5L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Agua 1L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
       

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Dorado',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Con queso',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 20,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => '1/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 140,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => '1/2',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 260,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => '3/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 360,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => '1 kg',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 450,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Quesabirria',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 50,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Refresco',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Agua de horchata 1/2L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Agua de horchata 1L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 35,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Plato Chico',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 95,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 2,
            'nombre' => 'Plato Grande',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 120,
            'imagen' => $img 
         ]);
         Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Envase (Botella)',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Coca Cola',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Sidral mundet',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Sprit',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Fanta Fresa',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Fanta naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Fresca',
            'tipo' => 'bebida',
            'detalle' => 'Lata',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Delawer',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Limonada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Naranjada',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Valle frut guayaba',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Valle frut naranja',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Generosa mango',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Generosa durazno',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Agua 1.5L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Agua 1L',
            'tipo' => 'bebida',
            'detalle' => 'Botella',
            'cantidad' => 0,
            'costo' => 20,
            'precio' => 22,
            'imagen' => $img 
         ]);
       

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Dorado',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 16,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Taco',
            'tipo' => 'comida',
            'detalle' => 'Con queso',
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 20,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => '1/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 140,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => '1/2',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 260,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => '3/4',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 360,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => '1 kg',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 450,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Quesabirria',
            'tipo' => 'comida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 50,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Refresco',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Agua de horchata 1/2L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 25,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Agua de horchata 1L',
            'tipo' => 'bebida',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 35,
            'imagen' => $img 
         ]);

        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Plato Chico',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 95,
            'imagen' => $img 
         ]);
        Inventario::create([
            'sucursal_id' => 3,
            'nombre' => 'Plato Grande',
            'tipo' => 'paquete',
            'detalle' => '' ,
            'cantidad' => 0,
            'costo' => 0.5,
            'precio' => 120,
            'imagen' => $img 
         ]);

        
    }
}
