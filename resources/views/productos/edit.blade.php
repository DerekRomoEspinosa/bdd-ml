<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Producto: {{ $producto->nombre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Mensajes de éxito/error --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Éxito!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">¡Error!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('productos.update', $producto) }}" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        {{-- SKU Mercado Libre (solo lectura) --}}
                        <div>
                            <label for="sku_ml" class="block text-sm font-medium text-gray-700">
                                SKU Mercado Libre
                            </label>
                            <input type="text" 
                                   id="sku_ml"
                                   value="{{ $producto->sku_ml }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm cursor-not-allowed"
                                   disabled>
                            <p class="mt-1 text-sm text-gray-500">El SKU no se puede modificar</p>
                        </div>

                        {{-- Nombre --}}
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre"
                                   value="{{ old('nombre', $producto->nombre) }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('nombre') border-red-300 @enderror"
                                   required
                                   maxlength="255">
                            @error('nombre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Inventario Interno --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Inventario Interno</h3>
                            
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                                {{-- Stock Bodega --}}
                                <div>
                                    <label for="stock_bodega" class="block text-sm font-medium text-gray-700">
                                        Stock en Bodega
                                    </label>
                                    <input type="number" 
                                           name="stock_bodega" 
                                           id="stock_bodega"
                                           value="{{ old('stock_bodega', $producto->stock_bodega) }}"
                                           min="0"
                                           step="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Unidades disponibles</p>
                                </div>

                                {{-- Stock Cortado --}}
                                <div>
                                    <label for="stock_cortado" class="block text-sm font-medium text-gray-700">
                                        Stock Cortado
                                    </label>
                                    <input type="number" 
                                           name="stock_cortado" 
                                           id="stock_cortado"
                                           value="{{ old('stock_cortado', $producto->stock_cortado) }}"
                                           min="0"
                                           step="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Unidades ya cortadas</p>
                                </div>

                                {{-- Stock Enviado a Full --}}
                                <div>
                                    <label for="stock_enviado_full" class="block text-sm font-medium text-gray-700">
                                        Enviado a Full
                                    </label>
                                    <input type="number" 
                                           name="stock_enviado_full" 
                                           id="stock_enviado_full"
                                           value="{{ old('stock_enviado_full', $producto->stock_enviado_full) }}"
                                           min="0"
                                           step="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">En tránsito a Full</p>
                                </div>
                            </div>
                        </div>

                        {{-- Cálculo de Fabricación --}}
                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Análisis</h3>
                            
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <p class="text-sm text-blue-600 font-medium">Stock Total</p>
                                    <p class="text-3xl font-bold text-blue-900">{{ $producto->stock_total }}</p>
                                    <p class="text-xs text-blue-600 mt-1">unidades</p>
                                </div>
                                
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-green-600 font-medium">Consumo Diario</p>
                                    <p class="text-3xl font-bold text-green-900">{{ number_format($producto->consumo_diario, 1) }}</p>
                                    <p class="text-xs text-green-600 mt-1">uds/día</p>
                                </div>
                                
                                <div class="bg-{{ $producto->recomendacion_fabricacion > 0 ? 'red' : 'gray' }}-50 p-4 rounded-lg">
                                    <p class="text-sm text-{{ $producto->recomendacion_fabricacion > 0 ? 'red' : 'gray' }}-600 font-medium">
                                        Recomendación
                                    </p>
                                    <p class="text-3xl font-bold text-{{ $producto->recomendacion_fabricacion > 0 ? 'red' : 'gray' }}-900">
                                        {{ $producto->recomendacion_fabricacion }}
                                    </p>
                                    <p class="text-xs text-{{ $producto->recomendacion_fabricacion > 0 ? 'red' : 'gray' }}-600 mt-1">
                                        {{ $producto->recomendacion_fabricacion > 0 ? 'unidades a fabricar' : 'Stock suficiente' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Botones --}}
                        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                            <a href="{{ route('productos.index') }}" 
                               class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Datos de Mercado Libre (FUERA del formulario principal) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Datos de Mercado Libre</h3>
                        
                        {{-- Botón de sincronización (FORMULARIO INDEPENDIENTE) --}}
                        <form action="{{ route('productos.sincronizar', $producto) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Actualizar desde ML
                            </button>
                        </form>
                    </div>
                    
                    @if($producto->stock_full !== null || $producto->ventas_30_dias !== null)
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Stock en Full</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $producto->stock_full ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Ventas últimos 30 días</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $producto->ventas_30_dias ?? '-' }}</p>
                            </div>
                        </div>
                        
                        @if($producto->ml_ultimo_sync)
                            <p class="mt-2 text-sm text-gray-500">
                                Última actualización: {{ $producto->ml_ultimo_sync->format('d/m/Y H:i') }}
                                ({{ $producto->ml_ultimo_sync->diffForHumans() }})
                            </p>
                        @endif
                    @else
                        <div class="text-center py-6 bg-yellow-50 rounded-lg">
                            <p class="text-sm text-yellow-800">
                                Aún no se han sincronizado datos desde Mercado Libre.
                            </p>
                            <p class="text-xs text-yellow-600 mt-1">
                                Haz clic en "Actualizar desde ML" para obtener los datos.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>