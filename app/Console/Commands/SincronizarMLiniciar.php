<?php

namespace App\Console\Commands;

use App\Jobs\SincronizarProductosMLMaestro;
use Illuminate\Console\Command;

/**
 * Comando para iniciar la sincronización masiva con ML
 * 
 * Uso: php artisan ml:sync:iniciar
 */
class SincronizarMLIniciar extends Command
{
    protected $signature = 'ml:sync:iniciar
                          {--force : Forzar sincronización sin confirmación}';

    protected $description = 'Inicia la sincronización masiva de productos con Mercado Libre';

    public function handle()
    {
        $this->info('🚀 Sistema de Sincronización con Mercado Libre');
        $this->newLine();

        // Contar productos
        $totalProductos = \App\Models\Producto::where('activo', true)->count();

        if ($totalProductos === 0) {
            $this->error('❌ No hay productos activos para sincronizar');
            return 1;
        }

        $this->info("📊 Productos a sincronizar: {$totalProductos}");
        $this->info("📦 Se crearán " . ceil($totalProductos / 25) . " lotes de 25 productos");
        $this->info("⏱️  Tiempo estimado: " . ceil($totalProductos * 0.5 / 60) . " minutos");
        $this->newLine();

        // Confirmar
        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas continuar?', true)) {
                $this->warn('Sincronización cancelada');
                return 0;
            }
        }

        // Despachar job maestro
        $this->info('🎯 Despachando job maestro...');
        SincronizarProductosMLMaestro::dispatch();

        $this->newLine();
        $this->info('✅ Sincronización iniciada correctamente');
        $this->newLine();
        
        $this->comment('Para monitorear el progreso:');
        $this->line('  → Ver logs: tail -f storage/logs/laravel.log');
        $this->line('  → Ver cola: php artisan queue:work ml-sync --verbose');
        
        $this->newLine();
        $this->warn('⚠️  NO OLVIDES ejecutar el worker:');
        $this->line('  php artisan queue:work --queue=ml-sync --verbose');

        return 0;
    }
}