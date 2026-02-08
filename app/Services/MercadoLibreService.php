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
                'expires_at' => now()->addHours(6),
            ]);
            return $data['access_token'];
        }
        
        Log::error('[ML Service] Refresh token failed: ' . $response->status());
        return null;
    }

    /**
     * ✨ VERSIÓN OPTIMIZADA: Usa ML ID directamente
     * 
     * @param string $identificador Puede ser ML ID completo (MLM3113495728) o código interno
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
            
            // ✅ ESTRATEGIA: Si empieza con "MLM", es el ID directo
            if (str_starts_with(strtoupper($identificador), 'MLM')) {
                $itemId = strtoupper($identificador);
                Log::info("[ML Service] Usando ML ID directo: {$itemId}");
            } else {
                // Si no empieza con MLM, intentar agregar el prefijo
                $itemId = 'MLM' . $identificador;
                Log::info("[ML Service] Intentando con prefijo MLM: {$itemId}");
            }
            
            // Obtener datos del item
            Log::info("[ML Service] Consultando item: {$itemId}");
            $response = Http::withToken($token)->get("{$this->baseUrl}/items/{$itemId}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                $result = [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                ];
                
                Log::info("[ML Service] ✓ {$itemId} - Status: {$data['status']}, Stock: {$result['stock_full']}, Ventas: {$result['ventas_30_dias']}");
                
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