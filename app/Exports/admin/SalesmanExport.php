<?php

namespace App\Exports\admin;

use App\Models\Mst_salesman;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesmanExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_salesman::leftJoin('mst_branches', 'mst_branches.id', '=', 'mst_salesmans.branch_id')
            ->leftJoin('mst_sub_districts', 'mst_sub_districts.id', '=', 'mst_salesmans.sub_district_id')
            ->leftJoin('mst_districts', 'mst_districts.id', '=', 'mst_salesmans.district_id')
            ->leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_salesmans.city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_salesmans.province_id')
            ->leftJoin('mst_globals', 'mst_globals.id', '=', 'mst_salesmans.gender_id')
            ->select(
                'mst_salesmans.id',
                'mst_salesmans.name AS salesman_name',
                'mst_salesmans.slug',
                'mst_salesmans.branch_id',
                'mst_branches.name',
                'mst_salesmans.address',
                'mst_salesmans.province_id',
                'mst_provinces.province_name',
                'mst_salesmans.city_id',
                'mst_cities.city_name',
                'mst_salesmans.district_id',
                'mst_districts.district_name',
                'mst_salesmans.sub_district_id',
                'mst_sub_districts.sub_district_name',
                'mst_salesmans.email',
                DB::raw('CONCAT("\'", mst_salesmans.post_code) AS post_code'),
                DB::raw('CONCAT("\'", mst_salesmans.id_no) AS id_no'),
                DB::raw('CONCAT("\'", mst_salesmans.mobilephone) AS mobilephone'),
                'mst_salesmans.gender_id',
                'mst_globals.title_ind',
                'mst_salesmans.birth_date',
                'mst_salesmans.join_date',
                'mst_salesmans.sales_target',
                'mst_salesmans.active',
                'mst_salesmans.created_by',
                'mst_salesmans.updated_by',
                'mst_salesmans.created_at',
                'mst_salesmans.updated_at'
            )
            ->orderBy('mst_salesmans.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            "id", "name", "slug", "branch id", "branch name", "address", "province id", "province name", "city id", "city name",
            "district id", "district name", "sub district id", "sub district name", "email", "postcode", "NIK", "mobilephone", "gender id",
            "gender", "birth date", "join date", "sales target", "active", "created by", "updated by", "created at", "updated at"
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
            'A' => 10,  // id
            'B' => 30,  // name
            'C' => 30,  // slug
            'D' => 10,  // branch id
            'E' => 30,  // branch name
            'F' => 75,  // address
            'G' => 10,  // province id
            'H' => 30,  // province name
            'I' => 10,  // city id
            'J' => 30,  // city name
            'K' => 10,  // district id
            'L' => 30,  // district name
            'M' => 10,  // sub district id
            'N' => 30,  // sub district name
            'O' => 30,  // email
            'P' => 10,  // postcode
            'Q' => 15,  // mobilephone
            'R' => 10,  // gender id
            'S' => 20,  // gender
            'T' => 10,  // active
            'U' => 10,  // active
            'V' => 10,  // active
            'W' => 10,  // active
            'X' => 10,  // active
            'Y' => 10,  // active
            'Z' => 10,  // created by
            'AA' => 10,  // updated by
            'AB' => 10,  // created at
            'AC' => 10,  // updated at
        ];
    }
}
