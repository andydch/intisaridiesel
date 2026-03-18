<?php

namespace App\Http\Controllers\main;

use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispOOstockmasterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // purchase memo
        $memo = Tx_purchase_memo_part::leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
        ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
        ->leftJoin('mst_suppliers as spl','tx_memo.supplier_id','=','spl.id')
        ->select(
            'tx_memo.id',
            'tx_memo.memo_no',
            'tx_memo.memo_date',
            'spl.name AS supplier_name',
            'tx_purchase_memo_parts.id',
            'tx_purchase_memo_parts.part_id',
        )
        ->addSelect(['memo_qty' => Tx_purchase_memo_part::selectRaw('SUM(qty)')
            ->whereColumn('memo_id','tx_memo.id')
            ->where('part_id','=',$request->part_id)
            ->where('active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_memo.memo_no')
            ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
            ->where('tx_receipt_order_parts.is_partial_received','=','Y')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty_no_partial' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_memo.memo_no')
            ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
            ->where('tx_receipt_order_parts.is_partial_received','=','N')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->where('tx_purchase_memo_parts.part_id','=',$request->part_id)
        // ->where('usr.branch_id','=',$request->branch_id)
        ->whereRaw('((usr.branch_id='.$request->branch_id.' AND tx_memo.branch_id IS null) OR tx_memo.branch_id='.$request->branch_id.')')
        ->where('tx_purchase_memo_parts.active','=','Y')
        ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
        ->where('tx_memo.active','=','Y')
        ->orderBy('tx_memo.memo_no','ASC')
        ->groupBy('tx_memo.id')
        ->groupBy('tx_memo.memo_no')
        ->groupBy('tx_memo.memo_date')
        ->groupBy('spl.name')
        ->groupBy('tx_purchase_memo_parts.id')
        ->groupBy('tx_purchase_memo_parts.part_id')
        ->get();

        // purchase order
        $po = Tx_purchase_order_part::leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
        ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
        ->leftJoin('mst_suppliers as spl','tx_order.supplier_id','=','spl.id')
        ->select(
            'tx_order.id',
            'tx_order.purchase_no',
            'tx_order.purchase_date',
            'spl.name AS supplier_name',
            'tx_purchase_order_parts.id',
            'tx_purchase_order_parts.part_id',
        )
        ->addSelect(['pr_order_qty' => Tx_purchase_order_part::selectRaw('SUM(qty)')
            ->whereColumn('order_id','tx_order.id')
            ->where('part_id','=',$request->part_id)
            ->where('active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_order.purchase_no')
            ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
            ->where('tx_receipt_order_parts.is_partial_received','=','Y')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty_no_partial' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_order.purchase_no')
            ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
            ->where('tx_receipt_order_parts.is_partial_received','=','N')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->where('tx_purchase_order_parts.part_id','=',$request->part_id)
        // ->where('usr.branch_id','=',$request->branch_id)
        ->whereRaw('((usr.branch_id='.$request->branch_id.' AND tx_order.branch_id IS null) OR tx_order.branch_id='.$request->branch_id.')')
        ->where('tx_purchase_order_parts.active','=','Y')
        ->where('tx_order.approved_by','<>',null)
        ->where('tx_order.active','=','Y')
        ->orderBy('tx_order.purchase_no','ASC')
        ->groupBy('tx_order.id')
        ->groupBy('tx_order.purchase_no')
        ->groupBy('tx_order.purchase_date')
        ->groupBy('spl.name')
        ->groupBy('tx_purchase_order_parts.id')
        ->groupBy('tx_purchase_order_parts.part_id')
        ->get();


        // $ro = Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
        // ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
        // ->select('tx_ro.receipt_no')
        // ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
        // ->where('usr.branch_id','=',$request->branch_id)
        // ->where('tx_receipt_order_parts.is_partial_received','=','Y')
        // ->where('tx_receipt_order_parts.active','=','Y')
        // ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
        // ->where('tx_ro.active','=','Y')
        // ->groupBy('tx_ro.receipt_no')
        // ->get();
        // $ro_nopartial = Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
        // ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
        // ->select('tx_ro.receipt_no')
        // ->where('tx_receipt_order_parts.part_id','=',$request->part_id)
        // ->where('usr.branch_id','=',$request->branch_id)
        // ->where('tx_receipt_order_parts.is_partial_received','=','N')
        // ->where('tx_receipt_order_parts.active','=','Y')
        // ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
        // ->where('tx_ro.active','=','Y')
        // ->groupBy('tx_ro.receipt_no')
        // ->get();

        $data = [
            'memo' => $memo->toArray(),
            'po' => $po->toArray(),
            // 'ro' => $ro->toArray(),
            // 'ro_nopartial' => $ro_nopartial->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
