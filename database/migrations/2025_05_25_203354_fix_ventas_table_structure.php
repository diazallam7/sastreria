<?php
// Archivo: database/migrations/2025_05_25_203354_fix_ventas_table_structure.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la tabla ventas existe y qué columnas tiene
        if (Schema::hasTable('ventas')) {
            // Eliminar columnas que no necesitamos si existen
            Schema::table('ventas', function (Blueprint $table) {
                if (Schema::hasColumn('ventas', 'tipo_producto')) {
                    $table->dropColumn('tipo_producto');
                }
                if (Schema::hasColumn('ventas', 'nombre_producto')) {
                    $table->dropColumn('nombre_producto');
                }
                if (Schema::hasColumn('ventas', 'producto_id')) {
                    $table->dropColumn('producto_id');
                }
                if (Schema::hasColumn('ventas', 'talle_id')) {
                    $table->dropColumn('talle_id');
                }
                if (Schema::hasColumn('ventas', 'cantidad')) {
                    $table->dropColumn('cantidad');
                }
                if (Schema::hasColumn('ventas', 'precio_unitario')) {
                    $table->dropColumn('precio_unitario');
                }
            });
            
            // Asegurar que tenemos las columnas correctas
            Schema::table('ventas', function (Blueprint $table) {
                if (!Schema::hasColumn('ventas', 'cliente_id')) {
                    $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
                }
                if (!Schema::hasColumn('ventas', 'fecha_venta')) {
                    $table->date('fecha_venta');
                }
                if (!Schema::hasColumn('ventas', 'precio_total')) {
                    $table->decimal('precio_total', 10, 2);
                }
            });
        } else {
            // Si no existe, crearla
            Schema::create('ventas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
                $table->date('fecha_venta');
                $table->decimal('precio_total', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // No hacer nada en el rollback para evitar problemas
    }
};