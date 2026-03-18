<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_invoice_part;

class DispInvpartNotaReturInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Tx_invoice_part::leftJoin('mst_parts AS mp','tx_invoice_parts.part_id','mp.id')
        ->leftJoin('mst_globals AS mg01','mp.quantity_type_id','=','mg01.id')
        ->leftJoin('mst_globals AS mg02','mp.weight_id','=','mg02.id')
        ->select(
            'mp.id AS mp_id',
            'mp.part_number',
            'mp.part_name',
            'mp.weight',
            'mg01.string_val AS unit_name',
            'mg02.string_val AS weight_name',
            'tx_invoice_parts.id AS inv_part_id',
            'tx_invoice_parts.qty AS inv_qty',
            'tx_invoice_parts.final_price',
            'tx_invoice_parts.total_price',
        )
        ->where('tx_invoice_parts.invoice_id','=',$request->inv_id)
        ->where('tx_invoice_parts.active','=','Y')
        ->get();

        $data = [
            'inv_part_info' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
