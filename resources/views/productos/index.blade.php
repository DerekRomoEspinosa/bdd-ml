<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Productos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de éxito --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm animate-fade-in">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Importar Excel
                </a>

                <a href="{{ route('productos.export') }}" 
                   class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
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
                    
                    @if($productos->isEmpty() && !request('buscar'))
                        <div class="text-center py-16">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-4">
                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay productos</h3>
                            <p class="text-gray-500 mb-6">Comienza importando productos desde Excel o creando uno nuevo</p>
                            <div class="flex justify-center gap-3">
                                <a href="{{ route('productos.import.form') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    Importar Excel
                                </a>
                                <a href="{{ route('productos.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
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
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" 
                                           name="buscar" 
                                           value="{{ request('buscar') }}"
                                           placeholder="Buscar por nombre, modelo o SKU..." 
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                </div>
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gray-700 hover:bg-gray-800 text-white rounded-xl font-medium transition">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                    Buscar
                                </button>
                                @if(request('buscar'))
                                    <a href="{{ route('productos.index') }}" 
                                       class="inline-flex items-center px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-xl font-medium transition">
                                        Limpiar
                                    </a>
                                @endif
                            </form>
                        </div>

                        @if($productos->isEmpty())
                            <div class="text-center py-16">
                                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-4">
                                    <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-900 mb-2">No se encontraron productos</h3>
                                <p class="text-gray-500 mb-4">Intenta con otro término de búsqueda</p>
                                <a href="{{ route('productos.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Ver todos los productos
                                </a>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Producto
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                SKU ML
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Inventario Interno
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Datos ML
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Stock Total
                                            </th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                                Fabricar
                                            </th>
                                            <th scope="col" class="relative px-6 py-4">
                                                <span class="sr-only">Acciones</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        @foreach($productos as $producto)
                                            <tr class="hover:bg-blue-50 transition-colors duration-150">
                                                <td class="px-6 py-4">
                                                    <div class="text-sm font-semibold text-gray-900">
                                                        {{ $producto->nombre }}
                                                    </div>
                                                    @if($producto->modelo)
                                                        <div class="text-xs text-gray-500 mt-1">{{ $producto->modelo }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm text-gray-600 font-mono bg-gray-50 px-2 py-1 rounded">
                                                        {{ $producto->sku_ml }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-xs text-gray-600 space-y-1">
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Bodega:</span>
                                                            <span class="font-semibold text-gray-900">{{ $producto->stock_bodega }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Cortado:</span>
                                                            <span class="font-semibold text-gray-900">{{ $producto->stock_cortado }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Enviado:</span>
                                                            <span class="font-semibold text-gray-900">{{ $producto->stock_enviado_full }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="text-xs text-gray-600 space-y-1">
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Full:</span>
                                                            <span class="font-semibold text-gray-900">{{ $producto->stock_full ?? '-' }}</span>
                                                        </div>
                                                        <div class="flex justify-center items-center gap-1">
                                                            <span class="text-gray-500">Ventas 30d:</span>
                                                            <span class="font-semibold text-gray-900">{{ $producto->ventas_30_dias ?? '-' }}</span>
                                                        </div>
                                                    </div>
                                                    @if($producto->ml_ultimo_sync)
                                                        <div class="text-xs text-gray-400 mt-2">
                                                            {{ $producto->ml_ultimo_sync->diffForHumans() }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                                        {{ $producto->stock_total }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    @if($producto->recomendacion_fabricacion > 0)
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                                            {{ $producto->recomendacion_fabricacion }} uds
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                            ✓ Stock OK
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex justify-end gap-2">
                                                        <a href="{{ route('productos.edit', $producto) }}" 
                                                           class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                            Editar
                                                        </a>
                                                        <form action="{{ route('productos.destroy', $producto) }}" 
                                                              method="POST" 
                                                              class="inline"
                                                              onsubmit="return confirm('¿Estás seguro de eliminar este producto?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
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
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>