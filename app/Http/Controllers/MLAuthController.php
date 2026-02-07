<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MLAuthController extends Controller
{
    public function redirectToML()
    {
        $appId = env('ML_CLIENT_ID');
        $redirectUri = env('ML_REDIRECT_URI');
        
        if (empty($appId) || empty($redirectUri)) {
            return back()->with('error', '❌ Configuración ML incompleta');
        }
        
        $state = Str::random(40);
        session(['ml_oauth_state' => $state]);
        
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
        
        $url = "https://auth.mercadolibre.com.mx/authorization?" . $params;
        
        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        // LOG INMEDIATO - ANTES DE CUALQUIER COSA
        \Log::info('========================================');
        \Log::info('ML CALLBACK EJECUTÁNDOSE');
        \Log::info('========================================');
        
        $code = $request->query('code');
        $state = $request->query('state');
        
        \Log::info('Callback params', [
            'has_code' => !empty($code),
            'code_start' => $code ? substr($code, 0, 20) . '...' : 'null',
            'has_state' => !empty($state),
        ]);

        if (!$code) {
            \Log::error('❌ No code received');
            return redirect('/dashboard')->with('error', '❌ No se recibió código');
        }

        try {
            \Log::info('Requesting token from ML...');
            
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => env('ML_CLIENT_ID'),
                'client_secret' => env('ML_CLIENT_SECRET'),
                'code' => $code,
                'redirect_uri' => env('ML_REDIRECT_URI'),
            ]);

            \Log::info('ML response received', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if (!$response->successful()) {
                \Log::error('Token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return redirect('/dashboard')->with('error', '❌ Error: ' . $response->status());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                \Log::error('No access_token in response');
                return redirect('/dashboard')->with('error', '❌ Token no recibido');
            }
            
            \Log::info('Saving token to database...');
            
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

            \Log::info('✅✅✅ TOKEN GUARDADO EXITOSAMENTE ✅✅✅');
            
            session()->forget('ml_oauth_state');

            return redirect('/dashboard')->with('success', '✅ Mercado Libre vinculado correctamente');

        } catch (\Exception $e) {
            \Log::error('❌❌❌ CALLBACK EXCEPTION ❌❌❌', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect('/dashboard')->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}