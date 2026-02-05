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
        $modelo = $this->getCellValue($worksheet, 'A', $row);        
        $marca = $this->getCellValue($worksheet, 'B', $row);         
        $nombre = $this->getCellValue($worksheet, 'C', $row);        
        $codigoBarras = $this->getCellValue($worksheet, 'D', $row);  
        $sku = $this->getCellValue($worksheet, 'F', $row);          
        
        // Inventario interno (según tu Excel original)
        $stockCortado = (int) $this->getCellValue($worksheet, 'Y', $row);      
        $stockAlmacen = (int) $this->getCellValue($worksheet, 'AB', $row);     
        $stockEnviadoFull = (int) $this->getCellValue($worksheet, 'AD', $row); 
        
     
        $skuMl = $this->getCellValue($worksheet, 'AS', $row); 
        
        // Validar datos mínimos - saltar filas completamente vacías
        if (empty($modelo) && empty($nombre)) {
            continue; // Fila vacía, saltar
        }
        
        // Decidir qué usar como SKU de ML
        if (!empty($skuMl)) {
            // Si tiene código ML, usarlo
            $skuFinal = $skuMl;
        } elseif (!empty($sku)) {
            // Si tiene SKU normal, usarlo
            $skuFinal = $sku;
        } elseif (!empty($modelo)) {
            // Si no tiene ningún SKU, usar el modelo
            $skuFinal = $modelo;
        } else {
            // Si no tiene nada, crear un SKU temporal
            $skuFinal = 'TEMP-' . $row;
        }
        
        // Decidir qué usar como nombre
        if (!empty($nombre)) {
            $nombreFinal = $nombre;
        } elseif (!empty($modelo)) {
            $nombreFinal = $modelo;
        } else {
            $nombreFinal = 'Producto fila ' . $row;
        }

        // Buscar si el producto ya existe (por SKU ML, SKU normal, o modelo)
        $producto = Producto::where(function($query) use ($skuFinal, $modelo) {
            $query->where('sku_ml', $skuFinal)
                  ->orWhere('modelo', $modelo);
        })->first();

        if ($producto) {
            // Actualizar producto existente
            $producto->update([
                'modelo' => $modelo,
                'nombre' => $nombreFinal,
                'sku_ml' => $skuFinal,
                'stock_bodega' => $stockAlmacen,
                'stock_cortado' => $stockCortado,
                'stock_enviado_full' => $stockEnviadoFull,
                'activo' => true,
            ]);
            $actualizados++;
            
            Log::info("Producto actualizado", [
                'row' => $row,
                'modelo' => $modelo,
                'sku' => $skuFinal
            ]);
        } else {
            // Crear nuevo producto
            Producto::create([
                'modelo' => $modelo,
                'nombre' => $nombreFinal,
                'sku_ml' => $skuFinal,
                'stock_bodega' => $stockAlmacen,
                'stock_cortado' => $stockCortado,
                'stock_enviado_full' => $stockEnviadoFull,
                'activo' => true,
            ]);
            $importados++;
            
            Log::info("Producto creado", [
                'row' => $row,
                'modelo' => $modelo,
                'nombre' => $nombreFinal,
                'sku' => $skuFinal
            ]);
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