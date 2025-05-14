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
        Schema::table('corte_caja', function (Blueprint $table) {
            $table->decimal('otros_ingresos', 10, 2)->default(0);
            $table->decimal('venta_real', 10, 2)->default(0);
            $table->decimal('efectivo_entregado', 10, 2)->default(0);
            $table->decimal('saldo_siguiente_corte', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('corte_caja', function (Blueprint $table) {
            $table->dropColumn('otros_ingresos');
            $table->dropColumn('venta_real');
            $table->dropColumn('efectivo_entregado');
            $table->dropColumn('saldo_siguiente_corte');
        });
    }
};
