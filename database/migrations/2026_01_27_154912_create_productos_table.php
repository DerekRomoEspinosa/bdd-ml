<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('sku_ml')->unique()->comment('ID del producto en Mercado Libre');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            
            // Inventario interno (editable por usuario)
            $table->integer('stock_bodega')->default(0)->comment('Unidades en bodega');
            $table->integer('stock_cortado')->default(0)->comment('Unidades ya cortadas');
            $table->integer('stock_enviado_full')->default(0)->comment('Unidades en tránsito a Full');
            
            // Datos de Mercado Libre (desde API)
            $table->integer('stock_full')->nullable()->comment('Stock en Full (ML)');
            $table->integer('ventas_30_dias')->nullable()->comment('Ventas últimos 30 días (ML)');
            $table->timestamp('ml_ultimo_sync')->nullable()->comment('Última actualización desde ML');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};