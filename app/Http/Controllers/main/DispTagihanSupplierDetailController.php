<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_tagihan_supplier_detail;

class DispTagihanSupplierDetailController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Tx_tagihan_supplier_detail::leftJoin('tx_receipt_orders as tx_ro', 'tx_tagihan_supplier_details.receipt_order_id', '=', 'tx_ro.id')
        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tagihan_supplier_details.tagihan_supplier_id', '=', 'tx_ts.id')
        ->select(
            'tx_tagihan_supplier_details.total_price_per_ro',
            'tx_ro.id as receipt_order_id',
            'tx_ro.receipt_no',
            'tx_ro.invoice_no',
            'tx_ts.bank_id',
        )
        ->whereNotIn('tx_ro.id', function($query) use ($request) {
            $query->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('is_full_payment', 'Y');
        })
        ->where([
            'tx_tagihan_supplier_details.tagihan_supplier_id' => $request->tagihan_supplier_id,
            'tx_tagihan_supplier_details.active' => 'Y',
        ])
        ->orderBy('tx_tagihan_supplier_details.id', 'DESC')
        ->get();
        $data = [
            'tagihan_supplier_details' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
