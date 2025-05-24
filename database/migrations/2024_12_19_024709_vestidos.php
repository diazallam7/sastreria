<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Vestidos extends Migration
{
    public function up()
    {
        Schema::create('vestidos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('talla');
            $table->string('color');
            $table->string('categoria');
            $table->decimal('precio_alquiler', 10, 2)->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->enum('estado', ['disponible', 'alquilado', 'vendido'])->default('disponible');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vestidos');
    }

};
