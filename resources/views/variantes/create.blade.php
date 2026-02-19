<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nueva Variante de Bafle
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-8">
                    <form method="POST" action="{{ route('variantes.store') }}">
                        @csrf

                        {{-- Nombre --}}
                        <div class="mb-6">
                            <label for="nombre" class="block text-sm font-bold text-gray-700 mb-2">
                                Nombre de la Variante *
                            </label>
                            <input type="text" name="nombre" id="nombre" required
                                value="{{ old('nombre') }}"
                                placeholder="Ej: Variante 49"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            @error('nombre')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Descripción --}}
                        <div class="mb-6">
                            <label for="descripcion" class="block text-sm font-bold text-gray-700 mb-2">
                                Descripción
                            </label>
                            <input type="text" name="descripcion" id="descripcion"
                                value="{{ old('descripcion') }}"
                                placeholder="Ej: Bafles 15 pulgadas formato A"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                            @error('descripcion')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Notas --}}
                        <div class="mb-6">
                            <label for="notas" class="block text-sm font-bold text-gray-700 mb-2">
                                Notas
                            </label>
                            <textarea name="notas" id="notas" rows="3"
                                placeholder="Información adicional sobre esta variante..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">{{ old('notas') }}</textarea>
                            @error('notas')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Productos --}}
                        <div class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-3">
                                Productos (Fundas Compatibles) *
                            </label>
                            <div class="border border-gray-300 rounded-xl p-4 max-h-96 overflow-y-auto bg-gray-50">
                                @foreach ($productos as $producto)
                                    <label class="flex items-center p-3 hover:bg-white rounded-lg transition cursor-pointer">
                                        <input type="checkbox" name="productos[]" value="{{ $producto->id }}"
                                            {{ in_array($producto->id, old('productos', [])) ? 'checked' : '' }}
                                            class="w-5 h-5 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                        <span class="ml-3 text-sm font-medium text-gray-900">
                                            {{ $producto->nombre }}
                                            <span class="text-xs text-gray-500 ml-2">({{ $producto->sku_ml }})</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('productos')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                Selecciona todas las fundas que comparten las mismas dimensiones
                            </p>
                        </div>

                        {{-- Botones --}}
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('variantes.index') }}"
                                class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-medium transition">
                                Cancelar
                            </a>
                            <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                Crear Variante
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>