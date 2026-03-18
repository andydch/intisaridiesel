<?php

namespace App\Exports\admin;

use App\Models\Mst_city;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CityExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_city::leftJoin('mst_countries', 'mst_countries.id', '=', 'mst_cities.country_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_cities.province_id')
            ->select(
                'mst_cities.id',
                'mst_cities.city_name',
                'mst_cities.city_type',
                'mst_cities.province_id',
                'mst_provinces.province_name',
                'mst_cities.country_id',
                'mst_countries.country_name',
                'mst_cities.active',
                'mst_cities.created_by',
                'mst_cities.updated_by',
                'mst_cities.created_at',
                'mst_cities.updated_at'
            )
            ->orderBy('mst_cities.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "city name", "city type", "province id", "province name", "country id", "country name",
            "active", "created by", "updated by", "created at", "updated at"
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
            'C' => 30,
            'D' => 10,
            'E' => 50,
            'F' => 10,
            'G' => 50,
            'H' => 35,
            'I' => 35,
            'K' => 35,
            'L' => 35,
            'M' => 35,
        ];
    }
}
