<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id'); // Relación con la sucursal
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('cantidad', 10, 2); // Cantidad del movimiento
            $table->enum('tipo', ['venta', 'entrada', 'salida', 'corte-salida', 'corte-entrada']); 
            $table->string('descripcion')->nullable(); 
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia', 'otro'])->default('efectivo'); // Método de ingreso o salida
            $table->decimal('dinero_antes', 10, 2); // Monto en caja antes del movimiento
            $table->decimal('dinero_despues', 10, 2); // Monto en caja después del movimiento
            $table->timestamps();
            
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('cascade'); // Relación con tabla sucursales
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_caja');
    }
};
