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
            'client_id' => config('services.mercadolibre.app_id'),
            'client_secret' => config('services.mercadolibre.secret_key'),
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