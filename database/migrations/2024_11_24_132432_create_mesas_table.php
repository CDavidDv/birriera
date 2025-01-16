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
        Schema::create('mesas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->integer('posicion')->default(99);
            $table->string('nombre'); // Ej: Mesa 1, Barra 1
            $table->string('estado')->default('libre'); // libre, ocupada, etc.
            $table->string('tipo');
            $table->integer('capacidad')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesas');
    }
};
