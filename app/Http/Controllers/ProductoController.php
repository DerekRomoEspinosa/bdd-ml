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
    
    $query = Producto::where('activo', true);
    
    if ($buscar) {
        $query->where(function($q) use ($buscar) {
            $q->where('nombre', 'like', "%{$buscar}%")
              ->orWhere('modelo', 'like', "%{$buscar}%")
              ->orWhere('sku_ml', 'like', "%{$buscar}%");
        });
    }
    
    $productos = $query->orderBy('nombre')->get();
    
    return view('productos.index', compact('productos'));
}

    public function create()
    {
        return view('productos.create');
    }

    public function store(Request $request)
    {
        // Validación con mensajes personalizados
        $validated = $request->validate([
            'sku_ml' => 'required|unique:productos|max:255|regex:/^[A-Z0-9\-]+$/',
            'nombre' => 'required|max:255|min:3',
            'stock_bodega' => 'nullable|integer|min:0|max:999999',
            'stock_cortado' => 'nullable|integer|min:0|max:999999',
            'stock_enviado_full' => 'nullable|integer|min:0|max:999999',
        ], [
            'sku_ml.required' => 'El SKU de Mercado Libre es obligatorio',
            'sku_ml.unique' => 'Este SKU ya existe en el sistema',
            'sku_ml.regex' => 'El SKU solo puede contener letras mayúsculas, números y guiones',
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        ]);

        Producto::create($validated);
        
        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto creado exitosamente');
    }

    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    public function update(Request $request, Producto $producto)
    {
        // Validación sin permitir cambiar SKU
        $validated = $request->validate([
            'nombre' => 'required|max:255|min:3',
            'stock_bodega' => 'nullable|integer|min:0|max:999999',
            'stock_cortado' => 'nullable|integer|min:0|max:999999',
            'stock_enviado_full' => 'nullable|integer|min:0|max:999999',
        ], [
            'nombre.required' => 'El nombre del producto es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres',
        ]);

        $producto->update($validated);
        
        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto actualizado exitosamente');
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
