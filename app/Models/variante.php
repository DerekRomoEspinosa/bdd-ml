<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Variante extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'notas',
        'activo',
        'stock_bodega',
        'stock_cortado',
        'stock_costura',
        'stock_por_empacar',
        'stock_enviado_full',
        'ventas_totales',
        'ventas_30_dias',
        'recomendacion_fabricacion',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Productos (fundas de bafle) compatibles con esta variante
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_variante')
            ->withTimestamps();
    }

    /**
     * Calcular ventas totales de todos los productos compatibles
     */
    public function calcularVentasTotales(): int
    {
        return $this->productos()
            ->where('activo', true)
            ->sum('ventas_totales');
    }

    /**
     * Calcular ventas de últimos 30 días de todos los productos compatibles
     */
    public function calcularVentas30Dias(): int
    {
        return $this->productos()
            ->where('activo', true)
            ->sum('ventas_30_dias_calculadas');
    }

    /**
     * Calcular stock total disponible (bodega + proceso)
     */
    public function getStockTotalAttribute(): int
    {
        return $this->stock_bodega 
            + $this->stock_cortado 
            + $this->stock_costura 
            + $this->stock_por_empacar 
            + $this->stock_enviado_full;
    }

    /**
     * Actualizar contadores de la variante basado en sus productos
     */
    public function actualizarContadores(): void
    {
        $this->ventas_totales = $this->calcularVentasTotales();
        $this->ventas_30_dias = $this->calcularVentas30Dias();
        
        // Calcular recomendación de fabricación
        $this->calcularRecomendacionFabricacion();
        
        $this->save();
    }

    /**
     * Calcular cuántas unidades se deben fabricar
     */
    public function calcularRecomendacionFabricacion(): void
    {
        $stockDisponible = $this->stock_total;
        $ventasProyectadas = $this->ventas_30_dias * 2; // Proyección a 60 días
        
        $deficit = $ventasProyectadas - $stockDisponible;
        
        $this->recomendacion_fabricacion = max(0, $deficit);
    }

    /**
     * Scope para variantes activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para variantes que necesitan fabricación
     */
    public function scopeNecesitanFabricacion($query)
    {
        return $query->where('recomendacion_fabricacion', '>', 0);
    }
}