<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_courier;

class DispCourierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_courier::leftJoin('mst_provinces', 'mst_couriers.province_id', '=', 'mst_provinces.id')
            ->leftJoin('mst_cities', 'mst_couriers.city_id', '=', 'mst_cities.id')
            ->leftJoin('mst_districts', 'mst_couriers.district_id', '=', 'mst_districts.id')
            ->leftJoin('mst_sub_districts', 'mst_couriers.sub_district_id', '=', 'mst_sub_districts.id')
            ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
            ->select(
                'mst_couriers.name',
                'mst_couriers.slug',
                'mst_couriers.office_address',
                'mst_couriers.npwp_no',
                'mst_provinces.province_name',
                'mst_cities.city_name',
                'mst_districts.district_name',
                'mst_sub_districts.sub_district_name',
                'mst_sub_districts.post_code',
                'mst_countries.country_name',
            )
            ->where('mst_couriers.slug', '=', $request->slug,)
            ->where('mst_couriers.active', '=', 'Y')
            ->get();
        $data = [
            'couriers' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
