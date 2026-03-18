<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use Illuminate\Http\Request;

class DispBankAccNoForAcceptancePlanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $month_id = strlen($request->month_id)==1?'0'.$request->month_id:$request->month_id;
        $year_id = $request->year_id;

        $coas = Mst_coa::whereIn('id', function($q) use($year_id, $month_id){
            $q->select('bank_id')
            ->from('tx_payment_plans')
            ->whereRaw('payment_month=\''.$year_id.'-'.$month_id.'-01\'')
            ->where([
                'is_draft' => 'N',
                'active' => 'Y',
            ]);
        })
        ->where([
            'active' => 'Y',
        ])
        ->orderBy('coa_name', 'ASC')
        ->get();

        $data = [
            'bankaccno' => ($coas?$coas->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
