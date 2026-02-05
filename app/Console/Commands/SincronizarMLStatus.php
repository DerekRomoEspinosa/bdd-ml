<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Comando para ver el estado de la cola de sincronización
 * 
 * Uso: php artisan ml:sync:status
 */
class SincronizarMLStatus extends Command
{
    protected $signature = 'ml:sync:status';

    protected $description = 'Muestra el estado actual de la cola de sincronización ML';

    public function handle()
    {
        $this->info('📊 Estado de la Cola de Sincronización ML');
        $this->newLine();

        // Jobs pendientes
        $jobsPendientes = DB::table('jobs')
            ->where('queue', 'ml-sync')
            ->count();

        // Jobs fallidos
        $jobsFallidos = DB::table('failed_jobs')
            ->whereDate('failed_at', today())
            ->count();

        // Total de jobs fallidos históricos
        $jobsfallidosTotal = DB::table('failed_jobs')->count();

        // Productos activos
        $productosActivos = DB::table('productos')
            ->where('activo', true)
            ->count();

        // Productos sincronizados hoy
        $sincronizadosHoy = DB::table('productos')
            ->where('activo', true)
            ->whereDate('ml_ultimo_sync', today())
            ->count();

        // Mostrar tabla
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Jobs en cola (ml-sync)', $jobsPendientes],
                ['Jobs fallidos hoy', $jobsFallidos],
                ['Jobs fallidos (histórico)', $jobsfallidosTotal],
                ['Productos activos', $productosActivos],
                ['Sincronizados hoy', $sincronizadosHoy],
            ]
        );

        // Alertas
        if ($jobsPendientes > 100) {
            $this->warn("⚠️  Hay muchos jobs en cola. Considera ejecutar más workers.");
        }

        if ($jobsFallidos > 10) {
            $this->error("❌ Hay muchos jobs fallidos hoy. Revisa los logs.");
            $this->line("   Ver: tail -f storage/logs/laravel.log");
        }

        if ($jobsPendientes === 0 && $sincronizadosHoy === 0) {
            $this->comment("💡 Para iniciar una sincronización: php artisan ml:sync:iniciar");
        }

        return 0;
    }
}