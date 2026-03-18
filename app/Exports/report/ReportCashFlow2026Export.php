<?php

namespace App\Exports\report;

use DateTime;
use App\Models\Mst_company;
use App\Models\Tx_cash_flow_2026;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ReportCashFlow2026Export implements FromView, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $period;
    protected $bank_id;
    protected $monthDays;
    protected $daysInMonth = [31,28,31,30,31,30,31,31,30,31,30,31];
    protected $MonthName = ['JANUARI','FEBRUARI','MARET','APRIL','MEI','JUNI','JULI','AGUSTUS','SEPTEMBER','OKTOBER','NOPEMBER','DESEMBER'];

    public function __construct($period, $bank_id)
    {
        ini_set('memory_limit', '256M');
        ini_set('max_execution_time', 3600);

        $this->period = $period;
        $this->bank_id = $bank_id;
    }

    public function view(): View
    {
        $startDateTimeObj = new DateTime('now');
        $start_datetime = $startDateTimeObj->format('Y-m-d H:i:s');

        $company = Mst_company::whereRaw('id=1')
        ->first();
        $companyName = $company?$company->name:'';

        $rndString = '';
        $qCashFlow = Tx_cash_flow_2026::where('period', '=', $this->period.'-01')
        ->where('bank_id', '=', $this->bank_id)
        ->orderBy('created_at', 'DESC')
        ->first();
        if ($qCashFlow){
            $rndString = $qCashFlow->report_code;
        }

        $data = [
            'period' => $this->period,
            'monthDays' => $this->monthDays,
            'randomString' => $rndString,
            'MonthName' => $this->MonthName,
            'companyName' => $companyName,
        ];
        return view('rpt.cash-flow.cash-flow-2026-xlsx', $data);
    }

    public function styles(Worksheet $sheet)
    {
        $bgHeaderRange = '';
        switch ($this->monthDays) {
            case 28:
                $bgHeaderRange = 'AE';
                break;
            case 29:
                $bgHeaderRange = 'AF';
                break;
            case 30:
                $bgHeaderRange = 'AG';
                break;
            default:
                $bgHeaderRange = 'AH';
        }

        // get highest row info
        $lastHighestRow = $sheet->getHighestRow();
        // $sheet->setCellValue('D'.$lastHighestRow, "TOTAL");
        // $sheet->setCellValue('M'.$lastHighestRow,'=SUM(M7:M'.($lastHighestRow-1).')');

        // set background color
        $sheet->getStyle('A3:B3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '0070c0'],
        ]);
        $sheet->getStyle('C3:'.$bgHeaderRange.'3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'ccffff'],
        ]);

        $styleArray = [
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // set center
        $sheet->getStyle('A3:'.$bgHeaderRange.'3')->applyFromArray($styleArray);
        $sheet->getStyle('A')->applyFromArray($styleArray);

        // set border
        $sheet->getStyle('A3:'.$bgHeaderRange.$lastHighestRow)->applyFromArray([
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
            // 'A3' => [
            //     'font' => [
            //         'bold' => true,
            //         'size' => 16,
            //     ]
            // ],
            // 'A1' => ['font' => ['bold' => true]],
            // 'N4' => ['font' => ['bold' => true]],
            // 'A4' => ['font' => ['bold' => true]],
            'A3:'.$bgHeaderRange.'3' => ['font' => ['bold' => true]],
            'A3:B3' => ['font' => [
                'color' => [
                    'rgb' => 'ffffff',
                ]
            ]],
            'C3:'.$bgHeaderRange.'3' => ['font' => [
                'color' => [
                    'rgb' => '0000000',
                ]
            ]],
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
            // 'C' => NumberFormat::FORMAT_NUMBER,
            // 'D' => NumberFormat::FORMAT_NUMBER,
            // 'E' => NumberFormat::FORMAT_NUMBER,
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

    private function isLeapYear($year) {
        if (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0)) {
            return true;
        } else {
            return false;
        }
    }
}
