<?php

namespace App\Services;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

class ExcelImportService
{
    public function import($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $importados = 0;
            $errores = 0;
            
            // Iterar desde la fila 2 (después del encabezado)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }
                
                // Saltar filas vacías
                if (empty($cells[0]) && empty($cells[1])) {
                    continue;
                }
                
                // Mapeo de columnas actualizado:
                // A: Nombre
                // B: Modelo  
                // C: SKU ML
                // D: Stock Bodega (Cortado)
                // E: Stock Cortado (En costura)
                // F: Stock Costura (Por empacar)
                // G: Stock Por Empacar
                // H: Stock Enviado Full
                // I: Código Interno ML
                // J: Costo envío ML (opcional, no se usa)
                
                $nombre = $cells[0] ?? '';
                $modelo = $cells[1] ?? '';
                $skuMl = $cells[2] ?? '';
                $stockBodega = (int) ($cells[3] ?? 0);
                $stockCortado = (int) ($cells[4] ?? 0);
                $stockCostura = (int) ($cells[5] ?? 0);
                $stockPorEmpacar = (int) ($cells[6] ?? 0);
                $stockEnviadoFull = (int) ($cells[7] ?? 0);
                $codigoInternoMl = $cells[8] ?? '';
                
                // Validar datos mínimos
                if (empty($nombre) || empty($skuMl)) {
                    $errores++;
                    continue;
                }
                
                try {
                    Producto::updateOrCreate(
                        ['sku_ml' => $skuMl],
                        [
                            'nombre' => $nombre,
                            'modelo' => $modelo,
                            'stock_bodega' => $stockBodega,
                            'stock_cortado' => $stockCortado,
                            'stock_costura' => $stockCostura,
                            'stock_por_empacar' => $stockPorEmpacar,
                            'stock_enviado_full' => $stockEnviadoFull,
                            'codigo_interno_ml' => $codigoInternoMl,
                            'activo' => true,
                        ]
                    );
                    
                    $importados++;
                    
                } catch (\Exception $e) {
                    Log::error("Error importando producto: {$e->getMessage()}", [
                        'nombre' => $nombre,
                        'sku' => $skuMl
                    ]);
                    $errores++;
                }
            }
            
            return [
                'success' => true,
                'importados' => $importados,
                'errores' => $errores,
                'mensaje' => "✅ Importación completada: {$importados} productos importados/actualizados" . 
                            ($errores > 0 ? ", {$errores} errores" : "")
            ];
            
        } catch (\Exception $e) {
            Log::error("Error en importación Excel: {$e->getMessage()}");
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}