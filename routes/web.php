<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MLAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

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
    
    // Sincronización en background
    Route::post('productos/sincronizar-ml-background', function () {
        \App\Jobs\SincronizarProductosMLMaestro::dispatch();
        return redirect()->route('dashboard')
            ->with('success', '🚀 Sincronización iniciada en segundo plano.');
    })->name('productos.sincronizar-ml-background');

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
    // RUTA DE PRUEBA - VERIFICAR TOKEN ML
    // ============================================
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
});

require __DIR__.'/auth.php';