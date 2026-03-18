<?php

namespace App\Exports\admin;

use App\Models\Mst_district;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class DistrictExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_district::leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_districts.city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_cities.province_id')
            ->leftJoin('mst_countries', 'mst_countries.id', '=', 'mst_provinces.country_id')
            ->select(
                'mst_districts.id',
                'mst_districts.district_name',
                'mst_districts.city_id',
                'mst_cities.city_name',
                'mst_provinces.province_name',
                'mst_countries.country_name',
                'mst_districts.active',
                'mst_districts.created_by',
                'mst_districts.updated_by',
                'mst_districts.created_at',
                'mst_districts.updated_at'
            )
            ->orderBy('mst_districts.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "district name", "city id", "city name", "province name", "country name",
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
            'C' => 10,
            'D' => 50,
            'E' => 50,
            'F' => 50,
            'G' => 10,
            'H' => 35,
            'I' => 35,
            'K' => 35,
            'L' => 35,
        ];
    }
}
