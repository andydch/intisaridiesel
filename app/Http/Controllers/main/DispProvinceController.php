<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_province;

class DispProvinceController extends Controller
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
            $query = Mst_province::where([
                'country_id' => $request->country_id,
                'active' => 'Y'
            ])
                ->orderBy('province_name', 'ASC')
                ->get();
        } else {
            $query = Mst_province::where([
                'active' => 'Y'
            ])
                ->orderBy('province_name', 'ASC')
                ->get();
        }
        $data = [
            'province' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
