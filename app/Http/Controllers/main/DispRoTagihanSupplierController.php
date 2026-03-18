<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;

class DispRoTagihanSupplierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $tx_ts_ignore_id = $request->tx_ts_ignore_id?$request->tx_ts_ignore_id:0;
        $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
        ->whereNotIn('id', function($q1) use($tx_ts_ignore_id){
            $q1->select('tx_tsd.receipt_order_id')
            ->from('tx_tagihan_supplier_details as tx_tsd')
            ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
            ->when($tx_ts_ignore_id!=0, function($q) use($tx_ts_ignore_id){
                $q->where('tx_ts.id', '!=', $tx_ts_ignore_id);
            })
            ->where([
                'tx_tsd.active' => 'Y',
                'tx_ts.active' => 'Y',
            ]);
        })
        ->whereNotIn('id', function($q1){
            $q1->select('tx_pvi.receipt_order_id')
            ->from('tx_payment_voucher_invoices AS tx_pvi')
            ->leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pvi.payment_voucher_id', '=', 'tx_pv.id')
            ->where([
                'tx_pvi.is_full_payment' => 'Y',
                'tx_pvi.active' => 'Y',
                'tx_pv.active' => 'Y',
            ]);
        })
        ->where([
            'supplier_id' => $request->supplier_id,
            'active' => 'Y',
        ])
        ->orderBy('receipt_no', 'desc')
        ->get();

        $data = [
            'receipt_orders' => $qRO->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
