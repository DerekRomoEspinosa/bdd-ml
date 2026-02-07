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
        // Log a archivo para debug
        $logFile = storage_path('logs/ml-debug.log');
        $log = function($message) use ($logFile) {
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
        };
        
        $log("========================================");
        $log("CALLBACK INICIADO");
        $log("========================================");
        
        try {
            $code = $request->query('code');
            $state = $request->query('state');
            
            $log("Code: " . ($code ? substr($code, 0, 20) . "..." : "NULL"));
            $log("State: " . ($state ? "presente" : "NULL"));

            if (!$code) {
                $log("ERROR: No code received");
                return redirect('/dashboard')->with('error', '❌ No code');
            }

            $log("Obteniendo credenciales...");
            $clientId = env('ML_CLIENT_ID');
            $clientSecret = env('ML_CLIENT_SECRET');
            $redirectUri = env('ML_REDIRECT_URI');
            
            $log("Client ID: " . ($clientId ? "OK" : "NULL"));
            $log("Client Secret: " . ($clientSecret ? "OK" : "NULL"));
            $log("Redirect URI: " . $redirectUri);
            
            $log("Solicitando token a ML...");
            
            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            $log("ML Response Status: " . $response->status());

            if (!$response->successful()) {
                $log("ERROR ML: " . $response->body());
                return redirect('/dashboard')->with('error', '❌ ML Error: ' . $response->status());
            }

            $data = $response->json();
            $log("ML Response OK - Has access_token: " . (isset($data['access_token']) ? 'YES' : 'NO'));
            
            if (!isset($data['access_token'])) {
                $log("ERROR: No access_token in response");
                return redirect('/dashboard')->with('error', '❌ No token');
            }
            
            $log("Access token length: " . strlen($data['access_token']));
            $log("Guardando en base de datos...");
            
            // Guardar con manejo de errores
            try {
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
                
                $log("✅ TOKEN GUARDADO EXITOSAMENTE");
                
                // Verificar que se guardó
                $saved = DB::table('mercadolibre_tokens')->where('id', 1)->first();
                $log("Verificación: " . ($saved ? "Token encontrado en DB" : "Token NO encontrado"));
                
            } catch (\Exception $dbEx) {
                $log("❌ DB ERROR: " . $dbEx->getMessage());
                return redirect('/dashboard')->with('error', '❌ DB Error: ' . $dbEx->getMessage());
            }
            
            session()->forget('ml_oauth_state');
            $log("Redirigiendo a dashboard con mensaje de éxito");

            return redirect('/dashboard')->with('success', '✅ Mercado Libre vinculado correctamente');

        } catch (\Exception $e) {
            $log("❌❌❌ EXCEPTION: " . $e->getMessage());
            $log("File: " . $e->getFile());
            $log("Line: " . $e->getLine());
            $log("Trace: " . $e->getTraceAsString());
            
            return redirect('/dashboard')->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}