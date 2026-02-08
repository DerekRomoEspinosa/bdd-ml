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

    public function showImportForm() { return view('productos.import'); }

    public function import(Request $request)
    {
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls|max:10240']);
        try {
            $resultado = $this->importService->importarProductos($request->file('excel_file')->getRealPath());
            return redirect()->route('productos.index')->with('success', "Importación finalizada.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $productos = Producto::all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados para que coincidan con la importación
        $headers = [
            'Modelo', 'Nombre', 'SKU ML', 'Codigo Interno', 'Stock Bodega', 
            'Stock Cortado', 'Stock Costura', 'Por Empacar', 'Enviado Full', 
            'Stock Full', 'Ventas 30d', 'Stock Total', 'Plantilla Corte'
        ];
        $sheet->fromArray($headers, NULL, 'A1');

        $row = 2;
        foreach ($productos as $p) {
            $sheet->setCellValue('A' . $row, $p->modelo);
            $sheet->setCellValue('B' . $row, $p->nombre);
            $sheet->setCellValue('C' . $row, $p->sku_ml);
            $sheet->setCellValue('D' . $row, $p->codigo_interno_ml);
            $sheet->setCellValue('E' . $row, $p->stock_bodega);
            $sheet->setCellValue('F' . $row, $p->stock_cortado);
            $sheet->setCellValue('G' . $row, $p->stock_costura);
            $sheet->setCellValue('H' . $row, $p->stock_por_empacar);
            $sheet->setCellValue('I' . $row, $p->stock_enviado_full);
            $sheet->setCellValue('J' . $row, $p->stock_full);
            $sheet->setCellValue('K' . $row, $p->ventas_30_dias);
            $sheet->setCellValue('L' . $row, $p->stock_total); // Calculado por el modelo
            $sheet->setCellValue('M' . $row, $p->plantilla_corte_url);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), 'inventario.xlsx');
    }
}