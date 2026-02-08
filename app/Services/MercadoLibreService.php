<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoLibreService
{
    private string $baseUrl = 'https://api.mercadolibre.com';

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
                'refresh_token' => $data['refresh_token'],
                'updated_at' => now(),
            ]);
            return $data['access_token'];
        }
        return null;
    }

    /**
     * Sincronizar producto buscando por SKU
     */
    public function sincronizarProductoPorSKU(string $sku): array
    {
        $token = $this->getToken();
        if (!$token) {
            Log::error('[ML Service] No token available');
            return ['stock_full' => 0, 'ventas_30_dias' => 0];
        }

        try {
            // Obtener el seller_id (tu user ID de ML)
            $meResponse = Http::withToken($token)->get("{$this->baseUrl}/users/me");
            
            if (!$meResponse->successful()) {
                Log::error('[ML Service] Cannot get user info');
                return ['stock_full' => 0, 'ventas_30_dias' => 0];
            }
            
            $sellerId = $meResponse->json()['id'];
            
            // Buscar items por SKU
            $searchUrl = "{$this->baseUrl}/users/{$sellerId}/items/search";
            $searchResponse = Http::withToken($token)->get($searchUrl, [
                'seller_custom_field' => $sku, // Buscar por SKU
                'limit' => 1
            ]);
            
            if (!$searchResponse->successful() || empty($searchResponse->json()['results'])) {
                Log::warning("[ML Service] No item found for SKU: {$sku}");
                return ['stock_full' => 0, 'ventas_30_dias' => 0];
            }
            
            $itemId = $searchResponse->json()['results'][0];
            
            // Obtener detalles del item
            return $this->sincronizarProducto($itemId);
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Error sync by SKU {$sku}: " . $e->getMessage());
        }

        return ['stock_full' => 0, 'ventas_30_dias' => 0];
    }

    /**
     * Sincronizar producto por ID directo de ML
     */
    public function sincronizarProducto(string $itemId): array
    {
        $token = $this->getToken();
        if (!$token) return ['stock_full' => 0, 'ventas_30_dias' => 0];

        try {
            $response = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error Sync ML: " . $e->getMessage());
        }

        return ['stock_full' => 0, 'ventas_30_dias' => 0];
    }
}