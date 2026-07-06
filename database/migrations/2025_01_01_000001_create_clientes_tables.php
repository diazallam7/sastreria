<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('documento', 20)->nullable()->unique();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable()->unique();
            $table->string('direccion')->nullable();
            $table->boolean('estado')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cliente_medidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20); // saco | pantalon | chaleco
            $table->json('medidas');
            $table->text('observaciones')->nullable();
            $table->boolean('vigente')->default(true);
            $table->timestamps();

            $table->index(['cliente_id', 'tipo', 'vigente']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_medidas');
        Schema::dropIfExists('clientes');
    }
};
