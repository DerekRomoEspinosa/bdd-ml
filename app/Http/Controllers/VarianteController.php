<?php

namespace App\Http\Controllers;

use App\Models\Variante;
use App\Models\Producto;
use Illuminate\Http\Request;

class VarianteController extends Controller
{
    public function index()
    {
        try {
            $variantes = Variante::where('activo', true)
                ->with('productos')
                ->orderBy('nombre')
                ->get();

            return view('variantes.index', compact('variantes'));

        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function create()
    {
        $productos = Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->orderBy('nombre')
            ->get();

        return view('variantes.create', compact('productos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:variantes',
            'descripcion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*' => 'exists:productos,id',
        ]);

        $variante = Variante::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'notas' => $validated['notas'] ?? null,
            'activo' => true,
        ]);

        $variante->productos()->attach($validated['productos']);

        Producto::whereIn('id', $validated['productos'])
            ->update(['usa_variante_para_fabricacion' => true]);

        return redirect()->route('variantes.index')
            ->with('success', '✅ Variante creada correctamente');
    }

    public function edit(Variante $variante)
    {
        $productos = Producto::where('activo', true)
            ->whereNotNull('codigo_interno_ml')
            ->where('codigo_interno_ml', '!=', '')
            ->orderBy('nombre')
            ->get();

        return view('variantes.edit', compact('variante', 'productos'));
    }

    public function update(Request $request, Variante $variante)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:variantes,nombre,' . $variante->id,
            'descripcion' => 'nullable|string|max:255',
            'notas' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*' => 'exists:productos,id',
        ]);

        $variante->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
            'notas' => $validated['notas'] ?? null,
        ]);

        $productosAnteriores = $variante->productos()->pluck('productos.id')->toArray();

        $variante->productos()->sync($validated['productos']);

        Producto::whereIn('id', $validated['productos'])
            ->update(['usa_variante_para_fabricacion' => true]);

        $productosRemovidos = array_diff($productosAnteriores, $validated['productos']);

        if (!empty($productosRemovidos)) {
            Producto::whereIn('id', $productosRemovidos)
                ->whereDoesntHave('variantes')
                ->update(['usa_variante_para_fabricacion' => false]);
        }

        return redirect()->route('variantes.index')
            ->with('success', '✅ Variante actualizada correctamente');
    }

    public function destroy(Variante $variante)
    {
        $productos = $variante->productos()->pluck('productos.id')->toArray();

        $variante->productos()->detach();
        $variante->update(['activo' => false]);

        Producto::whereIn('id', $productos)
            ->whereDoesntHave('variantes')
            ->update(['usa_variante_para_fabricacion' => false]);

        return redirect()->route('variantes.index')
            ->with('success', '✅ Variante eliminada');
    }
}
