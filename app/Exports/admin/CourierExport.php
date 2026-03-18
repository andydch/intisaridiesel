<?php

namespace App\Exports\admin;

use App\Models\Mst_courier;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CourierExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_courier::leftJoin('mst_globals AS ety_type', 'ety_type.id', '=', 'mst_couriers.entity_type_id')
            ->leftJoin('mst_sub_districts', 'mst_sub_districts.id', '=', 'mst_couriers.sub_district_id')
            ->leftJoin('mst_sub_districts AS mst_subdistrict_npwp', 'mst_subdistrict_npwp.id', '=', 'mst_couriers.npwp_sub_district_id')
            ->leftJoin('mst_districts', 'mst_districts.id', '=', 'mst_couriers.district_id')
            ->leftJoin('mst_districts AS mst_districts_npwp', 'mst_districts_npwp.id', '=', 'mst_couriers.npwp_district_id')
            ->leftJoin('mst_cities', 'mst_cities.id', '=', 'mst_couriers.city_id')
            ->leftJoin('mst_cities AS mst_cities_npwp', 'mst_cities_npwp.id', '=', 'mst_couriers.npwp_city_id')
            ->leftJoin('mst_provinces', 'mst_provinces.id', '=', 'mst_couriers.province_id')
            ->leftJoin('mst_provinces AS mst_provinces_npwp', 'mst_provinces_npwp.id', '=', 'mst_couriers.npwp_province_id')
            ->select(
                'mst_couriers.id',
                'mst_couriers.entity_type_id',
                'ety_type.title_ind AS ety_type_name',
                'mst_couriers.name',
                'mst_couriers.slug',
                'mst_couriers.office_address',
                'mst_couriers.province_id',
                'mst_provinces.province_name',
                'mst_couriers.city_id',
                'mst_cities.city_name',
                'mst_couriers.district_id',
                'mst_districts.district_name',
                'mst_couriers.sub_district_id',
                'mst_sub_districts.sub_district_name',
                DB::raw('CONCAT("\'", mst_couriers.post_code) AS post_code'),
                'mst_couriers.courier_email',
                DB::raw('CONCAT("\'", mst_couriers.phone1) AS phone1'),
                DB::raw('CONCAT("\'", mst_couriers.phone2) AS phone2'),
                'mst_couriers.pic1_name',
                DB::raw('CONCAT("\'", mst_couriers.pic1_phone) AS pic1_phone'),
                'mst_couriers.pic1_email',
                'mst_couriers.npwp_no',
                'mst_couriers.npwp_address',
                'mst_couriers.npwp_province_id',
                'mst_provinces_npwp.province_name AS province_name_npwp',
                'mst_couriers.npwp_city_id',
                'mst_cities_npwp.city_name AS city_name_npwp',
                'mst_couriers.npwp_district_id',
                'mst_districts_npwp.district_name AS district_name_npwp',
                'mst_couriers.npwp_sub_district_id',
                'mst_subdistrict_npwp.sub_district_name AS sub_district_name_npwp',
                'mst_couriers.active',
                'mst_couriers.created_by',
                'mst_couriers.updated_by',
                'mst_couriers.created_at',
                'mst_couriers.updated_at'
            )
            ->orderBy('mst_couriers.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'entity type id',
            'entity type name',
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
            'pic name',
            'pic phone',
            'pic email',
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
            'B' => 10,
            'C' => 50,
            'D' => 50,
            'E' => 50,
            'F' => 50,
            'G' => 10,
            'H' => 50,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 50,
            'M' => 10,
            'N' => 50,
            'O' => 10,
            'P' => 50,
            'Q' => 20,
            'R' => 20,
            'S' => 50,
            'T' => 20,
            'U' => 50,
            'V' => 50,
            'W' => 50,
            'X' => 10,
            'Y' => 50,
            'Z' => 10,
            'AA' => 50,
            'AB' => 10,
            'AC' => 50,
            'AD' => 10,
            'AE' => 50,
            'AF' => 10,
            'AG' => 10,
            'AH' => 10,
            'AI' => 10,
            'AJ' => 10,
        ];
    }
}
