<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_varios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_gasto');
            $table->date('fecha')->index();
            $table->decimal('monto', 10, 2);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_varios');
    }
};
