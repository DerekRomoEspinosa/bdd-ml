<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MLAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $stockTotal = $productos->sum('stock_total');
        $productosNecesitanFabricacion = $productos->where('recomendacion_fabricacion', '>', 0)->count();
        $unidadesAFabricar = $productos->sum('recomendacion_fabricacion');
        
        $productosPrioritarios = $productos
            ->where('recomendacion_fabricacion', '>', 0)
            ->sortByDesc('recomendacion_fabricacion')
            ->take(10);
        
        return view('dashboard', compact(
            'productos', 'totalProductos', 'stockTotal', 
            'productosNecesitanFabricacion', 'unidadesAFabricar', 'productosPrioritarios'
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
    // SINCRONIZACIÓN DIRECTA (SIN JOBS)
    // ============================================
    Route::post('productos/sync-ml-directo', function () {
        try {
            $inicio = now();
            
            Log::info("🎯 [Sync Directo] Iniciando");
            
            // Verificar token
            $token = DB::table('mercadolibre_tokens')->find(1);
            if (!$token) {
                return redirect()->route('dashboard')
                    ->with('error', '❌ No hay token de ML.');
            }
            
            // Obtener servicio
            $mlService = new \App\Services\MercadoLibreService();
            
            // Obtener productos con codigo_interno_ml
            $productos = \App\Models\Producto::where('activo', true)
                ->whereNotNull('codigo_interno_ml')
                ->where('codigo_interno_ml', '!=', '')
                ->limit(50)
                ->get();
            
            if ($productos->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('warning', '⚠️ No hay productos con código interno ML.');
            }
            
            Log::info("[Sync Directo] Productos: " . $productos->count());
            
            $sincronizados = 0;
            $errores = 0;
            
            foreach ($productos as $producto) {
                try {
                    $datos = $mlService->sincronizarProducto($producto->codigo_interno_ml);
                    
                    $producto->update([
                        'stock_full' => $datos['stock_full'],
                        'ventas_30_dias' => $datos['ventas_30_dias'],
                        'ml_ultimo_sync' => $datos['sincronizado_en'],
                    ]);
                    
                    $sincronizados++;
                    
                    Log::info("[Sync Directo] ✓ {$producto->codigo_interno_ml} - Stock: {$datos['stock_full']}");
                    
                    usleep(300000); // Pausa 300ms
                    
                } catch (\Exception $e) {
                    $errores++;
                    Log::error("[Sync Directo] ✗ {$producto->codigo_interno_ml}: {$e->getMessage()}");
                }
            }
            
            $tiempoTotal = $inicio->diffInSeconds(now());
            
            $mensaje = "✅ Sincronizados: {$sincronizados}";
            if ($errores > 0) $mensaje .= " | ⚠️ Errores: {$errores}";
            $mensaje .= " | ⏱️ {$tiempoTotal}s";
            
            return redirect()->route('dashboard')->with('success', $mensaje);
            
        } catch (\Exception $e) {
            Log::error('[Sync Directo] Error: ' . $e->getMessage());
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
    
    // Test de sincronización
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
});

require __DIR__.'/auth.php';