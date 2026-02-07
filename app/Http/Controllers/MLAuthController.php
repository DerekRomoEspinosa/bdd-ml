<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MLAuthController extends Controller
{
    public function redirectToML()
    {
        // Leer variables directamente
        $appId = env('ML_CLIENT_ID');
        $redirectUri = env('ML_REDIRECT_URI');
        
        // Log para debug
        \Log::info('ML Auth Redirect', [
            'app_id' => $appId,
            'has_app_id' => !empty($appId),
            'redirect_uri' => $redirectUri
        ]);
        
        // Validar que existan
        if (empty($appId)) {
            return back()->with('error', '❌ ML_CLIENT_ID no configurado');
        }
        
        if (empty($redirectUri)) {
            return back()->with('error', '❌ ML_REDIRECT_URI no configurado');
        }
        
        // Construir URL de autorización
        $url = "https://auth.mercadolibre.com.mx/authorization";
        $url .= "?response_type=code";
        $url .= "&client_id=" . $appId;
        $url .= "&redirect_uri=" . urlencode($redirectUri);
        
        \Log::info('Redirecting to', ['url' => $url]);
        
        // Redirigir a ML
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        
        \Log::info('ML Callback', ['has_code' => !empty($code)]);

        if (!$code) {
            return redirect()->route('dashboard')
                ->with('error', '❌ No se recibió código de ML');
        }

        try {
            // Intercambiar código por token
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => env('ML_CLIENT_ID'),
                'client_secret' => env('ML_CLIENT_SECRET'),
                'code' => $code,
                'redirect_uri' => env('ML_REDIRECT_URI'),
            ]);

            if (!$response->successful()) {
                \Log::error('ML Token Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                return redirect()->route('dashboard')
                    ->with('error', '❌ Error obteniendo token: ' . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                return redirect()->route('dashboard')
                    ->with('error', '❌ Token no recibido');
            }
            
            // Guardar en base de datos
            DB::table('mercadolibre_tokens')->updateOrInsert(
                ['id' => 1],
                [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? '',
                    'expires_in' => $data['expires_in'] ?? 21600,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            \Log::info('✅ Token guardado');

            return redirect()->route('dashboard')
                ->with('success', '✅ Mercado Libre vinculado correctamente');

        } catch (\Exception $e) {
            \Log::error('ML Callback Exception', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}