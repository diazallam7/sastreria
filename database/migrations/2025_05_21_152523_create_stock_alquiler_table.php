// Archivo para crear con: php artisan make:migration create_stock_alquiler_table
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_alquiler', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('talle');
            $table->string('color');
            $table->decimal('precio_alquiler', 10, 2);
            $table->integer('estado')->default(1); // 1: disponible por defecto
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });

        // Tabla pivote para la relación muchos a muchos entre alquileres y stock
        Schema::create('alquiler_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->onDelete('cascade');
            $table->foreignId('stock_id')->constrained('stock_alquiler')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alquiler_stock');
        Schema::dropIfExists('stock_alquiler');
    }
};