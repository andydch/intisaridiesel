<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_customer;

class DispCustomerPicController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_customer::leftJoin('mst_sub_districts', 'mst_customers.sub_district_id', '=', 'mst_sub_districts.id')
            ->leftJoin('mst_districts', 'mst_customers.district_id', '=', 'mst_districts.id')
            ->leftJoin('mst_cities', 'mst_customers.city_id', '=', 'mst_cities.id')
            ->leftJoin('mst_provinces', 'mst_customers.province_id', '=', 'mst_provinces.id')
            ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
            ->leftJoin('mst_globals AS entityType', 'mst_customers.entity_type_id', '=', 'entityType.id')
            ->select(
                'mst_customers.id AS customer_id',
                'mst_customers.name AS customer_name',
                'mst_customers.office_address',
                'mst_customers.pic1_name',
                'mst_customers.pic2_name',
                'mst_sub_districts.sub_district_name',
                'mst_sub_districts.post_code',
                'mst_districts.district_name',
                'mst_cities.city_name',
                'mst_cities.city_type',
                'mst_provinces.province_name',
                'mst_countries.country_name',
                'entityType.title_ind AS entity_type_name',
            )
            ->where([
                'mst_customers.id' => $request->customer_id,
                'mst_customers.active' => 'Y'
            ])
            ->orderBy('mst_customers.name', 'ASC')
            ->get();
        $data = [
            'customer_pic' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
