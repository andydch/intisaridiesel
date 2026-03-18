<?php

namespace App\Exports\admin;

use App\Models\Mst_sub_district;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SubDistrictExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function  __construct()
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Mst_sub_district::leftJoin('mst_districts', 'mst_districts.id', '=', 'mst_sub_districts.district_id')
            ->leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_districts.city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_cities.province_id')
            ->leftJoin('mst_countries', 'mst_countries.id', '=', 'mst_cities.country_id')
            ->select(
                'mst_sub_districts.id',
                'mst_sub_districts.sub_district_name',
                DB::raw('CONCAT("\'", mst_sub_districts.post_code) AS phone1'),
                'mst_sub_districts.district_id',
                'mst_districts.district_name',
                'mst_cities.city_name',
                'mst_provinces.province_name',
                'mst_countries.country_name',
                'mst_sub_districts.active',
                'mst_sub_districts.created_by',
                'mst_sub_districts.updated_by',
                'mst_sub_districts.created_at',
                'mst_sub_districts.updated_at'
            )
            ->orderBy('mst_sub_districts.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "sub district name", "post code", "district id", "district name", "city name", "province name", "country name",
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
            'F' => 50,
            'G' => 50,
            'H' => 50,
            'I' => 10,
            'K' => 35,
            'L' => 35,
            'M' => 35,
        ];
    }
}
