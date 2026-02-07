<?php

namespace App\Services;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExcelImportService
{
    /**
     * Importar productos desde Excel
     * Basado en la estructura del archivo de Carlos
     */
    public function importarProductos(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        
        $importados = 0;
        $actualizados = 0;
        $errores = 0;
        $erroresDetalle = [];

        // Iniciar desde fila 2 (la 1 son encabezados)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                // Leer datos de las columnas importantes (Estructura de Carlos)
                $modelo = $this->getCellValue($worksheet, 'A', $row); // Modelo
                $nombre = $this->getCellValue($worksheet, 'C', $row); // Nombre Genérico
                $skuMl = $this->getCellValue($worksheet, 'AS', $row); // Código interno ML (columna 45)
                
                // Inventario interno
                $stockCortado = (int) $this->getCellValue($worksheet, 'Y', $row); // Cortado
                $stockBodega = (int) $this->getCellValue($worksheet, 'AB', $row); // Stock almacén (columna 28)
                $stockEnviadoFull = (int) $this->getCellValue($worksheet, 'AD', $row); // Proceso envío Full (columna 30)
                
                // Validar datos mínimos
                if (empty($modelo) && empty($nombre)) {
                    continue; // Fila vacía, saltar
                }
                
                if (empty($skuMl)) {
                    // Si no tiene SKU de ML, usar el modelo como SKU temporal
                    $skuMl = $modelo ?: 'TEMP-' . $row;
                }
                
                if (empty($nombre)) {
                    $nombre = $modelo ?: 'Producto sin nombre';
                }

                // Buscar si el producto ya existe (por SKU o por modelo)
                $producto = Producto::where('sku_ml', $skuMl)
                    ->orWhere('modelo', $modelo)
                    ->first();

                if ($producto) {
                    // Actualizar producto existente
                    $producto->update([
                        'modelo' => $modelo,
                        'nombre' => $nombre,
                        'sku_ml' => $skuMl,
                        'stock_bodega' => $stockBodega,
                        'stock_cortado' => $stockCortado,
                        'stock_enviado_full' => $stockEnviadoFull,
                        'activo' => true,
                    ]);
                    $actualizados++;
                } else {
                    // Crear nuevo producto
                    Producto::create([
                        'modelo' => $modelo,
                        'nombre' => $nombre,
                        'sku_ml' => $skuMl,
                        'stock_bodega' => $stockBodega,
                        'stock_cortado' => $stockCortado,
                        'stock_enviado_full' => $stockEnviadoFull,
                        'activo' => true,
                    ]);
                    $importados++;
                }

            } catch (\Exception $e) {
                $errores++;
                $erroresDetalle[] = "Fila {$row}: " . $e->getMessage();
                Log::error("Error importando fila {$row}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return [
            'importados' => $importados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'errores_detalle' => $erroresDetalle,
            'total_procesado' => $importados + $actualizados,
        ];
    }

    /**
     * Obtener valor de celda de forma segura
     */
    private function getCellValue($worksheet, string $column, int $row): ?string
    {
        $value = $worksheet->getCell($column . $row)->getValue();
        
        if ($value === null || $value === '') {
            return null;
        }
        
        return trim((string) $value);
    }
}