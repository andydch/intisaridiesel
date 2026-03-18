<?php

namespace App\Exports\admin;

use App\Models\Mst_supplier;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_supplier::leftJoin('mst_globals AS ety_type', 'mst_suppliers.entity_type_id', '=', 'ety_type.id')
            ->leftJoin('mst_globals AS spl_type', 'mst_suppliers.supplier_type_id', '=', 'spl_type.id')
            ->leftJoin('mst_sub_districts AS mst_sdst', 'mst_suppliers.sub_district_id', '=', 'mst_sdst.id')
            ->leftJoin('mst_sub_districts AS mst_sdst_npwp', 'mst_suppliers.npwp_sub_district_id', '=', 'mst_sdst_npwp.id')
            ->leftJoin('mst_districts AS mst_dst', 'mst_suppliers.district_id', '=', 'mst_dst.id')
            ->leftJoin('mst_districts AS mst_dst_npwp', 'mst_suppliers.npwp_district_id', '=', 'mst_dst_npwp.id')
            ->leftJoin('mst_cities AS mst_ct', 'mst_suppliers.city_id', '=', 'mst_ct.id')
            ->leftJoin('mst_cities AS mst_ct_npwp', 'mst_suppliers.npwp_city_id', '=', 'mst_ct_npwp.id')
            ->leftJoin('mst_provinces AS mst_prov', 'mst_suppliers.province_id', '=', 'mst_prov.id')
            ->leftJoin('mst_provinces AS mst_prov_npwp', 'mst_suppliers.npwp_province_id', '=', 'mst_prov_npwp.id')
            ->leftJoin('mst_countries AS mst_ctry', 'mst_suppliers.country_id', '=', 'mst_ctry.id')
            ->leftJoin('mst_globals AS currency1', 'mst_suppliers.currency1', '=', 'currency1.id')
            ->leftJoin('mst_globals AS currency2', 'mst_suppliers.currency2', '=', 'currency2.id')
            ->select(
                'mst_suppliers.id',
                'mst_suppliers.supplier_type_id',
                'spl_type.title_ind AS spl_type_title_ind',
                'mst_suppliers.entity_type_id',
                'ety_type.title_ind AS ety_type_title_ind',
                'mst_suppliers.name',
                'mst_suppliers.slug',
                'mst_suppliers.office_address',
                'mst_suppliers.country_id',
                'mst_ctry.country_name AS country_name',
                'mst_suppliers.province_id',
                'mst_prov.province_name AS province_name',
                'mst_suppliers.city_id',
                'mst_ct.city_name AS city_name',
                'mst_suppliers.district_id',
                'mst_dst.district_name AS district_name',
                'mst_suppliers.sub_district_id',
                'mst_sdst.sub_district_name AS sub_district_name',
                DB::raw('CONCAT("\'", mst_suppliers.post_code) AS post_code'),
                'mst_suppliers.supplier_email',
                DB::raw('CONCAT("\'", mst_suppliers.phone1) AS phone1'),
                DB::raw('CONCAT("\'", mst_suppliers.phone2) AS phone2'),
                'mst_suppliers.currency1',
                'currency1.title_ind AS currency1_name',
                'mst_suppliers.currency2',
                'currency2.title_ind AS currency2_name',
                'mst_suppliers.pic1_name',
                DB::raw('CONCAT("\'", mst_suppliers.pic1_phone) AS pic1_phone'),
                'mst_suppliers.pic1_email',
                'mst_suppliers.pic2_name',
                DB::raw('CONCAT("\'", mst_suppliers.pic2_phone) AS pic2_phone'),
                'mst_suppliers.pic2_email',
                'mst_suppliers.npwp_no',
                'mst_suppliers.npwp_address',
                'mst_suppliers.npwp_province_id',
                'mst_prov_npwp.province_name AS npwp_province_name',
                'mst_suppliers.npwp_city_id',
                'mst_ct_npwp.city_name AS npwp_city_name',
                'mst_suppliers.npwp_district_id',
                'mst_dst_npwp.district_name AS npwp_district_name',
                'mst_suppliers.npwp_sub_district_id',
                'mst_sdst_npwp.sub_district_name AS npwp_sub_district_name',
                'mst_suppliers.top',
                'mst_suppliers.credit_limit',
                'mst_suppliers.active',
                'mst_suppliers.created_by',
                'mst_suppliers.updated_by',
            )
            ->orderBy('mst_suppliers.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'supplier type id',
            'supplier type name',
            'entity type id',
            'entity type name',
            'supplier name',
            'slug',
            'office address',
            'country id',
            'country name',
            'province id',
            'province name',
            'city id',
            'city name',
            'district id',
            'district name',
            'sub district id',
            'sub district name',
            'postcode',
            'supplier email',
            'phone1',
            'phone2',
            'currency id 1',
            'currency name 1',
            'currency id 2',
            'currency name 2',
            'pic name 1',
            'pic phone 1',
            'pic email 1',
            'pic name 2',
            'pic phone 2',
            'pic email 2',
            'npwp no',
            'npwp address',
            'npwp province id',
            'npwp province name',
            'npwp city id',
            'npwp city name',
            'npwp district id',
            'npwp district name',
            'npwp sub district id',
            'npwp sub district name',
            'top',
            'credit limit',
            'active',
            'created by',
            'updated by',
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
            'D' => 10,
            'E' => 50,
            'F' => 75,
            'G' => 75,
            'H' => 75,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 50,
            'M' => 10,
            'N' => 50,
            'O' => 10,
            'P' => 50,
            'Q' => 10,
            'R' => 50,
            'S' => 15,
            'T' => 50,
            'U' => 20,
            'V' => 20,
            'W' => 10,
            'X' => 25,
            'Y' => 10,
            'Z' => 25,
            'AA' => 50,
            'AB' => 20,
            'AC' => 50,
            'AD' => 50,
            'AE' => 20,
            'AF' => 50,
            'AG' => 50,
            'AH' => 50,
            'AI' => 10,
            'AJ' => 50,
            'AK' => 10,
            'AL' => 50,
            'AM' => 50,
            'AN' => 10,
            'AO' => 10,
            'AP' => 50,
            'AQ' => 10,
            'AR' => 20,
            'AS' => 10,
            'AT' => 10,
            'AU' => 10,
        ];
    }
}
