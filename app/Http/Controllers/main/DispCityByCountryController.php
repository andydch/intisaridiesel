<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_city;

class DispCityByCountryController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->country_id != '') {
            $query = Mst_city::where([
                'province_id' => 9999,
                'country_id' => $request->country_id,
                'active' => 'Y'
            ])
                ->orderBy('city_name', 'ASC')
                ->get();
        } else {
            $query = Mst_city::where([
                'active' => 'Y'
            ])
                ->orderBy('city_name', 'ASC')
                ->get();
        }
        $data = [
            'city' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
