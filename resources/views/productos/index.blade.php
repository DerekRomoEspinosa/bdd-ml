<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Productos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm animate-fade-in">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botones de acción --}}
            <div class="mb-6 flex flex-wrap gap-3">
                <a href="{{ route('productos.import.form') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                        </path>
                    </svg>
                    Importar Excel
                </a>

                <a href="{{ route('productos.export') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Exportar Excel
                </a>

                <a href="{{ route('productos.create') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Producto
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">

                    @if ($productos->isEmpty() && !request('buscar'))
                        <div class="text-center py-16">
                            <div
                                class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-4">
                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay productos</h3>
                            <p class="text-gray-500 mb-6">Comienza importando productos desde Excel o creando uno nuevo
                            </p>
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('productos.import.form') }}"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Importar Excel
                                </a>
                                <a href="{{ route('productos.create') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Crear Producto
                                </a>
                            </div>
                        </div>
                    @else
                        {{-- Buscador --}}
                        <div class="mb-6">
                            <form method="GET" action="{{ route('productos.index') }}" class="flex gap-3">
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="buscar" value="{{ request('buscar') }}"
                                        placeholder="Buscar por nombre, modelo o SKU..."
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-3 bg-gray-700 hover:bg-gray-800 text-white rounded-xl font-medium transition">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Buscar
                                </button>
                                @if (request('buscar'))
                                    <a href="{{ route('productos.index') }}"
                                        class="inline-flex items-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-xl font-medium transition">
                                        Limpiar
                                    </a>
                                @endif
                            </form>
                        </div>

                        {{-- Filtros rápidos --}}
                        <div class="mb-6 flex flex-wrap gap-2">
                            <a href="{{ route('productos.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition {{ !request('filtro') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"></path>
                                </svg>
                                Todos
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full {{ !request('filtro') ? 'bg-blue-500' : 'bg-gray-200' }}">
                                    {{ $contadores['todos'] }}
                                </span>
                            </a>

                            <a href="{{ route('productos.index', ['filtro' => 'criticos'] + request()->except('filtro')) }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition {{ request('filtro') == 'criticos' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                                🔴 Críticos
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full {{ request('filtro') == 'criticos' ? 'bg-red-500' : 'bg-red-200' }}">
                                    {{ $contadores['criticos'] }}
                                </span>
                            </a>

                            <a href="{{ route('productos.index', ['filtro' => 'urgentes'] + request()->except('filtro')) }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition {{ request('filtro') == 'urgentes' ? 'bg-orange-600 text-white' : 'bg-orange-50 text-orange-700 hover:bg-orange-100' }}">
                                🟠 Urgentes
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full {{ request('filtro') == 'urgentes' ? 'bg-orange-500' : 'bg-orange-200' }}">
                                    {{ $contadores['urgentes'] }}
                                </span>
                            </a>

                            <a href="{{ route('productos.index', ['filtro' => 'necesitan_fabricacion'] + request()->except('filtro')) }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition {{ request('filtro') == 'necesitan_fabricacion' ? 'bg-yellow-600 text-white' : 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' }}">
                                🟡 Necesitan Fabricación
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full {{ request('filtro') == 'necesitan_fabricacion' ? 'bg-yellow-500' : 'bg-yellow-200' }}">
                                    {{ $contadores['necesitan_fabricacion'] }}
                                </span>
                            </a>

                            <a href="{{ route('productos.index', ['filtro' => 'stock_ok'] + request()->except('filtro')) }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition {{ request('filtro') == 'stock_ok' ? 'bg-green-600 text-white' : 'bg-green-50 text-green-700 hover:bg-green-100' }}">
                                ✅ Stock OK
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs rounded-full {{ request('filtro') == 'stock_ok' ? 'bg-green-500' : 'bg-green-200' }}">
                                    {{ $contadores['stock_ok'] }}
                                </span>
                            </a>
                        </div>

                        @if ($productos->isEmpty())
                            <div class="text-center py-16">
                                <div
                                    class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-4">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">No se encontraron productos</h3>
                                <p class="text-gray-500 mb-4">
                                    @if (request('filtro'))
                                        No hay productos en esta categoría
                                        @if (request('buscar'))
                                            que coincidan con "{{ request('buscar') }}"
                                        @endif
                                    @else
                                        Intenta con otro término de búsqueda
                                    @endif
                                </p>
                                <a href="{{ route('productos.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Ver todos los productos
                                </a>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Producto
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Plantilla
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Inventario Interno
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Datos ML
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Stock Total
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Fabricar
                                            </th>
                                            <th scope="col" class="relative px-6 py-4">
                                                <span class="sr-only">Acciones</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach ($productos as $producto)
                                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                                {{-- PRODUCTO --}}
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $producto->nombre }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 mt-1 font-mono">
                                                        {{ $producto->sku_ml }}</div>
                                                </td>

                                                {{-- PLANTILLA DE CORTE --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if ($producto->plantilla_corte_url)
                                                        <a href="{{ $producto->plantilla_corte_url }}"
                                                            target="_blank"
                                                            class="inline-flex items-center px-3 py-1 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition text-xs font-medium">
                                                            <svg class="h-4 w-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                                </path>
                                                            </svg>
                                                            Ver
                                                        </a>
                                                    @else
                                                        <span class="text-xs text-gray-400">Sin plantilla</span>
                                                    @endif
                                                </td>

                                                {{-- INVENTARIO INTERNO --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-xs text-gray-600 space-y-1">
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Bodega:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_bodega }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Cortado:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_cortado }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Costura:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_costura }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Por empacar:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_por_empacar }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Enviado:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_enviado_full }}</span>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- DATOS ML --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-xs text-gray-600 space-y-1">
                                                        @if ($producto->codigo_interno_ml)
                                                            <div class="flex justify-center items-center gap-1">
                                                                <span class="text-gray-500">Código Int:</span>
                                                                <span
                                                                    class="font-semibold text-gray-900 font-mono text-xs">{{ $producto->codigo_interno_ml }}</span>
                                                            </div>
                                                        @endif
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Full:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->stock_full ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Ventas 30d:</span>
                                                            <span
                                                                class="font-semibold text-gray-900">{{ $producto->ventas_30_dias ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    @if ($producto->ml_ultimo_sync)
                                                        <div class="text-xs text-gray-400 mt-2">
                                                            {{ $producto->ml_ultimo_sync->diffForHumans() }}
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- STOCK TOTAL --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                                        {{ $producto->stock_total }}
                                                    </span>
                                                </td>

                                                {{-- FABRICAR --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if ($producto->recomendacion_fabricacion > 0)
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                                            {{ $producto->recomendacion_fabricacion }} uds
                                                        </span>
                                                    @else
                                                        <span
                                                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                            ✓ Stock OK
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- ACCIONES --}}
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end gap-2">
                                                        <a href="{{ route('productos.edit', $producto) }}"
                                                            class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                            Editar
                                                        </a>
                                                        <form action="{{ route('productos.destroy', $producto) }}"
                                                            method="POST" class="inline"
                                                            onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                                                Eliminar
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Paginación --}}
                            @if ($productos->hasPages())
                                <div
                                    class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-b-xl">
                                    <div class="flex flex-1 justify-between sm:hidden">
                                        @if ($productos->onFirstPage())
                                            <span
                                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
                                                Anterior
                                            </span>
                                        @else
                                            <a href="{{ $productos->previousPageUrl() }}"
                                                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                Anterior
                                            </a>
                                        @endif

                                        @if ($productos->hasMorePages())
                                            <a href="{{ $productos->nextPageUrl() }}"
                                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                Siguiente
                                            </a>
                                        @else
                                            <span
                                                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-400">
                                                Siguiente
                                            </span>
                                        @endif
                                    </div>
                                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Mostrando
                                                <span class="font-medium">{{ $productos->firstItem() }}</span>
                                                a
                                                <span class="font-medium">{{ $productos->lastItem() }}</span>
                                                de
                                                <span class="font-medium">{{ $productos->total() }}</span>
                                                productos
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                                {{-- Botón Anterior --}}
                                                @if ($productos->onFirstPage())
                                                    <span
                                                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @else
                                                    <a href="{{ $productos->previousPageUrl() }}"
                                                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </a>
                                                @endif

                                                {{-- Números de página --}}
                                                @foreach ($productos->getUrlRange(1, $productos->lastPage()) as $page => $url)
                                                    @if ($page == $productos->currentPage())
                                                        <span
                                                            class="relative z-10 inline-flex items-center bg-blue-600 px-4 py-2 text-sm font-semibold text-white">
                                                            {{ $page }}
                                                        </span>
                                                    @else
                                                        <a href="{{ $url }}"
                                                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                            {{ $page }}
                                                        </a>
                                                    @endif
                                                @endforeach

                                                {{-- Botón Siguiente --}}
                                                @if ($productos->hasMorePages())
                                                    <a href="{{ $productos->nextPageUrl() }}"
                                                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span
                                                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 cursor-not-allowed">
                                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                @endif
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
