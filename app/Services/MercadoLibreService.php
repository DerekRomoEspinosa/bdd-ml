<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoLibreService
{
    private string $baseUrl = 'https://api.mercadolibre.com';
    private ?string $accessToken;

    public function __construct()
    {
        // Importante: jalar el token desde la config corregida
        $this->accessToken = config('services.mercadolibre.token');
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function makeRequest(string $method, string $url, array $options = [])
    {
        $request = Http::withHeaders($this->getHeaders())->timeout(15);

        if ($this->accessToken) {
            $request->withToken($this->accessToken);
        }

        return $request->$method($url, $options);
    }

    public function sincronizarProducto(string $itemId): array
    {
        try {
            $response = $this->makeRequest('get', "{$this->baseUrl}/items/{$itemId}");

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'stock_full' => $data['available_quantity'] ?? 0,
                    'ventas_30_dias' => $data['sold_quantity'] ?? 0,
                    'sincronizado_en' => now(),
                ];
            }

            Log::error("ML API Error: " . $response->status() . " para el item " . $itemId);
        } catch (\Exception $e) {
            Log::error("Excepción en ML Service: " . $e->getMessage());
        }

        // Si falla, retornamos valores en 0 para no romper la vista
        return [
            'stock_full' => 0,
            'ventas_30_dias' => 0,
            'sincronizado_en' => now(),
        ];
    }
}