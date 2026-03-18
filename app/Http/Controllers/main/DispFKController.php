<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order;
use App\Models\Tx_nota_retur;

class DispFKController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_order = Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
        ->select('tx_delivery_orders.*','tx_tax_invoices.fp_no')
        ->where('tx_delivery_orders.id','=',$request->fk_id)
        ->first();

        $nota_retur = Tx_nota_retur::select(
            'total_before_vat'
        )
        ->whereRaw('approved_by IS NOT null')
        ->where([
            'delivery_order_id'=>$request->fk_id,
            'active'=>'Y',
        ])
        ->first();

        $data = [
            'delivery_order' => ($delivery_order?$delivery_order->toArray():[]),
            'nota_retur' => ($nota_retur?$nota_retur->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
