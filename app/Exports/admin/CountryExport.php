<?php

namespace App\Exports\admin;

use App\Models\Mst_country;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CountryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_country::select(
            'id',
            'country_name',
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
            "id", "country name", "active", "created by",
            "updated by", "created at", "updated at"
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
            'C' => 15,      // column E
            'D' => 15,
            'E' => 15,
            'F' => 35,
            'G' => 35,
        ];
    }
}
