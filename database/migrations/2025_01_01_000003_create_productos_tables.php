<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo', 20)->index(); // comprado | fabricado
            $table->decimal('precio_venta', 14, 0);
            $table->decimal('precio_compra', 14, 0)->nullable(); // solo comprado
            $table->date('fecha_compra')->nullable()->index();    // solo comprado
            $table->boolean('activo_para_venta')->default(false)->index();
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('producto_talles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('talle', 50);
            $table->integer('cantidad_total')->default(0);
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_vendida')->default(0);
            $table->timestamps();

            $table->unique(['producto_id', 'talle']);
        });

        DB::statement('ALTER TABLE producto_talles ADD CONSTRAINT chk_producto_talles_no_negativo
            CHECK (cantidad_total >= 0 AND cantidad_disponible >= 0 AND cantidad_vendida >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_talles');
        Schema::dropIfExists('productos');
    }
};
