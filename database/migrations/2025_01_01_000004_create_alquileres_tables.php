<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->date('fecha_inicio')->index();
            $table->date('fecha_fin');
            $table->decimal('costo_total', 14, 0);
            $table->decimal('garantia', 14, 0)->default(0);
            $table->string('estado', 20)->default('activo')->index(); // activo | completado | cancelado
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('alquiler_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained('stock_alquiler')->cascadeOnDelete();
            $table->foreignId('talle_id')->constrained('talle_stock')->cascadeOnDelete();
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_stock');
        Schema::dropIfExists('alquileres');
    }
};
