<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_customer_shipment_address;

class DispCustomerShipmentAddressController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_customer_shipment_address::leftJoin('mst_provinces', 'mst_customer_shipment_address.province_id', '=', 'mst_provinces.id')
            ->leftJoin('mst_cities', 'mst_customer_shipment_address.city_id', '=', 'mst_cities.id')
            ->leftJoin('mst_districts', 'mst_customer_shipment_address.district_id', '=', 'mst_districts.id')
            ->leftJoin('mst_sub_districts', 'mst_customer_shipment_address.sub_district_id', '=', 'mst_sub_districts.id')
            ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
            ->select(
                'mst_customer_shipment_address.id AS shipment_id',
                'mst_customer_shipment_address.address AS shipment_address',
                'mst_provinces.province_name',
                'mst_cities.city_name',
                'mst_districts.district_name',
                'mst_sub_districts.sub_district_name',
                'mst_sub_districts.post_code',
                'mst_countries.country_name',
            )
            ->where('mst_customer_shipment_address.customer_id', '=', $request->id,)
            ->where('mst_customer_shipment_address.active', '=', 'Y')
            ->get();
        $data = [
            'customers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
