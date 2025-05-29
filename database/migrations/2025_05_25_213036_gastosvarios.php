<?php
// Archivo: database/migrations/2024_01_15_000000_create_gastos_varios_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gastos_varios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_gasto');
            $table->date('fecha');
            $table->decimal('monto', 10, 2);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gastos_varios');
    }
};