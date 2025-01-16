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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('mesa_id')->onDelete('cascade')->nullable();
            $table->unsignedInteger('sucursal_id')->onDelete('cascade');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('estado')->default('pendiente'); // pendiente, en_preparacion, listo, pagado, cancelado
            $table->boolean('para_mesa')->default(true); // Pedido grupal o individual
            $table->boolean('para_llevar')->default(false);
            $table->string('nombre_cliente')->nullable();
            $table->string('prioridad')->default('normal');
            $table->string('tipo_pedido')->default('normal');
            $table->string('metodo_pago')->nullable();
            $table->string('descuento')->nullable();
            $table->string('propina')->nullable();
            $table->string('dinero_recibido')->nullable();
            $table->boolean('pagado')->default(false);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
