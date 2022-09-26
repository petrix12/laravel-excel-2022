<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
/* use Maatwebsite\Excel\Concerns\ShouldAutoSize; */    // Esta interfaz sirve cuando queremos establecer un ancho automático
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InvoiceExport implements FromCollection, WithCustomStartCell, Responsable,
WithMapping, WithColumnFormatting, WithHeadings /* , ShouldAutoSize */, WithColumnWidths,
WithDrawings, WithStyles
{
    use Exportable;

    private $filters;
    private $fileName = 'invoice.xlsx';
    private $writerType = Excel::XLSX;

    public function __construct($filters){
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Invoice::filter($this->filters)->get();
    }

    public function startCell(): string {
        return 'A10';
    }

    public function headings(): array {
        return [
            'Serie',
            'Correlativo',
            'Base',
            'IGV',
            'Total',
            'Usuario',
            'Fecha'
        ];
    }

    public function map($invoice): array {
        return [
            $invoice->serie,
            $invoice->correlative,
            $invoice->base,
            $invoice->igv,
            $invoice->total,
            $invoice->user->name,
            Date::dateTimeToExcel($invoice->created_at)
        ];
    }

    public function columnFormats(): array {
        return [
            'G' => 'dd/mm/yyyy'
        ];
    }

    public function columnWidths(): array {
        return [
            'A' => 10,
            'B' => 15,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 30,
            'G' => 15
        ];
    }

    public function drawings() {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logotipo');
        $drawing->setDescription('Logotipo de Soluciones++');
        $drawing->setPath(public_path('img\logos\logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('B3');

        return $drawing;
    }

    public function styles(Worksheet $sheet){
        // Nombrar hoja
        $sheet->setTitle('Invoices');
        // Unir celdas
        $sheet->mergeCells('B8:F8');
        // Introducir valor
        $sheet->setCellValue('B8', 'Soluciones++');
        // Introducir formula
        $sheet->setCellValue('B9', '=7+12');

        // Aplicar estilos a un rango
        /* $sheet->getStyle('A10:G10')->applyFromArray([
            'font' => [
                'bold' => true,
                'name' => 'Arial'
            ],
            'alignment' => [
                'horizontal' => 'center',
                'vertical' => 'center'
            ],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'argb' => 'C5D9F1',
                ]
            ]
        ]);

        // Aplicar bordes a una tabla
        $sheet->getStyle('A10:G' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                    //'borderStyle' => 'dotted'
                ]
            ]
        ]);

        // Dejar seleccionada una celda determinada
        $sheet->getStyle('A11')->applyFromArray([
        ]); */

        // Otra forma de aplicar estilos y bordes
        return [
            'A10:G10' => [
                'font' => [
                    'bold' => true,
                    'name' => 'Arial'
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center'
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => [
                        'argb' => 'C5D9F1',
                    ]
                ]
            ],
            'A10:G' . $sheet->getHighestRow() => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => 'thin'
                        //'borderStyle' => 'dotted'
                    ]
                ]
            ],
            'A11' => []
        ];
    }
}
