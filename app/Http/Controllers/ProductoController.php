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
        $filtro = $request->get('filtro');
        
        $query = Producto::where('activo', true);
        
        // Búsqueda por texto
        if ($buscar) {
            $query->where(function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('modelo', 'like', "%{$buscar}%")
                  ->orWhere('sku_ml', 'like', "%{$buscar}%")
                  ->orWhere('codigo_interno_ml', 'like', "%{$buscar}%");
            });
        }
        
        // Filtros por estado
        if ($filtro) {
            switch ($filtro) {
                case 'criticos':
                    $query->whereRaw('CASE 
                        WHEN (ventas_30_dias / 30) > 0 
                        THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 3
                        ELSE false 
                    END');
                    break;
                    
                case 'urgentes':
                    $query->whereRaw('CASE 
                        WHEN (ventas_30_dias / 30) > 0 
                        THEN (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0)) / (ventas_30_dias / 30) < 7
                        ELSE false 
                    END');
                    break;
                    
                case 'necesitan_fabricacion':
                    $query->whereRaw('GREATEST(
                        CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                        0
                    ) > 0');
                    break;
                    
                case 'stock_ok':
                    $query->whereRaw('GREATEST(
                        CEILING(((ventas_30_dias / 30) * 15) - (stock_bodega + stock_cortado + stock_enviado_full + COALESCE(stock_full, 0))),
                        0
                    ) = 0');
                    break;
            }
        }
        
        // ✨ Ordenar primero los que SÍ tienen codigo_interno_ml
        $productos = $query
            ->orderByRaw('CASE WHEN codigo_interno_ml IS NOT NULL AND codigo_interno_ml != "" THEN 0 ELSE 1 END')
            ->orderBy('nombre')
            ->paginate(50)
            ->appends($request->all());
        
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
     * ✨ Usa codigo_interno_ml
     */
    public function sincronizar(Producto $producto)
    {
        try {
            // Usar codigo_interno_ml
            $identificador = $producto->codigo_interno_ml;
            
            if (!$identificador) {
                return redirect()
                    ->route('productos.edit', $producto)
                    ->with('error', '❌ El producto no tiene código interno de Mercado Libre.');
            }
            
            Log::info("[Producto Sync] Iniciando", [
                'producto_id' => $producto->id,
                'codigo_interno_ml' => $identificador
            ]);
            
            // Sincronizar datos
            $datos = $this->mlService->sincronizarProducto($identificador);
            
            Log::info("[Producto Sync] Datos obtenidos", $datos);

            // Actualizar producto
            $producto->update([
                'stock_full' => $datos['stock_full'],
                'ventas_30_dias' => $datos['ventas_30_dias'],
                'ml_ultimo_sync' => $datos['sincronizado_en'],
            ]);

            $mensaje = "✅ Sincronizado | Stock: {$datos['stock_full']} | Ventas 30d: {$datos['ventas_30_dias']}";

            Log::info("[Producto Sync] Exitoso", ['mensaje' => $mensaje]);

            return redirect()
                ->route('productos.edit', $producto)
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error("[Producto Sync] Error", [
                'producto_id' => $producto->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()
                ->route('productos.edit', $producto)
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    /**
     * Sincronizar todos los productos activos
     * ✨ Usa codigo_interno_ml
     */
    public function sincronizarTodos()
    {
        set_time_limit(300); // 5 minutos
        
        try {
            $sincronizados = 0;
            $errores = 0;
            $sinCodigo = 0;
            $lote = 100;

            // Procesar en lotes
            Producto::where('activo', true)
                ->chunk($lote, function($productos) use (&$sincronizados, &$errores, &$sinCodigo) {
                    foreach ($productos as $producto) {
                        try {
                            if (!$producto->codigo_interno_ml) {
                                $sinCodigo++;
                                Log::warning("[Sync Todos] Sin código: {$producto->id}");
                                continue;
                            }
                            
                            $datos = $this->mlService->sincronizarProducto($producto->codigo_interno_ml);
                            
                            $producto->update([
                                'stock_full' => $datos['stock_full'],
                                'ventas_30_dias' => $datos['ventas_30_dias'],
                                'ml_ultimo_sync' => $datos['sincronizado_en'],
                            ]);
                            
                            $sincronizados++;
                            
                            // Pausa de 200ms entre productos
                            usleep(200000);
                            
                        } catch (\Exception $e) {
                            $errores++;
                            Log::error("[Sync Todos] Error en {$producto->id}: {$e->getMessage()}");
                        }
                    }
                });

            $totalProductos = Producto::where('activo', true)->count();
            $mensaje = "✅ Sincronizados: {$sincronizados}/{$totalProductos}";
            
            if ($errores > 0) {
                $mensaje .= " | ⚠️ Errores: {$errores}";
            }
            
            if ($sinCodigo > 0) {
                $mensaje .= " | ℹ️ Sin código: {$sinCodigo}";
            }

            Log::info("[Sync Todos] Completado", [
                'total' => $totalProductos,
                'sincronizados' => $sincronizados,
                'errores' => $errores,
                'sin_codigo' => $sinCodigo
            ]);

            return redirect()
                ->route('productos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error("[Sync Todos] Error general: {$e->getMessage()}");
            
            return redirect()
                ->route('productos.index')
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }
}