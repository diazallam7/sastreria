<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Medidas básicas para alquiler/reserva
            $table->string('medida_saco_basica')->nullable()->comment('Ej: 50-80');
            $table->string('medida_pantalon_basica')->nullable()->comment('Ej: 42-90');
            
            // Medidas completas para confección - SACO
            $table->decimal('saco_talle', 5, 2)->nullable();
            $table->decimal('saco_largo', 5, 2)->nullable();
            $table->decimal('saco_espalda', 5, 2)->nullable();
            $table->decimal('saco_manga', 5, 2)->nullable();
            $table->decimal('saco_pecho', 5, 2)->nullable();
            $table->decimal('saco_cintura', 5, 2)->nullable();
            $table->decimal('saco_cadera', 5, 2)->nullable();
            $table->decimal('saco_alto_hombro', 5, 2)->nullable();
            $table->decimal('saco_plomo_trasero', 5, 2)->nullable();
            $table->decimal('saco_plomo_delantero', 5, 2)->nullable();
            $table->decimal('saco_sisa', 5, 2)->nullable();
            $table->decimal('saco_puno', 5, 2)->nullable();
            
            // Medidas completas para confección - PANTALÓN
            $table->decimal('pantalon_largo', 5, 2)->nullable();
            $table->decimal('pantalon_cintura', 5, 2)->nullable();
            $table->decimal('pantalon_cadera', 5, 2)->nullable();
            $table->decimal('pantalon_entre_pierna', 5, 2)->nullable();
            $table->decimal('pantalon_muslo', 5, 2)->nullable();
            $table->decimal('pantalon_rodilla', 5, 2)->nullable();
            $table->decimal('pantalon_bajo', 5, 2)->nullable();
            
            // Medidas completas para confección - CHALECO
            $table->decimal('chaleco_talle', 5, 2)->nullable();
            $table->decimal('chaleco_pecho', 5, 2)->nullable();
            $table->decimal('chaleco_cintura', 5, 2)->nullable();
            $table->decimal('chaleco_escote', 5, 2)->nullable();
            $table->decimal('chaleco_largo', 5, 2)->nullable();
            $table->decimal('chaleco_largo_trasero', 5, 2)->nullable();
            $table->decimal('chaleco_cuello', 5, 2)->nullable();
            
            // Campo para observaciones de medidas
            $table->text('observaciones_medidas')->nullable();
        });
    }

    public function down()
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn([
                'medida_saco_basica', 'medida_pantalon_basica',
                'saco_talle', 'saco_largo', 'saco_espalda', 'saco_manga', 'saco_pecho', 'saco_cintura', 'saco_cadera',
                'saco_alto_hombro', 'saco_plomo_trasero', 'saco_plomo_delantero', 'saco_sisa', 'saco_puno',
                'pantalon_largo', 'pantalon_cintura', 'pantalon_cadera', 'pantalon_entre_pierna', 'pantalon_muslo', 'pantalon_rodilla', 'pantalon_bajo',
                'chaleco_talle', 'chaleco_pecho', 'chaleco_cintura', 'chaleco_escote', 'chaleco_largo', 'chaleco_largo_trasero', 'chaleco_cuello',
                'observaciones_medidas'
            ]);
        });
    }
};
