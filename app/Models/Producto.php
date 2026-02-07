<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'nombre', 'modelo', 'sku_ml', 'codigo_interno_ml',        
        'plantilla_corte_url', 'stock_bodega', 'stock_cortado',
        'stock_costura', 'stock_por_empacar', 'stock_enviado_full',
        'stock_full', 'ventas_30_dias', 'ml_ultimo_sync', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
    ];

    // Cálculo automático de Stock Total
    public function getStockTotalAttribute()
    {
        return $this->stock_bodega + $this->stock_cortado + $this->stock_costura          
             + $this->stock_por_empacar + $this->stock_enviado_full + ($this->stock_full ?? 0);
    }

    // Consumo Diario (Ventas / 30)
    public function getConsumoDiarioAttribute(): float
    {
        return $this->ventas_30_dias ? round($this->ventas_30_dias / 30, 2) : 0;
    }

    // Lógica de Fabricación
    public function getRecomendacionFabricacionAttribute()
    {
        if (!$this->ventas_30_dias) return 0;
        
        $stockParaQuinceDias = ($this->ventas_30_dias / 30) * 15;
        $necesario = $stockParaQuinceDias - $this->stock_total;
        
        return max(0, ceil($necesario));
    }
}