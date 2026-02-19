<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel; 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExcelController extends Controller
{
    private ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /*
    |--------------------------------------------------------------------------
    | FORMULARIO IMPORTACIÓN
    |--------------------------------------------------------------------------
    */
    public function showImportForm()
    {
        return view('productos.import');
    }

    /*
    |--------------------------------------------------------------------------
    | IMPORTAR PRODUCTOS
    |--------------------------------------------------------------------------
    */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240'
        ]);

        try {
            $resultado = $this->importService->importarProductos(
                $request->file('excel_file')->getRealPath()
            );

            $mensaje = "✅ Importación finalizada: {$resultado['importados']} nuevos, {$resultado['actualizados']} actualizados";

            if ($resultado['errores'] > 0) {
                $mensaje .= " | ⚠️ {$resultado['errores']} errores";
            }

            return redirect()->route('productos.index')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error importando Excel', [
                'mensaje' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', '❌ Error: ' . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORTAR INVENTARIO
    |--------------------------------------------------------------------------
    */
    public function export()
    {
        $productos = Producto::where('activo', true)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Modelo',
            'Nombre',
            'SKU ML',
            'Codigo Interno ML',
            'Piezas por Plancha',
            'Stock Minimo Deseado',
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

        $sheet->fromArray($headers, null, 'A1');

        // Estilo encabezados
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);
        $sheet->getStyle('A1:P1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
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

            // Colorear si necesita fabricación
            if ($p->recomendacion_fabricacion > 0) {
                $sheet->getStyle("A{$row}:P{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('FEF3C7');
            }

            $row++;
        }

        foreach (range('A', 'P') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'inventario_' . now()->format('Y-m-d_His') . '.xlsx';

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR FORMULARIO VENTAS 30 DÍAS
    |--------------------------------------------------------------------------
    */
    public function mostrarFormularioVentas30Dias()
    {
        return view('productos.ventas-30-dias');
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULAR VENTAS 30 DÍAS
    |--------------------------------------------------------------------------
    */
public function calcularVentas30Dias(Request $request)
{
    $request->validate([
        'excel_anterior' => 'required|file|mimes:xlsx,xls,csv',
        'excel_actual' => 'required|file|mimes:xlsx,xls,csv',
    ]);

    try {
        set_time_limit(300);

        // Leer Excel Anterior
        $pathAnterior = $request->file('excel_anterior')->getRealPath();
        $spreadsheetAnterior = \PhpOffice\PhpSpreadsheet\IOFactory::load($pathAnterior);
        $sheetAnterior = $spreadsheetAnterior->getActiveSheet();
        $dataAnterior = $sheetAnterior->toArray();

        // Leer Excel Actual
        $pathActual = $request->file('excel_actual')->getRealPath();
        $spreadsheetActual = \PhpOffice\PhpSpreadsheet\IOFactory::load($pathActual);
        $sheetActual = $spreadsheetActual->getActiveSheet();
        $dataActual = $sheetActual->toArray();

        // Convertir excel anterior a array asociativo
        $ventasAnterioresMap = [];
        foreach ($dataAnterior as $index => $row) {
            if ($index === 0) continue; // Saltar encabezado
            
            $codigo = $row[3] ?? null; // ✅ Columna D (index 3) = Codigo Interno ML
            $ventas = (int) ($row[12] ?? 0); // ✅ Columna M (index 12) = Ventas Totales ML
            
            if ($codigo) {
                $ventasAnterioresMap[$codigo] = $ventas;
            }
        }

        $actualizados = 0;
        $errores = 0;
        $noEncontrados = [];

        foreach ($dataActual as $index => $row) {
            if ($index === 0) continue; // Saltar encabezado

            $codigoML = $row[3] ?? null; // ✅ Columna D
            $ventasActuales = (int) ($row[12] ?? 0); // ✅ Columna M

            if (!$codigoML) continue;

            $ventasAnteriores = $ventasAnterioresMap[$codigoML] ?? 0;
            $ventas30Dias = max(0, $ventasActuales - $ventasAnteriores);

            $producto = Producto::where('codigo_interno_ml', $codigoML)->first();

            if ($producto) {
                $producto->update([
                    'ventas_totales' => $ventasActuales,
                    'ventas_totales_reporte_anterior' => $ventasAnteriores,
                    'ventas_30_dias_calculadas' => $ventas30Dias,
                    'fecha_ultimo_reporte' => now(),
                ]);
                $actualizados++;
            } else {
                $errores++;
                $noEncontrados[] = $codigoML;
            }
        }

        // Log de productos no encontrados
        if (!empty($noEncontrados)) {
            Log::warning("[Ventas 30 Días] Productos no encontrados", [
                'codigos' => array_slice($noEncontrados, 0, 10) // Solo primeros 10
            ]);
        }

        $mensaje = "✅ {$actualizados} productos actualizados";
        if ($errores > 0) {
            $mensaje .= " | ⚠️ {$errores} códigos no encontrados en la BD";
        }

        return redirect()->route('productos.ventas-30-dias')
            ->with('success', $mensaje);

    } catch (\Exception $e) {
        Log::error('[Ventas 30 Días] Error', [
            'mensaje' => $e->getMessage(),
            'linea' => $e->getLine(),
        ]);

        return redirect()->route('productos.ventas-30-dias')
            ->with('error', '❌ Error: ' . $e->getMessage());
    }
}
}