<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Crear Nuevo Producto
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- Errores de validación --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">¡Oops! Algo salió mal.</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('productos.store') }}" class="space-y-6">
                        @csrf
                        
                        {{-- SKU Mercado Libre --}}
                        <div>
                            <label for="sku_ml" class="block text-sm font-medium text-gray-700">
                                SKU Mercado Libre <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="sku_ml" 
                                   id="sku_ml" 
                                   value="{{ old('sku_ml') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('sku_ml') border-red-300 @enderror"
                                   placeholder="MLM123456789"
                                   required
                                   maxlength="255">
                            @error('sku_ml')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">El ID del producto en Mercado Libre (ej: MLM123456789)</p>
                        </div>

                        {{-- Nombre --}}
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700">
                                Nombre del Producto <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre"
                                   value="{{ old('nombre') }}"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('nombre') border-red-300 @enderror"
                                   placeholder="Funda para iPhone 15 Pro"
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
                                           value="{{ old('stock_bodega', 0) }}"
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
                                           value="{{ old('stock_cortado', 0) }}"
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
                                           value="{{ old('stock_enviado_full', 0) }}"
                                           min="0"
                                           step="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">En tránsito a Full</p>
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
                                Crear Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>