<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;

class DispInvoicesPerSupplierController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function ($q01) use ($request) {
            $q01->select('receipt_order_id')
            ->from('tx_payment_voucher_invoices')
            ->when($request->pv_id, function($q) use($request) {
                $q->whereRaw('payment_voucher_id<>'.$request->pv_id);
            })
            ->where([
                'is_full_payment'=>'Y',
                'active'=>'Y',
            ]);
        })
        ->when($request->is_ts=='N', function($q) use($request) {
            $q->whereNotIn('id', function ($q1) use ($request) {
                $q1->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                ->where([
                    'tx_tsd.active'=>'Y',
                    'tx_ts.active'=>'Y',
                ]);
            });
        })
        ->when($request->is_ts=='Y', function($q) use($request) {
            $q->whereIn('id', function ($q1) use ($request) {
                $q1->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                ->where([
                    'tx_tsd.active'=>'Y',
                    'tx_ts.id'=>$request->tagihan_supplier_id,
                    'tx_ts.active'=>'Y',
                ]);
            });
        })
        ->where([
            'supplier_id'=>$request->supplier_id,
            'active'=>'Y',
        ])
        ->when($request->journal_type_id, function($q) use($request) {
            $q->where([
                'journal_type_id' => $request->journal_type_id,
            ]);
        })
        // ->when($request->payment_type_id && $request->journal_type_id, function($q) use($request) {
        //     if ($request->payment_type_id=='P' && $request->journal_type_id=='P'){
        //         $q->whereRaw('vat_val>0');
        //     }
        //     if ($request->payment_type_id=='N' && $request->journal_type_id=='N'){
        //         $q->whereRaw('vat_val=0');
        //     }
        //     if ($request->payment_type_id=='N' && $request->journal_type_id=='P'){
        //         $q->where([
        //             'journal_type_id'=>'P',
        //             'vat_val'=>0,
        //         ]);
        //     }
        // })
        ->orderBy('invoice_no','ASC')
        ->get();
        $data = [
            'receipt_orders' => $receiptOrders->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
