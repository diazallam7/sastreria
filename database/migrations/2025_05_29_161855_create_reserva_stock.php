<?php
// Archivo: database/migrations/2024_01_17_000001_create_reserva_stock_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reserva_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->constrained('reservas')->onDelete('cascade');
            $table->foreignId('stock_id')->constrained('stock_alquiler')->onDelete('cascade');
            $table->foreignId('talle_id')->constrained('talle_stock')->onDelete('cascade');
            $table->integer('cantidad');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reserva_stock');
    }
};