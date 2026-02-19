<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('variantes.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900">{{ $variante->nombre }}</h2>
                <p class="text-sm text-gray-500 mt-1">Editando variante</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            {{-- Stats Header --}}
            <div class="mb-8 bg-gradient-to-br from-purple-600 via-purple-700 to-pink-600 rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    <h3 class="text-sm font-bold text-purple-100 mb-4 uppercase tracking-wider flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Estadísticas Actuales
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white/15 backdrop-blur-lg rounded-2xl p-5 text-center border border-white/20">
                            <p class="text-xs font-semibold text-purple-100 mb-2 uppercase tracking-wide">Productos</p>
                            <p class="text-4xl font-bold text-white">{{ $variante->productos->count() }}</p>
                        </div>
                        <div class="bg-white/15 backdrop-blur-lg rounded-2xl p-5 text-center border border-white/20">
                            <p class="text-xs font-semibold text-purple-100 mb-2 uppercase tracking-wide">Ventas</p>
                            <p class="text-4xl font-bold text-white">{{ number_format($variante->ventas_totales) }}</p>
                        </div>
                        <div class="bg-white/15 backdrop-blur-lg rounded-2xl p-5 text-center border border-white/20">
                            <p class="text-xs font-semibold text-purple-100 mb-2 uppercase tracking-wide">Stock</p>
                            <p class="text-4xl font-bold text-white">{{ number_format($variante->stock_total) }}</p>
                        </div>
                        <div class="bg-white/15 backdrop-blur-lg rounded-2xl p-5 text-center border border-white/20">
                            <p class="text-xs font-semibold text-purple-100 mb-2 uppercase tracking-wide">Fabricar</p>
                            <p class="text-4xl font-bold text-white">
                                {{ $variante->recomendacion_fabricacion > 0 ? number_format($variante->recomendacion_fabricacion) : '✓' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('variantes.update', $variante) }}" class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                @csrf
                @method('PUT')

                <div class="p-8 space-y-6">
                    {{-- Campos básicos en grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nombre --}}
                        <div>
                            <label for="nombre" class="block text-sm font-bold text-gray-900 mb-2 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Nombre *
                            </label>
                            <input type="text" name="nombre" id="nombre" required
                                value="{{ old('nombre', $variante->nombre) }}"
                                class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        </div>

                        {{-- Descripción --}}
                        <div>
                            <label for="descripcion" class="block text-sm font-bold text-gray-900 mb-2 flex items-center">
                                <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                </svg>
                                Descripción
                            </label>
                            <input type="text" name="descripcion" id="descripcion"
                                value="{{ old('descripcion', $variante->descripcion) }}"
                                class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="notas" class="block text-sm font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Notas
                        </label>
                        <textarea name="notas" id="notas" rows="2"
                            class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all resize-none">{{ old('notas', $variante->notas) }}</textarea>
                    </div>

                    <div class="border-t-2 border-gray-100 pt-6"></div>

                    {{-- Fundas actuales (collapsible) --}}
                    <details class="p-5 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl border-2 border-blue-200" open>
                        <summary class="cursor-pointer text-base font-bold text-blue-900 hover:text-blue-700 transition flex items-center justify-between">
                            <span class="flex items-center">
                                <svg class="h-6 w-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Fundas Actuales
                            </span>
                            <span class="text-sm font-bold bg-blue-600 text-white px-3 py-1 rounded-full">
                                {{ $variante->productos->count() }}
                            </span>
                        </summary>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 max-h-60 overflow-y-auto pr-2">
                            @foreach ($variante->productos as $prod)
                                <div class="flex items-center justify-between p-3 bg-white rounded-xl shadow-sm border border-blue-100 hover:border-blue-300 transition">
                                    <span class="text-sm text-gray-900 truncate flex-1">{{ $prod->nombre }}</span>
                                    @if ($prod->ventas_totales > 0)
                                        <span class="text-green-600 font-bold text-xs ml-3 bg-green-50 px-2 py-1 rounded-full">
                                            {{ number_format($prod->ventas_totales) }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>

                    {{-- Selector de productos --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            Seleccionar Fundas *
                        </label>

                        {{-- Buscador --}}
                        <div class="relative mb-4">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="searchProductos" placeholder="🔍 Buscar fundas..."
                                class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-sm">
                        </div>

                        {{-- Contador --}}
                        <div class="mb-4 p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border-2 border-purple-200">
                            <p class="text-sm font-semibold text-purple-900 flex items-center justify-between">
                                <span class="flex items-center">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                    </svg>
                                    Fundas seleccionadas
                                </span>
                                <span id="contadorSeleccionados" class="text-2xl font-bold text-purple-600">0</span>
                            </p>
                        </div>

                        {{-- Lista --}}
                        <div class="border-2 border-gray-200 rounded-2xl p-4 max-h-80 overflow-y-auto bg-gray-50 productos-list custom-scrollbar">
                            @php
                                $productosSeleccionados = old('productos', $variante->productos->pluck('id')->toArray());
                            @endphp
                            @foreach ($productos as $producto)
                                @php
                                    $estaSeleccionado = in_array($producto->id, $productosSeleccionados);
                                    $tieneOtraVariante = $producto->usa_variante_para_fabricacion && !$estaSeleccionado;
                                @endphp
                                <label
                                    class="producto-item flex items-center p-3 hover:bg-white rounded-xl transition-all cursor-pointer mb-2 border-2 border-transparent hover:border-purple-200 {{ $tieneOtraVariante ? 'opacity-40' : '' }}"
                                    data-nombre="{{ strtolower($producto->nombre) }}"
                                    data-sku="{{ strtolower($producto->sku_ml) }}">
                                    <input type="checkbox" name="productos[]" value="{{ $producto->id }}"
                                        {{ $estaSeleccionado ? 'checked' : '' }}
                                        {{ $tieneOtraVariante ? 'disabled' : '' }}
                                        class="checkbox-producto w-5 h-5 text-purple-600 border-gray-300 rounded-lg focus:ring-purple-500 focus:ring-2 flex-shrink-0">
                                    <div class="ml-4 flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">
                                            {{ $producto->nombre }}
                                        </span>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs text-gray-500 font-mono">{{ $producto->sku_ml }}</span>
                                            @if ($producto->ventas_totales > 0)
                                                <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                                    {{ number_format($producto->ventas_totales) }}
                                                </span>
                                            @endif
                                            @if ($tieneOtraVariante)
                                                <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">
                                                    🔒 En otra variante
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="px-8 py-6 bg-gray-50 border-t-2 border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('variantes.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 rounded-xl font-semibold transition-all shadow-sm hover:shadow">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #a855f7;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #9333ea;
        }
    </style>

    <script>
        // Buscador
        document.getElementById('searchProductos').addEventListener('input', function(e) {
            const search = e.target.value.toLowerCase();
            document.querySelectorAll('.producto-item').forEach(item => {
                const nombre = item.dataset.nombre;
                const sku = item.dataset.sku;
                item.style.display = (nombre.includes(search) || sku.includes(search)) ? 'flex' : 'none';
            });
        });

        // Contador
        function actualizarContador() {
            document.getElementById('contadorSeleccionados').textContent = 
                document.querySelectorAll('.checkbox-producto:checked:not(:disabled)').length;
        }
        actualizarContador();
        document.querySelectorAll('.checkbox-producto').forEach(cb => {
            cb.addEventListener('change', actualizarContador);
        });
    </script>
</x-app-layout>