<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-900">Variantes de Bafles</h2>
                <p class="text-sm text-gray-500 mt-1">Agrupa fundas por dimensiones para calcular fabricación</p>
            </div>
            <a href="{{ route('variantes.create') }}"
                class="inline-flex items-center justify-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nueva Variante
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-green-800">{!! session('success') !!}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($variantes->isEmpty())
                {{-- Estado vacío --}}
                <div class="bg-gradient-to-br from-purple-50 via-pink-50 to-blue-50 rounded-3xl shadow-xl overflow-hidden">
                    <div class="p-12 text-center">
                        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-purple-100 to-pink-100 mb-6">
                            <svg class="h-12 w-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">No hay variantes creadas</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            Las variantes te permiten agrupar fundas de bafles con las mismas dimensiones.<br>
                            <strong>El sistema calculará automáticamente cuántas fabricar en total.</strong>
                        </p>
                        <a href="{{ route('variantes.create') }}"
                            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-2xl font-bold text-lg shadow-xl hover:shadow-2xl transition-all transform hover:scale-105">
                            <svg class="h-6 w-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Crear Mi Primera Variante
                        </a>
                    </div>
                </div>
            @else
                {{-- Stats generales --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                    @php
                        $totalVariantes = $variantes->count();
                        $totalProductos = $variantes->sum(fn($v) => $v->productos->count());
                        $totalVentas = $variantes->sum('ventas_totales');
                        $totalFabricar = $variantes->sum('recomendacion_fabricacion');
                    @endphp
                    
                    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Variantes</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalVariantes }}</p>
                            </div>
                            <div class="bg-purple-100 rounded-xl p-3">
                                <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Fundas Totales</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalProductos }}</p>
                            </div>
                            <div class="bg-blue-100 rounded-xl p-3">
                                <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Ventas Totales</p>
                                <p class="text-3xl font-bold text-gray-900 mt-1">{{ number_format($totalVentas) }}</p>
                            </div>
                            <div class="bg-green-100 rounded-xl p-3">
                                <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow p-6 border-l-4 {{ $totalFabricar > 0 ? 'border-red-500' : 'border-gray-300' }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">A Fabricar</p>
                                <p class="text-3xl font-bold {{ $totalFabricar > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">
                                    {{ $totalFabricar > 0 ? number_format($totalFabricar) : '✓' }}
                                </p>
                            </div>
                            <div class="{{ $totalFabricar > 0 ? 'bg-red-100' : 'bg-gray-100' }} rounded-xl p-3">
                                <svg class="h-8 w-8 {{ $totalFabricar > 0 ? 'text-red-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Grid de variantes --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($variantes as $variante)
                        <div class="group bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden border border-gray-100 hover:border-purple-200">
                            {{-- Header con gradiente --}}
                            <div class="relative bg-gradient-to-br from-purple-600 via-purple-700 to-pink-600 p-6 pb-8">
                                {{-- Badge de productos --}}
                                <div class="absolute top-4 right-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-white/25 backdrop-blur-sm text-white border border-white/30">
                                        <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                        {{ $variante->productos->count() }}
                                    </span>
                                </div>

                                <h3 class="text-2xl font-bold text-white mb-2 pr-20">
                                    {{ $variante->nombre }}
                                </h3>
                                @if ($variante->descripcion)
                                    <p class="text-purple-100 text-sm line-clamp-2">
                                        {{ $variante->descripcion }}
                                    </p>
                                @endif
                            </div>

                            {{-- Stats cards --}}
                            <div class="-mt-4 px-6 pb-4">
                                <div class="grid grid-cols-3 gap-3">
                                    <div class="bg-white rounded-xl shadow-md p-4 text-center border border-gray-100">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Ventas</p>
                                        <p class="text-xl font-bold text-blue-600">{{ number_format($variante->ventas_totales) }}</p>
                                    </div>

                                    <div class="bg-white rounded-xl shadow-md p-4 text-center border border-gray-100">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Stock</p>
                                        <p class="text-xl font-bold text-green-600">{{ number_format($variante->stock_total) }}</p>
                                    </div>

                                    <div class="bg-white rounded-xl shadow-md p-4 text-center border border-gray-100">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Fabricar</p>
                                        <p class="text-xl font-bold {{ $variante->recomendacion_fabricacion > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                            @if ($variante->recomendacion_fabricacion > 0)
                                                {{ number_format($variante->recomendacion_fabricacion) }}
                                            @else
                                                ✓
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Contenido --}}
                            <div class="px-6 pb-6">
                                {{-- Fundas (collapsible) --}}
                                <details class="mb-4 group/details">
                                    <summary class="cursor-pointer flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                                        <span class="text-sm font-semibold text-gray-700 flex items-center">
                                            <svg class="h-4 w-4 mr-2 text-purple-500 group-open/details:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            Fundas incluidas
                                        </span>
                                        <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-1 rounded-full">
                                            {{ $variante->productos->count() }}
                                        </span>
                                    </summary>
                                    <div class="mt-3 space-y-2 max-h-40 overflow-y-auto pr-2">
                                        @foreach ($variante->productos->take(10) as $producto)
                                            <div class="flex items-center justify-between p-2.5 bg-gray-50 hover:bg-purple-50 rounded-lg transition-colors text-sm border border-transparent hover:border-purple-200">
                                                <span class="text-gray-700 truncate flex-1 mr-2">{{ $producto->nombre }}</span>
                                                @if ($producto->ventas_totales > 0)
                                                    <span class="text-green-600 font-bold text-xs whitespace-nowrap">
                                                        {{ number_format($producto->ventas_totales) }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($variante->productos->count() > 10)
                                            <p class="text-xs text-center text-gray-500 italic pt-1">
                                                +{{ $variante->productos->count() - 10 }} más...
                                            </p>
                                        @endif
                                    </div>
                                </details>

                                {{-- Notas --}}
                                @if ($variante->notas)
                                    <div class="mb-4 p-3 bg-yellow-50 border-l-3 border-yellow-400 rounded-lg">
                                        <p class="text-xs font-bold text-yellow-800 mb-1 flex items-center">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Notas
                                        </p>
                                        <p class="text-xs text-yellow-700 line-clamp-2">{{ $variante->notas }}</p>
                                    </div>
                                @endif

                                {{-- Botones de acción --}}
                                <div class="flex gap-2">
                                    <a href="{{ route('variantes.edit', $variante) }}"
                                        class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-semibold transition-all text-sm shadow-md hover:shadow-lg transform hover:scale-105">
                                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Editar
                                    </a>
                                    <form action="{{ route('variantes.destroy', $variante) }}" method="POST" class="flex-1"
                                        onsubmit="return confirm('⚠️ ¿Eliminar {{ $variante->nombre }}?\n\nLas fundas volverán a calcular fabricación individual.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold transition-all text-sm shadow-md hover:shadow-lg">
                                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>