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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sucursal_id');
            $table->string('nombre');
            $table->string('tipo');
            $table->integer('cantidad')->default(0);
            $table->decimal('costo', 10, 2)->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->string('detalle')->nullable();
            $table->longText('imagen')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
