<?php
// Archivo: database/migrations/2024_01_15_000000_create_devoluciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->onDelete('cascade');
            $table->date('fecha_devolucion');
            $table->boolean('retraso')->default(false);
            $table->decimal('multa', 10, 2)->default(0);
            $table->decimal('garantia_original', 10, 2)->default(0);
            $table->decimal('multa_aplicada', 10, 2)->default(0);
            $table->decimal('monto_devuelto', 10, 2)->default(0);
            $table->integer('dias_retraso')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devoluciones');
    }
};