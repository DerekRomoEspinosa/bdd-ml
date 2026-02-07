<?php

namespace App\Services;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    public function importarProductos(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $highestRow = $worksheet->getHighestRow();
        
        $importados = 0; $actualizados = 0; $errores = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $modelo = $this->getCellValue($worksheet, 'A', $row);
                if (empty($modelo)) continue;

                // Mapeo exacto basado en tu imagen de 13 columnas
                $data = [
                    'nombre'              => $this->getCellValue($worksheet, 'B', $row),
                    'sku_ml'              => $this->getCellValue($worksheet, 'C', $row),
                    'codigo_interno_ml'   => $this->getCellValue($worksheet, 'D', $row),
                    'stock_bodega'        => (int)$this->getCellValue($worksheet, 'E', $row),
                    'stock_cortado'       => (int)$this->getCellValue($worksheet, 'F', $row),
                    'stock_costura'       => (int)$this->getCellValue($worksheet, 'G', $row),
                    'stock_por_empacar'   => (int)$this->getCellValue($worksheet, 'H', $row),
                    'stock_enviado_full'  => (int)$this->getCellValue($worksheet, 'I', $row),
                    'plantilla_corte_url' => $this->getCellValue($worksheet, 'M', $row), // Columna M es la 13
                    'activo'              => true,
                ];

                // updateOrCreate es más limpio: busca por modelo, si existe actualiza, si no crea.
                $producto = Producto::updateOrCreate(['modelo' => $modelo], $data);
                
                $producto->wasRecentlyCreated ? $importados++ : $actualizados++;

            } catch (\Exception $e) {
                $errores++;
                Log::error("Error fila {$row}: " . $e->getMessage());
            }
        }

        return [
            'importados' => $importados,
            'actualizados' => $actualizados,
            'errores' => $errores,
        ];
    }

    private function getCellValue($worksheet, string $column, int $row): ?string
    {
        $value = $worksheet->getCell($column . $row)->getValue();
        return $value === null ? null : trim((string) $value);
    }
}