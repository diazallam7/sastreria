<?php
// Archivo: database/migrations/2025_05_25_204500_clean_ventas_table.php
// Ejecuta: php artisan make:migration clean_ventas_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Deshabilitar verificación de claves foráneas temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Eliminar tabla detalle_ventas si existe
        Schema::dropIfExists('detalle_ventas');
        
        // Eliminar tabla ventas completamente
        Schema::dropIfExists('ventas');
        
        // Recrear tabla ventas con estructura correcta
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->date('fecha_venta');
            $table->decimal('precio_total', 10, 2);
            $table->timestamps();
        });
        
        // Crear tabla detalle_ventas
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->enum('tipo_producto', ['compra', 'manual']);
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('talle_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
        
        // Reactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};