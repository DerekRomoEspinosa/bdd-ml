<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tabla de Variantes
        Schema::create('variantes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); // Ej: "Variante 49"
            $table->string('descripcion')->nullable(); // Ej: "Bafles 15 pulgadas formato A"
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            
            // Stock de la variante (calculado)
            $table->integer('stock_bodega')->default(0);
            $table->integer('stock_cortado')->default(0);
            $table->integer('stock_costura')->default(0);
            $table->integer('stock_por_empacar')->default(0);
            $table->integer('stock_enviado_full')->default(0);
            
            // Ventas y proyección
            $table->integer('ventas_totales')->default(0); // Suma de todas las fundas compatibles
            $table->integer('ventas_30_dias')->default(0); // Calculado por diferencia de reportes
            $table->integer('recomendacion_fabricacion')->default(0); // Stock a fabricar
            
            $table->timestamps();
        });

        // Tabla pivot: Productos <-> Variantes (muchos a muchos)
        Schema::create('producto_variante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('variante_id')->constrained('variantes')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['producto_id', 'variante_id']);
        });

        // Modificar tabla productos
        Schema::table('productos', function (Blueprint $table) {
            // Cambiar nombre de columna
            if (Schema::hasColumn('productos', 'ventas_30_dias')) {
                $table->renameColumn('ventas_30_dias', 'ventas_totales');
            } else {
                $table->integer('ventas_totales')->default(0)->after('stock_full');
            }
            
            // Agregar columnas para comparación de reportes
            $table->integer('ventas_totales_reporte_anterior')->default(0)->after('ventas_totales');
            $table->integer('ventas_30_dias_calculadas')->default(0)->after('ventas_totales_reporte_anterior');
            $table->timestamp('fecha_ultimo_reporte')->nullable()->after('ventas_30_dias_calculadas');
            
            // Columna para saber si usa variante o cálculo propio
            $table->boolean('usa_variante_para_fabricacion')->default(false)->after('fecha_ultimo_reporte');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'ventas_totales_reporte_anterior',
                'ventas_30_dias_calculadas',
                'fecha_ultimo_reporte',
                'usa_variante_para_fabricacion'
            ]);
            
            if (Schema::hasColumn('productos', 'ventas_totales')) {
                $table->renameColumn('ventas_totales', 'ventas_30_dias');
            }
        });
        
        Schema::dropIfExists('producto_variante');
        Schema::dropIfExists('variantes');
    }
};