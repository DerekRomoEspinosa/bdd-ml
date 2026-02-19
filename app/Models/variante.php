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
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Productos que pertenecen a esta variante
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_variante')
            ->withTimestamps();
    }

    /**
     * Stock total de la variante (suma de todos sus productos)
     */
    public function getStockTotalAttribute(): int
    {
        return $this->productos->sum(function ($producto) {
            return $producto->stock_bodega 
                + $producto->stock_enviado_full 
                + ($producto->stock_full ?? 0);
        });
    }

    /**
     * Ventas totales de la variante (suma de todas las fundas compatibles)
     */
    public function getVentasTotalesAttribute(): int
    {
        return $this->productos->sum('ventas_totales');
    }

    /**
     * Ventas de 30 días de la variante
     */
    public function getVentas30DiasAttribute(): int
    {
        return $this->productos->sum('ventas_30_dias_calculadas');
    }

    /**
     * Consumo diario promedio
     */
    public function getConsumoDiarioAttribute(): float
    {
        $ventas30 = $this->ventas_30_dias;
        return $ventas30 > 0 ? round($ventas30 / 30, 2) : 0;
    }

    /**
     * Recomendación de fabricación
     * 
     * Lógica: (Ventas 30 días × 2) - Stock Total
     */
    public function getRecomendacionFabricacionAttribute(): int
    {
        $ventas30 = $this->ventas_30_dias;
        
        if ($ventas30 <= 0) {
            return 0;
        }
        
        $inventarioDeseado = $ventas30 * 2; // 60 días de inventario
        $faltante = $inventarioDeseado - $this->stock_total;
        
        return max(0, $faltante);
    }

    /**
     * Actualizar contadores (se llama cuando un producto cambia)
     */
    public function actualizarContadores(): void
    {
        // Por ahora no hace nada, los atributos se calculan dinámicamente
        // En el futuro podríamos cachear estos valores si es necesario
    }
}