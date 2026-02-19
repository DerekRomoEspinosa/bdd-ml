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

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Producto::class, 'producto_variante')
            ->withTimestamps();
    }

    public function getVentasTotalesAttribute(): int
    {
        if (!$this->relationLoaded('productos')) {
            $this->load('productos');
        }

        return (int) $this->productos->sum(fn($producto) =>
            $producto->ventas_totales ?? 0
        );
    }

    public function getStockTotalAttribute(): int
    {
        if (!$this->relationLoaded('productos')) {
            $this->load('productos');
        }

        return (int) $this->productos->sum(fn($producto) =>
            $producto->stock_actual ?? 0
        );
    }

    public function getRecomendacionFabricacionAttribute(): int
    {
        $faltante = $this->ventas_totales - $this->stock_total;
        return $faltante > 0 ? $faltante : 0;
    }
}
