<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_part;

class DispDOpartController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_order = Tx_delivery_order_part::leftJoin('mst_parts AS mp','tx_delivery_order_parts.part_id','=','mp.id')
        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
        ->select(
            'tx_delivery_order_parts.*',
            'tx_delivery_order_parts.final_price AS final_price_part',
            'tx_delivery_order_parts.total_price AS total_price_part',
            'mp.*',
            'mg01.title_ind'
        )
        ->where('tx_delivery_order_parts.delivery_order_id','=',$request->delivery_order_id)
        ->where('tx_delivery_order_parts.active','=','Y')
        ->get();

        $data = [
            'delivery_order' => $delivery_order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
