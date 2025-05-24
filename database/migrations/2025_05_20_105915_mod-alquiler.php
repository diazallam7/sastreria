<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            // Eliminamos la restricción de clave foránea
            $table->dropForeign(['vestido_id']);
            // Eliminamos la columna vestido_id
            $table->dropColumn('vestido_id');
        });
    }

    public function down()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->foreignId('vestido_id')->constrained('vestidos')->onDelete('cascade');
        });
    }
};