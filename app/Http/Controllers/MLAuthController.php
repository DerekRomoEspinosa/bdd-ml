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
        // DEBUG: Ver qué valores tiene config
        $appId = config('services.mercadolibre.app_id');
        $redirectUri = config('services.mercadolibre.redirect_uri');
        
        Log::info('=== REDIRECT TO ML DEBUG ===', [
            'app_id' => $appId,
            'redirect_uri' => $redirectUri,
            'env_ML_CLIENT_ID' => env('ML_CLIENT_ID'),
            'env_ML_REDIRECT_URI' => env('ML_REDIRECT_URI'),
            'config_loaded' => config('services.mercadolibre'),
        ]);
        
        // Si no hay app_id, mostrar error
        if (empty($appId)) {
            Log::error('ML App ID is empty!');
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: Configuración de Mercado Libre incompleta. App ID: ' . $appId);
        }
        
        if (empty($redirectUri)) {
            Log::error('ML Redirect URI is empty!');
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: Configuración de Mercado Libre incompleta. Redirect URI vacío.');
        }
        
        $url = "https://auth.mercadolibre.com.mx/authorization?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}";
        
        Log::info('Redirecting to ML', ['url' => $url]);
        
        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        Log::info('=== ML CALLBACK DEBUG ===', [
            'code' => $code ? substr($code, 0, 20) . '...' : 'missing',
            'full_url' => $request->fullUrl(),
            'all_query' => $request->query()
        ]);

        if (!$code) {
            Log::error('ML Callback: No code received');
            return redirect()->route('dashboard')
                ->with('error', '❌ Código no recibido de Mercado Libre.');
        }

        try {
            $clientId = config('services.mercadolibre.app_id');
            $clientSecret = config('services.mercadolibre.secret_key');
            $redirectUri = config('services.mercadolibre.redirect_uri');
            
            Log::info('ML Token Request Config', [
                'has_client_id' => !empty($clientId),
                'has_client_secret' => !empty($clientSecret),
                'redirect_uri' => $redirectUri
            ]);
            
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
                'has_body' => !empty($response->body())
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('ML Token Data', [
                    'has_access_token' => isset($data['access_token']),
                    'has_refresh_token' => isset($data['refresh_token']),
                    'access_token_length' => isset($data['access_token']) ? strlen($data['access_token']) : 0
                ]);
                
                if (!isset($data['access_token'])) {
                    Log::error('ML Token Response missing access_token', ['data' => $data]);
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
                
                Log::info('Saving ML Token to database...');
                
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
                'body' => $response->body()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error al obtener token de ML. Status: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('ML Callback Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}