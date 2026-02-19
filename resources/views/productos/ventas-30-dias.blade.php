<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('productos.index') }}" class="text-gray-600 hover:text-gray-900 transition">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <h2 class="font-bold text-2xl text-gray-900">Calcular Ventas de 30 Días</h2>
                <p class="text-sm text-gray-500 mt-1">Compara dos reportes de Excel para calcular ventas mensuales</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensajes --}}
            @if (session('success'))
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 p-4 rounded-r-xl shadow-sm animate-fade-in">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-green-800">{!! session('success') !!}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                    <div class="flex items-center">
                        <svg class="h-6 w-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- Banner informativo --}}
            <div class="mb-8 bg-gradient-to-r from-blue-50 to-cyan-50 border-l-4 border-blue-500 rounded-r-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 flex-1">
                            <h3 class="text-lg font-bold text-blue-900 mb-2">¿Cómo funciona?</h3>
                            <div class="text-sm text-blue-800 space-y-2">
                                <p>1️⃣ Descarga el <strong>reporte de ventas de ML de hace 30 días</strong></p>
                                <p>2️⃣ Descarga el <strong>reporte de ventas actual</strong></p>
                                <p>3️⃣ Súbelos aquí y el sistema calculará: <code class="bg-blue-100 px-2 py-0.5 rounded">ventas_actuales - ventas_hace_30_dias</code></p>
                                <p class="mt-3 pt-3 border-t border-blue-200">
                                    <strong>📋 Formato del Excel:</strong><br>
                                    Columna A: Código de ML (MLM...)<br>
                                    Columna B: Ventas Totales
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            <form method="POST" action="{{ route('productos.calcular-ventas-30dias') }}" enctype="multipart/form-data" 
                class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                @csrf

                <div class="p-8 space-y-8">
                    {{-- Excel Anterior --}}
                    <div>
                        <label for="excel_anterior" class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="h-6 w-6 mr-2 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            📅 Excel Anterior (hace 30 días) *
                        </label>
                        <div class="relative">
                            <input type="file" name="excel_anterior" id="excel_anterior" required
                                accept=".xlsx,.xls,.csv"
                                class="block w-full text-sm text-gray-900 border-2 border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-l-xl
                                file:border-0
                                file:text-sm file:font-semibold
                                file:bg-purple-600 file:text-white
                                hover:file:bg-purple-700
                                file:cursor-pointer">
                        </div>
                        @error('excel_anterior')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Reporte del mes pasado o hace 30 días
                        </p>
                    </div>

                    {{-- Separador visual --}}
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t-2 border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-4 bg-white text-sm font-bold text-gray-500">VS</span>
                        </div>
                    </div>

                    {{-- Excel Actual --}}
                    <div>
                        <label for="excel_actual" class="block text-sm font-bold text-gray-900 mb-3 flex items-center">
                            <svg class="h-6 w-6 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            📅 Excel Actual (hoy) *
                        </label>
                        <div class="relative">
                            <input type="file" name="excel_actual" id="excel_actual" required
                                accept=".xlsx,.xls,.csv"
                                class="block w-full text-sm text-gray-900 border-2 border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all
                                file:mr-4 file:py-3 file:px-6
                                file:rounded-l-xl
                                file:border-0
                                file:text-sm file:font-semibold
                                file:bg-green-600 file:text-white
                                hover:file:bg-green-700
                                file:cursor-pointer">
                        </div>
                        @error('excel_actual')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Reporte más reciente de Mercado Libre
                        </p>
                    </div>

                    {{-- Info adicional --}}
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-xl">
                        <div class="flex">
                            <svg class="h-5 w-5 text-yellow-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h4 class="text-sm font-bold text-yellow-800 mb-1">⚠️ Importante</h4>
                                <p class="text-xs text-yellow-700">
                                    Asegúrate de que ambos archivos tengan el mismo formato:<br>
                                    <strong>Columna A:</strong> Código ML (MLM123456789)<br>
                                    <strong>Columna B:</strong> Ventas Totales (número)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="px-8 py-6 bg-gradient-to-r from-gray-50 to-gray-100 border-t-2 border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-3">
                    <a href="{{ route('productos.index') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-gray-700 border-2 border-gray-300 rounded-xl font-semibold transition-all shadow-sm hover:shadow">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancelar
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white rounded-xl font-bold shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Calcular Ventas de 30 Días
                    </button>
                </div>
            </form>

            {{-- Ejemplo visual --}}
            <div class="mt-8 bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                    <svg class="h-6 w-6 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Ejemplo de formato Excel
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">A - Código ML</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">B - Ventas Totales</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3 text-gray-900 font-mono">MLM3113495728</td>
                                <td class="px-4 py-3 text-gray-900 font-semibold">150</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-900 font-mono">MLM3549222002</td>
                                <td class="px-4 py-3 text-gray-900 font-semibold">87</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 text-gray-900 font-mono">MLM3524874564</td>
                                <td class="px-4 py-3 text-gray-900 font-semibold">203</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>