<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    protected $fillable = [
        'nombre',
        'modelo', 
        'sku_ml',
        'codigo_interno_ml',
        'plantilla_corte_url', 
        'piezas_por_plancha', 
        'stock_minimo_deseado', 
        'variante_bafle', 
        'stock_bodega',
        'stock_cortado',
        'stock_costura',
        'stock_por_empacar',
        'stock_enviado_full',
        'stock_full',
        'ventas_totales',
        'ventas_totales_reporte_anterior',
        'ventas_30_dias_calculadas',
        'fecha_ultimo_reporte',
        'usa_variante_para_fabricacion', 
        'ml_ultimo_sync',
        'ml_published_at',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'usa_variante_para_fabricacion' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
        'ml_published_at' => 'datetime',
        'fecha_ultimo_reporte' => 'datetime',
        'piezas_por_plancha' => 'integer',
        'stock_minimo_deseado' => 'integer',
        'stock_full' => 'integer',
        'ventas_totales' => 'integer',
        'ventas_totales_reporte_anterior' => 'integer',
        'ventas_30_dias_calculadas' => 'integer',
    ];

    /**
     * Variantes compatibles con este producto (funda de bafle)
     */
    public function variantes(): BelongsToMany
    {
        return $this->belongsToMany(Variante::class, 'producto_variante')
            ->withTimestamps();
    }

    /**
     * Stock Total = Bodega + Enviado Full + Full ML
     */
    public function getStockTotalAttribute(): int
    {
        return ($this->stock_bodega ?? 0)
            + ($this->stock_enviado_full ?? 0)
            + ($this->stock_full ?? 0);
    }

    /**
     * Stock mínimo: Si Carlos lo definió, usarlo. Si no, calcular 2 × piezas_por_plancha
     */
    public function getStockMinimoAttribute(): int
    {
        if ($this->stock_minimo_deseado > 0) {
            return $this->stock_minimo_deseado;
        }
        
        if (!$this->piezas_por_plancha || $this->piezas_por_plancha <= 0) {
            return 0;
        }
        
        return $this->piezas_por_plancha * 2;
    }

/**
 * Calcular recomendación de fabricación
 * 
 * Si usa variante: NO calcular (se calcula en la variante)
 * Si NO usa variante: calcular basado en ventas de 30 días
 */
public function getRecomendacionFabricacionAttribute(): int
{
    // Si usa variante, no calcular aquí (lo hace la variante)
    if ($this->usa_variante_para_fabricacion) {
        return 0;
    }

    // Si no tiene ventas en 30 días, no fabricar
    if (!$this->ventas_30_dias_calculadas || $this->ventas_30_dias_calculadas <= 0) {
        return 0;
    }
    
    // Calcular: (Ventas 30 días × 2) - Stock Total
    // Esto da inventario para 60 días
    $inventarioDeseado = $this->ventas_30_dias_calculadas * 2;
    $faltante = $inventarioDeseado - $this->stock_total;
    
    return max(0, (int) $faltante);
}

    /**
     * Consumo diario promedio
     */
    public function getConsumoDiarioAttribute(): float
    {
        return $this->ventas_30_dias_calculadas 
            ? round($this->ventas_30_dias_calculadas / 30, 2) 
            : 0;
    }

    /**
     * Calcular ventas de últimos 30 días
     */
    public function calcularVentas30Dias(): void
    {
        $ventasActuales = $this->ventas_totales ?? 0;
        $ventasAntes = $this->ventas_totales_reporte_anterior ?? 0;

        $this->ventas_30_dias_calculadas = max(0, $ventasActuales - $ventasAntes);
        $this->save();
    }

    /**
     * Actualizar desde un nuevo reporte
     */
    public function actualizarDesdeReporte(int $nuevasVentasTotales): void
    {
        $this->ventas_totales_reporte_anterior = $this->ventas_totales ?? 0;
        $this->ventas_totales = $nuevasVentasTotales;
        $this->calcularVentas30Dias();
        $this->fecha_ultimo_reporte = now();
        $this->save();

        if ($this->usa_variante_para_fabricacion) {
            foreach ($this->variantes as $variante) {
                $variante->actualizarContadores();
            }
        }
    }

    /**
     * Scopes
     */
    public function scopeSinVariante($query)
    {
        return $query->where('usa_variante_para_fabricacion', false)
            ->orWhereNull('usa_variante_para_fabricacion');
    }

    public function scopeConVariante($query)
    {
        return $query->where('usa_variante_para_fabricacion', true);
    }
}