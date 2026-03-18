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

class ReportFinanceGeneralLedgerExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $coa_id;
    protected $branch_id;
    protected $date_start;
    protected $date_end;

    public function __construct($coa_id,$branch_id,$date_start,$date_end)
    {
        ini_set('memory_limit', '128M');
        // ini_set('max_execution_time', 1800);

        $this->coa_id = $coa_id;
        $this->branch_id = $branch_id;
        $this->date_start = urldecode($date_start);
        $this->date_end = urldecode($date_end);
    }

    public function view(): View
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qCompany = Mst_company::where([
            'id'=>1,
            'active'=>'Y',
        ])
        ->first();

        $data = [
            'coa_id' => $this->coa_id,
            'branch_id' => $this->branch_id,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'qCurrency' => ($qCurrency?$qCurrency:[]),
            'qCompany' => $qCompany,
        ];
        return view('rpt.rpt-finance-general-ledger.rpt-finance-general-ledger-xlsx',$data);
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
        $sheet->getStyle('A4')->applyFromArray($styleArray);
        $sheet->getStyle('A5')->applyFromArray($styleArray);
        // $sheet->getStyle('B3')->applyFromArray($styleArray);

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
            'A4' => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ]
            ],
            // 'A1' => ['font' => ['bold' => true]],
            // 'N4' => ['font' => ['bold' => true]],
            // 'A4' => ['font' => ['bold' => true]],

            'A8:F8' => ['font' => ['bold' => true]],

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
            // 'B' => NumberFormat::FORMAT_NUMBER,
            // 'C' => NumberFormat::FORMAT_TEXT,
            // 'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
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
