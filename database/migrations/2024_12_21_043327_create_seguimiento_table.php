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
        Schema::create('seguimiento_orden', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sucursal_id'); 
            $table->unsignedBigInteger('pedido_id');

            $table->string('tipo_pedido', 50)->default('normal'); 
            
            $table->unsignedBigInteger('creo')->nullable(); //
            
            $table->unsignedBigInteger('cocino')->nullable(); //
            $table->dateTime('hora_cocino')->nullable(); 
            $table->unsignedBigInteger('mandar_caja')->nullable(); //
            $table->dateTime('hora_mandar_caja')->nullable();
            $table->unsignedBigInteger('reconsumo')->nullable();  //
            $table->dateTime('hora_reconsumo')->nullable();
            $table->unsignedBigInteger('entrego')->nullable(); //
            $table->dateTime('hora_entrego')->nullable();
            $table->unsignedBigInteger('cobro')->nullable(); //
            $table->dateTime('hora_cobro')->nullable();
            $table->unsignedBigInteger('empaco')->nullable(); //
            $table->dateTime('hora_empaco')->nullable();
            $table->unsignedBigInteger('cancelo')->default(1); //
            $table->dateTime('hora_cancelo')->nullable();

            $table->string('estado', 50)->default('pendiente');
    
            $table->timestamps();
    
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seguimiento_orden');
    }
};
