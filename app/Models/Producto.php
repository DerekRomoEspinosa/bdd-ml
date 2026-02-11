<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'nombre', 'modelo', 'sku_ml', 'codigo_interno_ml',        
        'plantilla_corte_url', 'piezas_por_plancha', 'stock_minimo_deseado', 'variante_bafle',
        'stock_bodega', 'stock_cortado', 'stock_costura', 
        'stock_por_empacar', 'stock_enviado_full',
        'stock_full', 'ventas_30_dias', 'ml_published_at', 'ml_ultimo_sync', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
        'ml_published_at' => 'datetime',
        'piezas_por_plancha' => 'integer',
        'stock_minimo_deseado' => 'integer',
    ];

    // ✅ Stock Total = Bodega + Enviado Full + Full
    public function getStockTotalAttribute()
    {
        return $this->stock_bodega + $this->stock_enviado_full + ($this->stock_full ?? 0);
    }

    // Consumo Diario (Ventas / 30)
    public function getConsumoDiarioAttribute(): float
    {
        return $this->ventas_30_dias ? round($this->ventas_30_dias / 30, 2) : 0;
    }

    // Stock mínimo: Si Carlos definió uno, usarlo. Si no, usar 2 × piezas_por_plancha
    public function getStockMinimoAttribute(): int
    {
        return $this->stock_minimo_deseado > 0 
            ? $this->stock_minimo_deseado 
            : ($this->piezas_por_plancha * 2);
    }

    // ✅ SÚPER SIMPLE: Solo restar lo que falta
    public function getRecomendacionFabricacionAttribute()
    {
        $faltante = $this->stock_minimo - $this->stock_total;
        return max(0, $faltante);
    }

    // Para productos con variantes (bafles)
    public function getVentasVarianteAttribute()
    {
        if (!$this->variante_bafle) {
            return $this->ventas_30_dias;
        }

        return self::where('variante_bafle', $this->variante_bafle)
            ->where('activo', true)
            ->sum('ventas_30_dias');
    }
}