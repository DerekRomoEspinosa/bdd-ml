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
     * Buscar item ID por código interno ML
     */
    private function buscarItemPorCodigoInterno(string $codigoInterno): ?string
    {
        $token = $this->getToken();
        $sellerId = $this->getSellerId();
        
        if (!$token || !$sellerId) {
            return null;
        }

        try {
            // Buscar en los items del seller por seller_custom_field
            $searchUrl = "{$this->baseUrl}/users/{$sellerId}/items/search";
            
            $response = Http::withToken($token)->get($searchUrl, [
                'seller_custom_field' => $codigoInterno,
                'limit' => 1
            ]);
            
            if ($response->successful()) {
                $results = $response->json()['results'] ?? [];
                
                if (!empty($results)) {
                    $itemId = $results[0];
                    Log::info("[ML Service] ✓ Found item {$itemId} for code {$codigoInterno}");
                    return $itemId;
                }
            }
            
            Log::warning("[ML Service] No item found for code: {$codigoInterno}");
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Error searching by code {$codigoInterno}: " . $e->getMessage());
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
            return ['stock_full' => 0, 'ventas_30_dias' => 0];
        }

        try {
            $itemId = null;
            
            // Si empieza con "MLM" es un ID directo
            if (str_starts_with($identificador, 'MLM')) {
                $itemId = $identificador;
            } 
            // Si es numérico de ~10 dígitos, es código interno
            elseif (is_numeric($identificador) && strlen($identificador) >= 8) {
                $itemId = $this->buscarItemPorCodigoInterno($identificador);
                
                if (!$itemId) {
                    return ['stock_full' => 0, 'ventas_30_dias' => 0];
                }
            }
            // Si no, intentar como código interno de todos modos
            else {
                $itemId = $this->buscarItemPorCodigoInterno($identificador);
                
                if (!$itemId) {
                    return ['stock_full' => 0, 'ventas_30_dias' => 0];
                }
            }
            
            // Obtener datos del item
            Log::info("[ML Service] Fetching data for item: {$itemId}");
            
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
                Log::error("[ML Service] API error for {$itemId}: " . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Exception for {$identificador}: " . $e->getMessage());
        }

        return ['stock_full' => 0, 'ventas_30_dias' => 0];
    }
}