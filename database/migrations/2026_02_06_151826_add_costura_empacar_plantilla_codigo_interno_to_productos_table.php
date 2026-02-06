<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            // Nuevas columnas de inventario
            $table->integer('stock_costura')->default(0)->after('stock_cortado');
            $table->integer('stock_por_empacar')->default(0)->after('stock_costura');
            
            // Link de plantilla de corte (OneDrive)
            $table->text('plantilla_corte_url')->nullable()->after('sku_ml');
            
            // Código interno ML
            $table->string('codigo_interno_ml')->nullable()->after('sku_ml');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'stock_costura',
                'stock_por_empacar',
                'plantilla_corte_url',
                'codigo_interno_ml'
            ]);
        });
    }
};