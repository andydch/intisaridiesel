<?php

namespace App\Exports\admin;

use App\Models\Mst_customer_shipment_address;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CustomerShipmentExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
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
        return Mst_customer_shipment_address::leftJoin('mst_customers', 'mst_customer_shipment_address.customer_id', '=', 'mst_customers.id')
            ->leftJoin('mst_sub_districts AS mst_sdst', 'mst_customer_shipment_address.sub_district_id', '=', 'mst_sdst.id')
            ->leftJoin('mst_districts AS mst_dst', 'mst_customer_shipment_address.district_id', '=', 'mst_dst.id')
            ->leftJoin('mst_cities AS mst_ct', 'mst_customer_shipment_address.city_id', '=', 'mst_ct.id')
            ->leftJoin('mst_provinces AS mst_prov', 'mst_customer_shipment_address.province_id', '=', 'mst_prov.id')
            ->select(
                'mst_customer_shipment_address.id',
                'mst_customers.id AS cust_id',
                'mst_customers.name AS cust_name',
                'mst_customer_shipment_address.address',
                'mst_customer_shipment_address.province_id',
                'mst_prov.province_name AS province_name',
                'mst_customer_shipment_address.city_id',
                'mst_ct.city_name AS city_name',
                'mst_customer_shipment_address.district_id',
                'mst_dst.district_name AS district_name',
                'mst_customer_shipment_address.sub_district_id',
                'mst_sdst.sub_district_name AS sub_district_name',
                DB::raw('CONCAT("\'", mst_customer_shipment_address.phone) AS phone'),
                'mst_customer_shipment_address.active',
                'mst_customer_shipment_address.created_by',
                'mst_customer_shipment_address.updated_by',
            )
            ->orderBy('mst_customer_shipment_address.id', 'ASC')
            ->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'customer id',
            'name',
            'address',
            'province id',
            'province name',
            'city id',
            'city name',
            'district id',
            'district name',
            'sub district id',
            'sub district name',
            'phone',
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
            'D' => 50,
            'E' => 10,
            'F' => 50,
            'G' => 10,
            'H' => 50,
            'I' => 10,
            'J' => 50,
            'K' => 10,
            'L' => 50,
            'M' => 15,
            'N' => 10,
            'O' => 15,
            'P' => 15,
        ];
    }
}
