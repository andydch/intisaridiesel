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

class ReportFinanceIncomeStatementExport implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $lokal_input;
    protected $branch_id;
    protected $month_id;
    protected $year_id;

    protected $sales_coa_code;
    protected $sales_retur_coa_code;
    protected $cogs_coa_code;
    protected $expenses_coa_code;
    protected $other_income_coa_code;
    protected $other_expense_coa_code;

    public function __construct($lokal_input,$branch_id,$month_id,$year_id)
    {
        ini_set('memory_limit', '128M');
        // ini_set('max_execution_time', 1800);

        $this->lokal_input = $lokal_input;
        $this->branch_id = $branch_id;
        $this->month_id = urldecode($month_id);
        $this->year_id = urldecode($year_id);

        $this->sales_coa_code = 51;
        $this->sales_retur_coa_code = 52;
        $this->cogs_coa_code = 7;
        $this->expenses_coa_code = 6;
        $this->other_income_coa_code = 8;
        $this->other_expense_coa_code = 9;
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
            'qCurrency' => $qCurrency,
            'qCompany' => $qCompany,
            'lokal_input' => $this->lokal_input,
            'branch_id' => $this->branch_id,
            'month_id' => $this->month_id,
            'year_id' => $this->year_id,
            'sales_coa_code' => $this->sales_coa_code,
            'sales_retur_coa_code' => $this->sales_retur_coa_code,
            'cogs_coa_code' => $this->cogs_coa_code,
            'expenses_coa_code' => $this->expenses_coa_code,
            'other_income_coa_code' => $this->other_income_coa_code,
            'other_expense_coa_code' => $this->other_expense_coa_code,
            'isLeapYear' => $this->isLeapYear(date_format(now(),"Y")),
        ];
        return view('rpt.rpt-finance-income-statement.rpt-finance-income-statement-xlsx',$data);
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
        $sheet->getStyle('A8:O8')->applyFromArray($styleArray);

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

            'A8:N8' => ['font' => ['bold' => true]],

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
            'A' => NumberFormat::FORMAT_TEXT,
            // 'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_NUMBER,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_NUMBER,
            'N' => NumberFormat::FORMAT_NUMBER,
            'O' => NumberFormat::FORMAT_NUMBER,
            // 'P' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    private function isLeapYear($year) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            return true;
        } else {
            return false;
        }
    }
}
