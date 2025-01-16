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
        Schema::create('corte_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id')->constrained('sucursales')->onDelete('cascade');
            $table->unsignedBigInteger('usuario_id')->constrained('users')->onDelete('cascade');
            $table->date('fecha')->nullable();
            $table->decimal('saldo_inicial', 10, 2)->nullable();  
            $table->decimal('saldo_final', 10, 2)->nullable(); // Combina saldo_actual y dinero_final
            $table->decimal('gastos', 10, 2)->nullable();  
            $table->decimal('dinero_total', 10, 2)->nullable();  
            $table->decimal('total_entradas', 10, 2)->default(0); 
            $table->decimal('total_salidas', 10, 2)->default(0); 
            $table->decimal('ventas_total', 10, 2)->nullable();
            $table->decimal('dinero_en_efectivo', 10, 2)->nullable();
            $table->decimal('dinero_tarjeta', 10, 2)->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corte_caja');
    }
};
