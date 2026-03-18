<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_purchase_order;
use App\Models\Userdetail;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Database\Query\Builder;

class DispPoPmController extends Controller
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

        $memo = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
        ->select(
            'tx_purchase_memos.memo_no AS order_no',
            'tx_purchase_memos.is_vat',
        )
        ->addSelect(['memo_po_qty' => Tx_purchase_memo_part::selectRaw('SUM(tx_purchase_memo_parts.qty)')
            ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
            ->where('tx_purchase_memo_parts.active','=','Y')
        ])
        ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_memos.memo_no')
            ->where('tx_receipt_order_parts.active','=','Y')
        ])
        ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%')
        ->where([
            'tx_purchase_memos.supplier_id' => $request->supplier_id,
            'tx_purchase_memos.branch_id' => $request->branch_id,
            'tx_purchase_memos.active' => 'Y'
        ]);

        // ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
        //     $q->where('usr.branch_id','=',$userLogin->branch_id);
        // });

        $order = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
        ->select(
            'tx_purchase_orders.purchase_no AS order_no',
            'tx_purchase_orders.is_vat',
        )
        ->addSelect(['memo_po_qty' => Tx_purchase_order_part::selectRaw('SUM(tx_purchase_order_parts.qty)')
            ->whereColumn('tx_purchase_order_parts.order_id','tx_purchase_orders.id')
            ->where('tx_purchase_order_parts.active','=','Y')
        ])
        ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
            ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_orders.purchase_no')
            ->where('tx_receipt_order_parts.active','=','Y')
        ])
        ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
        ->where('tx_purchase_orders.approved_by','<>',null)
        ->where([
            'tx_purchase_orders.supplier_id' => $request->supplier_id,
            'tx_purchase_orders.branch_id' => $request->branch_id,
            'tx_purchase_orders.active' => 'Y'
        ])
        // ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
        //     $q->where('usr.branch_id','=',$userLogin->branch_id);
        // })
        ->union($memo)
        ->get();

        $data = [
            'po_pm' => $order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
