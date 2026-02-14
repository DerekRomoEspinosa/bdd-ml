<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Renombrar columna ventas_30_dias a ventas_totales
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'ventas_30_dias')) {
                $table->renameColumn('ventas_30_dias', 'ventas_totales');
            }
        });

        // Agregar nuevas columnas para el sistema de reportes
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'ventas_totales_reporte_anterior')) {
                $table->integer('ventas_totales_reporte_anterior')->default(0)->after('ventas_totales');
            }
            
            if (!Schema::hasColumn('productos', 'ventas_30_dias_calculadas')) {
                $table->integer('ventas_30_dias_calculadas')->default(0)->after('ventas_totales_reporte_anterior');
            }
            
            if (!Schema::hasColumn('productos', 'fecha_ultimo_reporte')) {
                $table->timestamp('fecha_ultimo_reporte')->nullable()->after('ventas_30_dias_calculadas');
            }
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'ventas_totales_reporte_anterior',
                'ventas_30_dias_calculadas',
                'fecha_ultimo_reporte'
            ]);
            
            if (Schema::hasColumn('productos', 'ventas_totales')) {
                $table->renameColumn('ventas_totales', 'ventas_30_dias');
            }
        });
    }
};