<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGarantiaToAlquileresTable extends Migration
{
    public function up()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->decimal('garantia', 10, 2)->nullable(); // o cambiá "total" por la columna después de la que querés agregar
        });
    }

    public function down()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn('garantia');
        });
    }
}
