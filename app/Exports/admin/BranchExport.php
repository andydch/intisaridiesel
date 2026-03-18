<?php

namespace App\Exports\admin;

use App\Models\Mst_branch;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_branch::leftJoin('mst_sub_districts', 'mst_sub_districts.id', '=', 'mst_branches.sub_district_id')
            ->leftJoin('mst_districts', 'mst_districts.id', '=', 'mst_branches.district_id')
            ->leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_branches.city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_branches.province_id')
            ->select(
                'mst_branches.id',
                'mst_branches.initial',
                'mst_branches.name',
                'mst_branches.slug',
                'mst_branches.address',
                'mst_branches.province_id',
                'mst_provinces.province_name',
                'mst_branches.city_id',
                'mst_cities.city_name',
                'mst_branches.district_id',
                'mst_districts.district_name',
                'mst_branches.sub_district_id',
                'mst_sub_districts.sub_district_name',
                DB::raw('CONCAT("\'", mst_branches.post_code) AS post_code'),
                DB::raw('CONCAT("\'", mst_branches.phone1) AS phone1'),
                DB::raw('CONCAT("\'", mst_branches.phone2) AS phone2'),
                'mst_branches.active',
                'mst_branches.created_by',
                'mst_branches.updated_by',
                'mst_branches.created_at',
                'mst_branches.updated_at'
            )
            ->orderBy('mst_branches.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "initial", "name", "slug", "address", "province id", "province name", "city id", "city name", "district id", "district name",
            "sub district id", "sub district name", "postcode", "phone 1", "phone 2", "active", "created by", "updated by",
            "created at", "updated at"
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
            'B' => 20,
            'C' => 50,
            'D' => 50,
            'E' => 50,
            'F' => 10,
            'G' => 50,
            'H' => 10,
            'I' => 50,
            'J' => 10,
            'K' => 50,
            'L' => 10,
            'M' => 50,
            'N' => 20,
            'O' => 20,
            'P' => 20,
            'Q' => 10,
            'R' => 10,
            'S' => 10,
            'T' => 10,
            'U' => 10,
        ];
    }
}
