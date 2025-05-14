<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePedidoProductosTable2 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pedido_productos', function (Blueprint $table) {
            // Agregar nuevos campos para llevar o para comer
            $table->string('tipo_servicio')->nullable()->after('personalizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pedido_productos', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'tipo_servicio',
            ]);
        });
    }
}