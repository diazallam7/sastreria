<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('vestidos', function (Blueprint $table) {
        $table->unsignedInteger('estado')->default(1)->change(); // Cambia el estado a entero
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vestidos', function (Blueprint $table) {
            //
        });
    }
};
