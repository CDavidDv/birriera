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
        Schema::create('pedido_productos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pedido_id')->constrained('pedidos');
            $table->unsignedInteger('inventario_id')->constrained('inventario');
            $table->unsignedInteger('persona_id');
            $table->integer('cantidad');
            $table->decimal('subtotal', 10, 2);
            $table->string('estado')->default('pendiente');
            $table->string('personalizacion');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_productos');
    }
};
