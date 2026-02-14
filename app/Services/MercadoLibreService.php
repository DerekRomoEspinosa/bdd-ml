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

        // Refrescar token si tiene más de 5 horas de antigüedad
        if (now()->diffInHours($tokenData->updated_at) >= 5) {
            return $this->refreshToken($tokenData->refresh_token);
        }

        return $tokenData->access_token;
    }

    private function refreshToken($refreshToken)
    {
        if (empty($refreshToken)) {
            Log::error('[ML Service] No hay refresh token disponible');
            return null;
        }

        try {
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
        } catch (\Exception $e) {
            Log::error('[ML Service] Excepción en refresh token: ' . $e->getMessage());
        }
        
        return null;
    }

    public function sincronizarProducto(string $identificador): array
    {
        $token = $this->getToken();
        if (!$token) {
            return [
                'stock_full' => 0,
                'ventas_30_dias' => 0,
                'ml_published_at' => null,
                'sincronizado_en' => now(),
                'status' => 'error'
            ];
        }

        try {
            // Limpieza y formateo del ID
            $itemId = trim(strtoupper($identificador));
            if (!str_starts_with($itemId, 'MLM')) {
                $itemId = 'MLM' . $itemId;
            }
            
            $response = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'ml_published_at' => isset($data['date_created']) ? \Carbon\Carbon::parse($data['date_created']) : null,
                    'sincronizado_en' => now(),
                    'status' => 'success',
                ];
            } else {
                Log::error("[ML Service] API error {$itemId}: " . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Exception para {$identificador}: " . $e->getMessage());
        }

        return [
            'stock_full' => 0,
            'ventas_30_dias' => 0,
            'ml_published_at' => null,
            'sincronizado_en' => now(),
            'status' => 'error'
        ];
    }
}