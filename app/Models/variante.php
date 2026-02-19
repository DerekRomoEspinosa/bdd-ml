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

    /*
    |--------------------------------------------------------------------------
    | RELACIÓN
    |--------------------------------------------------------------------------
    */

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Producto::class, 'producto_variante')
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    // 🔥 Ventas totales (lo que usa tu vista)
    public function getVentasTotalesAttribute(): int
    {
        return (int) $this->productos->sum(function ($producto) {
            return $producto->ventas_totales ?? 0;
        });
    }

    // 🔥 Stock total (lo que usa tu vista)
    public function getStockTotalAttribute(): int
    {
        return (int) $this->productos->sum(function ($producto) {
            return $producto->stock_actual ?? 0;
        });
    }

    // 🔥 Recomendación fabricar
    public function getRecomendacionFabricacionAttribute(): int
    {
        $ventas = $this->ventas_totales;
        $stock = $this->stock_total;

        $faltante = $ventas - $stock;

        return $faltante > 0 ? $faltante : 0;
    }
}
