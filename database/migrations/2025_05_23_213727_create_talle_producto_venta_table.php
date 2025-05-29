<?php
// Archivo: database/migrations/xxxx_create_talle_producto_venta_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('talle_producto_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_venta_id')->constrained('productos_venta')->onDelete('cascade');
            $table->string('talle');
            $table->integer('cantidad_total')->default(0);
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_vendida')->default(0);
            $table->timestamps();
            
            $table->unique(['producto_venta_id', 'talle']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('talle_producto_venta');
    }
};