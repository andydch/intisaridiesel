<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_coa;

class DispRoTagihanSupplierNoRekBankController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qCoas = Mst_coa::where(function($q){
            $q->where('coa_code_complete','LIKE','111%')
            ->orWhere('coa_code_complete','LIKE','112%')
            ->orWhere('coa_code_complete','LIKE','116%');
        })
        ->where('is_master_coa', '=', 'N')
        ->when($request->lc=='A', function($q) {
            $q->whereIn('local', ['A', 'P', 'N']);
        })
        ->when($request->lc=='P', function($q) {
            $q->whereIn('local', ['A', 'P']);
        })
        ->when($request->lc=='N', function($q) {
            $q->whereIn('local', ['A', 'N']);
        })
        ->when($request->lc!='A' && $request->lc!='P' && $request->lc!='N', function($q) {
            $q->where('local', '=', 'X');
        })
        ->where('active', '=', 'Y')
        ->orderBy('coa_name', 'ASC')
        ->get();

        $data = [
            'acc_nos' => $qCoas->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
