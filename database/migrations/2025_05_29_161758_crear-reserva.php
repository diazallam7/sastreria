<?php
// Archivo: database/migrations/2024_01_17_000000_create_reservas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->date('fecha_reserva');
            $table->date('fecha_entrega_programada');
            $table->date('fecha_devolucion_programada');
            $table->decimal('monto_total', 10, 2);
            $table->decimal('garantia_total', 10, 2);
            $table->decimal('seña_garantia', 10, 2);
            $table->decimal('seña_alquiler', 10, 2)->default(0);
            $table->enum('estado', ['pendiente', 'confirmada', 'entregada', 'cancelada'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->foreignId('alquiler_id')->nullable()->constrained('alquileres')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reservas');
    }
};