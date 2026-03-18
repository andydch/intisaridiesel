<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order_part;

class DispPoPmWithInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $querySupplier = Mst_supplier::where('id','=',$request->supplier_id)
        ->first();

        $queryPart = [];
        $po_mo_no = 'part-'.$request->po_or_mo;
        if(strpos($po_mo_no,'MO')>0 || strpos($po_mo_no,'MP')>0){
            $queryPart = Tx_purchase_memo_part::leftJoin('tx_purchase_memos','tx_purchase_memo_parts.memo_id','=','tx_purchase_memos.id')
            ->leftJoin('mst_parts','tx_purchase_memo_parts.part_id','=','mst_parts.id')
            ->select(
                'tx_purchase_memo_parts.id AS pomo_part_id',
                'tx_purchase_memo_parts.part_id',
                'tx_purchase_memo_parts.qty',
                'tx_purchase_memo_parts.price',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.price_list',
                'tx_purchase_memos.memo_no AS po_mo_no',
                'tx_purchase_memos.is_vat',
            )
            ->addSelect([
                'currency_name' => Mst_global::select('string_val')
                ->where([
                    'id' => 3,
                    'data_cat' => 'currency'
                ])
            ])
            ->addSelect(['last_qty_total' => Tx_receipt_order_part::selectRaw('SUM(qty)')
                ->where('tx_receipt_order_parts.po_mo_no','=',$request->po_or_mo)
                ->whereColumn('tx_receipt_order_parts.po_mo_id','tx_purchase_memo_parts.id')
                ->whereColumn('tx_receipt_order_parts.part_id','tx_purchase_memo_parts.part_id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where([
                'tx_purchase_memos.memo_no' => $request->po_or_mo,
                'tx_purchase_memo_parts.active' => 'Y'
            ])
            ->get();
        }
        if(strpos($po_mo_no,'PO')>0){
            $queryPart = Tx_purchase_order_part::leftJoin('tx_purchase_orders','tx_purchase_order_parts.order_id','=','tx_purchase_orders.id')
            ->leftJoin('mst_parts','tx_purchase_order_parts.part_id','=','mst_parts.id')
            ->leftJoin('mst_globals','tx_purchase_orders.currency_id','=','mst_globals.id')
            ->select(
                'tx_purchase_order_parts.id AS pomo_part_id',
                'tx_purchase_order_parts.part_id',
                'tx_purchase_order_parts.qty',
                'tx_purchase_order_parts.price',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.price_list',
                'mst_globals.string_val AS currency_name',
                'tx_purchase_orders.purchase_no AS po_mo_no',
                'tx_purchase_orders.is_vat',
            )
            ->addSelect(['last_qty_total' => Tx_receipt_order_part::selectRaw('SUM(qty)')
                ->where('tx_receipt_order_parts.po_mo_no','=',$request->po_or_mo)
                ->whereColumn('tx_receipt_order_parts.po_mo_id','tx_purchase_order_parts.id')
                ->whereColumn('tx_receipt_order_parts.part_id','tx_purchase_order_parts.part_id')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->limit(1)
            ])
            ->where([
                'tx_purchase_orders.purchase_no' => $request->po_or_mo,
                'tx_purchase_order_parts.active' => 'Y'
            ])
            ->get();
        }

        $data = [
            'po_pm' => $queryPart->toArray(),
            'supplier' => $querySupplier
        ];
        return response()->json([
            $data
        ], 200);
    }
}
