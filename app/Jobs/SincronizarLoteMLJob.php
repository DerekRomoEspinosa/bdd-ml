<?php

namespace App\Jobs;

use App\Models\Producto;
use App\Models\SyncProgress;
use App\Services\MercadoLibreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SincronizarLoteMLJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    public $tries = 3;
    public $backoff = [60, 300, 900];

    protected array $productosIds;
    protected string $sessionId;
    protected int $numeroLote;

    public function __construct(array $productosIds, string $sessionId, int $numeroLote)
    {
        $this->productosIds = $productosIds;
        $this->sessionId = $sessionId;
        $this->numeroLote = $numeroLote;
    }

    public function handle(MercadoLibreService $mlService)
    {
        $inicioLote = now();
        
        Log::info("🔄 Procesando Lote #{$this->numeroLote}", [
            'session_id' => $this->sessionId,
            'productos_en_lote' => count($this->productosIds),
            'intento' => $this->attempts()
        ]);

        $productos = Producto::whereIn('id', $this->productosIds)
            ->where('activo', true)
            ->get();

        if ($productos->isEmpty()) {
            Log::warning("⚠️ Lote #{$this->numeroLote} sin productos válidos");
            return;
        }

        // Obtener registro de progreso
        $progress = SyncProgress::where('session_id', $this->sessionId)->first();

        foreach ($productos as $index => $producto) {
            $success = false;
            
            try {
                $numero = $index + 1;
                $total = $productos->count();
                Log::debug("  → Sincronizando {$producto->sku_ml} ({$numero}/{$total})");

                $datos = $mlService->sincronizarProducto($producto->codigo_interno_ml);

                $producto->update([
                    'stock_full' => $datos['stock_full'],
                    'ventas_totales' => $datos['ventas_totales'],
                    'ml_published_at' => $datos['ml_published_at'] ?? null,
                    'ml_ultimo_sync' => $datos['sincronizado_en'],
                ]);
                
                $success = true;

            } catch (\Exception $e) {
                Log::error("  ✗ Error en {$producto->sku_ml}: " . $e->getMessage());
            }

            // Actualizar progreso
            if ($progress) {
                $progress->incrementProcessed($success);
            }

            usleep(250000); // 250ms entre productos
        }

        $tiempoTotal = $inicioLote->diffInSeconds(now());

        Log::info("✅ Lote #{$this->numeroLote} COMPLETADO", [
            'session_id' => $this->sessionId,
            'total' => $productos->count(),
            'tiempo_segundos' => $tiempoTotal,
        ]);

        unset($productos);
        gc_collect_cycles();
    }

    public function failed(\Throwable $exception)
    {
        Log::error("💥 Lote #{$this->numeroLote} FALLÓ", [
            'session_id' => $this->sessionId,
            'productos_ids' => $this->productosIds,
            'error' => $exception->getMessage(),
        ]);

        // Marcar productos como fallidos en el progreso
        $progress = SyncProgress::where('session_id', $this->sessionId)->first();
        if ($progress) {
            $progress->increment('failed', count($this->productosIds));
            $progress->increment('processed', count($this->productosIds));
            
            if ($progress->processed >= $progress->total) {
                $progress->update([
                    'is_complete' => true,
                    'completed_at' => now(),
                ]);
            }
        }
    }
}