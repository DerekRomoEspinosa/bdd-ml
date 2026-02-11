<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('stock_minimo_deseado')->default(0)->after('piezas_por_plancha');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('stock_minimo_deseado');
        });
    }
};