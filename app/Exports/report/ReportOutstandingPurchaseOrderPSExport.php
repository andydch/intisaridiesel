<?php

namespace App\Exports\report;

use App\Models\Mst_company;
use App\Models\Mst_global;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ReportOutstandingPurchaseOrderPSExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $date_start;
    protected $date_end;

    public function __construct($date_start,$date_end)
    {
        // ini_set('memory_limit', '64M');
        // ini_set('max_execution_time', 1800);

        $this->date_start = $date_start;
        $this->date_end = $date_end;
    }

    public function view(): View
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $company = Mst_company::whereRaw('id=1')
        ->first();

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $data = [
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'qCurrency' => $qCurrency,
            'company' => $company,
            'vat' => $vat,
        ];
        return view('rpt.outstanding-purchase-order-ps.outstanding-purchase-order-ps-xlsx',$data);
    }

    public function styles(Worksheet $sheet)
    {
        // get highest row info
        $lastHighestRow = $sheet->getHighestRow();
        // $sheet->setCellValue('D'.$lastHighestRow, "TOTAL");
        // $sheet->setCellValue('M'.$lastHighestRow,'=SUM(M7:M'.($lastHighestRow-1).')');

        // // set background color
        // $sheet->getStyle('G6:L6')->getFill()->applyFromArray([
        //     'fillType' => 'solid',
        //     'rotation' => 0,
        //     'color' => ['rgb' => 'eaf1dd'],
        // ]);

        // // set background color
        // $sheet->getStyle('G5:L5')->getFill()->applyFromArray([
        //     'fillType' => 'solid',
        //     'rotation' => 0,
        //     'color' => ['rgb' => '92d050'],
        // ]);

        // set header background color
        // $sheet->getStyle('A6:K7')->getFill()->applyFromArray([
        //     'fillType' => 'solid',
        //     'rotation' => 0,
        //     'color' => ['rgb' => 'eaf1dd'],
        // ]);

        // set header background color
        // $sheet->getStyle('F6:I6')->getFill()->applyFromArray([
        //     'fillType' => 'solid',
        //     'rotation' => 0,
        //     'color' => ['rgb' => '92d050'],
        // ]);

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // set center
        $sheet->getStyle('A3')->applyFromArray($styleArray);
        $sheet->getStyle('A4')->applyFromArray($styleArray);

        // $sheet->getStyle('A6:K'.$lastHighestRow)->applyFromArray([
        //     'borders' => [
        //         'allBorders' => [
        //             'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //             'color' => ['argb' => '000000'],
        //         ],
        //     ]
        // ]);

        // set text style
        return [
            // Style the first row as bold text.
            // 1 => ['font' => ['bold' => true]],
            // 2 => ['font' => ['bold' => true]],
            // 3 => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // '3' => ['font' => ['bold' => true]],
            'A3' => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ]
            ],
            // 'A1' => ['font' => ['bold' => true]],
            // 'N4' => ['font' => ['bold' => true]],
            'A4' => ['font' => ['bold' => true]],
            'A6:E6' => ['font' => ['bold' => true]],
            // 'D'.$lastHighestRow => ['font' => ['bold' => true]],
            // 'M'.$lastHighestRow => ['font' => ['bold' => true]],

            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function columnFormats(): array
    {
        return [
            // 'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_NUMBER,
            // 'F' => NumberFormat::FORMAT_NUMBER,
            // 'G' => NumberFormat::FORMAT_NUMBER,
            // 'H' => NumberFormat::FORMAT_NUMBER,
            // 'I' => NumberFormat::FORMAT_NUMBER,
            // 'J' => NumberFormat::FORMAT_NUMBER,
            // 'K' => NumberFormat::FORMAT_NUMBER,
            // 'L' => NumberFormat::FORMAT_NUMBER,
            // 'M' => NumberFormat::FORMAT_NUMBER,
            // 'N' => NumberFormat::FORMAT_NUMBER,
            // 'O' => NumberFormat::FORMAT_NUMBER,
            // 'P' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
