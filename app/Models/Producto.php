<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'nombre', 'modelo', 'sku_ml', 'codigo_interno_ml',        
        'plantilla_corte_url', 'piezas_por_plancha', 'variante_bafle',
        'stock_bodega', 'stock_cortado', 'stock_costura', 
        'stock_por_empacar', 'stock_enviado_full',
        'stock_full', 'ventas_30_dias', 'ml_published_at', 'ml_ultimo_sync', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
        'ml_published_at' => 'datetime',
        'piezas_por_plancha' => 'integer',
    ];

    // ✅ ARREGLADO: Stock Total = Bodega + Enviado Full + Full
    public function getStockTotalAttribute()
    {
        return $this->stock_bodega + $this->stock_enviado_full + ($this->stock_full ?? 0);
    }

    // Consumo Diario (Ventas / 30)
    public function getConsumoDiarioAttribute(): float
    {
        return $this->ventas_30_dias ? round($this->ventas_30_dias / 30, 2) : 0;
    }

    // Stock mínimo = 2 × piezas_por_plancha
    public function getStockMinimoAttribute(): int
    {
        return $this->piezas_por_plancha * 2;
    }

    // ✅ MEJORADO: Recomendación de fabricación basada en ventas mensuales reales
    public function getRecomendacionFabricacionAttribute()
    {
        // Si no tiene ventas, verificar si está debajo del stock mínimo
        if ($this->ventas_30_dias == 0) {
            $faltante = $this->stock_minimo - $this->stock_total;
            return max(0, $faltante);
        }
        
        // Si tiene ventas, calcular para cubrir 30 días (1 mes)
        $stockParaUnMes = $this->ventas_30_dias;
        $necesario = $stockParaUnMes - $this->stock_total;
        
        // Comparar con stock mínimo
        $porVentas = max(0, ceil($necesario));
        $porMinimo = max(0, $this->stock_minimo - $this->stock_total);
        
        return max($porVentas, $porMinimo);
    }

    // Para productos con variantes (bafles)
    public function getVentasVarianteAttribute()
    {
        if (!$this->variante_bafle) {
            return $this->ventas_30_dias;
        }

        // Sumar ventas de todos los productos con la misma variante
        return self::where('variante_bafle', $this->variante_bafle)
            ->where('activo', true)
            ->sum('ventas_30_dias');
    }
}