<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Calcular Ventas de Últimos 30 Días
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Explicación --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800">¿Cómo funciona?</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ol class="list-decimal ml-5 space-y-1">
                                <li>Exporta el reporte actual de ventas usando el botón de <strong>"Exportar Excel"</strong></li>
                                <li>Espera 30 días (o el periodo que quieras medir)</li>
                                <li>Exporta nuevamente el reporte</li>
                                <li>Sube ambos archivos aquí para calcular las ventas del periodo</li>
                            </ol>
                            <p class="mt-3 font-medium">
                                📊 El sistema calculará: <code class="bg-blue-100 px-2 py-1 rounded">ventas_30_dias = ventas_actual - ventas_anterior</code>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <form action="{{ route('productos.calcular-ventas-30dias') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="p-6 space-y-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Subir Reportes de Ventas</h3>

                        {{-- Reporte Anterior --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📅 Reporte Anterior (hace 30 días)
                            </label>
                            <input type="file" 
                                   name="reporte_anterior" 
                                   accept=".xlsx,.xls"
                                   required
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-blue-50 file:text-blue-700
                                          hover:file:bg-blue-100
                                          cursor-pointer border border-gray-300 rounded-lg">
                            <p class="mt-1 text-xs text-gray-500">Excel exportado hace 30 días (o tu periodo deseado)</p>
                        </div>

                        {{-- Reporte Actual --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                📅 Reporte Actual (hoy)
                            </label>
                            <input type="file" 
                                   name="reporte_actual" 
                                   accept=".xlsx,.xls"
                                   required
                                   class="block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-lg file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-green-50 file:text-green-700
                                          hover:file:bg-green-100
                                          cursor-pointer border border-gray-300 rounded-lg">
                            <p class="mt-1 text-xs text-gray-500">Excel exportado hoy con las ventas actualizadas</p>
                        </div>

                        {{-- Ayuda --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">💡 Consejos:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Los archivos deben tener la misma estructura (exportados desde este sistema)</li>
                                <li>• Se compararán usando el <strong>SKU ML</strong> de cada producto</li>
                                <li>• Productos nuevos (que no existían antes) tendrán todas sus ventas como "últimos 30 días"</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                        <a href="{{ route('productos.export') }}" 
                           class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Exportar Reporte Actual
                        </a>

                        <button type="submit"
                                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-semibold shadow-lg transition">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Calcular Ventas 30 Días
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>