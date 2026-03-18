<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_customer_shipment_address;

class DispShipmentAddressController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $addr = Mst_customer_shipment_address::leftJoin('mst_sub_districts AS msd','mst_customer_shipment_address.sub_district_id','=','msd.id')
        ->leftJoin('mst_districts AS md','mst_customer_shipment_address.district_id','=','md.id')
        ->leftJoin('mst_cities AS mc','mst_customer_shipment_address.city_id','=','mc.id')
        ->leftJoin('mst_provinces AS mp','mst_customer_shipment_address.province_id','=','mp.id')
        ->leftJoin('mst_countries AS mcs','mp.country_id','=','mcs.id')
        ->select(
            'mst_customer_shipment_address.id AS shipment_address_id',
            'mst_customer_shipment_address.address',
            'msd.sub_district_name',
            'md.district_name',
            'mc.city_type',
            'mc.city_name',
            'mp.province_name',
            'mcs.country_name',
        )
        ->where([
            'mst_customer_shipment_address.customer_id' => $request->customer_id,
            'mst_customer_shipment_address.active' => 'Y'
        ])
        ->get();
        $data = [
            'shipment_addr' => $addr->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
