<?php

namespace App\Exports\admin;

use App\Models\Mst_company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompanyExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_company::leftJoin('mst_sub_districts', 'mst_sub_districts.id', '=', 'mst_companies.sub_district_id')
            ->leftJoin('mst_sub_districts AS mst_subdistrict_npwp', 'mst_subdistrict_npwp.id', '=', 'mst_companies.npwp_sub_district_id')
            ->leftJoin('mst_districts', 'mst_districts.id', '=', 'mst_companies.district_id')
            ->leftJoin('mst_districts AS mst_districts_npwp', 'mst_districts_npwp.id', '=', 'mst_companies.npwp_district_id')
            ->leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_companies.city_id')
            ->leftJoin('mst_cities AS mst_cities_npwp', 'mst_cities_npwp.id', '=', 'mst_companies.npwp_city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_companies.province_id')
            ->leftJoin('mst_provinces AS mst_provinces_npwp', 'mst_provinces_npwp.id', '=', 'mst_companies.npwp_province_id')
            ->select(
                'mst_companies.id',
                'mst_companies.name',
                'mst_companies.slug',
                'mst_companies.office_address',
                'mst_companies.province_id',
                'mst_provinces.province_name',
                'mst_companies.city_id',
                'mst_cities.city_name',
                'mst_companies.district_id',
                'mst_districts.district_name',
                'mst_companies.sub_district_id',
                'mst_sub_districts.sub_district_name',
                DB::raw('CONCAT("\'", mst_companies.post_code) AS post_code'),
                'mst_companies.company_email',
                DB::raw('CONCAT("\'", mst_companies.phone1) AS phone1'),
                DB::raw('CONCAT("\'", mst_companies.phone2) AS phone2'),
                'mst_companies.npwp_no',
                'mst_companies.npwp_address',
                'mst_companies.npwp_province_id',
                'mst_provinces_npwp.province_name AS province_name_npwp',
                'mst_companies.npwp_city_id',
                'mst_cities_npwp.city_name AS city_name_npwp',
                'mst_companies.npwp_district_id',
                'mst_districts_npwp.district_name AS district_name_npwp',
                'mst_companies.npwp_sub_district_id',
                'mst_subdistrict_npwp.sub_district_name AS sub_district_name_npwp',
                'mst_companies.active',
                'mst_companies.created_by',
                'mst_companies.updated_by',
                'mst_companies.created_at',
                'mst_companies.updated_at'
            )
            ->orderBy('mst_companies.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'slug',
            'office address',
            'province id',
            'province name',
            'city id',
            'city name',
            'district id',
            'district name',
            'sub district id',
            'sub district name',
            'postcode',
            'company email',
            'phone #1',
            'phone #2',
            'npwp no',
            'npwp address',
            'npwp province id',
            'province name npwp',
            'npwp city id',
            'city name npwp',
            'npwp district id',
            'district name npwp',
            'npwp sub district id',
            'sub district name npwp',
            'active',
            'created by',
            'updated by',
            'created at',
            'updated at'
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
            'E' => 10,
            'F' => 50,
            'G' => 10,
            'H' => 50,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 50,
            'M' => 20,
            'N' => 30,
            'O' => 20,
            'P' => 20,
            'Q' => 50,
            'R' => 50,
            'S' => 10,
            'T' => 50,
            'U' => 10,
            'V' => 50,
            'W' => 10,
            'X' => 50,
            'Y' => 10,
            'Z' => 50,
            'AA' => 10,
            'AB' => 10,
            'AC' => 10,
            'AD' => 10,
        ];
    }
}
