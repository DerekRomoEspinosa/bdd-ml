<?php

namespace App\Jobs;

use App\Models\Producto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job Maestro SIMPLIFICADO - Sin Cache
 */
class SincronizarProductosMLMaestro implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 1;

    public function handle()
    {
        $sessionId = 'ml_sync_' . now()->format('YmdHis');
        
        Log::info("🎯 INICIANDO SINCRONIZACIÓN MASIVA ML", [
            'session_id' => $sessionId,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Obtener productos activos
        $productosActivos = Producto::where('activo', true)
            ->select('id', 'sku_ml', 'nombre')
            ->get();

        $totalProductos = $productosActivos->count();
        
        if ($totalProductos === 0) {
            Log::warning("⚠️ No hay productos activos para sincronizar");
            return;
        }

        Log::info("📊 Total de productos a sincronizar: {$totalProductos}");

        // Dividir en lotes de 25
        $loteSize = 25;
        $lotes = $productosActivos->chunk($loteSize);
        $numeroLote = 0;

        foreach ($lotes as $lote) {
            $numeroLote++;
            $ids = $lote->pluck('id')->toArray();

            // Despachar job para este lote
            SincronizarLoteMLJob::dispatch($ids, $sessionId, $numeroLote)
                ->onQueue('ml-sync');

            Log::info("📦 Lote #{$numeroLote} encolado", [
                'productos' => count($ids),
                'sku_ejemplo' => $lote->first()->sku_ml ?? 'N/A'
            ]);
        }

        Log::info("✅ SINCRONIZACIÓN INICIADA - {$numeroLote} lotes creados", [
            'session_id' => $sessionId,
            'total_productos' => $totalProductos,
            'total_lotes' => $numeroLote,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("💥 JOB MAESTRO FALLÓ", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}