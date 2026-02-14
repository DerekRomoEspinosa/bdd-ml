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
        'stock_full' => 'integer',
        'ventas_30_dias' => 'integer',
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

    // Stock mínimo: Si Carlos lo definió, usarlo. Si no, calcular 2 × piezas_por_plancha
    public function getStockMinimoAttribute(): int
    {
        // Si tiene stock mínimo deseado definido, usarlo
        if ($this->stock_minimo_deseado > 0) {
            return $this->stock_minimo_deseado;
        }
        
        // Si no tiene piezas por plancha, retornar 0 (no fabricar)
        if (!$this->piezas_por_plancha || $this->piezas_por_plancha <= 0) {
            return 0;
        }
        
        // Calcular: 2 × piezas por plancha
        return $this->piezas_por_plancha * 2;
    }

    // ✅ LÓGICA SÚPER CONSERVADORA: Solo fabricar si está por debajo del mínimo
    public function getRecomendacionFabricacionAttribute()
    {
        // Si no tiene stock mínimo definido, no fabricar
        $stockMinimo = $this->stock_minimo;
        
        if ($stockMinimo <= 0) {
            return 0;
        }
        
        // Calcular faltante
        $faltante = $stockMinimo - $this->stock_total;
        
        // Solo retornar si realmente falta (no números negativos)
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