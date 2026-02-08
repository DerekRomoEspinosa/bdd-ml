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
    // SINCRONIZACIÓN DIRECTA (SIN JOBS) - NUEVA
    // ============================================
    Route::post('productos/sync-ml-directo', function () {
        try {
            $inicio = now();
            
            Log::info("🎯 Sincronización directa iniciada");
            
            // Verificar token
            $token = DB::table('mercadolibre_tokens')->find(1);
            if (!$token) {
                return redirect()->route('dashboard')
                    ->with('error', '❌ No hay token de ML. Vincula tu cuenta primero.');
            }
            
            // Obtener servicio
            $mlService = new \App\Services\MercadoLibreService();
            
            // Obtener productos con ml_item_id
            $productos = \App\Models\Producto::where('activo', true)
                ->whereNotNull('ml_item_id')
                ->where('ml_item_id', '!=', '')
                ->limit(50) // Limitar a 50 por seguridad
                ->get();
            
            if ($productos->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('warning', '⚠️ No hay productos con ID de Mercado Libre para sincronizar.');
            }
            
            Log::info("📊 Productos a sincronizar: " . $productos->count());
            
            $sincronizados = 0;
            $errores = 0;
            
            foreach ($productos as $producto) {
                try {
                    Log::info("  → Sincronizando: {$producto->ml_item_id}");
                    
                    $datos = $mlService->sincronizarProducto($producto->ml_item_id);
                    
                    $producto->update([
                        'stock_full' => $datos['stock_full'],
                        'ventas_30_dias' => $datos['ventas_30_dias'],
                        'sincronizado_en' => $datos['sincronizado_en'],
                    ]);
                    
                    $sincronizados++;
                    
                    // Pausa de 200ms entre productos para no saturar la API
                    usleep(200000);
                    
                } catch (\Exception $e) {
                    $errores++;
                    Log::error("  ✗ Error en {$producto->ml_item_id}: " . $e->getMessage());
                }
            }
            
            $tiempoTotal = $inicio->diffInSeconds(now());
            
            Log::info("✅ Sincronización completada", [
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'tiempo_segundos' => $tiempoTotal
            ]);
            
            $mensaje = "✅ Sincronizados: {$sincronizados} productos";
            if ($errores > 0) {
                $mensaje .= " | ⚠️ Errores: {$errores}";
            }
            $mensaje .= " | ⏱️ Tiempo: {$tiempoTotal}s";
            
            return redirect()->route('dashboard')->with('success', $mensaje);
            
        } catch (\Exception $e) {
            Log::error('[Sync Directo] Error: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
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
    // RUTAS DE DEBUG Y PRUEBA
    // ============================================
    
    // Verificar token ML
    Route::get('/test-ml-token', function () {
        $token = DB::table('mercadolibre_tokens')->find(1);
        
        $data = [
            'timestamp' => now()->toDateTimeString(),
            'session_driver' => config('session.driver'),
            'cache_driver' => config('cache.default'),
            'token_existe' => $token ? '✅ SÍ' : '❌ NO',
        ];
        
        if ($token) {
            $data['token_info'] = [
                'id' => $token->id,
                'access_token_length' => strlen($token->access_token),
                'access_token_prefix' => substr($token->access_token, 0, 30) . '...',
                'has_refresh_token' => !empty($token->refresh_token) ? 'SÍ' : 'NO',
                'expires_in' => $token->expires_in,
                'expires_at' => $token->expires_at,
                'created_at' => $token->created_at,
                'updated_at' => $token->updated_at,
            ];
        }
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    })->name('test.ml');
    
    // Ver productos con ML ID
    Route::get('/debug-ml-products', function () {
        $productos = \App\Models\Producto::where('activo', true)
            ->whereNotNull('ml_item_id')
            ->where('ml_item_id', '!=', '')
            ->select('id', 'sku', 'nombre', 'ml_item_id', 'stock_full', 'ventas_30_dias', 'sincronizado_en')
            ->get();
        
        return response()->json([
            'total_productos_activos' => \App\Models\Producto::where('activo', true)->count(),
            'total_con_ml_id' => $productos->count(),
            'productos' => $productos
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.ml.products');
});

require __DIR__.'/auth.php';