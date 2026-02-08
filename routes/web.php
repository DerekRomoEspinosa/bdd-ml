<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MLAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

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
    // SINCRONIZACIÓN DIRECTA (SIN JOBS) - MEJORADA
    // ============================================
    Route::post('productos/sync-ml-directo', function () {
        try {
            $inicio = now();
            
            Log::info("🎯 [Sync Directo] Iniciando sincronización");
            
            // Verificar token
            $token = DB::table('mercadolibre_tokens')->find(1);
            if (!$token) {
                Log::error("[Sync Directo] No hay token");
                return redirect()->route('dashboard')
                    ->with('error', '❌ No hay token de ML. Vincula tu cuenta primero.');
            }
            
            Log::info("[Sync Directo] Token encontrado");
            
            // Obtener columnas disponibles
            $columnas = DB::select("SHOW COLUMNS FROM productos");
            $nombresColumnas = array_map(fn($col) => $col->Field, $columnas);
            
            Log::info("[Sync Directo] Columnas disponibles: " . implode(', ', $nombresColumnas));
            
            // Obtener servicio
            $mlService = new \App\Services\MercadoLibreService();
            
            // Construir query dinámicamente según las columnas disponibles
            $productos = \App\Models\Producto::where('activo', true);
            
            // Priorizar codigo_interno_ml > ml_item_id > sku_ml
            if (in_array('codigo_interno_ml', $nombresColumnas)) {
                $productos->where(function($query) {
                    $query->whereNotNull('codigo_interno_ml')
                          ->where('codigo_interno_ml', '!=', '');
                });
            } elseif (in_array('ml_item_id', $nombresColumnas)) {
                $productos->where(function($query) {
                    $query->whereNotNull('ml_item_id')
                          ->where('ml_item_id', '!=', '');
                });
            } elseif (in_array('sku_ml', $nombresColumnas)) {
                $productos->where(function($query) {
                    $query->whereNotNull('sku_ml')
                          ->where('sku_ml', '!=', '');
                });
            } else {
                Log::error("[Sync Directo] No hay columna para IDs de ML");
                return redirect()->route('dashboard')
                    ->with('error', '❌ La tabla productos no tiene columna para IDs de Mercado Libre.');
            }
            
            $productos = $productos->limit(50)->get();
            
            if ($productos->isEmpty()) {
                Log::warning("[Sync Directo] No hay productos con ML ID");
                return redirect()->route('dashboard')
                    ->with('warning', '⚠️ No hay productos con ID/código de Mercado Libre.');
            }
            
            Log::info("[Sync Directo] Productos encontrados: " . $productos->count());
            
            $sincronizados = 0;
            $errores = 0;
            $sinId = 0;
            
            foreach ($productos as $producto) {
                // Determinar qué ID usar (prioridad: codigo_interno_ml > ml_item_id > sku_ml)
                $mlId = null;
                if (in_array('codigo_interno_ml', $nombresColumnas) && !empty($producto->codigo_interno_ml)) {
                    $mlId = $producto->codigo_interno_ml;
                } elseif (in_array('ml_item_id', $nombresColumnas) && !empty($producto->ml_item_id)) {
                    $mlId = $producto->ml_item_id;
                } elseif (in_array('sku_ml', $nombresColumnas) && !empty($producto->sku_ml)) {
                    $mlId = $producto->sku_ml;
                }
                
                if (!$mlId) {
                    $sinId++;
                    Log::warning("[Sync Directo] Producto ID {$producto->id} sin ML ID");
                    continue;
                }
                
                try {
                    Log::info("[Sync Directo] Sincronizando: {$mlId}");
                    
                    $datos = $mlService->sincronizarProducto($mlId);
                    
                    // Actualizar solo las columnas que existen
                    $updateData = [];
                    if (in_array('stock_full', $nombresColumnas)) {
                        $updateData['stock_full'] = $datos['stock_full'];
                    }
                    if (in_array('ventas_30_dias', $nombresColumnas)) {
                        $updateData['ventas_30_dias'] = $datos['ventas_30_dias'];
                    }
                    if (in_array('sincronizado_en', $nombresColumnas)) {
                        $updateData['sincronizado_en'] = $datos['sincronizado_en'];
                    }
                    
                    if (!empty($updateData)) {
                        $producto->update($updateData);
                    }
                    
                    $sincronizados++;
                    
                    Log::info("[Sync Directo] ✓ {$mlId} - Stock: {$datos['stock_full']}, Ventas: {$datos['ventas_30_dias']}");
                    
                    // Pausa de 300ms entre productos
                    usleep(300000);
                    
                } catch (\Exception $e) {
                    $errores++;
                    Log::error("[Sync Directo] ✗ Error en {$mlId}: " . $e->getMessage());
                }
            }
            
            $tiempoTotal = $inicio->diffInSeconds(now());
            
            Log::info("[Sync Directo] ✅ Completado", [
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'sin_id' => $sinId,
                'tiempo_segundos' => $tiempoTotal
            ]);
            
            $mensaje = "✅ Sincronizados: {$sincronizados} productos";
            if ($errores > 0) {
                $mensaje .= " | ⚠️ Errores: {$errores}";
            }
            if ($sinId > 0) {
                $mensaje .= " | ℹ️ Sin ML ID: {$sinId}";
            }
            $mensaje .= " | ⏱️ {$tiempoTotal}s";
            
            return redirect()->route('dashboard')
                ->with('success', $mensaje);
            
        } catch (\Exception $e) {
            Log::error('[Sync Directo] ❌ Excepción: ' . $e->getMessage());
            Log::error('[Sync Directo] Trace: ' . $e->getTraceAsString());
            
            return redirect()->route('dashboard')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    })->name('productos.sync-ml-directo');

    // ============================================
    // MAPEO AUTOMÁTICO DE CÓDIGOS ML
    // ============================================
    
    // Ver página de mapeo
    Route::get('/admin/mapear-ml', function () {
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
    Route::post('/admin/mapear-ml/ejecutar', function (Illuminate\Http\Request $request) {
        set_time_limit(600); // 10 minutos
        
        $limit = $request->input('limit', 50);
        
        try {
            Artisan::call('ml:mapear-codigos', ['--limit' => $limit]);
            $output = Artisan::output();
            
            return redirect()->route('admin.mapear-ml')
                ->with('success', '✅ Mapeo ejecutado correctamente')
                ->with('output', $output);
                
        } catch (\Exception $e) {
            return redirect()->route('admin.mapear-ml')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    })->name('admin.mapear-ml.ejecutar');

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
                'created_at' => $token->created_at,
                'updated_at' => $token->updated_at,
            ];
        }
        
        return response()->json($data, 200, [], JSON_PRETTY_PRINT);
    })->name('test.ml');
    
    // Ver productos con ML ID
    Route::get('/debug-ml-products', function () {
        $columnas = DB::select("SHOW COLUMNS FROM productos");
        $nombresColumnas = array_map(fn($col) => $col->Field, $columnas);
        
        $selectFields = ['id', 'nombre'];
        
        if (in_array('sku', $nombresColumnas)) $selectFields[] = 'sku';
        if (in_array('sku_ml', $nombresColumnas)) $selectFields[] = 'sku_ml';
        if (in_array('codigo_interno_ml', $nombresColumnas)) $selectFields[] = 'codigo_interno_ml';
        if (in_array('ml_item_id', $nombresColumnas)) $selectFields[] = 'ml_item_id';
        if (in_array('stock_full', $nombresColumnas)) $selectFields[] = 'stock_full';
        if (in_array('ventas_30_dias', $nombresColumnas)) $selectFields[] = 'ventas_30_dias';
        if (in_array('sincronizado_en', $nombresColumnas)) $selectFields[] = 'sincronizado_en';
        if (in_array('activo', $nombresColumnas)) $selectFields[] = 'activo';
        
        $query = \App\Models\Producto::select($selectFields);
        
        if (in_array('activo', $nombresColumnas)) {
            $query->where('activo', true);
        }
        
        if (in_array('codigo_interno_ml', $nombresColumnas)) {
            $query->where(function($q) {
                $q->whereNotNull('codigo_interno_ml')->where('codigo_interno_ml', '!=', '');
            });
        } elseif (in_array('ml_item_id', $nombresColumnas)) {
            $query->where(function($q) {
                $q->whereNotNull('ml_item_id')->where('ml_item_id', '!=', '');
            });
        } elseif (in_array('sku_ml', $nombresColumnas)) {
            $query->where(function($q) {
                $q->whereNotNull('sku_ml')->where('sku_ml', '!=', '');
            });
        }
        
        $productos = $query->get();
        
        $totalActivos = \App\Models\Producto::query();
        if (in_array('activo', $nombresColumnas)) {
            $totalActivos->where('activo', true);
        }
        
        return response()->json([
            'columnas_disponibles' => $nombresColumnas,
            'total_productos_activos' => $totalActivos->count(),
            'total_con_ml_id' => $productos->count(),
            'productos' => $productos
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.ml.products');
});

require __DIR__.'/auth.php';