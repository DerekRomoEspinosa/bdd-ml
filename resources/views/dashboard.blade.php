<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- BLOQUE DE CONEXIÓN MERCADO LIBRE --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 rounded-xl bg-gray-50 mr-4">
                        <svg class="h-6 w-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Mercado Libre API</h3>
                        <p class="text-sm text-gray-500">
                            {{ DB::table('mercadolibre_tokens')->find(1) ? 'Conectado.' : 'No vinculado. Los datos de ML no se actualizarán.' }}
                        </p>
                    </div>
                </div>
                @if(!DB::table('mercadolibre_tokens')->find(1))
                    <a href="{{ route('ml.login') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 shadow-sm rounded-xl text-sm font-bold text-gray-900">
                        <span class="mr-2">🟡</span> Vincular Cuenta
                    </a>
                @endif
            </div>

            {{-- Tarjetas de métricas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Total Productos --}}
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-100 text-sm font-medium">Total Productos</p>
                                <p class="text-white text-3xl font-bold mt-2">{{ number_format($totalProductos) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stock Total --}}
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-emerald-100 text-sm font-medium">Stock Total</p>
                                <p class="text-white text-3xl font-bold mt-2">{{ number_format($stockTotal) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Necesitan Fabricación --}}
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-100 text-sm font-medium">Necesitan Fabricación</p>
                                <p class="text-white text-3xl font-bold mt-2">{{ number_format($productosNecesitanFabricacion) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unidades a Fabricar --}}
                <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-100 text-sm font-medium">Unidades a Fabricar</p>
                                <p class="text-white text-3xl font-bold mt-2">{{ number_format($unidadesAFabricar) }}</p>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-xl p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Acciones Rápidas --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('productos.create') }}" class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl transition-all duration-300 group">
                            <div class="bg-blue-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </div>
                            <span class="ml-4 text-sm font-medium text-gray-900">Nuevo Producto</span>
                        </a>

                        <form action="{{ route('productos.sync-ml-directo') }}" method="POST" class="contents">
                            @csrf
                            <button type="submit" class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 rounded-xl transition-all duration-300 group">
                                <div class="bg-purple-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </div>
                                <span class="ml-4 text-sm font-medium text-gray-900 text-left">Sincronizar con ML</span>
                            </button>
                        </form>

                        <a href="{{ route('productos.index') }}" class="flex items-center p-4 bg-gradient-to-r from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100 rounded-xl transition-all duration-300 group">
                            <div class="bg-emerald-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                            </div>
                            <span class="ml-4 text-sm font-medium text-gray-900">Ver Todos los Productos</span>
                        </a>

                        <a href="{{ route('productos.export') }}" class="flex items-center p-4 bg-gradient-to-r from-red-50 to-orange-50 hover:from-red-100 hover:to-orange-100 rounded-xl transition-all duration-300 group">
                            <div class="bg-red-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <span class="ml-4 text-sm font-medium text-gray-900">Exportar Excel</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Gráfica de Stock --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Estado General del Stock</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div><canvas id="stockChart" style="max-height: 300px;"></canvas></div>
                        <div class="flex flex-col justify-center space-y-4">
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                                <div class="flex items-center"><div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div><span class="text-sm font-medium text-gray-700">Stock OK</span></div>
                                <span class="text-2xl font-bold text-green-600">{{ $totalProductos - $productosNecesitanFabricacion }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-red-50 rounded-xl">
                                <div class="flex items-center"><div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div><span class="text-sm font-medium text-gray-700">Necesitan Fabricación</span></div>
                                <span class="text-2xl font-bold text-red-600">{{ $productosNecesitanFabricacion }}</span>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                                <div class="flex items-center"><div class="w-4 h-4 bg-blue-500 rounded-full mr-3"></div><span class="text-sm font-medium text-gray-700">Porcentaje OK</span></div>
                                <span class="text-2xl font-bold text-blue-600">{{ $totalProductos > 0 ? round((($totalProductos - $productosNecesitanFabricacion) / $totalProductos) * 100, 1) : 0 }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla de Productos Prioritarios --}}
            @if($productosPrioritarios->count() > 0)
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Productos Prioritarios para Fabricar</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fabricar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridad</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($productosPrioritarios as $producto)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $producto->sku }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $producto->nombre }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($producto->stock_total) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ number_format($producto->recomendacion_fabricacion) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                                <div class="bg-red-600 h-2 rounded-full" style="width: {{ min(($producto->recomendacion_fabricacion / ($unidadesAFabricar > 0 ? $unidadesAFabricar : 1)) * 100, 100) }}%"></div>
                                            </div>
                                            <span class="text-sm text-gray-500">{{ round(($producto->recomendacion_fabricacion / ($unidadesAFabricar > 0 ? $unidadesAFabricar : 1)) * 100, 1) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Script Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('stockChart');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Stock OK', 'Necesitan Fabricación'],
                datasets: [{
                    data: [{{ $totalProductos - $productosNecesitanFabricacion }}, {{ $productosNecesitanFabricacion }}],
                    backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                    borderWidth: 0
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</x-app-layout>