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
        $table->integer('estado')->default(1)->change(); // Cambia "1" según corresponda al estado "disponible".
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vestido', function (Blueprint $table) {
            //
        });
    }
};
