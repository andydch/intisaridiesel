<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_brand_type;
use Illuminate\Http\Request;

class DispBrandTypeByBrandController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $brandTypes = Mst_brand_type::where('brand_id','=',$request->brand_id)
        ->where('active','=','Y')
        ->orderBy('brand_type','ASC')
        ->get();

        $data = [
            'brand_type' => $brandTypes->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
