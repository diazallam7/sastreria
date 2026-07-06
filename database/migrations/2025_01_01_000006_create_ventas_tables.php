<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_venta')->index();
            $table->decimal('precio_total', 14, 0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            // Nullable: si el talle/producto se elimina, el detalle conserva su snapshot.
            $table->foreignId('producto_talle_id')->nullable()->constrained('producto_talles')->nullOnDelete();
            $table->string('nombre_producto'); // snapshot inmutable
            $table->string('talle', 50)->nullable();
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 14, 0);
            $table->decimal('subtotal', 14, 0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
    }
};
