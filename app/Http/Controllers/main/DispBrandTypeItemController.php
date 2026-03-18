<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_brand_type;

class DispBrandTypeItemController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $data = [];
        if ($request->brand_id != '') {
            $query = Mst_brand_type::where([
                'brand_id' => $request->brand_id,
                'active' => 'Y'
            ])
            ->orderBy('brand_type', 'ASC')
            ->get();
        }
        $data = [
            'brand_type' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
