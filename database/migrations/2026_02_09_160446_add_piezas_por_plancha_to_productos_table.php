<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->integer('piezas_por_plancha')->default(4)->after('plantilla_corte_url');
            $table->string('variante_bafle')->nullable()->after('piezas_por_plancha');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['piezas_por_plancha', 'variante_bafle']);
        });
    }
};