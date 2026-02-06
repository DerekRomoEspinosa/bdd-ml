<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\MercadoLibreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    private MercadoLibreService $mlService;

    public function __construct(MercadoLibreService $mlService)
    {
        $this->mlService = $mlService;
    }

public function index(Request $request)
{
    $buscar = $request->get('buscar');
    $filtro = $request->get('filtro'); // NUEVO
    
    $query = Producto::where('activo', true);
    
    // Búsqueda por texto
    if ($buscar) {
        $query->where(function($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('modelo', 'like', "%{$buscar}%")
              ->orWhere('sku_ml', 'like', "%{$buscar}%");
        });
    }
    
    // NUEVO: Filtros por estado
    if ($filtro) {
        switch ($filtro) {
            case 'criticos':
                // Productos con menos de 3 días de stock
                $query->whereRaw('CASE 
                    WHEN (ventas_30_dias / 30) > 0 
                    THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 3
                    ELSE false 
                END');
                break;
                
            case 'urgentes':
                // Productos con menos de 7 días de stock
                $query->whereRaw('CASE 
                    WHEN (ventas_30_dias / 30) > 0 
                    THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 7
                    ELSE false 
                END');
                break;
                
            case 'necesitan_fabricacion':
                // Productos que necesitan fabricar (cualquier cantidad)
                $query->whereRaw('GREATEST(
                    CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                    0
                ) > 0');
                break;
                
            case 'stock_ok':
                // Productos con stock suficiente
                $query->whereRaw('GREATEST(
                    CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                    0
                ) = 0');
                break;
        }
    }
    
    $productos = $query->orderBy('nombre')->paginate(50)->appends($request->all());
    
    // Contar productos por categoría para los badges
    $contadores = [
        'todos' => Producto::where('activo', true)->count(),
        'criticos' => Producto::where('activo', true)
            ->whereRaw('CASE 
                WHEN (ventas_30_dias / 30) > 0 
                THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 3
                ELSE false 
            END')->count(),
        'urgentes' => Producto::where('activo', true)
            ->whereRaw('CASE 
                WHEN (ventas_30_dias / 30) > 0 
                THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 7
                ELSE false 
            END')->count(),
        'necesitan_fabricacion' => Producto::where('activo', true)
            ->whereRaw('GREATEST(
                CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                0
            ) > 0')->count(),
        'stock_ok' => Producto::where('activo', true)
            ->whereRaw('GREATEST(
                CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                0
            ) = 0')->count(),
    ];
    
    return view('productos.index', compact('productos', 'contadores'));
}

    public function create()
    {
        return view('productos.create');
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'modelo' => 'nullable|string|max:255',
        'sku_ml' => 'required|string|max:255|unique:productos',
        'codigo_interno_ml' => 'nullable|string|max:255',
        'plantilla_corte_url' => 'nullable|url|max:2000',
        'stock_bodega' => 'required|integer|min:0',
        'stock_cortado' => 'required|integer|min:0',
        'stock_costura' => 'required|integer|min:0',
        'stock_por_empacar' => 'required|integer|min:0',
        'stock_enviado_full' => 'required|integer|min:0',
    ]);

    $validated['activo'] = true;

    Producto::create($validated);

    return redirect()
        ->route('productos.index')
        ->with('success', '✅ Producto creado correctamente');
}

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
{
    $validated = $request->validate([
        'nombre' => 'required|string|max:255',
        'modelo' => 'nullable|string|max:255',
        'sku_ml' => 'required|string|max:255|unique:productos,sku_ml,' . $producto->id,
        'codigo_interno_ml' => 'nullable|string|max:255',
        'plantilla_corte_url' => 'nullable|url|max:2000',
        'stock_bodega' => 'required|integer|min:0',
        'stock_cortado' => 'required|integer|min:0',
        'stock_costura' => 'required|integer|min:0',
        'stock_por_empacar' => 'required|integer|min:0',
        'stock_enviado_full' => 'required|integer|min:0',
    ]);

    $producto->update($validated);

    return redirect()
        ->route('productos.index')
        ->with('success', '✅ Producto actualizado correctamente');
}

    public function destroy(Producto $producto)
    {
        // Soft delete (marcar como inactivo en lugar de eliminar)
        $producto->update(['activo' => false]);
        
        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado');
    }
    /**
 * Sincronizar un producto desde Mercado Libre
 */
public function sincronizar(Producto $producto)
{
    try {
        Log::info("Iniciando sincronización", ['producto_id' => $producto->id]);
        
        // Sincronizar datos
        $datos = $this->mlService->sincronizarProducto($producto->sku_ml);
        
        Log::info("Datos obtenidos", ['datos' => $datos]);

        // Actualizar producto
        $producto->update([
            'stock_full' => $datos['stock_full'],
            'ventas_30_dias' => $datos['ventas_30_dias'],
            'ml_ultimo_sync' => $datos['sincronizado_en'],
        ]);

        $mensaje = "✅ Datos actualizados desde Mercado Libre";
        
        if ($datos['stock_full'] !== null) {
            $mensaje .= " | Stock Full: {$datos['stock_full']}";
        }
        
        if ($datos['ventas_30_dias'] !== null) {
            $mensaje .= " | Ventas 30d: {$datos['ventas_30_dias']}";
        }

        Log::info("Sincronización exitosa", ['mensaje' => $mensaje]);

        return redirect()
            ->route('productos.edit', $producto)
            ->with('success', $mensaje);

    } catch (\Exception $e) {
        Log::error("Error completo en sincronización", [
            'producto_id' => $producto->id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return redirect()
            ->route('productos.edit', $producto)
            ->with('error', '❌ Error al sincronizar: ' . $e->getMessage());
    }
}

/**
 * Sincronizar todos los productos activos (optimizado por lotes)
 */
public function sincronizarTodos()
{
    set_time_limit(300); // 5 minutos
    
    try {
        $totalProductos = Producto::where('activo', true)->count();
        $sincronizados = 0;
        $errores = 0;
        $lote = 100; // Procesar 100 productos a la vez

        // Procesar en lotes
        Producto::where('activo', true)
            ->chunk($lote, function($productos) use (&$sincronizados, &$errores) {
                foreach ($productos as $producto) {
                    try {
                        $datos = $this->mlService->sincronizarProducto($producto->sku_ml);
                        
                        $producto->update([
                            'stock_full' => $datos['stock_full'],
                            'ventas_30_dias' => $datos['ventas_30_dias'],
                            'ml_ultimo_sync' => $datos['sincronizado_en'],
                        ]);
                        
                        $sincronizados++;
                        
                    } catch (\Exception $e) {
                        $errores++;
                        Log::error("Error sincronizando producto {$producto->id}: {$e->getMessage()}");
                    }
                }
            });

        $mensaje = "🎉 Sincronización completada: {$sincronizados} de {$totalProductos} productos actualizados";
        
        if ($errores > 0) {
            $mensaje .= " | ⚠️ {$errores} errores (revisa los logs)";
        }

        Log::info("Sincronización masiva completada", [
            'total' => $totalProductos,
            'sincronizados' => $sincronizados,
            'errores' => $errores
        ]);

        return redirect()
            ->route('productos.index')
            ->with('success', $mensaje);

    } catch (\Exception $e) {
        Log::error("Error en sincronización masiva: {$e->getMessage()}");
        
        return redirect()
            ->route('productos.index')
            ->with('error', '❌ Error en sincronización masiva: ' . $e->getMessage());
    }
}
}
