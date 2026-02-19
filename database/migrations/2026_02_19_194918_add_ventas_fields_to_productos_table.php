<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::table('productos', function (Blueprint $table) {

        $table->integer('ventas_totales_reporte_anterior')->nullable();

        $table->integer('ventas_30_dias_calculadas')->nullable();

        $table->timestamp('fecha_ultimo_reporte')->nullable();

    });
}

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('productos', function (Blueprint $table) {

        $table->dropColumn([
            'ventas_totales_reporte_anterior',
            'ventas_30_dias_calculadas',
            'fecha_ultimo_reporte'
        ]);

    });
}

};
