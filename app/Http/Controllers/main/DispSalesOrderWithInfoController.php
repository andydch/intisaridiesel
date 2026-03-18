<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_sales_order_part;

class DispSalesOrderWithInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $parts = Tx_sales_order_part::leftJoin('mst_parts AS mp','tx_sales_order_parts.part_id','=','mp.id')
        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
        ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
        ->select(
            'tx_sales_order_parts.id AS sales_order_part_id',
            'tx_sales_order_parts.order_id AS sales_order_id',
            'tx_sales_order_parts.part_id',
            'tx_sales_order_parts.part_no',
            'tx_sales_order_parts.qty',
            'tx_sales_order_parts.price',
            'mp.part_name',
            'mg01.string_val AS part_unit',
            'mp.weight',
            'mg02.string_val AS weight_unit',
        )
        ->where([
            'tx_sales_order_parts.order_id' => $request->order_id,
            'tx_sales_order_parts.active' => 'Y'
        ])
        ->get();

        $data = [
            'parts' => $parts->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
