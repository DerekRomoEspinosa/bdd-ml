<?php

namespace App\Services;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ReporteVentasService
{
    /**
     * Procesar dos reportes y calcular ventas de últimos 30 días
     * 
     * @param string $archivoAnterior Path del Excel anterior
     * @param string $archivoActual Path del Excel actual
     * @return array Resultado del procesamiento
     */
    public function procesarReportes(string $archivoAnterior, string $archivoActual): array
    {
        try {
            // 1. Leer ambos archivos
            $ventasAnteriores = $this->leerReporte($archivoAnterior);
            $ventasActuales = $this->leerReporte($archivoActual);

            // 2. Calcular diferencias y actualizar productos
            $actualizados = 0;
            $sinCambios = 0;
            $productosNuevos = 0;
            $errores = 0;

            foreach ($ventasActuales as $sku => $ventasActual) {
                try {
                    $producto = Producto::where('sku_ml', $sku)
                        ->where('activo', true)
                        ->first();

                    if (!$producto) {
                        Log::warning("Producto no encontrado en BD", ['sku' => $sku]);
                        $errores++;
                        continue;
                    }

                    // Ventas anteriores (0 si no existía en el reporte anterior)
                    $ventasAnterior = $ventasAnteriores[$sku] ?? 0;

                    // Calcular ventas de últimos 30 días
                    $ventas30Dias = max(0, $ventasActual - $ventasAnterior);

                    // Si es producto nuevo (no estaba en reporte anterior)
                    if (!isset($ventasAnteriores[$sku])) {
                        $productosNuevos++;
                    }

                    // Actualizar producto
                    $producto->ventas_totales_reporte_anterior = $ventasAnterior;
                    $producto->ventas_totales = $ventasActual;
                    $producto->ventas_30_dias_calculadas = $ventas30Dias;
                    $producto->fecha_ultimo_reporte = now();
                    $producto->save();

                    // Recalcular recomendación de fabricación
                    $producto->calcularRecomendacionFabricacion();

                    // Si tiene variantes, actualizar sus contadores
                    if ($producto->usa_variante_para_fabricacion) {
                        foreach ($producto->variantes as $variante) {
                            $variante->actualizarContadores();
                        }
                    }

                    $actualizados++;

                } catch (\Exception $e) {
                    Log::error("Error procesando producto", [
                        'sku' => $sku,
                        'error' => $e->getMessage()
                    ]);
                    $errores++;
                }
            }

            return [
                'success' => true,
                'actualizados' => $actualizados,
                'sin_cambios' => $sinCambios,
                'productos_nuevos' => $productosNuevos,
                'errores' => $errores,
                'total_anterior' => count($ventasAnteriores),
                'total_actual' => count($ventasActuales),
            ];

        } catch (\Exception $e) {
            Log::error("Error procesando reportes", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Leer un reporte Excel y extraer ventas por SKU
     * 
     * @param string $archivoPath
     * @return array ['SKU' => ventas_totales]
     */
    private function leerReporte(string $archivoPath): array
    {
        $spreadsheet = IOFactory::load($archivoPath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        $ventas = [];

        // Leer desde fila 2 (saltando encabezados)
        for ($row = 2; $row <= $highestRow; $row++) {
            // Columna C = SKU ML
            // Columna M = Ventas Totales ML
            $sku = trim($sheet->getCell("C{$row}")->getValue());
            $ventasTotales = (int) $sheet->getCell("M{$row}")->getValue();

            if ($sku) {
                $ventas[$sku] = $ventasTotales;
            }
        }

        Log::info("Reporte leído", [
            'archivo' => basename($archivoPath),
            'productos' => count($ventas)
        ]);

        return $ventas;
    }

    /**
     * Validar que un archivo tenga la estructura correcta
     */
    public function validarEstructura(string $archivoPath): bool
    {
        try {
            $spreadsheet = IOFactory::load($archivoPath);
            $sheet = $spreadsheet->getActiveSheet();

            // Verificar encabezados esperados
            $expectedHeaders = [
                'C1' => 'SKU ML',
                'M1' => 'Ventas Totales ML',
            ];

            foreach ($expectedHeaders as $cell => $expectedValue) {
                $actualValue = trim($sheet->getCell($cell)->getValue());
                if ($actualValue !== $expectedValue) {
                    Log::warning("Estructura incorrecta", [
                        'celda' => $cell,
                        'esperado' => $expectedValue,
                        'encontrado' => $actualValue
                    ]);
                    return false;
                }
            }

            return true;

        } catch (\Exception $e) {
            Log::error("Error validando estructura", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}