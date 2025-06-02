<?php
// Archivo: database/migrations/2024_01_17_000002_add_cantidad_reservada_to_talle_stock.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('talle_stock', function (Blueprint $table) {
            $table->integer('cantidad_reservada')->default(0)->after('cantidad_alquilada');
        });
    }

    public function down()
    {
        Schema::table('talle_stock', function (Blueprint $table) {
            $table->dropColumn('cantidad_reservada');
        });
    }
};