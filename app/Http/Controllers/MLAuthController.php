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
        $appId = config('services.mercadolibre.app_id');
        $redirectUri = config('services.mercadolibre.redirect_uri');
        
        Log::info('Redirect to ML', [
            'app_id' => $appId,
            'redirect_uri' => $redirectUri
        ]);
        
        $url = "https://auth.mercadolibre.com.mx/authorization?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}";
        
        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        Log::info('ML Callback received', ['code' => $code ? 'present' : 'missing']);

        if (!$code) {
            Log::error('ML Callback: No code received');
            return redirect()->route('dashboard')->with('error', '❌ Código no recibido de Mercado Libre.');
        }

        try {
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.mercadolibre.app_id'),
                'client_secret' => config('services.mercadolibre.secret_key'),
                'code' => $code,
                'redirect_uri' => config('services.mercadolibre.redirect_uri'),
            ]);

            Log::info('ML Token Response', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Verificar que tengamos al menos el access_token
                if (!isset($data['access_token'])) {
                    Log::error('ML Token Response missing access_token', ['data' => $data]);
                    return redirect()->route('dashboard')->with('error', '❌ Respuesta inválida de Mercado Libre.');
                }
                
                // Preparar datos para guardar
                $tokenData = [
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? '', // Puede venir vacío
                    'expires_in' => $data['expires_in'] ?? 21600, // Default 6 horas
                    'updated_at' => now(),
                ];
                
                // Si no existe el registro, agregar created_at
                $exists = DB::table('mercadolibre_tokens')->where('id', 1)->exists();
                if (!$exists) {
                    $tokenData['created_at'] = now();
                }
                
                Log::info('Saving ML Token', [
                    'has_refresh_token' => !empty($data['refresh_token']),
                    'expires_in' => $tokenData['expires_in']
                ]);
                
                // Guardar en la base de datos
                DB::table('mercadolibre_tokens')->updateOrInsert(
                    ['id' => 1],
                    $tokenData
                );

                Log::info('ML Token saved successfully');

                return redirect()->route('dashboard')->with('success', '✅ Mercado Libre vinculado correctamente.');
            }

            Log::error('ML Token request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
            return redirect()->route('dashboard')->with('error', '❌ Error al obtener token de ML: ' . $response->status());

        } catch (\Exception $e) {
            Log::error('ML Callback Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('dashboard')->with('error', '❌ Error en la vinculación: ' . $e->getMessage());
        }
    }
}