<?php

namespace App\Http\Controllers;

use App\Services\ExcelImportService;
use App\Models\Producto;
use Illuminate\Http\Request;
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
        $this->importService->importarProductos($request->file('excel_file')->getRealPath());
        return redirect()->route('productos.index')->with('success', '✅ Inventario actualizado.');
    }

    public function export()
    {
        $productos = Producto::all();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Modelo', 'Nombre', 'SKU ML', 'Cod Interno', 'Bodega', 
            'Cortado', 'Costura', 'Empacar', 'Enviado Full', 
            'Stock Full', 'Ventas 30d', 'Stock Total', 'Plantilla URL'
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
            $sheet->setCellValue('L' . $row, $p->stock_total);
            $sheet->setCellValue('M' . $row, $p->plantilla_corte_url);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(fn() => $writer->save('php://output'), 'inventario_maestro.xlsx');
    }
}