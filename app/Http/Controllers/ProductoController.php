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

        // 🛠 Lógica centralizada de Stock Real para cálculos
        $stockReal = "(stock_bodega + stock_enviado_full + COALESCE(stock_full, 0))";
        // ✅ CAMBIADO: usar ventas_30_dias_calculadas en lugar de ventas_30_dias
        $promedioVenta = "(COALESCE(ventas_30_dias_calculadas, 0) / 30)";

        // Búsqueda por texto
        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('modelo', 'like', "%{$buscar}%")
                    ->orWhere('sku_ml', 'like', "%{$buscar}%")
                    ->orWhere('codigo_interno_ml', 'like', "%{$buscar}%");
            });
        }

        // Filtros por estado ajustados
        if ($filtro) {
            switch ($filtro) {
                case 'criticos':
                    $query->whereRaw("CASE 
                        WHEN $promedioVenta > 0 
                        THEN $stockReal / $promedioVenta < 3
                        ELSE false 
                    END");
                    break;

                case 'urgentes':
                    $query->whereRaw("CASE 
                        WHEN $promedioVenta > 0 
                        THEN $stockReal / $promedioVenta < 7
                        ELSE false 
                    END");
                    break;

                case 'necesitan_fabricacion':
                    $query->whereRaw("GREATEST(
                        CEILING(($promedioVenta * 15) - $stockReal),
                        0
                    ) > 0");
                    break;

                case 'stock_ok':
                    $query->whereRaw("GREATEST(
                        CEILING(($promedioVenta * 15) - $stockReal),
                        0
                    ) = 0");
                    break;
            }
        }

        $productos = $query
            ->orderByRaw('CASE WHEN codigo_interno_ml IS NOT NULL AND codigo_interno_ml != "" THEN 0 ELSE 1 END')
            ->orderBy('nombre')
            ->paginate(50)
            ->appends($request->all());

        // Contadores corregidos
        $contadores = [
            'todos' => Producto::where('activo', true)->count(),
            'criticos' => Producto::where('activo', true)
                ->whereRaw("CASE 
                    WHEN $promedioVenta > 0 
                    THEN $stockReal / $promedioVenta < 3
                    ELSE false 
                END")->count(),
            'urgentes' => Producto::where('activo', true)
                ->whereRaw("CASE 
                    WHEN $promedioVenta > 0 
                    THEN $stockReal / $promedioVenta < 7
                    ELSE false 
                END")->count(),
            'necesitan_fabricacion' => Producto::where('activo', true)
                ->whereRaw("GREATEST(
                    CEILING(($promedioVenta * 15) - $stockReal),
                    0
                ) > 0")->count(),
            'stock_ok' => Producto::where('activo', true)
                ->whereRaw("GREATEST(
                    CEILING(($promedioVenta * 15) - $stockReal),
                    0
                ) = 0")->count(),
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

        return redirect()->route('productos.index')->with('success', '✅ Producto creado correctamente');
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

        return redirect()->route('productos.index')->with('success', '✅ Producto actualizado correctamente');
    }

    public function destroy(Producto $producto)
    {
        $producto->update(['activo' => false]);
        return redirect()->route('productos.index')->with('success', 'Producto eliminado');
    }

    public function sincronizar(Producto $producto)
    {
        try {
            $identificador = $producto->codigo_interno_ml;

            if (!$identificador) {
                return redirect()->route('productos.edit', $producto)
                    ->with('error', '❌ El producto no tiene código interno de Mercado Libre.');
            }

            $datos = $mlService->sincronizarProducto($identificador);

            if ($datos['status'] === 'error') {
                return redirect()->route('productos.edit', $producto)
                    ->with('error', '❌ Error al sincronizar con la API de ML.');
            }

            $producto->update([
                'stock_full' => $datos['stock_full'] ?? 0,
                'ventas_30_dias' => $datos['ventas_totales'] ?? 0,
                'ml_ultimo_sync' => $datos['sincronizado_en'],
            ]);

            return redirect()->route('productos.edit', $producto)
                ->with('success', "✅ Sincronizado | Stock Full: {$datos['stock_full']} | Ventas Totales: {$datos['ventas_totales']}");
        } catch (\Exception $e) {
            Log::error("[Producto Sync] Error: " . $e->getMessage());
            return redirect()->route('productos.edit', $producto)->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    public function sincronizarTodos()
    {
        set_time_limit(600);

        try {
            $sincronizados = 0;
            $errores = 0;
            $sinCodigo = 0;

            Producto::where('activo', true)->chunk(50, function ($productos) use (&$sincronizados, &$errores, &$sinCodigo) {
                foreach ($productos as $producto) {
                    try {
                        if (!$producto->codigo_interno_ml) {
                            $sinCodigo++;
                            continue;
                        }

                        $datos = $this->mlService->sincronizarProducto($producto->codigo_interno_ml);

                        if ($datos['status'] !== 'error') {
                            $producto->update([
                                'stock_full' => $datos['stock_full'] ?? 0,
                                'ventas_30_dias' => $datos['ventas_totales'] ?? 0, // ✅
                                'ml_ultimo_sync' => $datos['sincronizado_en'],
                            ]);
                            $sincronizados++;
                        } else {
                            $errores++;
                        }

                        usleep(250000);
                    } catch (\Exception $e) {
                        $errores++;
                        Log::error("[Sync Todos] Error en ID {$producto->id}: {$e->getMessage()}");
                    }
                }
            });

            $mensaje = "📊 Sincronización Finalizada: ✅ $sincronizados actualizados | ⚠️ $errores errores | ℹ️ $sinCodigo sin código.";

            return redirect()->route('productos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            Log::error("[Sync Todos] Error general: {$e->getMessage()}");
            return redirect()->route('productos.index')->with('error', '❌ Error crítico en sincronización.');
        }
    }
}
