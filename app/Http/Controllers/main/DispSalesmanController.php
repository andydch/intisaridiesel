<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_salesman;

class DispSalesmanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_salesman::leftJoin('mst_provinces', 'mst_salesmans.province_id', '=', 'mst_provinces.id')
            ->leftJoin('mst_cities', 'mst_salesmans.city_id', '=', 'mst_cities.id')
            ->leftJoin('mst_districts', 'mst_salesmans.district_id', '=', 'mst_districts.id')
            ->leftJoin('mst_sub_districts', 'mst_salesmans.sub_district_id', '=', 'mst_sub_districts.id')
            ->leftJoin('mst_countries', 'mst_provinces.country_id', '=', 'mst_countries.id')
            ->select(
                'mst_salesmans.name',
                'mst_salesmans.slug',
                'mst_salesmans.address',
                // 'mst_salesmans.npwp_no',
                'mst_provinces.province_name',
                'mst_cities.city_name',
                'mst_districts.district_name',
                'mst_sub_districts.sub_district_name',
                'mst_sub_districts.post_code',
                'mst_countries.country_name',
            )
            ->where('mst_salesmans.slug', '=', $request->slug,)
            ->where('mst_salesmans.active', '=', 'Y')
            ->get();
        $data = [
            'salesmans' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
