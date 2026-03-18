<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Tx_invoice;
use App\Models\Tx_kwitansi;
use Illuminate\Http\Request;

class DispInvoicesPerDOController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $queryAllInvoice = [];
        $pc_id = $request->pc_id;
        $coa_id = $request->coa_id;
        $payment_type_id = $request->payment_type_id;

        if ($payment_type_id=='P'){
            // PPN
            $queryAllInvoice = Tx_invoice::select(
                'id',
                'invoice_no',
            )
            ->selectRaw('\'Y\' as is_vat')
            ->where('invoice_no','NOT LIKE','%Draft%')
            ->whereNotIn('invoice_no', function ($q01) use($pc_id) {
                $q01->select('invoice_no')
                ->from('tx_payment_receipt_invoices')
                ->where('payment_receipt_id', '<>', $pc_id)
                ->where([
                    'is_full_payment'=>'Y',
                    'is_vat'=>'Y',
                    'active'=>'Y',
                ]);
            })
            ->where([
                'customer_id'=>$request->customer_id,
                'payment_to_id'=>$coa_id,
                'active'=>'Y',
            ])
            ->orderBy('invoice_no','ASC')
            ->get();
        }


        if ($payment_type_id=='N'){
            // Non PPN
            $queryAllInvoice = Tx_kwitansi::select(
                'id',
                'kwitansi_no as invoice_no',
            )
            ->selectRaw('\'N\' as is_vat')
            ->where('kwitansi_no','NOT LIKE','%Draft%')
            ->whereNotIn('kwitansi_no', function ($q01) use($pc_id) {
                $q01->select('invoice_no')
                ->from('tx_payment_receipt_invoices')
                ->where('payment_receipt_id', '<>', $pc_id)
                ->where([
                    'is_full_payment'=>'Y',
                    'is_vat'=>'N',
                    'active'=>'Y',
                ]);
            })
            ->where([
                'customer_id'=>$request->customer_id,
                'payment_to_id'=>$coa_id,
                'active'=>'Y',
            ])
            ->orderBy('kwitansi_no','ASC')
            ->get();
        }

        $data = [
            'invoices' => ($queryAllInvoice?$queryAllInvoice->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
