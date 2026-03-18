<?php

namespace App\Exports\admin;

use App\Models\Mst_global;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class GlobalExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_global::select(
            'id',
            'data_cat',
            'title_ind',
            'title_eng',
            'slug',
            'order_no',
            'notes',
            'small_desc_ind',
            'small_desc_eng',
            'long_desc_ind',
            'long_desc_eng',
            'string_val',
            'numeric_val',
            'active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at'
        )
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'data category',
            'title indonesia',
            'title english',
            'slug',
            'order no',
            'notes',
            'small desc indonesia',
            'small desc english',
            'long desc indonesia',
            'long desc english',
            'string value',
            'numeric value',
            'active',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // set text style
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function columnWidths(): array
    {
        // set column width
        return [
            'A' => 10,
            'B' => 50,
            'C' => 50,
            'D' => 50,
            'E' => 35,
            'F' => 10,
            'G' => 50,
            'H' => 50,
            'I' => 50,
            'J' => 50,
            'K' => 50,
            'L' => 30,
            'M' => 30,
            'N' => 30,
            'O' => 30,
            'P' => 30,
            'Q' => 30,
        ];
    }
}
