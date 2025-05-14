<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCorteCajaTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('corte_caja', function (Blueprint $table) {
            // Agregar nuevos campos
            $table->datetime('opening_time')->nullable()->after('dinero_tarjeta');
            $table->datetime('closing_time')->nullable()->after('opening_time');;
            $table->text('note')->nullable()->after('closing_time');
            $table->boolean('status')->default(true)->after('note');
            $table->decimal('balance', 10, 2)->default(0)->after('status');
            $table->decimal('difference', 10, 2)->default(0)->after('balance');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('corte_caja', function (Blueprint $table) {
            // Eliminar los campos agregados
            $table->dropColumn([
                'opening_time',
                'closing_time',
                'note',
                'status',
                'balance',
                'difference'
            ]);
        });
    }
}