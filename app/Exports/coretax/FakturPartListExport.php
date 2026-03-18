<?php

namespace App\Exports\coretax;

use App\Models\Mst_company;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class FakturPartListExport extends DefaultValueBinder implements FromView, WithTitle, WithColumnFormatting, ShouldAutoSize, WithCustomValueBinder
{
    protected $fakturs;

    public function __construct($fakturs)
    {
        $this->fakturs = $fakturs;
    }

    public function view(): View
    {
        $data = [
            'fakturs'=>$this->fakturs,
        ];
        return view('tx.delivery-order.faktur_parts', $data);
    }

    /**
     * Value binding
     * https://docs.laravel-excel.com/3.1/exports/column-formatting.html#value-binders
     */
    public function bindValue(Cell $cell, $value)
    {
        if ($cell->getColumn() == 'F' && $cell->getRow()>=2) {
            // $cell->setValueExplicit($value, DataType::TYPE_STRING2);

            // return true;
        }

        // else return default behavior
        return parent::bindValue($cell, $value);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'DetailFaktur';
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_NUMBER_00,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_00,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'L' => NumberFormat::FORMAT_NUMBER_00,
            'M' => NumberFormat::FORMAT_NUMBER_00,
            'N' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
