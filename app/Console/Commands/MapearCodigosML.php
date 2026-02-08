<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;

class MapearCodigosML extends Command
{
    protected $signature = 'ml:mapear-codigos {--limit=50 : Número máximo de items a procesar}';
    protected $description = 'Mapea automáticamente los códigos internos de Mercado Libre a los productos';

    public function handle()
    {
        $this->info('🚀 Iniciando mapeo de códigos internos de Mercado Libre...');
        $this->newLine();

        // Verificar token
        $tokenData = DB::table('mercadolibre_tokens')->find(1);
        
        if (!$tokenData) {
            $this->error('❌ No hay token de Mercado Libre. Vincula tu cuenta primero.');
            return 1;
        }

        $token = $tokenData->access_token;

        try {
            // Obtener seller ID
            $this->info('📋 Obteniendo información del vendedor...');
            $meResponse = Http::withToken($token)->get('https://api.mercadolibre.com/users/me');
            
            if (!$meResponse->successful()) {
                $this->error('❌ No se pudo obtener información del vendedor');
                return 1;
            }

            $sellerId = $meResponse->json()['id'];
            $this->info("✓ Seller ID: {$sellerId}");
            $this->newLine();

            // Obtener TODOS los items del seller
            $this->info('📦 Obteniendo tus publicaciones de Mercado Libre...');
            
            $allItems = [];
            $offset = 0;
            $limit = 50;
            $maxItems = $this->option('limit');

            $bar = $this->output->createProgressBar();
            $bar->setFormat('Descargando: %current% items');

            do {
                $searchResponse = Http::withToken($token)->get("https://api.mercadolibre.com/users/{$sellerId}/items/search", [
                    'offset' => $offset,
                    'limit' => $limit,
                    'status' => 'active' // Solo activos
                ]);

                if (!$searchResponse->successful()) {
                    break;
                }

                $results = $searchResponse->json()['results'] ?? [];
                $allItems = array_merge($allItems, $results);
                
                $bar->advance(count($results));
                
                $offset += $limit;
                
                // Limitar si se especificó
                if ($maxItems && count($allItems) >= $maxItems) {
                    $allItems = array_slice($allItems, 0, $maxItems);
                    break;
                }
                
                // Pausa para no saturar la API
                usleep(300000); // 300ms
                
            } while (count($results) > 0);

            $bar->finish();
            $this->newLine(2);

            $totalItems = count($allItems);
            $this->info("✓ Se encontraron {$totalItems} publicaciones activas");
            $this->newLine();

            // Procesar cada item
            $this->info('🔍 Mapeando códigos internos...');
            $this->newLine();

            $mapeados = 0;
            $sinCodigo = 0;
            $noEncontrados = 0;

            $progressBar = $this->output->createProgressBar($totalItems);
            $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
            $progressBar->setMessage('Iniciando...');

            foreach ($allItems as $itemId) {
                try {
                    // Obtener detalles del item
                    $itemResponse = Http::withToken($token)->get("https://api.mercadolibre.com/items/{$itemId}");
                    
                    if (!$itemResponse->successful()) {
                        $progressBar->setMessage("Error en {$itemId}");
                        $progressBar->advance();
                        continue;
                    }

                    $item = $itemResponse->json();
                    $codigoInterno = $item['seller_custom_field'] ?? null;
                    $sku = $item['seller_custom_field'] ?? null; // ML usa el mismo campo

                    if (!$codigoInterno) {
                        $sinCodigo++;
                        $progressBar->setMessage("Sin código: {$itemId}");
                        $progressBar->advance();
                        continue;
                    }

                    // Buscar producto por SKU_ML que coincida con el seller_custom_field
                    // O intentar buscar por nombre similar
                    $producto = Producto::where('sku_ml', $codigoInterno)
                        ->orWhere('codigo_interno_ml', $codigoInterno)
                        ->first();

                    if ($producto) {
                        // Actualizar código interno y otros datos
                        $producto->update([
                            'codigo_interno_ml' => $codigoInterno,
                            'stock_full' => $item['available_quantity'] ?? 0,
                            'ventas_30_dias' => $item['sold_quantity'] ?? 0,
                            'ml_ultimo_sync' => now(),
                        ]);

                        $mapeados++;
                        $progressBar->setMessage("✓ Mapeado: {$producto->nombre}");
                    } else {
                        $noEncontrados++;
                        $progressBar->setMessage("No encontrado: {$codigoInterno}");
                    }

                    $progressBar->advance();
                    
                    // Pausa entre requests
                    usleep(200000); // 200ms

                } catch (\Exception $e) {
                    $progressBar->setMessage("Error: " . $e->getMessage());
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            // Resumen
            $this->info('═══════════════════════════════════════════');
            $this->info('               RESUMEN                     ');
            $this->info('═══════════════════════════════════════════');
            $this->line("Total publicaciones: <fg=cyan>{$totalItems}</>");
            $this->line("✓ Mapeados correctamente: <fg=green>{$mapeados}</>");
            $this->line("⚠ Sin código interno en ML: <fg=yellow>{$sinCodigo}</>");
            $this->line("❌ No encontrados en DB: <fg=red>{$noEncontrados}</>");
            $this->info('═══════════════════════════════════════════');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}