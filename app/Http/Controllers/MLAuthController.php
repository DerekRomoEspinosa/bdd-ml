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
        
        $url = "https://auth.mercadolibre.com.mx/authorization?response_type=code&client_id={$appId}&redirect_uri={$redirectUri}";
        
        return redirect($url);
    }

    public function callback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) return redirect()->route('dashboard')->with('error', 'Código no recibido.');

        $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.mercadolibre.app_id'),
            'client_secret' => config('services.mercadolibre.secret_key'),
            'code' => $code,
            'redirect_uri' => config('services.mercadolibre.redirect_uri'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            DB::table('mercadolibre_tokens')->updateOrInsert(['id' => 1], [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_in' => $data['expires_in'],
                'updated_at' => now(),
            ]);

            return redirect()->route('dashboard')->with('success', '✅ Mercado Libre vinculado correctamente.');
        }

        Log::error("Error ML Callback: " . $response->body());
        return redirect()->route('dashboard')->with('error', '❌ Error en la vinculación.');
    }
}