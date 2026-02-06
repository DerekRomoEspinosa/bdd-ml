<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Producto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('productos.store') }}" class="space-y-6">
                        @csrf

                        {{-- Información Básica --}}
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Información Básica</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nombre --}}
                                <div class="col-span-2">
                                    <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre del Producto *</label>
                                    <input type="text" 
                                           name="nombre" 
                                           id="nombre" 
                                           value="{{ old('nombre') }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('nombre')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Modelo --}}
                                <div>
                                    <label for="modelo" class="block text-sm font-medium text-gray-700">Modelo</label>
                                    <input type="text" 
                                           name="modelo" 
                                           id="modelo" 
                                           value="{{ old('modelo') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('modelo')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- SKU ML --}}
                                <div>
                                    <label for="sku_ml" class="block text-sm font-medium text-gray-700">SKU Mercado Libre *</label>
                                    <input type="text" 
                                           name="sku_ml" 
                                           id="sku_ml" 
                                           value="{{ old('sku_ml') }}"
                                           required
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('sku_ml')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Código Interno ML --}}
                                <div>
                                    <label for="codigo_interno_ml" class="block text-sm font-medium text-gray-700">Código Interno ML</label>
                                    <input type="text" 
                                           name="codigo_interno_ml" 
                                           id="codigo_interno_ml" 
                                           value="{{ old('codigo_interno_ml') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="3113495728">
                                    <p class="mt-1 text-xs text-gray-500">Código interno de 10 dígitos de Mercado Libre</p>
                                    @error('codigo_interno_ml')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Plantilla de Corte --}}
                                <div class="col-span-2">
                                    <label for="plantilla_corte_url" class="block text-sm font-medium text-gray-700">Link Plantilla de Corte (OneDrive)</label>
                                    <input type="url" 
                                           name="plantilla_corte_url" 
                                           id="plantilla_corte_url" 
                                           value="{{ old('plantilla_corte_url') }}"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           placeholder="https://1drv.ms/...">
                                    <p class="mt-1 text-xs text-gray-500">Link compartido de OneDrive con la imagen de la plantilla de corte</p>
                                    @error('plantilla_corte_url')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Inventario Interno --}}
                        <div class="border-b border-gray-200 pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Inventario Interno</h3>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                                {{-- Stock Bodega --}}
                                <div>
                                    <label for="stock_bodega" class="block text-sm font-medium text-gray-700">Stock en Bodega</label>
                                    <input type="number" 
                                           name="stock_bodega" 
                                           id="stock_bodega" 
                                           value="{{ old('stock_bodega', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('stock_bodega')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Stock Cortado --}}
                                <div>
                                    <label for="stock_cortado" class="block text-sm font-medium text-gray-700">Fundas Cortadas</label>
                                    <input type="number" 
                                           name="stock_cortado" 
                                           id="stock_cortado" 
                                           value="{{ old('stock_cortado', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('stock_cortado')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Stock en Costura --}}
                                <div>
                                    <label for="stock_costura" class="block text-sm font-medium text-gray-700">Fundas en Costura</label>
                                    <input type="number" 
                                           name="stock_costura" 
                                           id="stock_costura" 
                                           value="{{ old('stock_costura', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('stock_costura')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Stock por Empacar --}}
                                <div>
                                    <label for="stock_por_empacar" class="block text-sm font-medium text-gray-700">Fundas por Empacar</label>
                                    <input type="number" 
                                           name="stock_por_empacar" 
                                           id="stock_por_empacar" 
                                           value="{{ old('stock_por_empacar', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('stock_por_empacar')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Stock Enviado Full --}}
                                <div>
                                    <label for="stock_enviado_full" class="block text-sm font-medium text-gray-700">Stock Enviado a Full</label>
                                    <input type="number" 
                                           name="stock_enviado_full" 
                                           id="stock_enviado_full" 
                                           value="{{ old('stock_enviado_full', 0) }}"
                                           min="0"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    @error('stock_enviado_full')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex items-center justify-end space-x-3">
                            <a href="{{ route('productos.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition">
                                Cancelar
                            </a>
                            
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                Crear Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>