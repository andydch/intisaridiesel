<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use Illuminate\Http\Request;

class DispCoaParentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $coa_level = $request->coa_level;
        $coa_id_not_in = ($request->coa_id_not_in?(is_numeric($request->coa_id_not_in)?$request->coa_id_not_in:0):0);
        $query = Mst_coa::when($coa_id_not_in>0, function($q) use($coa_id_not_in){
            $q->where('id','<>',$coa_id_not_in);
        })
        ->where([
            'coa_level' => ($coa_level>0)?$coa_level-1:0,
            'is_draft' => 'N',
            'active' => 'Y'
        ])
        ->get();
        $data = [
            'coas' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
