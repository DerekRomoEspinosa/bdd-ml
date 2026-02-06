<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelController extends Controller
{
    private ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Mostrar formulario de importación
     */
    public function showImportForm()
    {
        return view('productos.import');
    }

    /**
     * Procesar la importación del archivo Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ], [
            'excel_file.required' => 'Debes seleccionar un archivo Excel',
            'excel_file.mimes' => 'El archivo debe ser formato Excel (.xlsx o .xls)',
            'excel_file.max' => 'El archivo no debe pesar más de 10MB',
        ]);

        try {
            $file = $request->file('excel_file');
            $path = $file->getRealPath();

            Log::info("Iniciando importación de Excel", [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
            ]);

            // Importar productos
           $resultado = $this->importService->import($path);

            Log::info("Importación completada", $resultado);

            $mensaje = "✅ Importación exitosa: ";
            $mensaje .= "{$resultado['importados']} productos nuevos creados";
            
            if ($resultado['actualizados'] > 0) {
                $mensaje .= ", {$resultado['actualizados']} productos actualizados";
            }
            
            if ($resultado['errores'] > 0) {
                $mensaje .= " | ⚠️ {$resultado['errores']} errores";
            }

            return redirect()
                ->route('productos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error("Error en importación de Excel", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->with('error', '❌ Error al importar: ' . $e->getMessage());
        }
    }
    /**
 * Exportar productos a Excel
 */
public function export()
{
    try {
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Crear nuevo spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configurar encabezados
        $sheet->setTitle('Productos');
        
        // Estilo para encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ];
        
        // Encabezados
        $headers = [
            'A1' => 'Modelo',
            'B1' => 'Nombre',
            'C1' => 'SKU ML',
            'D1' => 'Stock Bodega',
            'E1' => 'Stock Cortado',
            'F1' => 'Stock Enviado Full',
            'G1' => 'Stock Full (ML)',
            'H1' => 'Ventas 30 días',
            'I1' => 'Stock Total',
            'J1' => 'Consumo Diario',
            'K1' => '✅ FABRICAR',
            'L1' => 'Última Sync ML',
        ];
        
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }
        
        // Auto-width para columnas
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Llenar datos
        $row = 2;
        foreach ($productos as $producto) {
            $sheet->setCellValue("A{$row}", $producto->modelo);
            $sheet->setCellValue("B{$row}", $producto->nombre);
            $sheet->setCellValue("C{$row}", $producto->sku_ml);
            $sheet->setCellValue("D{$row}", $producto->stock_bodega);
            $sheet->setCellValue("E{$row}", $producto->stock_cortado);
            $sheet->setCellValue("F{$row}", $producto->stock_enviado_full);
            $sheet->setCellValue("G{$row}", $producto->stock_full ?? 0);
            $sheet->setCellValue("H{$row}", $producto->ventas_30_dias ?? 0);
            $sheet->setCellValue("I{$row}", $producto->stock_total);
            $sheet->setCellValue("J{$row}", number_format($producto->consumo_diario, 2));
            
            // Columna de fabricación con color
            $fabricar = $producto->recomendacion_fabricacion;
            $sheet->setCellValue("K{$row}", $fabricar);
            
            if ($fabricar > 0) {
                $sheet->getStyle("K{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FF0000']],
                ]);
            } else {
                $sheet->getStyle("K{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6FFE6']],
                    'font' => ['color' => ['rgb' => '006600']],
                ]);
            }
            
            $sheet->setCellValue("L{$row}", $producto->ml_ultimo_sync ? $producto->ml_ultimo_sync->format('Y-m-d H:i') : 'Nunca');
            
            $row++;
        }
        
        // Crear archivo temporal
        $filename = 'productos_' . date('Y-m-d_His') . '.xlsx';
        $tempFile = storage_path('app/' . $filename);
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        Log::info("Excel exportado", [
            'filename' => $filename,
            'productos' => $productos->count()
        ]);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        
    } catch (\Exception $e) {
        Log::error("Error exportando Excel", [
            'error' => $e->getMessage()
        ]);
        
        return redirect()
            ->back()
            ->with('error', '❌ Error al exportar: ' . $e->getMessage());
    }
}
}