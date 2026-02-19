<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Variantes de Bafles
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensaje de éxito --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Botón crear variante --}}
            <div class="mb-6">
                <a href="{{ route('variantes.create') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nueva Variante
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    @if ($variantes->isEmpty())
                        <div class="text-center py-16">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 mb-4">
                                <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay variantes</h3>
                            <p class="text-gray-500 mb-6">Crea una variante para agrupar fundas de bafles con las mismas
                                dimensiones</p>
                            <a href="{{ route('variantes.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                                Crear Primera Variante
                            </a>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Variante
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Productos
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Ventas Totales
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Stock Total
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Fabricar
                                        </th>
                                        <th scope="col" class="relative px-6 py-4">
                                            <span class="sr-only">Acciones</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach ($variantes as $variante)
                                        <tr class="hover:bg-purple-50 transition-colors duration-150">
                                            {{-- VARIANTE --}}
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $variante->nombre }}
                                                </div>
                                                @if ($variante->descripcion)
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        {{ $variante->descripcion }}
                                                    </div>
                                                @endif
                                            </td>

                                            {{-- PRODUCTOS --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800">
                                                    {{ $variante->productos->count() }} fundas
                                                </span>
                                            </td>

                                            {{-- VENTAS TOTALES --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-sm font-semibold text-gray-900">
                                                    {{ $variante->ventas_totales }}
                                                </span>
                                            </td>

                                            {{-- STOCK TOTAL --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                    {{ $variante->stock_total }}
                                                </span>
                                            </td>

                                            {{-- FABRICAR --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if ($variante->recomendacion_fabricacion > 0)
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                                        {{ $variante->recomendacion_fabricacion }} uds
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-800">
                                                        ✓ Stock OK
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- ACCIONES --}}
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    <a href="{{ route('variantes.edit', $variante) }}"
                                                        class="inline-flex items-center px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition">
                                                        Editar
                                                    </a>
                                                    <form action="{{ route('variantes.destroy', $variante) }}"
                                                        method="POST" class="inline"
                                                        onsubmit="return confirm('¿Estás seguro de eliminar esta variante?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
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