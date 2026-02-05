<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inicio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
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
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
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
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                                </svg>
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
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
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
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
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
                        <a href="{{ route('productos.create') }}" 
                           class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl transition-all duration-300 group">
                            <div class="bg-blue-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </div>
                            <span class="ml-4 text-sm font-medium text-gray-900">Nuevo Producto</span>
                        </a>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('productos.create') }}" 
       class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl transition-all duration-300 group">
        <div class="bg-blue-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
        </div>
        <span class="ml-4 text-sm font-medium text-gray-900">Nuevo Producto</span>
    </a>

    <a href="{{ route('productos.index') }}" 
       class="flex items-center p-4 bg-gradient-to-r from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100 rounded-xl transition-all duration-300 group">
        <div class="bg-emerald-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
        </div>
        <span class="ml-4 text-sm font-medium text-gray-900">Ver Todos los Productos</span>
    </a>

    {{-- NUEVO: Sincronizar con ML --}}
    <form action="{{ route('productos.sincronizar-ml-background') }}" method="POST" class="contents">
        @csrf
        <button type="submit"
                onclick="return confirm('¿Sincronizar todos los productos con Mercado Libre? Esto puede tardar varios minutos.')"
                class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-pink-50 hover:from-purple-100 hover:to-pink-100 rounded-xl transition-all duration-300 group">
            <div class="bg-purple-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <span class="ml-4 text-sm font-medium text-gray-900">Sincronizar con ML</span>
        </button>
    </form>
</div>

                        <a href="{{ route('productos.index') }}" 
                           class="flex items-center p-4 bg-gradient-to-r from-emerald-50 to-green-50 hover:from-emerald-100 hover:to-green-100 rounded-xl transition-all duration-300 group">
                            <div class="bg-emerald-500 rounded-lg p-3 group-hover:scale-110 transition-transform">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <span class="ml-4 text-sm font-medium text-gray-900">Ver Todos los Productos</span>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Productos Prioritarios --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <div class="bg-red-100 rounded-lg p-2 mr-3">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Productos Prioritarios</h3>
                        </div>
                        @if($productosNecesitanFabricacion > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 animate-pulse">
                                {{ $productosNecesitanFabricacion }} productos necesitan atención
                            </span>
                        @endif
                    </div>

                    @if($productosPrioritarios->isEmpty())
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                                <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">¡Todo en orden!</h3>
                            <p class="mt-1 text-sm text-gray-500">No hay productos que necesiten fabricación urgente</p>
                        </div>
                    @else
                        {{-- Alerta de productos críticos --}}
                        @php
                            $productosCriticos = $productosPrioritarios->filter(function($p) {
                                $dias = $p->consumo_diario > 0 ? $p->stock_total / $p->consumo_diario : 999;
                                return $dias < 3;
                            });
                        @endphp
                        
                        @if($productosCriticos->count() > 0)
                            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">
                                            ⚠️ {{ $productosCriticos->count() }} producto(s) CRÍTICO(S)
                                        </h3>
                                        <p class="mt-1 text-sm text-red-700">
                                            Menos de 3 días de stock disponible. ¡Fabricar urgentemente!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prioridad</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Días restantes</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Fabricar</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acción</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($productosPrioritarios as $producto)
                                        @php
                                            $diasRestantes = $producto->consumo_diario > 0 
                                                ? round($producto->stock_total / $producto->consumo_diario, 1)
                                                : 999;
                                            
                                            if ($diasRestantes < 3) {
                                                $prioridadColor = 'red';
                                                $prioridadTexto = '🔴 CRÍTICO';
                                            } elseif ($diasRestantes < 7) {
                                                $prioridadColor = 'orange';
                                                $prioridadTexto = '🟠 URGENTE';
                                            } else {
                                                $prioridadColor = 'yellow';
                                                $prioridadTexto = '🟡 MEDIO';
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-{{ $prioridadColor }}-100 text-{{ $prioridadColor }}-800">
                                                    {{ $prioridadTexto }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $producto->nombre }}</div>
                                                @if($producto->modelo)
                                                    <div class="text-xs text-gray-500">{{ $producto->modelo }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-sm font-semibold text-gray-900">{{ $producto->stock_total }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $prioridadColor }}-100 text-{{ $prioridadColor }}-800">
                                                    @if($diasRestantes < 999)
                                                        {{ $diasRestantes }} días
                                                    @else
                                                        Sin ventas
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                                    {{ $producto->recomendacion_fabricacion }} uds
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('productos.edit', $producto) }}" 
                                                   class="text-blue-600 hover:text-blue-900 font-medium">
                                                    Ver detalles →
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>