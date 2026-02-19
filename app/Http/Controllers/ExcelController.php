<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelController extends Controller
{
    private ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showImportForm() 
    { 
        return view('productos.import'); 
    }

    public function import(Request $request)
    {
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls|max:10240']);
        
        try {
            $resultado = $this->importService->importarProductos($request->file('excel_file')->getRealPath());
            
            $mensaje = "✅ Importación finalizada: {$resultado['importados']} nuevos, {$resultado['actualizados']} actualizados";
            if ($resultado['errores'] > 0) {
                $mensaje .= " | ⚠️ {$resultado['errores']} errores";
            }
            
            return redirect()->route('productos.index')->with('success', $mensaje);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $productos = Producto::where('activo', true)->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // ✅ Encabezados actualizados
        $headers = [
            'Modelo', 
            'Nombre', 
            'SKU ML', 
            'Codigo Interno ML', 
            'Piezas por Plancha', 
            'Stock Minimo Deseado', // ✅ NUEVA COLUMNA
            'Stock Bodega', 
            'Stock Cortado', 
            'Stock Costura', 
            'Por Empacar', 
            'Enviado Full', 
            'Stock Full ML', 
            'Ventas Totales ML', 
            'Stock Total', 
            'Fabricar',
            'Plantilla Corte'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        // Estilo para encabezados
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet->getStyle('A1:P1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4F46E5');
        $sheet->getStyle('A1:P1')->getFont()->getColor()->setRGB('FFFFFF');

        $row = 2;
        foreach ($productos as $p) {
            $sheet->setCellValue('A' . $row, $p->modelo);
            $sheet->setCellValue('B' . $row, $p->nombre);
            $sheet->setCellValue('C' . $row, $p->sku_ml);
            $sheet->setCellValue('D' . $row, $p->codigo_interno_ml);
            $sheet->setCellValue('E' . $row, $p->piezas_por_plancha);
            $sheet->setCellValue('F' . $row, $p->stock_minimo_deseado); 
            $sheet->setCellValue('G' . $row, $p->stock_bodega);
            $sheet->setCellValue('H' . $row, $p->stock_cortado);
            $sheet->setCellValue('I' . $row, $p->stock_costura);
            $sheet->setCellValue('J' . $row, $p->stock_por_empacar);
            $sheet->setCellValue('K' . $row, $p->stock_enviado_full);
            $sheet->setCellValue('L' . $row, $p->stock_full);
            $sheet->setCellValue('M' . $row, $p->ventas_totales);
            $sheet->setCellValue('N' . $row, $p->stock_total); 
            $sheet->setCellValue('O' . $row, $p->recomendacion_fabricacion); 
            $sheet->setCellValue('P' . $row, $p->plantilla_corte_url);
            
            // ✅ Colorear fila si necesita fabricación
            if ($p->recomendacion_fabricacion > 0) {
                $sheet->getStyle('A' . $row . ':P' . $row)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FEF3C7'); // Amarillo claro
            }
            
            $row++;
        }

        // Auto-ajustar ancho de columnas
        foreach(range('A','P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'inventario_' . date('Y-m-d_His') . '.xlsx';
        
        return response()->streamDownload(
            fn() => $writer->save('php://output'), 
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }
}