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
     * Productos asociados
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_variante')
            ->withTimestamps();
    }

    /**
     * Atributos calculados (Accessors)
     */
    public function getStockTotalAttribute(): int
    {
        return (int) $this->productos->sum(function ($producto) {
            return ($producto->stock_bodega ?? 0) 
                + ($producto->stock_enviado_full ?? 0) 
                + ($producto->stock_full ?? 0);
        });
    }

    public function getVentas30DiasAttribute(): int
    {
        // Nota: Asegúrate que el modelo Producto tenga este campo o lógica similar
        return (int) $this->productos->sum('ventas_30_dias_calculadas');
    }

    public function getRecomendacionFabricacionAttribute(): int
    {
        $ventas30 = $this->ventas_30_dias;
        if ($ventas30 <= 0) return 0;

        $objetivo = $ventas30 * 2; 
        return (int) max(0, $objetivo - $this->stock_total);
    }
}