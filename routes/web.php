<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MLAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

// Página de inicio
Route::get('/', function () {
    return view('welcome');
});

// ============================================
// CALLBACK DE MERCADO LIBRE - FUERA DE AUTH
// ============================================
Route::get('mercadolibre/callback', [MLAuthController::class, 'callback'])
    ->name('ml.callback');

// ============================================
// RUTAS PROTEGIDAS POR AUTENTICACIÓN
// ============================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        $productos = \App\Models\Producto::where('activo', true)->get();
        $totalProductos = $productos->count();

        // ✅ Calcular stock total correctamente
        $stockTotal = $productos->sum(function ($producto) {
            return $producto->stock_bodega + $producto->stock_enviado_full + ($producto->stock_full ?? 0);
        });

        // ✅ Calcular productos que necesitan fabricación
        $productosNecesitanFabricacion = $productos->filter(function ($producto) {
            return $producto->recomendacion_fabricacion > 0;
        })->count();

        // ✅ Calcular unidades a fabricar
        $unidadesAFabricar = $productos->sum(function ($producto) {
            return $producto->recomendacion_fabricacion;
        });

        $productosPrioritarios = $productos
            ->filter(function ($producto) {
                return $producto->recomendacion_fabricacion > 0;
            })
            ->sortByDesc('recomendacion_fabricacion')
            ->take(10);

        return view('dashboard', compact(
            'productos',
            'totalProductos',
            'stockTotal',
            'productosNecesitanFabricacion',
            'unidadesAFabricar',
            'productosPrioritarios'
        ));
    })->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // CRUD DE PRODUCTOS
    // ============================================
    Route::resource('productos', ProductoController::class);

    // ============================================
    // MERCADO LIBRE
    // ============================================

    // Iniciar OAuth con Mercado Libre
    Route::get('mercadolibre/auth', [MLAuthController::class, 'redirectToML'])
        ->name('ml.login');

    // Refrescar token de ML manualmente
    Route::post('/ml/refresh-token', function () {
        try {
            $tokenData = DB::table('mercadolibre_tokens')->find(1);

            if (!$tokenData) {
                return redirect()->back()->with('error', '❌ No hay token para refrescar');
            }

            $response = Http::asForm()->post('https://api.mercadolibre.com/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => env('ML_CLIENT_ID'),
                'client_secret' => env('ML_CLIENT_SECRET'),
                'refresh_token' => $tokenData->refresh_token,
            ]);

            if (!$response->successful()) {
                return redirect()->back()->with('error', '❌ Error refrescando token: ' . $response->body());
            }

            $data = $response->json();

            DB::table('mercadolibre_tokens')->where('id', 1)->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? $tokenData->refresh_token,
                'updated_at' => now(),
                'expires_at' => now()->addHours(6),
            ]);

            return redirect()->back()->with('success', '✅ Token refrescado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Error: ' . $e->getMessage());
        }
    })->name('ml.refresh-token');

    // Sincronización individual
    Route::post('productos/{producto}/sincronizar', [ProductoController::class, 'sincronizar'])
        ->name('productos.sincronizar');

    // Sincronizar todos los productos
    Route::post('productos/sincronizar-todos', [ProductoController::class, 'sincronizarTodos'])
        ->name('productos.sincronizar-todos');

    // Sincronización en background (con Jobs)
    Route::post('productos/sincronizar-ml-background', function () {
        try {
            $token = DB::table('mercadolibre_tokens')->find(1);

            if (!$token) {
                return redirect()->route('dashboard')
                    ->with('error', '❌ No hay token de ML. Vincula tu cuenta primero.');
            }

            \App\Jobs\SincronizarProductosMLMaestro::dispatch();

            return redirect()->route('dashboard')
                ->with('success', '🚀 Sincronización iniciada en segundo plano.');
        } catch (\Exception $e) {
            Log::error('[Sync Background] Error: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    })->name('productos.sincronizar-ml-background');

    // ============================================
    // SINCRONIZACIÓN DIRECTA OPTIMIZADA
    // ============================================
    Route::post('productos/sync-ml-directo', function () {
    try {
        $token = DB::table('mercadolibre_tokens')->find(1);
        if (!$token) {
            return redirect()->route('dashboard')
                ->with('error', '❌ No hay token de ML.');
        }

        $totalProductos = \App\Models\Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->count();

        if ($totalProductos === 0) {
            return redirect()->route('dashboard')
                ->with('warning', '⚠️ No hay productos con código interno ML.');
        }

        // ✅ SIEMPRE sincronizar directo (sin jobs)
        set_time_limit(1800); // 30 minutos max
        
        $inicio = now();
        Log::info("🎯 [Sync Directo] Sincronización directa de {$totalProductos} productos");

        $productos = \App\Models\Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->get();

        $sincronizados = 0;
        $pausados = 0;
        $errores = 0;
        $mlService = new \App\Services\MercadoLibreService();

        foreach ($productos as $producto) {
            try {
                $datos = $mlService->sincronizarProducto($producto->codigo_interno_ml);

                if (isset($datos['status']) && $datos['status'] === 'paused') {
                    $pausados++;
                }

                $producto->update([
                    'stock_full' => $datos['stock_full'],
                    'ventas_totales' => $datos['ventas_totales'],
                    'ml_published_at' => $datos['ml_published_at'] ?? null,
                    'ml_ultimo_sync' => $datos['sincronizado_en'],
                ]);

                $sincronizados++;
                
                // Pausa de 250ms entre productos
                usleep(250000);

            } catch (\Exception $e) {
                $errores++;
                Log::error("[Sync Directo] Error en producto {$producto->id}: " . $e->getMessage());
            }
        }

        $tiempoTotal = $inicio->diffInSeconds(now());
        $minutos = floor($tiempoTotal / 60);
        $segundos = $tiempoTotal % 60;
        $tiempoStr = $minutos > 0 ? "{$minutos}m {$segundos}s" : "{$segundos}s";

        $mensaje = "✅ Sincronización completada en {$tiempoStr}:<br>";
        $mensaje .= "• {$sincronizados} productos sincronizados";
        if ($pausados > 0) $mensaje .= "<br>• ⏸️ {$pausados} pausados en ML";
        if ($errores > 0) $mensaje .= "<br>• ⚠️ {$errores} errores (ver logs)";

        return redirect()->route('dashboard')->with('success', $mensaje);

    } catch (\Exception $e) {
        Log::error('[Sync] Error: ' . $e->getMessage());
        return redirect()->route('dashboard')->with('error', '❌ Error: ' . $e->getMessage());
    }
})->name('productos.sync-ml-directo');


    // ============================================
    // IMPORTACIÓN Y EXPORTACIÓN EXCEL
    // ============================================
    Route::get('productos-import', [ExcelController::class, 'showImportForm'])
        ->name('productos.import.form');
    Route::post('productos-import', [ExcelController::class, 'import'])
        ->name('productos.import');
    Route::get('productos-export', [ExcelController::class, 'export'])
        ->name('productos.export');

    // ============================================
    // MAPEO DE CÓDIGOS ML
    // ============================================

    // Vista del formulario de mapeo
    Route::get('admin/mapear-ml', function () {
        $tokenData = DB::table('mercadolibre_tokens')->find(1);
        $totalProductos = \App\Models\Producto::where('activo', true)->count();
        $conCodigo = \App\Models\Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->count();
        $sinCodigo = $totalProductos - $conCodigo;

        return view('admin.mapear-ml', compact('tokenData', 'totalProductos', 'conCodigo', 'sinCodigo'));
    })->name('admin.mapear-ml');

    // Ejecutar mapeo
    Route::post('admin/mapear-ml/ejecutar', function (\Illuminate\Http\Request $request) {
        $limit = $request->input('limit', 50);

        try {
            // Ejecutar comando y capturar output
            \Illuminate\Support\Facades\Artisan::call('ml:mapear-codigos', [
                '--limit' => $limit
            ]);

            $output = \Illuminate\Support\Facades\Artisan::output();

            return redirect()
                ->route('admin.mapear-ml')
                ->with('success', '✅ Mapeo completado correctamente')
                ->with('output', $output);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.mapear-ml')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    })->name('admin.mapear-ml.ejecutar');

    // ============================================
    // RUTAS DE DEBUG
    // ============================================

    // Verificar token ML
    Route::get('/test-ml-token', function () {
        $token = DB::table('mercadolibre_tokens')->find(1);

        return response()->json([
            'timestamp' => now()->toDateTimeString(),
            'token_existe' => $token ? '✅ SÍ' : '❌ NO',
            'token_info' => $token ? [
                'access_token_length' => strlen($token->access_token),
                'expires_at' => $token->expires_at,
                'user_id' => $token->user_id,
            ] : null
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('test.ml');

    // Ver productos con codigo_interno_ml
    Route::get('/debug-ml-products', function () {
        $productos = \App\Models\Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->select('id', 'nombre', 'codigo_interno_ml', 'stock_full', 'ventas_30_dias', 'ml_ultimo_sync')
            ->get();

        return response()->json([
            'total_activos' => \App\Models\Producto::where('activo', true)->count(),
            'con_codigo_ml' => $productos->count(),
            'productos' => $productos
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.ml.products');

    // Probar con un producto específico por ID de BD
    Route::get('/test-sync-producto/{id}', function ($id) {
        try {
            $producto = \App\Models\Producto::findOrFail($id);

            if (!$producto->codigo_interno_ml) {
                return response()->json(['error' => 'Este producto no tiene código interno ML'], 404);
            }

            $token = DB::table('mercadolibre_tokens')->find(1);
            if (!$token) {
                return response()->json(['error' => 'No hay token'], 400);
            }

            $mlService = new \App\Services\MercadoLibreService();

            $datos = $mlService->sincronizarProducto($producto->codigo_interno_ml);

            $producto->update([
                'stock_full' => $datos['stock_full'],
                'ventas_30_dias' => $datos['ventas_30_dias'],
                'ml_ultimo_sync' => $datos['sincronizado_en'],
            ]);

            return response()->json([
                'success' => true,
                'producto_id' => $producto->id,
                'producto' => $producto->nombre,
                'codigo_ml' => $producto->codigo_interno_ml,
                'datos_ml' => $datos,
                'actualizado' => [
                    'stock_full' => $producto->fresh()->stock_full,
                    'ventas_30_dias' => $producto->fresh()->ventas_30_dias,
                ]
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('test.sync.producto');

    // Test de sincronización simple (primer producto)
    Route::get('/test-sync-simple', function () {
        try {
            $token = DB::table('mercadolibre_tokens')->find(1);
            if (!$token) {
                return response()->json(['error' => 'No hay token'], 400);
            }

            $mlService = new \App\Services\MercadoLibreService();

            $producto = \App\Models\Producto::where('activo', true)
                ->whereNotNull('codigo_interno_ml')
                ->where('codigo_interno_ml', '!=', '')
                ->first();

            if (!$producto) {
                return response()->json(['error' => 'No hay productos con código ML'], 404);
            }

            $datos = $mlService->sincronizarProducto($producto->codigo_interno_ml);

            $producto->update([
                'stock_full' => $datos['stock_full'],
                'ventas_30_dias' => $datos['ventas_30_dias'],
                'ml_ultimo_sync' => $datos['sincronizado_en'],
            ]);

            return response()->json([
                'success' => true,
                'producto' => $producto->nombre,
                'codigo_ml' => $producto->codigo_interno_ml,
                'datos' => $datos,
                'actualizado' => [
                    'stock_full' => $producto->fresh()->stock_full,
                    'ventas_30_dias' => $producto->fresh()->ventas_30_dias,
                ]
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('test.sync.simple');

    // Ver datos RAW de un item específico en ML
    Route::get('/debug-ml-item/{itemId}', function ($itemId) {
        try {
            $token = DB::table('mercadolibre_tokens')->find(1);

            if (!$token) {
                return response()->json(['error' => 'No hay token de ML'], 401);
            }

            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/items/{$itemId}");

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Error de ML API',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status(), [], JSON_PRETTY_PRINT);
            }

            $data = $response->json();

            return response()->json([
                'item_id' => $itemId,
                'status' => $data['status'] ?? 'N/A',
                'title' => $data['title'] ?? 'N/A',
                'available_quantity' => $data['available_quantity'] ?? 0,
                'sold_quantity' => $data['sold_quantity'] ?? 0,
                'seller_custom_field' => $data['seller_custom_field'] ?? null,
                'health' => $data['health'] ?? null,
                'listing_type_id' => $data['listing_type_id'] ?? 'N/A',
                'permalink' => $data['permalink'] ?? 'N/A',
                'datos_completos' => $data
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('debug.ml.item');

    // Ver TODOS los items del seller
    Route::get('/debug-ml-all-items', function () {
        try {
            $token = DB::table('mercadolibre_tokens')->find(1);

            if (!$token) {
                return response()->json(['error' => 'No hay token de ML'], 401);
            }

            // Obtener seller ID
            $userResponse = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/users/me");

            if (!$userResponse->successful()) {
                return response()->json(['error' => 'No se pudo obtener seller ID'], 500);
            }

            $sellerId = $userResponse->json()['id'];

            // Obtener items del seller
            $response = Http::withToken($token->access_token)
                ->get("https://api.mercadolibre.com/users/{$sellerId}/items/search", [
                    'status' => 'active',
                    'limit' => 50
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Error obteniendo items',
                    'status' => $response->status()
                ], $response->status());
            }

            $data = $response->json();
            $itemIds = $data['results'] ?? [];

            // Obtener detalles de los primeros 10 items
            $itemsConDetalles = [];
            foreach (array_slice($itemIds, 0, 10) as $itemId) {
                $itemResponse = Http::withToken($token->access_token)
                    ->get("https://api.mercadolibre.com/items/{$itemId}");

                if ($itemResponse->successful()) {
                    $itemData = $itemResponse->json();
                    $itemsConDetalles[] = [
                        'item_id' => $itemId,
                        'title' => $itemData['title'] ?? 'N/A',
                        'seller_custom_field' => $itemData['seller_custom_field'] ?? null,
                        'available_quantity' => $itemData['available_quantity'] ?? 0,
                        'sold_quantity' => $itemData['sold_quantity'] ?? 0,
                        'status' => $itemData['status'] ?? 'N/A',
                    ];
                }

                usleep(100000); // Pausa de 100ms
            }

            return response()->json([
                'seller_id' => $sellerId,
                'total_items' => count($itemIds),
                'primeros_10_items' => $itemsConDetalles,
                'todos_los_item_ids' => $itemIds
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT);
        }
    })->name('debug.ml.all.items');

    // ============================================
// PROGRESO DE SINCRONIZACIÓN
// ============================================
Route::get('/sync-progress', function () {
    try {
        // Total de productos con código ML (activos)
        $totalProductos = \App\Models\Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->count();

        // Productos sincronizados en los últimos 5 minutos
        $sincronizados = \App\Models\Producto::where('activo', true)
            ->whereNotNull('ml_ultimo_sync')
            ->where('ml_ultimo_sync', '>=', now()->subMinutes(5))
            ->count();

        $porcentaje = $totalProductos > 0 ? round(($sincronizados / $totalProductos) * 100, 1) : 0;
        $completado = $sincronizados >= $totalProductos && $totalProductos > 0;

        // Última sincronización
        $ultimaSync = \App\Models\Producto::where('activo', true)
            ->whereNotNull('ml_ultimo_sync')
            ->orderBy('ml_ultimo_sync', 'desc')
            ->first();

        return response()->json([
            'total' => $totalProductos,
            'sincronizados' => $sincronizados,
            'porcentaje' => $porcentaje,
            'ultima_sync' => $ultimaSync ? $ultimaSync->ml_ultimo_sync->format('H:i:s') : 'nunca',
            'completado' => $completado,
        ]);
    } catch (\Exception $e) {
        \Log::error('[Sync Progress] Error: ' . $e->getMessage());
        return response()->json([
            'total' => 0,
            'sincronizados' => 0,
            'porcentaje' => 0,
            'ultima_sync' => 'error',
            'completado' => false,
            'error' => $e->getMessage()
        ]);
    }
})->name('sync.progress');

Route::get('productos/ventas-30-dias', [ExcelController::class, 'mostrarFormularioVentas30Dias'])
    ->name('productos.ventas-30-dias');

Route::post('productos/calcular-ventas-30-dias', [ExcelController::class, 'calcularVentas30Dias'])
    ->name('productos.calcular-ventas-30dias');
    
});



require __DIR__ . '/auth.php';
