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
        // Leer directamente de env() en lugar de config()
        $appId = env('ML_CLIENT_ID');
        $redirectUri = env('ML_REDIRECT_URI');
        
        Log::info('=== REDIRECT TO ML DEBUG ===', [
            'app_id' => $appId,
            'redirect_uri' => $redirectUri,
        ]);
        
        if (empty($appId)) {
            Log::error('ML App ID is empty!');
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ML_CLIENT_ID no está configurado');
        }
        
        if (empty($redirectUri)) {
            Log::error('ML Redirect URI is empty!');
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ML_REDIRECT_URI no está configurado');
        }
        
        $url = "https://auth.mercadolibre.com.mx/authorization?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}";
        
        Log::info('Redirecting to ML', ['url' => $url]);
        
        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        Log::info('=== ML CALLBACK ===', [
            'has_code' => !empty($code),
        ]);

        if (!$code) {
            Log::error('ML Callback: No code received');
            return redirect()->route('dashboard')
                ->with('error', '❌ Código no recibido de Mercado Libre.');
        }

        try {
            $clientId = env('ML_CLIENT_ID');
            $clientSecret = env('ML_CLIENT_SECRET');
            $redirectUri = env('ML_REDIRECT_URI');
            
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            Log::info('ML Token Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (!isset($data['access_token'])) {
                    Log::error('ML Token Response missing access_token');
                    return redirect()->route('dashboard')
                        ->with('error', '❌ Respuesta inválida de Mercado Libre.');
                }
                
                $tokenData = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? '',
                    'expires_in' => $data['expires_in'] ?? 21600,
                    'updated_at' => now(),
                ];
                
                $exists = DB::table('mercadolibre_tokens')->where('id', 1)->exists();
                if (!$exists) {
                    $tokenData['created_at'] = now();
                }
                
                DB::table('mercadolibre_tokens')->updateOrInsert(
                    ['id' => 1],
                    $tokenData
                );

                Log::info('✅ ML Token saved successfully!');

                return redirect()->route('dashboard')
                    ->with('success', '✅ Mercado Libre vinculado correctamente.');
            }

            Log::error('ML Token request failed', [
                'status' => $response->status(),
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error al obtener token. Status: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('ML Callback Exception', [
                'message' => $e->getMessage(),
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}