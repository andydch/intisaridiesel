<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_order_part;

class DispPartPriceRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_part::leftJoin('tx_qty_parts', 'mst_parts.id', '=', 'tx_qty_parts.part_id')
            ->leftJoin('mst_globals as q_type', 'mst_parts.quantity_type_id', '=', 'q_type.id')
            ->leftJoin('mst_globals as cur', 'mst_parts.fob_currency', '=', 'cur.id')
            ->select(
                'mst_parts.id as part_id',
                'mst_parts.price_list',
                'mst_parts.final_price',
                'mst_parts.final_cost',
                'mst_parts.final_fob',
                'mst_parts.quantity_type_id',
                'tx_qty_parts.qty',
                'q_type.title_ind AS quantity_type',
                'cur.string_val AS fob_curr',
            )
            ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('IFNULL(SUM(qty),0)')    // total qty dari memo yg aktif
                ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
                ->whereColumn('tx_memo.branch_id','tx_qty_parts.branch_id')
                ->where('tx_purchase_memo_parts.active','=','Y')
                ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                ->where('tx_memo.active','=','Y')
            ])
            ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari po yg aktif
                ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
                ->whereColumn('tx_order.branch_id','tx_qty_parts.branch_id')
                ->where('tx_purchase_order_parts.active','=','Y')
                ->where('tx_order.approved_by','<>',null)
                ->where('tx_order.active','=','Y')
            ])
            ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereColumn('tx_ro.branch_id','tx_qty_parts.branch_id')
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
            ])
            ->addSelect(['purchase_ro_qty_no_partial' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO dg is_partial_received=N
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                ->whereColumn('tx_ro.branch_id','tx_qty_parts.branch_id')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
            ])
            ->where([
                'mst_parts.id' => $request->part_id,
                'mst_parts.active' => 'Y',
                'tx_qty_parts.branch_id' => $request->branch_id,
            ])
            ->get();
        $data = [
            'parts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
