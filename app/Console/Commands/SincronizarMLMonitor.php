<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Comando para monitorear el progreso de la sincronización
 * 
 * Uso: php artisan ml:sync:monitor [session_id]
 */
class SincronizarMLMonitor extends Command
{
    protected $signature = 'ml:sync:monitor
                          {session_id? : ID de sesión a monitorear (opcional)}
                          {--refresh=5 : Segundos entre actualizaciones}';

    protected $description = 'Monitorea el progreso de la sincronización con ML en tiempo real';

    public function handle()
    {
        $sessionId = $this->argument('session_id');
        $refresh = (int) $this->option('refresh');

        if (!$sessionId) {
            // Buscar última sesión
            $sessionId = $this->buscarUltimaSesion();
            
            if (!$sessionId) {
                $this->error('❌ No se encontró ninguna sesión de sincronización activa');
                $this->info('💡 Inicia una con: php artisan ml:sync:iniciar');
                return 1;
            }
            
            $this->info("📡 Monitoreando última sesión: {$sessionId}");
        }

        $this->newLine();
        $this->info('🔄 Actualizando cada ' . $refresh . ' segundos. Presiona Ctrl+C para salir.');
        $this->newLine();

        while (true) {
            // Limpiar pantalla
            if (PHP_OS_FAMILY === 'Windows') {
                system('cls');
            } else {
                system('clear');
            }

            $this->mostrarEstadisticas($sessionId);

            // Verificar si terminó
            $total = Cache::get("{$sessionId}:total", 0);
            $procesados = Cache::get("{$sessionId}:procesados", 0);

            if ($total > 0 && $procesados >= $total) {
                $this->newLine();
                $this->info('🎉 ¡SINCRONIZACIÓN COMPLETADA!');
                break;
            }

            sleep($refresh);
        }

        return 0;
    }

    protected function buscarUltimaSesion()
    {
        // Buscar en cache la última sesión
        $keys = Cache::get('ml_sync_sessions', []);
        return end($keys) ?: null;
    }

    protected function mostrarEstadisticas($sessionId)
    {
        $total = Cache::get("{$sessionId}:total", 0);
        $procesados = Cache::get("{$sessionId}:procesados", 0);
        $exitosos = Cache::get("{$sessionId}:exitosos", 0);
        $fallidos = Cache::get("{$sessionId}:fallidos", 0);
        $iniciado = Cache::get("{$sessionId}:iniciado");

        $this->line('╔════════════════════════════════════════════════════════╗');
        $this->line('║      🔄 SINCRONIZACIÓN MERCADO LIBRE - MONITOR       ║');
        $this->line('╚════════════════════════════════════════════════════════╝');
        $this->newLine();

        // Sesión
        $this->line("📌 Sesión: <fg=cyan>{$sessionId}</>");
        $this->line("🕐 Iniciado: <fg=yellow>{$iniciado}</>");
        $this->newLine();

        // Progreso
        $porcentaje = $total > 0 ? round(($procesados / $total) * 100, 2) : 0;
        $barraLongitud = 40;
        $barraLlena = (int) round(($porcentaje / 100) * $barraLongitud);
        $barraVacia = $barraLongitud - $barraLlena;
        
        $barra = str_repeat('█', $barraLlena) . str_repeat('░', $barraVacia);
        
        $this->line("📊 Progreso: <fg=green>{$barra}</> {$porcentaje}%");
        $this->newLine();

        // Estadísticas
        $this->line("┌─────────────────────────────────────────┐");
        $this->line("│ <fg=white>Total productos:</>    <fg=cyan>" . str_pad($total, 18, ' ', STR_PAD_LEFT) . "</> │");
        $this->line("│ <fg=blue>Procesados:</>         <fg=blue>" . str_pad($procesados, 18, ' ', STR_PAD_LEFT) . "</> │");
        $this->line("│ <fg=green>Exitosos:</>           <fg=green>" . str_pad($exitosos, 18, ' ', STR_PAD_LEFT) . "</> │");
        $this->line("│ <fg=red>Fallidos:</>           <fg=red>" . str_pad($fallidos, 18, ' ', STR_PAD_LEFT) . "</> │");
        $this->line("│ <fg=yellow>Pendientes:</>         <fg=yellow>" . str_pad($total - $procesados, 18, ' ', STR_PAD_LEFT) . "</> │");
        $this->line("└─────────────────────────────────────────┘");
        $this->newLine();

        // Tasa de éxito
        if ($procesados > 0) {
            $tasaExito = round(($exitosos / $procesados) * 100, 2);
            $color = $tasaExito >= 90 ? 'green' : ($tasaExito >= 70 ? 'yellow' : 'red');
            $this->line("✨ Tasa de éxito: <fg={$color}>{$tasaExito}%</>");
        }

        // Jobs en cola
        $jobsPendientes = DB::table('jobs')->where('queue', 'ml-sync')->count();
        $jobsFallidos = DB::table('failed_jobs')->whereDate('failed_at', today())->count();
        
        $this->newLine();
        $this->line("🔧 Jobs en cola: <fg=cyan>{$jobsPendientes}</>");
        if ($jobsFallidos > 0) {
            $this->line("⚠️  Jobs fallidos hoy: <fg=red>{$jobsFallidos}</>");
        }

        // Tiempo estimado
        if ($procesados > 0 && $procesados < $total) {
            $tiempoTranscurrido = now()->diffInSeconds($iniciado);
            $velocidad = $procesados / $tiempoTranscurrido; // productos por segundo
            $pendientes = $total - $procesados;
            $tiempoRestante = (int) ($pendientes / $velocidad);
            
            $minutos = floor($tiempoRestante / 60);
            $segundos = $tiempoRestante % 60;
            
            $this->newLine();
            $this->line("⏱️  Tiempo restante estimado: ~{$minutos}m {$segundos}s");
        }

        $this->newLine();
        $this->line("<fg=gray>Última actualización: " . now()->format('H:i:s') . "</>");
    }
}