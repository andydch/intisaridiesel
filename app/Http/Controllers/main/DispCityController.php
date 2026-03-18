<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_city;

class DispCityController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->province_id != '') {
            $query = Mst_city::where([
                'province_id' => $request->province_id,
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
