<?php
// Archivo: database/migrations/xxxx_modify_ventas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            
            // Agregar nuevos campos
            $table->string('tipo_producto')->after('cliente_id'); // 'compra' o 'manual'
            $table->foreignId('compra_id')->nullable()->constrained('compras')->onDelete('cascade');
            $table->foreignId('producto_venta_id')->nullable()->constrained('productos_venta')->onDelete('cascade');
            $table->foreignId('talle_id')->nullable(); // Puede ser talle_compra o talle_producto_venta
            $table->integer('cantidad')->default(1);
            $table->string('nombre_producto')->after('tipo_producto');
            $table->string('talle')->after('nombre_producto');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['tipo_producto', 'compra_id', 'producto_venta_id', 'talle_id', 'cantidad', 'nombre_producto', 'talle']);
        });
    }
};