<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mapear Códigos Internos de Mercado Libre
            </h2>
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition text-sm font-medium">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Mensajes --}}
            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Información --}}
            <div class="bg-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">¿Qué hace esta herramienta?</h3>
                        <div class="text-sm text-blue-800 space-y-2">
                            <p>Este proceso automático hará lo siguiente:</p>
                            <ol class="list-decimal list-inside space-y-1 ml-2">
                                <li>Obtiene todas tus publicaciones activas de Mercado Libre</li>
                                <li>Por cada publicación, extrae el <strong>código interno (seller_custom_field)</strong></li>
                                <li>Busca en tu base de datos el producto con ese SKU</li>
                                <li>Actualiza automáticamente el campo <code class="bg-blue-100 px-2 py-0.5 rounded">codigo_interno_ml</code></li>
                                <li>También actualiza stock_full, ventas_30_dias y fecha de sincronización</li>
                            </ol>
                            <p class="mt-3"><strong>⚠️ Nota:</strong> El proceso puede tardar ~10-20 minutos para 893 productos.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estado actual --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-blue-50 mr-4">
                            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Total Productos</p>
                            <p class="text-3xl font-bold text-gray-900">{{ number_format($totalProductos) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-green-50 mr-4">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Con Código ML</p>
                            <p class="text-3xl font-bold text-green-600">{{ number_format($conCodigo) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-xl bg-red-50 mr-4">
                            <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Sin Código ML</p>
                            <p class="text-3xl font-bold text-red-600">{{ number_format($sinCodigo) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Formulario --}}
            @if($tokenData)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Ejecutar Mapeo</h3>
                    
                    <form action="{{ route('admin.mapear-ml.ejecutar') }}" method="POST" id="mapeoForm">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label for="limit" class="block text-sm font-medium text-gray-700 mb-2">
                                    Número de productos a procesar
                                </label>
                                <select name="limit" id="limit" class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="10">10 (Prueba rápida)</option>
                                    <option value="50" selected>50 (Recomendado)</option>
                                    <option value="100">100 productos</option>
                                    <option value="200">200 productos</option>
                                    <option value="500">500 productos</option>
                                    <option value="999999">TODOS los productos (puede tardar 20+ min)</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    Se recomienda empezar con 10-50 para probar que funcione correctamente.
                                </p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition transform hover:scale-105"
                                        onclick="return confirm('¿Estás seguro? Este proceso puede tardar varios minutos.')">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Iniciar Mapeo
                                </button>

                                <div id="loading" class="hidden flex items-center text-blue-600">
                                    <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando... Esto puede tardar varios minutos.
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-yellow-900 mb-2">⚠️ No estás conectado a Mercado Libre</h3>
                            <p class="text-sm text-yellow-800 mb-4">Necesitas vincular tu cuenta de Mercado Libre primero.</p>
                            <a href="{{ route('ml.login') }}" 
                               class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition text-sm font-medium">
                                🔗 Vincular Cuenta de ML
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Output del comando --}}
            @if(session('output'))
                <div class="bg-gray-900 rounded-2xl shadow-lg p-6 overflow-auto">
                    <h3 class="text-lg font-semibold text-white mb-4">Resultado del Mapeo:</h3>
                    <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ session('output') }}</pre>
                </div>
            @endif

        </div>
    </div>

    <script>
        document.getElementById('mapeoForm')?.addEventListener('submit', function() {
            document.getElementById('loading').classList.remove('hidden');
        });
    </script>
</x-app-layout>