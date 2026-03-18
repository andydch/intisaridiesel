<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_coa;

class DispCoaForGnLedgerRptController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qCoas = Mst_coa::when(strtolower($request->lokal_input)=='p', function($q){
            $q->where('local', '=', 'P');
        })
        ->when(strtolower($request->lokal_input)=='n', function($q){
            $q->where('local', '=', 'N');
        })
        ->when(strtolower($request->lokal_input)=='a', function($q){
            $q->where('local', '=', 'A')
            ->orWhere('local', '=', 'P')
            ->orWhere('local', '=', 'N');
        })
        ->when($request->branch_id!='', function($q) use($request){
            $q->where('branch_id', '=', $request->branch_id);
        })
        ->whereRaw('branch_id IS NOT null')
        ->whereRaw('local IS NOT null')
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('coa_name', 'ASC')
        ->get();
        $data = [
            'qCoas' => $qCoas->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
