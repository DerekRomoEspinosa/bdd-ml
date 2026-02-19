<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('variantes.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900">Nueva Variante</h2>
                <p class="text-sm text-gray-500 mt-1">Agrupa fundas con las mismas dimensiones</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Banner informativo --}}
            <div class="mb-8 bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-500 rounded-r-2xl shadow-lg overflow-hidden">
                <div class="p-6 flex">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
                            <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5">
                        <h3 class="text-lg font-bold text-purple-900 mb-2">¿Qué es una variante?</h3>
                        <p class="text-sm text-purple-800 leading-relaxed">
                            Agrupa todas las fundas que tienen las <strong>mismas dimensiones</strong> (ej: todas las fundas para bafles de 15").<br>
                            El sistema <strong>sumará automáticamente sus ventas</strong> y calculará cuántas fabricar en total.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('variantes.store') }}" class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                @csrf

                <div class="p-8 space-y-6">
                    {{-- Nombre --}}
                    <div>
                        <label for="nombre" class="block text-sm font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                            Nombre de la Variante *
                        </label>
                        <input type="text" name="nombre" id="nombre" required
                            value="{{ old('nombre') }}" placeholder="Ej: Variante 49, Bafles 15 pulgadas..."
                            class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-gray-900 placeholder-gray-400">
                        @error('nombre')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">Usa un nombre que te ayude a identificarla rápidamente</p>
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
                            value="{{ old('descripcion') }}"
                            placeholder="Ej: Bafles 15 pulgadas formato A"
                            class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-gray-900 placeholder-gray-400">
                        <p class="mt-2 text-xs text-gray-500">Breve descripción para identificación rápida</p>
                    </div>

                    {{-- Notas --}}
                    <div>
                        <label for="notas" class="block text-sm font-bold text-gray-900 mb-2 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Notas
                        </label>
                        <textarea name="notas" id="notas" rows="3"
                            placeholder="Información adicional sobre esta variante (opcional)..."
                            class="w-full px-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all text-gray-900 placeholder-gray-400 resize-none">{{ old('notas') }}</textarea>
                    </div>

                    <div class="border-t-2 border-gray-100 pt-6"></div>

                    {{-- Selector de productos --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="h-5 w-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            Fundas Compatibles *
                        </label>

                        {{-- Buscador --}}
                        <div class="relative mb-4">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="searchProductos" placeholder="Buscar fundas por nombre o SKU..."
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

                        {{-- Lista de productos --}}
                        <div class="border-2 border-gray-200 rounded-2xl p-4 max-h-96 overflow-y-auto bg-gray-50 productos-list custom-scrollbar">
                            @foreach ($productos as $producto)
                                <label class="producto-item flex items-center p-3 hover:bg-white rounded-xl transition-all cursor-pointer mb-2 border-2 border-transparent hover:border-purple-200 hover:shadow-sm"
                                    data-nombre="{{ strtolower($producto->nombre) }}"
                                    data-sku="{{ strtolower($producto->sku_ml) }}">
                                    <input type="checkbox" name="productos[]" value="{{ $producto->id }}"
                                        {{ in_array($producto->id, old('productos', [])) ? 'checked' : '' }}
                                        class="checkbox-producto w-5 h-5 text-purple-600 border-gray-300 rounded-lg focus:ring-purple-500 focus:ring-2 transition">
                                    <div class="ml-4 flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 block truncate">
                                            {{ $producto->nombre }}
                                        </span>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="text-xs text-gray-500 font-mono">{{ $producto->sku_ml }}</span>
                                            @if ($producto->ventas_totales > 0)
                                                <span class="inline-flex items-center text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                                    <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                                    </svg>
                                                    {{ number_format($producto->ventas_totales) }} ventas
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        @error('productos')
                            <p class="mt-3 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <div class="mt-3 p-3 bg-blue-50 rounded-xl border border-blue-200">
                            <p class="text-xs text-blue-800 flex items-start">
                                <svg class="h-4 w-4 mr-2 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <span>Selecciona todas las fundas que tienen las <strong>mismas dimensiones</strong>. Solo la variante calculará cuánto fabricar.</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Botones ARREGLADOS --}}
                <div class="px-8 py-6 bg-gray-50 border-t-2 border-gray-100 flex flex-col-reverse sm:flex-row justify-end gap-3">
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
                        Crear Variante
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
            const count = document.querySelectorAll('.checkbox-producto:checked').length;
            document.getElementById('contadorSeleccionados').textContent = count;
        }
        
        actualizarContador();
        
        document.querySelectorAll('.checkbox-producto').forEach(checkbox => {
            checkbox.addEventListener('change', actualizarContador);
        });
    </script>
</x-app-layout>