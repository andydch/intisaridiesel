<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_customer;

class DispCustomerByIdController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_customer::leftJoin('mst_provinces', 'mst_customers.province_id', '=', 'mst_provinces.id')
            ->leftJoin('mst_cities', 'mst_customers.city_id', '=', 'mst_cities.id')
            ->leftJoin('mst_districts', 'mst_customers.district_id', '=', 'mst_districts.id')
            ->leftJoin('mst_sub_districts', 'mst_customers.sub_district_id', '=', 'mst_sub_districts.id')
            ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
            ->select(
                'mst_customers.name AS custName',
                'mst_customers.slug',
                'mst_customers.office_address',
                'mst_customers.npwp_no',
                'mst_customers.pic1_name',
                'mst_customers.pic2_name',
                'mst_provinces.province_name',
                'mst_cities.city_name',
                'mst_cities.city_type',
                'mst_districts.district_name',
                'mst_sub_districts.sub_district_name',
                'mst_sub_districts.post_code',
                'mst_countries.country_name',
            )
            ->where('mst_customers.id', '=', $request->id,)
            ->where('mst_customers.active', '=', 'Y')
            ->get();
        $data = [
            'customers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
