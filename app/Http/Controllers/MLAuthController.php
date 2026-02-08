<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class MLAuthController extends Controller
{
    public function redirectToML()
    {
        $appId = env('ML_CLIENT_ID');
        $redirectUri = env('ML_REDIRECT_URI');

        if (empty($appId) || empty($redirectUri)) {
            Log::error("[ML OAuth] Configuración incompleta", [
                'has_client_id' => !empty($appId),
                'has_redirect_uri' => !empty($redirectUri)
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Configuración de Mercado Libre incompleta');
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

        Log::info("[ML OAuth] Iniciando flujo de autorización", [
            'client_id' => $appId,
            'redirect_uri' => $redirectUri,
            'state_prefix' => substr($state, 0, 10) . '...',
            'url' => $url
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {
        Log::info("[ML OAuth] ==================== CALLBACK RECIBIDO ====================");
        Log::info("[ML OAuth] URL completa: " . $request->fullUrl());
        Log::info("[ML OAuth] Query params: ", $request->query());
        Log::info("[ML OAuth] Headers: ", $request->headers->all());

        try {
            // 1. Verificar si hay error de ML
            $error = $request->query('error');
            if ($error) {
                Log::error("[ML OAuth] Error desde Mercado Libre", [
                    'error' => $error,
                    'error_description' => $request->query('error_description')
                ]);
                
                return redirect()->route('dashboard')
                    ->with('error', '❌ Mercado Libre rechazó la autorización: ' . $error);
            }

            // 2. Obtener el código de autorización
            $code = $request->query('code');
            $state = $request->query('state');

            if (!$code) {
                Log::error("[ML OAuth] No se recibió código de autorización");
                return redirect()->route('dashboard')
                    ->with('error', '❌ No se recibió código de autorización');
            }

            Log::info("[ML OAuth] Código recibido exitosamente", [
                'code_length' => strlen($code),
                'code_prefix' => substr($code, 0, 20) . '...',
                'state_present' => !empty($state)
            ]);

            // 3. Obtener credenciales de .env
            $clientId = env('ML_CLIENT_ID');
            $clientSecret = env('ML_CLIENT_SECRET');
            $redirectUri = env('ML_REDIRECT_URI');

            Log::info("[ML OAuth] Credenciales cargadas", [
                'client_id' => $clientId,
                'has_secret' => !empty($clientSecret),
                'redirect_uri' => $redirectUri
            ]);

            if (!$clientId || !$clientSecret || !$redirectUri) {
                Log::error("[ML OAuth] Credenciales incompletas en .env");
                return redirect()->route('dashboard')
                    ->with('error', '❌ Credenciales de ML incompletas');
            }

            // 4. Intercambiar código por access_token
            Log::info("[ML OAuth] Solicitando access_token a Mercado Libre...");
            
            $tokenUrl = 'https://api.mercadolibre.com/oauth/token';
            $requestData = [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ];

            Log::info("[ML OAuth] Datos de la petición (sin secret)", [
                'grant_type' => $requestData['grant_type'],
                'client_id' => $requestData['client_id'],
                'code_prefix' => substr($requestData['code'], 0, 20) . '...',
                'redirect_uri' => $requestData['redirect_uri']
            ]);

            $response = Http::timeout(30)
                ->asForm()
                ->post($tokenUrl, $requestData);

            Log::info("[ML OAuth] Respuesta de ML recibida", [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'response_size' => strlen($response->body())
            ]);

            if (!$response->successful()) {
                $errorBody = $response->json();
                Log::error("[ML OAuth] Error al obtener token", [
                    'status' => $response->status(),
                    'error' => $errorBody
                ]);
                
                $errorMsg = $errorBody['message'] ?? $errorBody['error'] ?? 'Error desconocido';
                
                return redirect()->route('dashboard')
                    ->with('error', '❌ Error de Mercado Libre: ' . $errorMsg);
            }

            $data = $response->json();

            Log::info("[ML OAuth] Respuesta parseada", [
                'has_access_token' => isset($data['access_token']),
                'has_refresh_token' => isset($data['refresh_token']),
                'expires_in' => $data['expires_in'] ?? null,
                'user_id' => $data['user_id'] ?? null
            ]);

            if (!isset($data['access_token'])) {
                Log::error("[ML OAuth] Respuesta sin access_token", ['data' => $data]);
                return redirect()->route('dashboard')
                    ->with('error', '❌ Respuesta inválida de Mercado Libre');
            }

            // 5. Guardar en base de datos
            Log::info("[ML OAuth] Guardando tokens en base de datos...");

            $expiresAt = now()->addSeconds($data['expires_in'] ?? 21600);
            
            $tokenData = [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_in' => $data['expires_in'] ?? 21600,
                'expires_at' => $expiresAt,
                'user_id' => $data['user_id'] ?? null,
                'updated_at' => now(),
            ];

            // Si es la primera vez, agregar created_at
            $exists = DB::table('mercadolibre_tokens')->where('id', 1)->exists();
            if (!$exists) {
                $tokenData['created_at'] = now();
            }

            DB::table('mercadolibre_tokens')->updateOrInsert(
                ['id' => 1],
                $tokenData
            );

            Log::info("[ML OAuth] Tokens guardados en DB");

            // 6. Verificar que se guardó
            $saved = DB::table('mercadolibre_tokens')->where('id', 1)->first();
            
            if ($saved) {
                Log::info("[ML OAuth] ✅ Verificación exitosa", [
                    'id' => $saved->id,
                    'access_token_length' => strlen($saved->access_token),
                    'has_refresh' => !empty($saved->refresh_token),
                    'expires_at' => $saved->expires_at
                ]);
            } else {
                Log::warning("[ML OAuth] ⚠️ Token no encontrado después de guardar");
            }

            // 7. Limpiar sesión
            session()->forget('ml_oauth_state');

            Log::info("[ML OAuth] ==================== CALLBACK EXITOSO ====================");

            return redirect()->route('dashboard')
                ->with('success', '✅ Mercado Libre vinculado correctamente. Tokens guardados.');

        } catch (\Exception $e) {
            Log::error("[ML OAuth] ❌❌❌ EXCEPCIÓN CRÍTICA ❌❌❌", [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}