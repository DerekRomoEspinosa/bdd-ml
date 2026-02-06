<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Producto
            </h2>
            <a href="{{ route('productos.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition text-sm font-medium">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver a la lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Errores de validación --}}
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Hay errores en el formulario:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('productos.update', $producto) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    {{-- COLUMNA IZQUIERDA: Información Básica --}}
                    <div class="lg:col-span-2 space-y-6">
                        
                        {{-- Card: Información Básica --}}
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-4">
                                <h3 class="text-lg font-semibold text-white">Información Básica</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                {{-- Nombre --}}
                                <div>
                                    <label for="nombre" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nombre del Producto *
                                    </label>
                                    <input type="text" 
                                           name="nombre" 
                                           id="nombre" 
                                           value="{{ old('nombre', $producto->nombre) }}"
                                           required
                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition @error('nombre') border-red-500 @enderror">
                                    @error('nombre')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {{-- Modelo --}}
                                    <div>
                                        <label for="modelo" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Modelo
                                        </label>
                                        <input type="text" 
                                               name="modelo" 
                                               id="modelo" 
                                               value="{{ old('modelo', $producto->modelo) }}"
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition">
                                    </div>

                                    {{-- SKU ML --}}
                                    <div>
                                        <label for="sku_ml" class="block text-sm font-semibold text-gray-700 mb-2">
                                            SKU Mercado Libre *
                                        </label>
                                        <input type="text" 
                                               name="sku_ml" 
                                               id="sku_ml" 
                                               value="{{ old('sku_ml', $producto->sku_ml) }}"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition font-mono text-sm @error('sku_ml') border-red-500 @enderror">
                                        @error('sku_ml')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Código Interno ML --}}
                                <div>
                                    <label for="codigo_interno_ml" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Código Interno ML
                                    </label>
                                    <input type="text" 
                                           name="codigo_interno_ml" 
                                           id="codigo_interno_ml" 
                                           value="{{ old('codigo_interno_ml', $producto->codigo_interno_ml) }}"
                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition font-mono text-sm"
                                           placeholder="3113495728">
                                    <p class="mt-1 text-xs text-gray-500">Código interno de 10 dígitos de Mercado Libre</p>
                                </div>

                                {{-- Plantilla de Corte --}}
                                <div>
                                    <label for="plantilla_corte_url" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Link Plantilla de Corte (OneDrive)
                                    </label>
                                    <input type="url" 
                                           name="plantilla_corte_url" 
                                           id="plantilla_corte_url" 
                                           value="{{ old('plantilla_corte_url', $producto->plantilla_corte_url) }}"
                                           class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 transition"
                                           placeholder="https://1drv.ms/...">
                                    @if($producto->plantilla_corte_url)
                                        <div class="mt-2">
                                            <a href="{{ $producto->plantilla_corte_url }}" 
                                               target="_blank"
                                               class="inline-flex items-center px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 rounded-lg transition text-sm font-medium">
                                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Ver plantilla actual
                                            </a>
                                        </div>
                                    @endif
                                    <p class="mt-1 text-xs text-gray-500">Link compartido de OneDrive con la imagen de la plantilla</p>
                                </div>
                            </div>
                        </div>

                        {{-- Card: Inventario Interno --}}
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-4">
                                <h3 class="text-lg font-semibold text-white">Inventario Interno</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    {{-- Stock Bodega --}}
                                    <div>
                                        <label for="stock_bodega" class="block text-sm font-semibold text-gray-700 mb-2">
                                            🏭 Stock en Bodega
                                        </label>
                                        <input type="number" 
                                               name="stock_bodega" 
                                               id="stock_bodega" 
                                               value="{{ old('stock_bodega', $producto->stock_bodega) }}"
                                               min="0"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition text-lg font-semibold text-center">
                                    </div>

                                    {{-- Stock Cortado --}}
                                    <div>
                                        <label for="stock_cortado" class="block text-sm font-semibold text-gray-700 mb-2">
                                            ✂️ Fundas Cortadas
                                        </label>
                                        <input type="number" 
                                               name="stock_cortado" 
                                               id="stock_cortado" 
                                               value="{{ old('stock_cortado', $producto->stock_cortado) }}"
                                               min="0"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition text-lg font-semibold text-center">
                                    </div>

                                    {{-- Stock en Costura --}}
                                    <div>
                                        <label for="stock_costura" class="block text-sm font-semibold text-gray-700 mb-2">
                                            🧵 En Costura
                                        </label>
                                        <input type="number" 
                                               name="stock_costura" 
                                               id="stock_costura" 
                                               value="{{ old('stock_costura', $producto->stock_costura) }}"
                                               min="0"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition text-lg font-semibold text-center">
                                    </div>

                                    {{-- Stock por Empacar --}}
                                    <div>
                                        <label for="stock_por_empacar" class="block text-sm font-semibold text-gray-700 mb-2">
                                            📦 Por Empacar
                                        </label>
                                        <input type="number" 
                                               name="stock_por_empacar" 
                                               id="stock_por_empacar" 
                                               value="{{ old('stock_por_empacar', $producto->stock_por_empacar) }}"
                                               min="0"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition text-lg font-semibold text-center">
                                    </div>

                                    {{-- Stock Enviado Full --}}
                                    <div>
                                        <label for="stock_enviado_full" class="block text-sm font-semibold text-gray-700 mb-2">
                                            🚚 Enviado a Full
                                        </label>
                                        <input type="number" 
                                               name="stock_enviado_full" 
                                               id="stock_enviado_full" 
                                               value="{{ old('stock_enviado_full', $producto->stock_enviado_full) }}"
                                               min="0"
                                               required
                                               class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition text-lg font-semibold text-center">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('productos.index') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-semibold transition">
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition transform hover:scale-105">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Guardar Cambios
                            </button>
                        </div>
                    </div>

                    {{-- COLUMNA DERECHA: Análisis y ML --}}
                    <div class="space-y-6">
                        
                        {{-- Card: Análisis --}}
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-lg overflow-hidden border border-blue-100">
                            <div class="px-6 py-4 border-b border-blue-100">
                                <h3 class="text-lg font-semibold text-gray-900">📊 Análisis</h3>
                            </div>
                            <div class="p-6 space-y-4">
                                {{-- Stock Total --}}
                                <div class="bg-white rounded-xl p-4 shadow-sm">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Stock Total</p>
                                    <p class="text-3xl font-bold text-blue-600">{{ $producto->stock_total }}</p>
                                    <p class="text-xs text-gray-500 mt-1">unidades disponibles</p>
                                </div>
                                
                                {{-- Consumo Diario --}}
                                <div class="bg-white rounded-xl p-4 shadow-sm">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Consumo Diario</p>
                                    <p class="text-3xl font-bold text-green-600">{{ number_format($producto->consumo_diario, 1) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">uds/día promedio</p>
                                </div>
                                
                                {{-- Recomendación --}}
                                <div class="bg-white rounded-xl p-4 shadow-sm">
                                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Recomendación</p>
                                    @if($producto->recomendacion_fabricacion > 0)
                                        <p class="text-3xl font-bold text-red-600">{{ $producto->recomendacion_fabricacion }}</p>
                                        <p class="text-xs text-red-600 mt-1 font-semibold uppercase">⚠️ Fabricar urgente</p>
                                    @else
                                        <p class="text-3xl font-bold text-green-600">0</p>
                                        <p class="text-xs text-green-600 mt-1 font-semibold uppercase">✓ Stock suficiente</p>
                                    @endif
                                </div>

                                {{-- Días restantes --}}
                                @if($producto->consumo_diario > 0)
                                    <div class="bg-white rounded-xl p-4 shadow-sm">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Días Restantes</p>
                                        @php
                                            $diasRestantes = round($producto->stock_total / $producto->consumo_diario, 1);
                                        @endphp
                                        <p class="text-3xl font-bold {{ $diasRestantes < 3 ? 'text-red-600' : ($diasRestantes < 7 ? 'text-orange-600' : 'text-green-600') }}">
                                            {{ $diasRestantes }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">días de inventario</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card: Datos ML --}}
                        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                            <div class="bg-gradient-to-r from-purple-500 to-pink-500 px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-white">Mercado Libre</h3>
                                    <form action="{{ route('productos.sincronizar', $producto) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg transition text-xs font-medium backdrop-blur-sm">
                                            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Actualizar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Stock en Full:</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $producto->stock_full ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-600">Ventas 30 días:</span>
                                    <span class="text-lg font-bold text-gray-900">{{ $producto->ventas_30_dias ?? '-' }}</span>
                                </div>
                                <div class="pt-2">
                                    <p class="text-xs text-gray-500">Última sincronización:</p>
                                    <p class="text-sm text-gray-700 font-medium">
                                        {{ $producto->ml_ultimo_sync ? $producto->ml_ultimo_sync->diffForHumans() : 'Nunca sincronizado' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>