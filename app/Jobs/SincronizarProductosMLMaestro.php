<?php

namespace App\Jobs;

use App\Models\Producto;
use App\Models\SyncProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SincronizarProductosMLMaestro implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 1;
    
    protected string $sessionId;

    public function __construct(?string $sessionId = null)
    {
        $this->sessionId = $sessionId ?? 'ml_sync_' . now()->format('YmdHis_u');
    }

    public function handle()
    {
        Log::info("🎯 INICIANDO SINCRONIZACIÓN MASIVA ML", [
            'session_id' => $this->sessionId,
            'timestamp' => now()->toDateTimeString()
        ]);

        // Obtener productos activos con código ML
        $productos = Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->select('id', 'sku_ml', 'nombre')
            ->get();

        $totalProductos = $productos->count();
        
        if ($totalProductos === 0) {
            Log::warning("⚠️ No hay productos para sincronizar");
            return;
        }

        // Crear registro de progreso
        $progress = SyncProgress::create([
            'session_id' => $this->sessionId,
            'total' => $totalProductos,
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'is_complete' => false,
            'started_at' => now(),
        ]);

        Log::info("📊 Total de productos a sincronizar: {$totalProductos}");

        // Dividir en lotes de 25
        $loteSize = 25;
        $lotes = $productos->chunk($loteSize);
        $numeroLote = 0;

        foreach ($lotes as $lote) {
            $numeroLote++;
            $ids = $lote->pluck('id')->toArray();

            // Despachar job para este lote
            SincronizarLoteMLJob::dispatch($ids, $this->sessionId, $numeroLote);

            Log::info("📦 Lote #{$numeroLote} encolado", [
                'productos' => count($ids),
                'sku_ejemplo' => $lote->first()->sku_ml ?? 'N/A'
            ]);
        }

        Log::info("✅ SINCRONIZACIÓN INICIADA - {$numeroLote} lotes creados", [
            'session_id' => $this->sessionId,
            'total_productos' => $totalProductos,
            'total_lotes' => $numeroLote,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error("💥 JOB MAESTRO FALLÓ", [
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
        ]);

        // Marcar progreso como fallido
        SyncProgress::where('session_id', $this->sessionId)->update([
            'is_complete' => true,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);
    }
    
    /**
     * Obtener el session ID
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
}