<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_reserva');
            $table->date('fecha_entrega_programada');
            $table->date('fecha_devolucion_programada');
            $table->decimal('monto_total', 14, 0);
            $table->decimal('garantia_total', 14, 0);
            $table->decimal('senia_garantia', 14, 0);
            $table->decimal('senia_devuelta', 14, 0)->nullable();
            $table->string('motivo_devolucion')->nullable();
            $table->decimal('senia_alquiler', 14, 0)->default(0);
            $table->string('estado', 20)->default('pendiente')->index(); // pendiente|confirmada|entregada|cancelada
            $table->text('observaciones')->nullable();
            $table->foreignId('alquiler_id')->nullable()->constrained('alquileres')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('updated_at'); // cierre de caja agrupa cancelaciones por fecha de update
        });

        Schema::create('reserva_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained('stock_alquiler')->cascadeOnDelete();
            $table->foreignId('talle_id')->constrained('talle_stock')->cascadeOnDelete();
            $table->integer('cantidad');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reserva_stock');
        Schema::dropIfExists('reservas');
    }
};
