<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'modelo',
        'sku_ml',
        'nombre',
        'activo',
        'stock_bodega',
        'stock_cortado',
        'stock_enviado_full',
        'stock_full',
        'ventas_30_dias',
        'ml_ultimo_sync',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
    ];
    
    /**
     * Calcula el stock total disponible
     */
    public function getStockTotalAttribute(): int
    {
        return $this->stock_bodega 
             + $this->stock_cortado 
             + $this->stock_enviado_full 
             + ($this->stock_full ?? 0);
    }
    
    /**
     * Calcula el consumo diario promedio
     */
    public function getConsumoDiarioAttribute(): float
    {
        if (!$this->ventas_30_dias) {
            return 0;
        }
        return round($this->ventas_30_dias / 30, 2);
    }
    
 /**
 * Calcula cuántas unidades fabricar
 * Fórmula: si el stock total < ventas de 15 días, fabricar la diferencia
 * Si no hay datos de ventas, pero el stock es 0, recomendar fabricar un mínimo
 */
public function getRecomendacionFabricacionAttribute(): int
{
    // Si no hay ventas registradas
    if (!$this->ventas_30_dias || $this->ventas_30_dias == 0) {
        // Si el stock es 0, recomendar fabricar un mínimo de 10 unidades
        if ($this->stock_total == 0) {
            return 10;
        }
        // Si hay stock pero no hay ventas, no recomendar fabricación
        return 0;
    }
    
    // Cálculo normal cuando hay datos de ventas
    $consumo15Dias = $this->consumo_diario * 15;
    $faltante = $consumo15Dias - $this->stock_total;
    
    return max(0, (int) ceil($faltante));
}
}