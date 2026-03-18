<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Models\Tx_purchase_retur;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DispRoTagihanSupplierDtlController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qRO = Tx_receipt_order::select(
            'tx_receipt_orders.id as ro_id',
            'tx_receipt_orders.receipt_no',
            'tx_receipt_orders.invoice_no',
            'tx_receipt_orders.journal_type_id',
            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
            DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
            'tx_receipt_orders.vat_val',
            'tx_receipt_orders.active as ro_active',
            // DB::raw('IF(tx_pr.total_before_vat IS NULL, 0, tx_pr.total_before_vat) as pr_total_before_vat'),
        )
        ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
        ->where([
            'tx_receipt_orders.id' => $request->r,
            'tx_receipt_orders.active' => 'Y',
        ])
        ->orderBy('tx_receipt_orders.receipt_no', 'desc')
        ->get();

        $qPR = Tx_purchase_retur::where('receipt_order_id', $request->r)
        ->whereRaw('approved_by IS NOT NULL')
        ->where('is_draft', 'N')
        ->where('active', 'Y')
        ->get();

        $data = [
            'receipt_orders' => $qRO->toArray(),
            'purchase_returs' => $qPR->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
