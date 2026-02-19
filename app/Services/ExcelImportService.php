<?php

namespace App\Services;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelImportService
{
    public function importarProductos(string $filePath): array
    {
        DB::beginTransaction();

        try {

            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();

            $importados = 0;
            $actualizados = 0;
            $errores = 0;
            $erroresDetalle = [];

            for ($row = 2; $row <= $highestRow; $row++) {

                try {

                    // 🔹 Lectura de columnas (estructura Carlos)
                    $modelo = $this->clean($this->getCellValue($worksheet, 'A', $row));
                    $nombre = $this->clean($this->getCellValue($worksheet, 'C', $row));
                    $plantillaCorteUrl = $this->clean($this->getCellValue($worksheet, 'U', $row));
                    $piezasPorPlancha = (int) $this->getCellValue($worksheet, 'V', $row);
                    $skuMl = $this->clean($this->getCellValue($worksheet, 'AS', $row));
                    $codigoInternoMl = $this->clean($this->getCellValue($worksheet, 'AY', $row));

                    $stockCortado = (int) $this->getCellValue($worksheet, 'Y', $row);
                    $stockBodega = (int) $this->getCellValue($worksheet, 'AB', $row);
                    $stockEnviadoFull = (int) $this->getCellValue($worksheet, 'AD', $row);

                    // 🔹 Validación mínima
                    if (empty($modelo) && empty($nombre)) {
                        continue;
                    }

                    // 🔹 Defaults inteligentes
                    $nombre = $nombre ?: ($modelo ?: 'Producto sin nombre');
                    $skuMl = $skuMl ?: ($modelo ?: 'TEMP-' . $row);

                    if (!empty($codigoInternoMl) && !str_starts_with(strtoupper($codigoInternoMl), 'MLM')) {
                        $codigoInternoMl = 'MLM' . $codigoInternoMl;
                    }

                    $piezasPorPlancha = $piezasPorPlancha > 0 ? $piezasPorPlancha : 4;
                    $stockMinimoDeseado = $piezasPorPlancha * 2;

                    // 🔹 Buscar producto existente (optimizado)
                    $producto = Producto::query()
                        ->when($skuMl, fn($q) => $q->where('sku_ml', $skuMl))
                        ->orWhere('modelo', $modelo)
                        ->when($codigoInternoMl, fn($q) =>
                            $q->orWhere('codigo_interno_ml', $codigoInternoMl)
                        )
                        ->first();

                    $datosProducto = [
                        'modelo' => $modelo,
                        'nombre' => $nombre,
                        'sku_ml' => $skuMl,
                        'stock_bodega' => $stockBodega,
                        'stock_cortado' => $stockCortado,
                        'stock_enviado_full' => $stockEnviadoFull,
                        'piezas_por_plancha' => $piezasPorPlancha,
                        'stock_minimo_deseado' => $stockMinimoDeseado,
                        'activo' => true,
                    ];

                    if (!empty($codigoInternoMl)) {
                        $datosProducto['codigo_interno_ml'] = $codigoInternoMl;
                    }

                    if (!empty($plantillaCorteUrl)) {
                        $datosProducto['plantilla_corte_url'] = $plantillaCorteUrl;
                    }

                    if ($producto) {

                        $producto->update($datosProducto);
                        $actualizados++;

                        Log::info('Producto actualizado', [
                            'id' => $producto->id,
                            'modelo' => $modelo,
                        ]);

                    } else {

                        Producto::create($datosProducto);
                        $importados++;

                        Log::info('Producto creado', [
                            'modelo' => $modelo,
                        ]);
                    }

                } catch (\Exception $e) {

                    $errores++;
                    $erroresDetalle[] = "Fila {$row}: " . $e->getMessage();

                    Log::error("Error fila {$row}", [
                        'mensaje' => $e->getMessage()
                    ]);
                }
            }

            DB::commit();

            return [
                'importados' => $importados,
                'actualizados' => $actualizados,
                'errores' => $errores,
                'errores_detalle' => $erroresDetalle,
                'total_procesado' => $importados + $actualizados,
            ];

        } catch (\Exception $e) {

            DB::rollBack();

            Log::critical('Error crítico importando Excel', [
                'mensaje' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function getCellValue($worksheet, string $column, int $row): ?string
    {
        $value = $worksheet->getCell($column . $row)->getCalculatedValue();

        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }

    private function clean(?string $value): ?string
    {
        if (!$value) return null;

        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
