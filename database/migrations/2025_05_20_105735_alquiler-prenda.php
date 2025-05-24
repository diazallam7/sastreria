<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alquiler_prenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->onDelete('cascade');
            $table->foreignId('prenda_id')->constrained('vestidos')->onDelete('cascade'); // Mantenemos 'vestidos' como nombre de tabla por ahora
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alquiler_prenda');
    }
};