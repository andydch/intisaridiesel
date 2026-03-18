<?php

namespace App\Exports\admin;

use App\Models\Mst_customer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_customer::leftJoin('mst_globals AS ety_type', 'mst_customers.entity_type_id', '=', 'ety_type.id')
            ->leftJoin('mst_sub_districts AS mst_sdst', 'mst_customers.sub_district_id', '=', 'mst_sdst.id')
            ->leftJoin('mst_sub_districts AS mst_sdst_npwp', 'mst_customers.npwp_sub_district_id', '=', 'mst_sdst_npwp.id')
            ->leftJoin('mst_districts AS mst_dst', 'mst_customers.district_id', '=', 'mst_dst.id')
            ->leftJoin('mst_districts AS mst_dst_npwp', 'mst_customers.npwp_district_id', '=', 'mst_dst_npwp.id')
            ->leftJoin('mst_cities AS mst_ct', 'mst_customers.city_id', '=', 'mst_ct.id')
            ->leftJoin('mst_cities AS mst_ct_npwp', 'mst_customers.npwp_city_id', '=', 'mst_ct_npwp.id')
            ->leftJoin('mst_provinces AS mst_prov', 'mst_customers.province_id', '=', 'mst_prov.id')
            ->leftJoin('mst_provinces AS mst_prov_npwp', 'mst_customers.npwp_province_id', '=', 'mst_prov_npwp.id')
            ->leftJoin('mst_salesmans AS sales', 'mst_customers.salesman_id', '=', 'sales.id')
            ->leftJoin('mst_salesmans AS sales2', 'mst_customers.salesman_id2', '=', 'sales2.id')
            ->select(
                'mst_customers.id',
                'mst_customers.entity_type_id',
                'ety_type.title_ind AS title_ind',
                'mst_customers.name',
                'mst_customers.slug',
                'mst_customers.office_address',
                'mst_customers.province_id',
                'mst_prov.province_name AS province_name',
                'mst_customers.city_id',
                'mst_ct.city_name AS city_name',
                'mst_customers.district_id',
                'mst_dst.district_name AS district_name',
                'mst_customers.sub_district_id',
                'mst_sdst.sub_district_name AS sub_district_name',
                DB::raw('CONCAT("\'", mst_customers.post_code) AS post_code'),
                'mst_customers.cust_email',
                DB::raw('CONCAT("\'", mst_customers.phone1) AS phone1'),
                DB::raw('CONCAT("\'", mst_customers.phone2) AS phone2'),
                'mst_customers.pic1_name',
                DB::raw('CONCAT("\'", mst_customers.pic1_phone) AS pic1_phone'),
                'mst_customers.pic1_email',
                'mst_customers.pic2_name',
                DB::raw('CONCAT("\'", mst_customers.pic2_phone) AS pic2_phone'),
                'mst_customers.pic2_email',
                'mst_customers.npwp_no',
                'mst_customers.npwp_address',
                'mst_customers.npwp_province_id',
                'mst_prov_npwp.province_name AS npwp_province_name',
                'mst_customers.npwp_city_id',
                'mst_ct_npwp.city_name AS npwp_city_name',
                'mst_customers.npwp_district_id',
                'mst_dst_npwp.district_name AS npwp_district_name',
                'mst_customers.npwp_sub_district_id',
                'mst_sdst_npwp.sub_district_name AS npwp_sub_district_name',
                'mst_customers.credit_limit',
                'mst_customers.limit_balance',
                'mst_customers.top',
                'mst_customers.salesman_id',
                'sales.name AS salesman_name',
                'mst_customers.salesman_id2',
                'sales2.name AS salesman_name2',
                'mst_customers.customer_status',
                'mst_customers.payment_status',
                'mst_customers.active',
                'mst_customers.created_by',
                'mst_customers.updated_by',
            )
            ->orderBy('mst_customers.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'entity type id',
            'entity type name',
            'customer name',
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
            'customer email',
            'phone1',
            'phone2',
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
            'credit limit',
            'limit balance',
            'top',
            'salesman id #1',
            'salesman name #1',
            'salesman id #2',
            'salesman name #2',
            'customer status',
            'payment status',
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
            'C' => 20,
            'D' => 50,
            'E' => 50,
            'F' => 75,
            'G' => 10,
            'H' => 50,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 50,
            'M' => 10,
            'N' => 50,
            'O' => 15,
            'P' => 50,
            'Q' => 15,
            'R' => 15,
            'S' => 50,
            'T' => 15,
            'U' => 50,
            'V' => 50,
            'W' => 15,
            'X' => 50,
            'Y' => 30,
            'Z' => 75,
            'AA' => 10,
            'AB' => 50,
            'AC' => 10,
            'AD' => 50,
            'AE' => 10,
            'AF' => 50,
            'AG' => 10,
            'AH' => 50,
            'AI' => 50,
            'AJ' => 50,
            'AK' => 15,
            'AL' => 10,
            'AM' => 50,
            'AN' => 10,
            'AO' => 50,
            'AP' => 10,
            'AQ' => 10,
            'AR' => 10,
            'AS' => 15,
            'AT' => 15,
        ];
    }
}
