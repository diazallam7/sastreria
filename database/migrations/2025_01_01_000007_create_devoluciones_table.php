<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devoluciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id')->constrained('alquileres')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('fecha_devolucion')->index();
            $table->boolean('retraso')->default(false);
            $table->integer('dias_retraso')->default(0);
            $table->decimal('multa_calculada', 14, 0)->default(0);   // corresponde por retraso
            $table->decimal('multa_aplicada', 14, 0)->default(0);    // realmente cobrada
            $table->decimal('garantia_original', 14, 0)->default(0);
            $table->decimal('monto_devuelto', 14, 0)->default(0);    // garantia - multa_aplicada
            $table->string('motivo_ajuste')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devoluciones');
    }
};
