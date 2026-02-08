<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoLibreService
{
    private string $baseUrl = 'https://api.mercadolibre.com';
    private ?int $sellerId = null;

    private function getToken()
    {
        $tokenData = DB::table('mercadolibre_tokens')->find(1);
        if (!$tokenData) return null;

        // Si el token tiene más de 5 horas, lo refrescamos (duran 6)
        if (now()->diffInHours($tokenData->updated_at) >= 5) {
            return $this->refreshToken($tokenData->refresh_token);
        }

        return $tokenData->access_token;
    }

    private function refreshToken($refreshToken)
    {
        if (empty($refreshToken)) {
            Log::error('[ML Service] No refresh token available');
            return null;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/oauth/token", [
            'grant_type' => 'refresh_token',
            'client_id' => env('ML_CLIENT_ID'),
            'client_secret' => env('ML_CLIENT_SECRET'),
            'refresh_token' => $refreshToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            DB::table('mercadolibre_tokens')->where('id', 1)->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                'updated_at' => now(),
            ]);
            return $data['access_token'];
        }
        
        Log::error('[ML Service] Refresh token failed: ' . $response->status());
        return null;
    }

    private function getSellerId()
    {
        if ($this->sellerId) {
            return $this->sellerId;
        }

        $token = $this->getToken();
        if (!$token) return null;

        try {
            $response = Http::withToken($token)->get("{$this->baseUrl}/users/me");
            
            if ($response->successful()) {
                $this->sellerId = $response->json()['id'];
                return $this->sellerId;
            }
        } catch (\Exception $e) {
            Log::error('[ML Service] Error getting seller ID: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * 🔥 NUEVA ESTRATEGIA: Buscar item ID usando múltiples métodos
     */
    private function buscarItemPorCodigoInterno(string $codigoInterno): ?string
    {
        $token = $this->getToken();
        $sellerId = $this->getSellerId();
        
        if (!$token || !$sellerId) {
            Log::error('[ML Service] No token or seller ID available');
            return null;
        }

        try {
            // 🎯 ESTRATEGIA 1: Buscar en todos los items del seller y filtrar localmente
            Log::info("[ML Service] Buscando item con código interno: {$codigoInterno}");
            
            $offset = 0;
            $limit = 50; // ML permite hasta 50 por request
            $maxPages = 20; // Buscar hasta 1000 items (50 * 20)
            
            for ($page = 0; $page < $maxPages; $page++) {
                $response = Http::withToken($token)->get("{$this->baseUrl}/users/{$sellerId}/items/search", [
                    'offset' => $offset,
                    'limit' => $limit,
                    'status' => 'active', // Solo items activos
                ]);
                
                if (!$response->successful()) {
                    Log::error("[ML Service] Error fetching items page {$page}: " . $response->status());
                    break;
                }
                
                $data = $response->json();
                $itemIds = $data['results'] ?? [];
                
                if (empty($itemIds)) {
                    Log::info("[ML Service] No more items found at page {$page}");
                    break;
                }
                
                // Obtener detalles de cada item en este lote
                foreach ($itemIds as $itemId) {
                    $itemResponse = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
                    
                    if ($itemResponse->successful()) {
                        $itemData = $itemResponse->json();
                        $sellerCustomField = $itemData['seller_custom_field'] ?? null;
                        
                        // ✅ Comparar código interno
                        if ($sellerCustomField == $codigoInterno) {
                            Log::info("[ML Service] ✓ ENCONTRADO! Item {$itemId} = código {$codigoInterno}");
                            return $itemId;
                        }
                    }
                    
                    // Pausa para no saturar la API
                    usleep(100000); // 100ms entre items
                }
                
                $offset += $limit;
                
                // Si no hay más páginas
                if (count($itemIds) < $limit) {
                    break;
                }
            }
            
            Log::warning("[ML Service] ❌ No se encontró item con código interno: {$codigoInterno}");
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Exception buscando código {$codigoInterno}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Sincronizar producto usando código interno ML o item ID
     * 
     * @param string $identificador Puede ser código interno (ej: 3113495728) o ML ID (ej: MLM123456)
     */
    public function sincronizarProducto(string $identificador): array
    {
        $token = $this->getToken();
        if (!$token) {
            Log::error('[ML Service] No token available');
            return [
                'stock_full' => 0,
                'ventas_30_dias' => 0,
                'sincronizado_en' => now()
            ];
        }

        try {
            $itemId = null;
            
            // Si empieza con "MLM" es un ID directo
            if (str_starts_with(strtoupper($identificador), 'MLM')) {
                $itemId = $identificador;
                Log::info("[ML Service] Usando ML ID directo: {$itemId}");
            } 
            // Si no, buscar por código interno
            else {
                Log::info("[ML Service] Buscando por código interno: {$identificador}");
                $itemId = $this->buscarItemPorCodigoInterno($identificador);
                
                if (!$itemId) {
                    Log::warning("[ML Service] No se pudo encontrar item con código: {$identificador}");
                    return [
                        'stock_full' => 0,
                        'ventas_30_dias' => 0,
                        'sincronizado_en' => now()
                    ];
                }
            }
            
            // Obtener datos del item
            Log::info("[ML Service] Obteniendo datos del item: {$itemId}");
            
            $response = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                ];
                
                Log::info("[ML Service] ✓ {$itemId} - Stock: {$result['stock_full']}, Ventas: {$result['ventas_30_dias']}");
                
                return $result;
            } else {
                Log::error("[ML Service] API error para {$itemId}: " . $response->status() . " - " . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Exception para {$identificador}: " . $e->getMessage());
            Log::error("[ML Service] Trace: " . $e->getTraceAsString());
        }

        return [
            'stock_full' => 0,
            'ventas_30_dias' => 0,
            'sincronizado_en' => now()
        ];
    }
}