<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\MLAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// Todas las rutas protegidas por autenticación
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
    
    // CRUD de Productos
    Route::resource('productos', ProductoController::class);
    
    // Autenticación Mercado Libre (OAuth) - CORREGIDO
    Route::get('mercadolibre/auth', [MLAuthController::class, 'redirectToML'])->name('ml.login');
    Route::get('mercadolibre/callback', [MLAuthController::class, 'callback'])->name('ml.callback');
    
    // Sincronización con Mercado Libre
    Route::post('productos/{producto}/sincronizar', [ProductoController::class, 'sincronizar'])->name('productos.sincronizar');
    Route::post('productos/sincronizar-todos', [ProductoController::class, 'sincronizarTodos'])->name('productos.sincronizar-todos');
    Route::post('productos/sincronizar-ml-background', function () {
        \App\Jobs\SincronizarProductosMLMaestro::dispatch();
        return redirect()->route('dashboard')
            ->with('success', '🚀 Sincronización iniciada en segundo plano.');
    })->name('productos.sincronizar-ml-background');

    // Importación y Exportación Excel
    Route::get('productos-import', [ExcelController::class, 'showImportForm'])->name('productos.import.form');
    Route::post('productos-import', [ExcelController::class, 'import'])->name('productos.import');
    Route::get('productos-export', [ExcelController::class, 'export'])->name('productos.export');  
});

require __DIR__.'/auth.php';