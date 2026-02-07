<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MLAuthController extends Controller
{
    public function redirectToML()
    {
        $appId = env('ML_CLIENT_ID');
        $redirectUri = env('ML_REDIRECT_URI');
        
        \Log::info('ML Redirect', [
            'app_id' => $appId,
            'redirect_uri' => $redirectUri
        ]);
        
        if (empty($appId) || empty($redirectUri)) {
            return back()->with('error', '❌ Configuración ML incompleta');
        }
        
        // Generar state para seguridad
        $state = Str::random(40);
        session(['ml_oauth_state' => $state]);
        
        // Construir URL con todos los parámetros
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
        
        $url = "https://auth.mercadolibre.com.mx/authorization?" . $params;
        
        \Log::info('Redirecting to ML', ['url' => $url]);
        
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        
        \Log::info('ML Callback', [
            'has_code' => !empty($code),
            'has_state' => !empty($state),
            'state_matches' => $state === session('ml_oauth_state')
        ]);

        if (!$code) {
            \Log::error('No code received');
            return redirect()->route('dashboard')
                ->with('error', '❌ No se recibió código de autorización');
        }

        // Verificar state (opcional pero recomendado)
        if ($state && $state !== session('ml_oauth_state')) {
            \Log::error('State mismatch');
            return redirect()->route('dashboard')
                ->with('error', '❌ Error de seguridad (state mismatch)');
        }

        try {
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => env('ML_CLIENT_ID'),
                'client_secret' => env('ML_CLIENT_SECRET'),
                'code' => $code,
                'redirect_uri' => env('ML_REDIRECT_URI'),
            ]);

            \Log::info('Token response', [
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            if (!$response->successful()) {
                \Log::error('Token error', ['body' => $response->body()]);
                return redirect()->route('dashboard')
                    ->with('error', '❌ Error obteniendo token: ' . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                \Log::error('No access token in response');
                return redirect()->route('dashboard')
                    ->with('error', '❌ Token no recibido');
            }
            
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

            \Log::info('✅ Token saved successfully');
            
            // Limpiar state de sesión
            session()->forget('ml_oauth_state');

            return redirect()->route('dashboard')
                ->with('success', '✅ Mercado Libre vinculado correctamente');

        } catch (\Exception $e) {
            \Log::error('Callback exception', ['error' => $e->getMessage()]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}