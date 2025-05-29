<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {

        // Elimina las tablas en orden inverso si ya existen
        if (Schema::hasTable('alquiler_stock')) {
            Schema::dropIfExists('alquiler_stock');
        }
        
        if (Schema::hasTable('talle_stock')) {
            Schema::dropIfExists('talle_stock');
        }
        
        // Modifica la tabla stock_alquiler
        Schema::table('stock_alquiler', function (Blueprint $table) {
            // Elimina las columnas que ya no necesitas
            // Por ejemplo, si antes tenías una columna 'talle'
            if (Schema::hasColumn('stock_alquiler', 'talle')) {
                $table->dropColumn('talle');
            }
            if (Schema::hasColumn('stock_alquiler', 'color')) {
                $table->dropColumn('color');
            }
            
            // Asegúrate de que las columnas necesarias existan
            if (!Schema::hasColumn('stock_alquiler', 'codigo')) {
                $table->string('codigo')->unique();
            }
            
            if (!Schema::hasColumn('stock_alquiler', 'nombre')) {
                $table->string('nombre');
            }
        
            
            if (!Schema::hasColumn('stock_alquiler', 'precio_alquiler')) {
                $table->decimal('precio_alquiler', 10, 2);
            }
            
            if (!Schema::hasColumn('stock_alquiler', 'descripcion')) {
                $table->text('descripcion')->nullable();
            }
        });
        
        // Crea la tabla talle_stock
        Schema::create('talle_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained('stock_alquiler')->onDelete('cascade');
            $table->string('talle');
            $table->integer('cantidad_total')->default(0);
            $table->integer('cantidad_disponible')->default(0);
            $table->integer('cantidad_alquilada')->default(0);
            $table->timestamps();
            
            // Índice único para evitar duplicados de talle para un mismo stock
            $table->unique(['stock_id', 'talle']);
        });

        // Crea la tabla pivote para la relación muchos a muchos entre alquileres y stock
        Schema::create('alquiler_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->onDelete('cascade');
            $table->foreignId('stock_id')->constrained('stock_alquiler')->onDelete('cascade');
            $table->foreignId('talle_id')->constrained('talle_stock')->onDelete('cascade');
            $table->integer('cantidad')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('alquiler_stock');
        Schema::dropIfExists('talle_stock');
        
        // Aquí podrías restaurar la estructura original de stock_alquiler si es necesario
    }
};