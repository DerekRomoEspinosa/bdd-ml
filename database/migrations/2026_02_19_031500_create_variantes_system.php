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
            $table->string('descripcion')->nullable(); // Ej: "Bafles 15 pulgadas"
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
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

        // Agregar columna a productos
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'usa_variante_para_fabricacion')) {
                $table->boolean('usa_variante_para_fabricacion')->default(false)->after('activo');
            }
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('usa_variante_para_fabricacion');
        });
        
        Schema::dropIfExists('producto_variante');
        Schema::dropIfExists('variantes');
    }
};