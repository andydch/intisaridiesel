<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_sub_district;

class DispSubDistrictController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if ($request->district_id != '') {
            $query = Mst_sub_district::where([
                'district_id' => $request->district_id,
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
        } else {
            $query = Mst_sub_district::where([
                'active' => 'Y'
            ])
            ->orderBy('sub_district_name', 'ASC')
            ->get();
        }
        $data = [
            'sub_district' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
