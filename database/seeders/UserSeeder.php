<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Sucursal 1',
            'sucursal_id' => '1',   
            'email' => 'admin1',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');
        $admin2 = User::create([
            'name' => 'Admin Sucursal 2',
            'sucursal_id' => '2',   
            'email' => 'admin2',
            'password' => Hash::make('password'),
        ]);
        $admin2->assignRole('admin');
        $admin3 = User::create([
            'name' => 'Admin Sucursal 3',
            'sucursal_id' => '3',   
            'email' => 'admin3',
            'password' => Hash::make('password'),
        ]);
        $admin3->assignRole('admin');

        $sucursal = User::create([
            'name' => 'Sucursal 1',
            'sucursal_id' => '1',
            'email' => 'sucursal1',
            'password' => Hash::make('password'),
        ]);
        $sucursal->assignRole('sucursal');
        $sucursal2 = User::create([
            'name' => 'Sucursal 2',
            'sucursal_id' => '2',
            'email' => 'sucursal2',
            'password' => Hash::make('password'),
        ]);
        $sucursal2->assignRole('sucursal');
        $sucursal3 = User::create([
            'name' => 'Sucursal 3',
            'sucursal_id' => '3',
            'email' => 'sucursal3',
            'password' => Hash::make('password'),
        ]);
        $sucursal3->assignRole('sucursal');
    
        $mesero1 = User::create([
            'name' => 'User Sucursal 1',
            'sucursal_id' => '1',
            'email' => '101',
            'password' => Hash::make('password'),
        ]);
        $mesero1->assignRole('mesero');
        $mesero2 = User::create([
            'name' => 'User Sucursal 1',
            'sucursal_id' => '1',
            'email' => '102',
            'password' => Hash::make('password'),
        ]);
        $mesero2->assignRole('mesero');
        $mesero3 = User::create([
            'name' => 'User Sucursal 1',
            'sucursal_id' => '1',
            'email' => '103',
            'password' => Hash::make('password'),
        ]);
        $mesero3->assignRole('mesero');
        $mesero4 = User::create([
            'name' => 'User Sucursal 1',
            'sucursal_id' => '1',
            'email' => '104',
            'password' => Hash::make('password'),
        ]);
        $mesero4->assignRole('mesero');

        $mesero5 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '201',
            'password' => Hash::make('password'),
        ]);
        $mesero5->assignRole('mesero');
        $mesero6 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '202',
            'password' => Hash::make('password'),
        ]);
        $mesero6->assignRole('mesero');
        $mesero7 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '203',
            'password' => Hash::make('password'),
        ]);
        $mesero7->assignRole('mesero');
        $mesero8 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '204',
            'password' => Hash::make('password'),
        ]);
        $mesero8->assignRole('mesero');
        $mesero9 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '301',
            'password' => Hash::make('password'),
        ]);
        $mesero9->assignRole('mesero');
        $mesero10 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '302',
            'password' => Hash::make('password'),
        ]);
        $mesero10->assignRole('mesero');
        $mesero11 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '303',
            'password' => Hash::make('password'),
        ]);
        $mesero11->assignRole('mesero');
        $mesero12 = User::create([
            'name' => 'User Sucursal 2',
            'sucursal_id' => '2',
            'email' => '304',
            'password' => Hash::make('password'),
        ]);
        $mesero12->assignRole('mesero');


        $cocina = User::create([
            'name' => 'Cocina',
            'sucursal_id' => '1',
            'email' => 'cocina1',
            'password' => Hash::make('password'),
        ]);
        $cocina->assignRole('cocina');
        $cocina2 = User::create([
            'name' => 'Cocina',
            'sucursal_id' => '2',
            'email' => 'cocina2',
            'password' => Hash::make('password'),
        ]);
        $cocina2->assignRole('cocina');
        $cocina3 = User::create([
            'name' => 'Cocina',
            'sucursal_id' => '3',
            'email' => 'cocina3',
            'password' => Hash::make('password'),
        ]);
        $cocina3->assignRole('cocina');

        $caja = User::create([
            'name' => 'Caja',
            'sucursal_id' => '1',
            'email' => 'caja1',
            'password' => Hash::make('password'),
        ]);
        $caja->assignRole('caja');
        $caja2 = User::create([
            'name' => 'Caja',
            'sucursal_id' => '2',
            'email' => 'caja2',
            'password' => Hash::make('password'),
        ]);
        $caja2->assignRole('caja');
        $caja3 = User::create([
            'name' => 'Caja',
            'sucursal_id' => '3',
            'email' => 'caja3',
            'password' => Hash::make('password'),
        ]);
        $caja3->assignRole('caja');

        $empacador = User::create([
            'name' => 'Empacador',
            'sucursal_id' => '1',
            'email' => 'empacador1',
            'password' => Hash::make('password'),
        ]);
        $empacador->assignRole('empacador');
        $empacador2 = User::create([
            'name' => 'Empacador',
            'sucursal_id' => '2',
            'email' => 'empacador2',
            'password' => Hash::make('password'),
        ]);
        $empacador2->assignRole('empacador');
        $empacador3 = User::create([
            'name' => 'Empacador',
            'sucursal_id' => '3',
            'email' => 'empacador3',
            'password' => Hash::make('password'),
        ]);
        $empacador3->assignRole('empacador');
    }
}
