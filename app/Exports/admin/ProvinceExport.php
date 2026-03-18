<?php

namespace App\Exports\admin;

use App\Models\Mst_province;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ProvinceExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_province::leftJoin('mst_countries', 'mst_countries.id', '=', 'mst_provinces.country_id')
            ->select(
                'mst_provinces.id',
                'mst_provinces.province_name',
                'mst_provinces.country_id',
                'mst_countries.country_name',
                'mst_provinces.active',
                'mst_provinces.created_by',
                'mst_provinces.updated_by',
                'mst_provinces.created_at',
                'mst_provinces.updated_at'
            )->with('country')
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "province name", "country id", "country name", "active", "created by",
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
            'C' => 10,      // column E
            'D' => 50,
            'E' => 15,
            'F' => 35,
            'G' => 35,
            'H' => 35,
            'I' => 35,
        ];
    }
}
