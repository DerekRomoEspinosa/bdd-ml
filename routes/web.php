<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductoController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $productos = \App\Models\Producto::where('activo', true)->get();
    
    $totalProductos = $productos->count();
    $stockTotal = $productos->sum('stock_total');
    $productosNecesitanFabricacion = $productos->where('recomendacion_fabricacion', '>', 0)->count();
    $unidadesAFabricar = $productos->sum('recomendacion_fabricacion');
    
    // Productos prioritarios ordenados por urgencia
    $productosPrioritarios = $productos
        ->where('recomendacion_fabricacion', '>', 0)
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
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas de productos
    Route::resource('productos', ProductoController::class);
    
    // Rutas de sincronización
    Route::post('productos/{producto}/sincronizar', [ProductoController::class, 'sincronizar'])->name('productos.sincronizar');
    Route::post('productos/sincronizar-todos', [ProductoController::class, 'sincronizarTodos'])->name('productos.sincronizar-todos');
    
   // Rutas de importación/exportación Excel
    Route::get('productos-import', [\App\Http\Controllers\ExcelController::class, 'showImportForm'])->name('productos.import.form');
    Route::post('productos-import', [\App\Http\Controllers\ExcelController::class, 'import'])->name('productos.import');
    Route::get('productos-export', [\App\Http\Controllers\ExcelController::class, 'export'])->name('productos.export');  

Route::post('productos/sincronizar-ml-background', function () {
    // Despachar el job a la cola
\App\Jobs\SincronizarProductosMLMaestro::dispatch();
    
    return redirect()
        ->route('dashboard')
        ->with('success', '🚀 Sincronización iniciada en segundo plano. Revisa el archivo de logs para ver el progreso: storage/logs/laravel.log');
})->name('productos.sincronizar-ml-background')->middleware('auth');

});

require __DIR__.'/auth.php';