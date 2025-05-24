<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracionesTable extends Migration
{
    public function up()
    {
        Schema::create('configuraciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Nombre del parámetro (ej: "multa", "impuesto")
            $table->string('descripcion')->nullable(); // Descripción breve
            $table->decimal('valor', 10, 2)->default(0); // Valor del parámetro
            $table->timestamps();
        });

        // Valores iniciales por defecto
        DB::table('configuraciones')->insert([
            ['nombre' => 'multa', 'descripcion' => 'Multa por retraso (por día)', 'valor' => 10000],
            ['nombre' => 'impuesto', 'descripcion' => 'Porcentaje de impuesto', 'valor' => 10],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('configuraciones');
    }
}
