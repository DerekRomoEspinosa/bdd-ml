<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MercadoLibreService
{
    public function sincronizarProducto(string $itemId): array
    {
        Log::info("Obteniendo producto desde ML", ['item_id' => $itemId]);

        $token = DB::table('mercadolibre_tokens')->find(1);

        if (!$token) {
            throw new \Exception('No hay token de ML disponible');
        }

        $response = Http::withToken($token->access_token)
            ->timeout(10)
            ->get("https://api.mercadolibre.com/items/{$itemId}");

        Log::info("Respuesta ML para producto", [
            'status' => $response->status(),
            'successful' => $response->successful()
        ]);

        if (!$response->successful()) {
            Log::warning("ML API Error al obtener producto", [
                'status' => $response->status(),
                'item_id' => $itemId,
                'response' => $response->json()
            ]);

            // Simular datos si API falla (solo en desarrollo)
            if (app()->environment('local')) {
                Log::warning("API de ML bloqueada, usando datos simulados para desarrollo", [
                    'item_id' => $itemId
                ]);

                return [
                    'stock_full' => rand(0, 100),
                    'ventas_totales' => rand(10, 200), // ← CAMBIADO
                    'ml_published_at' => now()->subDays(rand(30, 365)),
                    'sincronizado_en' => now(),
                    'status' => 'active',
                    'modo' => 'simulado'
                ];
            }

            throw new \Exception("Error al obtener producto de ML: " . $response->body());
        }

        $data = $response->json();

        // ✅ OBTENER VENTAS TOTALES (no ventas de 30 días)
        $ventasTotales = $data['sold_quantity'] ?? 0;

        // Stock en Full
        $stockFull = 0;
        if (isset($data['shipping']['logistic_type']) && $data['shipping']['logistic_type'] === 'fulfillment') {
            $stockFull = $data['available_quantity'] ?? 0;
        }

        // Fecha de publicación
        $publishedAt = null;
        if (isset($data['date_created'])) {
            try {
                $publishedAt = \Carbon\Carbon::parse($data['date_created']);
            } catch (\Exception $e) {
                Log::warning("Error parseando fecha de publicación", [
                    'item_id' => $itemId,
                    'date' => $data['date_created']
                ]);
            }
        }

        $resultado = [
            'stock_full' => $stockFull,
            'ventas_totales' => $ventasTotales, // ← CAMBIADO
            'ml_published_at' => $publishedAt,
            'sincronizado_en' => now(),
            'status' => $data['status'] ?? 'unknown',
            'modo' => 'real'
        ];

        Log::info("Producto sincronizado", [
            'item_id' => $itemId,
            'stock' => $stockFull,
            'ventas' => $ventasTotales, // ← CAMBIADO
            'modo' => 'real'
        ]);

        return $resultado;
    }
}