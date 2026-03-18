<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order;

class DispDOController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_order = Tx_delivery_order::where('customer_id','=',$request->customer_id)
        ->whereNotIn('id', function($q){
            $q->select('fk_id')
            ->from('tx_invoice_details')
            ->where('active','=','Y');
        })
        ->where('delivery_order_no','NOT LIKE','%Draft%')
        ->whereRaw('tax_invoice_id IS NOT NULL')
        ->where('active','=','Y')
        ->orderBy('delivery_order_date','DESC')
        ->orderBy('created_at','DESC')
        ->get();

        $data = [
            'delivery_order' => $delivery_order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
