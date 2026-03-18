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

class SummaryStockPerGudangPerMerkExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $title = 'Summary Stock Per Branch Per Merk';
    protected $folder = 'summary-stock-per-gudang-per-merk';
    protected $branch_id;
    protected $brand_id;
    protected $date;

    public function __construct($branch_id,$brand_id,$date)
    {
        // ini_set('memory_limit', '2048M');
        // ini_set('max_execution_time', 1800);

        $this->branch_id = $branch_id;
        $this->brand_id = $brand_id;
        $this->date = $date;
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

        $data = [
            'title' => $this->title,
            'branch_id' => $this->branch_id,
            'brand_id' => $this->brand_id,
            'date' => $this->date,
            'qCurrency' => $qCurrency,
            'company' => $company,
        ];
        return view('rpt.'.$this->folder.'.summary-stock-per-gudang-per-merk-xls',$data);
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
        $sheet->getStyle('A5:D5')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'eaf1dd'],
        ]);

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

        // set tanggal center
        $sheet->getStyle('D4')->applyFromArray($styleArray);

        $sheet->getStyle('A5:D'.$lastHighestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ]
        ]);

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
            // 'H4' => ['font' => ['bold' => true]],
            'A5:D5' => ['font' => ['bold' => true]],
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
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
        ];
    }
}
