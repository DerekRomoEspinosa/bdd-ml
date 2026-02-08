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
        
        if (!$tokenData) {
            Log::warning('[ML Service] No hay token guardado');
            return null;
        }

        // Si el token está por vencer (menos de 1 hora restante), refrescarlo
        if ($tokenData->expires_at && now()->diffInMinutes($tokenData->expires_at) < 60) {
            Log::info('[ML Service] Token por vencer, refrescando...');
            return $this->refreshToken($tokenData->refresh_token);
        }

        return $tokenData->access_token;
    }

    private function refreshToken($refreshToken)
    {
        try {
            Log::info('[ML Service] Refrescando token...');
            
            $response = Http::timeout(30)
                ->asForm()
                ->post("{$this->baseUrl}/oauth/token", [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('services.mercadolibre.app_id'),
                    'client_secret' => config('services.mercadolibre.secret_key'),
                    'refresh_token' => $refreshToken,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $expiresAt = now()->addSeconds($data['expires_in'] ?? 21600);
                
                DB::table('mercadolibre_tokens')->where('id', 1)->update([
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                    'expires_in' => $data['expires_in'] ?? 21600,
                    'expires_at' => $expiresAt,
                    'updated_at' => now(),
                ]);
                
                Log::info('[ML Service] Token refrescado exitosamente');
                
                return $data['access_token'];
            }
            
            Log::error('[ML Service] Error al refrescar token', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ML Service] Excepción al refrescar token: ' . $e->getMessage());
        }
        
        return null;
    }

    public function sincronizarProducto(string $itemId): array
    {
        $token = $this->getToken();
        
        if (!$token) {
            Log::error('[ML Service] No hay token disponible para sincronizar');
            return ['stock_full' => 0, 'ventas_30_dias' => 0, 'sincronizado_en' => now()];
        }

        try {
            Log::info("[ML Service] Consultando item: {$itemId}");
            
            $response = Http::timeout(30)
                ->withToken($token)
                ->get("{$this->baseUrl}/items/{$itemId}");

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info("[ML Service] Item {$itemId} - Stock: {$data['available_quantity']}, Ventas: {$data['sold_quantity']}");
                
                return [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                ];
            }
            
            Log::error("[ML Service] Error al consultar item {$itemId}", [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
        } catch (\Exception $e) {
            Log::error("[ML Service] Excepción al sincronizar {$itemId}: " . $e->getMessage());
        }

        return ['stock_full' => 0, 'ventas_30_dias' => 0, 'sincronizado_en' => now()];
    }
}