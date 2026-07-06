<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_alquiler', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->nullable()->unique(); // referencia manual; se autogenera si se deja vacío
            $table->string('nombre');
            $table->decimal('precio_alquiler', 14, 0);
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('talle_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stock_alquiler')->cascadeOnDelete();
            $table->string('talle');
            $table->integer('cantidad_total')->default(0);
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_alquilada')->default(0);
            $table->integer('cantidad_reservada')->default(0);
            $table->timestamps();

            $table->unique(['stock_id', 'talle']);
        });

        // Invariante de stock a nivel BD: contadores nunca negativos.
        DB::statement('ALTER TABLE talle_stock ADD CONSTRAINT chk_talle_stock_no_negativo
            CHECK (cantidad_total >= 0 AND cantidad_disponible >= 0
                   AND cantidad_alquilada >= 0 AND cantidad_reservada >= 0)');

        // Unidad física de alquiler (fuente de verdad de disponibilidad real; los contadores
        // de arriba son una denormalización/caché que se sincroniza en las mismas transacciones).
        Schema::create('unidad_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talle_stock_id')->constrained('talle_stock')->cascadeOnDelete();
            $table->string('codigo')->nullable()->unique();
            $table->string('estado', 20)->default('disponible')->index(); // disponible | alquilada | baja
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unidad_stock');
        Schema::dropIfExists('talle_stock');
        Schema::dropIfExists('stock_alquiler');
    }
};
