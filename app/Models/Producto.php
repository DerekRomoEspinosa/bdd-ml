<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
    'nombre',
    'modelo',
    'sku_ml',
    'codigo_interno_ml',        
    'plantilla_corte_url',      
    'stock_bodega',
    'stock_cortado',
    'stock_costura',            
    'stock_por_empacar',        
    'stock_enviado_full',
    'stock_full',
    'ventas_30_dias',
    'ml_ultimo_sync',
    'activo',
];

    protected $casts = [
        'activo' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
    ];
    
    /**
     * Calcula el stock total disponible
     */
    public function getStockTotalAttribute()
{
    return $this->stock_bodega 
         + $this->stock_cortado 
         + $this->stock_costura          
         + $this->stock_por_empacar      
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

public function getRecomendacionFabricacionAttribute()
{
    if (!$this->ventas_30_dias || $this->ventas_30_dias == 0) {
        return 0;
    }
    
    $consumoDiario = $this->ventas_30_dias / 30;
    $stockParaQuinceDias = $consumoDiario * 15;
    $stockTotal = $this->stock_bodega 
                + $this->stock_cortado 
                + $this->stock_costura          // NUEVO
                + $this->stock_por_empacar      // NUEVO
                + $this->stock_enviado_full 
                + ($this->stock_full ?? 0);
    
    $necesario = $stockParaQuinceDias - $stockTotal;
    
    return max(0, ceil($necesario));
}
}