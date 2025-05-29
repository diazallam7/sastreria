<?php
// Archivo: database/migrations/xxxx_create_productos_venta_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos_venta', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_producto');
            $table->decimal('precio_venta', 10, 2);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos_venta');
    }
};