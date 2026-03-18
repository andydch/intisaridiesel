<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_purchase_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;

class DispReceiptOrderPartInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $p_no = $request->p_no;
        $q = [];
        switch (substr($p_no, 0, 2)) {
            case 'MP':
                $part = Tx_purchase_memo::where('memo_no', '=', $p_no)->first();
                $q = Tx_purchase_memo_part::leftJoin('mst_parts AS parts', 'tx_purchase_memo_parts.part_id', '=', 'parts.id')
                    ->select(
                        'tx_purchase_memo_parts.part_id AS part_id',
                        'parts.part_name',
                        'parts.part_number',
                        'tx_purchase_memo_parts.qty AS part_qty',
                    )
                    ->addSelect(DB::raw("0 as part_price"))
                    ->where('tx_purchase_memo_parts.memo_id', '=', $part->id)
                    ->where('tx_purchase_memo_parts.active', '=', 'Y')
                    ->get();

                break;
            case 'PO':
                $part = Tx_purchase_order::where('purchase_no', '=', $p_no)->first();
                $q = Tx_purchase_order_part::leftJoin('mst_parts AS parts', 'tx_purchase_order_parts.part_id', '=', 'parts.id')
                    ->select(
                        'tx_purchase_order_parts.part_id AS part_id',
                        'parts.part_name',
                        'parts.part_number',
                        'tx_purchase_order_parts.qty AS part_qty',
                        'tx_purchase_order_parts.price AS part_price',
                    )
                    ->where('tx_purchase_order_parts.order_id', '=', $part->id)
                    ->where('tx_purchase_order_parts.active', '=', 'Y')
                    ->get();

                break;
            default:
                // code to be executed if n is different from all labels;
        }
        $data = [
            'receipt_order_part' => $q->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
