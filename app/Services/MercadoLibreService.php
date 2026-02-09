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
                'expires_at' => now()->addHours(6),
            ]);
            return $data['access_token'];
        }
        
        Log::error('[ML Service] Refresh token failed: ' . $response->status());
        return null;
    }

    public function sincronizarProducto(string $identificador): array
    {
        $token = $this->getToken();
        if (!$token) {
            Log::error('[ML Service] No token available');
            return [
                'stock_full' => 0,
                'ventas_30_dias' => 0,
                'sincronizado_en' => now(),
                'status' => 'error'
            ];
        }

        try {
            $itemId = str_starts_with(strtoupper($identificador), 'MLM') 
                ? strtoupper($identificador) 
                : 'MLM' . $identificador;
            
            Log::info("[ML Service] Consultando item: {$itemId}");
            $response = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                    'status' => $data['status'] ?? 'unknown',
                ];
                
                Log::info("[ML Service] ✓ {$itemId} - Status: {$result['status']}, Stock: {$result['stock_full']}, Ventas: {$result['ventas_30_dias']}");
                
                return $result;
            } else {
                Log::error("[ML Service] API error para {$itemId}: " . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Exception para {$identificador}: " . $e->getMessage());
        }

        return [
            'stock_full' => 0,
            'ventas_30_dias' => 0,
            'sincronizado_en' => now(),
            'status' => 'error'
        ];
    }
}