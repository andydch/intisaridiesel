<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_nota_retur_non_tax;

class DispNPController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_order = Tx_delivery_order_non_tax::where('tx_delivery_order_non_taxes.id','=',$request->np_id)
        ->where('active','=','Y')
        ->first();

        $nota_retur = Tx_nota_retur_non_tax::select(
            'total_price'
        )
        ->whereRaw('approved_by IS NOT null')
        ->where([
            'delivery_order_id'=>$request->np_id,
            'active'=>'Y',
        ])
        ->first();

        $data = [
            'delivery_order' => ($delivery_order!=null?$delivery_order->toArray():[]),
            'nota_retur' => ($nota_retur!=null?$nota_retur->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
