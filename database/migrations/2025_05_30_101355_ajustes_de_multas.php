<?php
// Archivo: database/migrations/xxxx_xx_xx_add_adjustment_fields_to_devoluciones_and_reservas.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Agregar campos a la tabla devoluciones
        Schema::table('devoluciones', function (Blueprint $table) {
            $table->decimal('multa_calculada', 10, 2)->after('multa')->nullable();
            $table->decimal('multa_aplicada_real', 10, 2)->after('multa_calculada')->nullable();
            $table->decimal('monto_devuelto_real', 10, 2)->after('monto_devuelto')->nullable();
            $table->string('motivo_ajuste')->after('monto_devuelto_real')->nullable();
        });

        // Agregar campos a la tabla reservas
        Schema::table('reservas', function (Blueprint $table) {
            $table->decimal('seña_devuelta', 10, 2)->after('seña_garantia')->nullable();
            $table->string('motivo_devolucion')->after('seña_devuelta')->nullable();
        });
    }

    public function down()
    {
        Schema::table('devoluciones', function (Blueprint $table) {
            $table->dropColumn(['multa_calculada', 'multa_aplicada_real', 'monto_devuelto_real', 'motivo_ajuste']);
        });

        Schema::table('reservas', function (Blueprint $table) {
            $table->dropColumn(['seña_devuelta', 'motivo_devolucion']);
        });
    }
};