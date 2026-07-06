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

        // Pivote alquiler↔unidad física (una fila = una prenda concreta entregada).
        // Sin UNIQUE en unidad_id: una unidad tiene muchas filas a lo largo de su vida (un
        // alquiler pasado por unidad); la exclusividad "no alquilar dos veces la misma unidad
        // a la vez" se garantiza por lógica + estado (ver AlquilerService::asignarUnidades),
        // no por constraint — así lo indica docs/barcode-spec.md §5.1.
        Schema::create('alquiler_unidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->cascadeOnDelete();
            $table->foreignId('unidad_id')->constrained('unidad_stock')->cascadeOnDelete();
            $table->decimal('precio', 14, 0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alquiler_unidad');
        Schema::dropIfExists('alquileres');
    }
};
