<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_part;
use App\Models\Userdetail;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DispMemoPartRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if(isset($request->memo_created_by)){
            $userLogin = Userdetail::where('user_id','=',$request->memo_created_by)
            ->first();
        }

        $query = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
        ->select(
            'mst_parts.id AS part_id',
            'mst_parts.part_number',
            'mst_parts.price_list',
            'mst_parts.final_cost',
            'mst_parts.avg_cost',
            'tx_qty_parts.qty as total_qty',
        )
        ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('IFNULL(SUM(qty),0)')    // total qty dari memo yg aktif
            ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
            ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
            ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
            ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
            ->where('tx_purchase_memo_parts.active','=','Y')
            ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
            ->where('tx_memo.active','=','Y')
        ])
        ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari po yg aktif
            ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
            ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
            ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
            ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
            ->where('tx_purchase_order_parts.active','=','Y')
            ->where('tx_order.approved_by','<>',null)
            ->where('tx_order.active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO yg approved
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
            ->where('tx_receipt_order_parts.is_partial_received','=','Y')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->addSelect(['purchase_ro_qty_no_partial' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(qty),0)')  // total qty dari RO dg is_partial_received=N
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
            ->where('tx_receipt_order_parts.is_partial_received','=','N')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
        ])
        ->where([
            'mst_parts.id' => $request->part_id,
            'mst_parts.active' => 'Y',
            'tx_qty_parts.branch_id' => $userLogin->branch_id
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
