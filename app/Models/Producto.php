<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Producto extends Model
{
    protected $fillable = [
        // ... tus campos existentes ...
        'nombre',
        'sku_ml',
        'codigo_interno_ml',
        'stock_bodega',
        'stock_cortado',
        'stock_costura',
        'stock_por_empacar',
        'stock_enviado_full',
        'stock_full',
        'ventas_totales', // ← RENOMBRADO (era ventas_30_dias)
        'ventas_totales_reporte_anterior', // ← NUEVO
        'ventas_30_dias_calculadas', // ← NUEVO
        'fecha_ultimo_reporte', // ← NUEVO
        'usa_variante_para_fabricacion', // ← NUEVO
        'ml_ultimo_sync',
        'ml_published_at',
        'activo',
        'recomendacion_fabricacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'usa_variante_para_fabricacion' => 'boolean',
        'ml_ultimo_sync' => 'datetime',
        'ml_published_at' => 'datetime',
        'fecha_ultimo_reporte' => 'datetime',
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
     * Calcular ventas de últimos 30 días
     * (Ventas actuales - Ventas reporte anterior)
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
        // Guardar ventas actuales como reporte anterior
        $this->ventas_totales_reporte_anterior = $this->ventas_totales ?? 0;
        
        // Actualizar ventas totales
        $this->ventas_totales = $nuevasVentasTotales;
        
        // Calcular diferencia (ventas últimos 30 días)
        $this->calcularVentas30Dias();
        
        // Marcar fecha del reporte
        $this->fecha_ultimo_reporte = now();
        
        $this->save();
        
        // Si tiene variantes, actualizar contadores de variantes
        if ($this->usa_variante_para_fabricacion) {
            foreach ($this->variantes as $variante) {
                $variante->actualizarContadores();
            }
        }
    }

    /**
     * Calcular stock total del producto
     */
    public function getStockTotalAttribute(): int
    {
        return ($this->stock_bodega ?? 0)
            + ($this->stock_cortado ?? 0)
            + ($this->stock_costura ?? 0)
            + ($this->stock_por_empacar ?? 0)
            + ($this->stock_enviado_full ?? 0);
    }

    /**
     * Calcular recomendación de fabricación
     * 
     * Si usa variante: NO calcular (se calcula en la variante)
     * Si NO usa variante: calcular basado en sus propias ventas
     */
    public function calcularRecomendacionFabricacion(): void
    {
        if ($this->usa_variante_para_fabricacion) {
            // No calcular, lo hace la variante
            $this->recomendacion_fabricacion = 0;
            $this->save();
            return;
        }

        $stockDisponible = $this->stock_total;
        $ventasProyectadas = ($this->ventas_30_dias_calculadas ?? 0) * 2; // 60 días
        
        $deficit = $ventasProyectadas - $stockDisponible;
        
        $this->recomendacion_fabricacion = max(0, $deficit);
        $this->save();
    }

    /**
     * Scope: Productos que NO usan variante
     */
    public function scopeSinVariante($query)
    {
        return $query->where('usa_variante_para_fabricacion', false);
    }

    /**
     * Scope: Productos que SÍ usan variante
     */
    public function scopeConVariante($query)
    {
        return $query->where('usa_variante_para_fabricacion', true);
    }
}