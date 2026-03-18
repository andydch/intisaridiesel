<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_district;

class DispDistrictController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->city_id != '') {
            $query = Mst_district::where([
                'city_id' => $request->city_id,
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
        } else {
            $query = Mst_district::where([
                'active' => 'Y'
            ])
            ->orderBy('district_name', 'ASC')
            ->get();
        }
        $data = [
            'district' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
