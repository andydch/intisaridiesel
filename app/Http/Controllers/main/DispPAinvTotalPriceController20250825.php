<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Tx_payment_receipt_invoice;
use Illuminate\Http\Request;
use App\Models\Tx_invoice;
use App\Models\Tx_invoice_detail;
use App\Models\Tx_kwitansi;
use App\Models\Tx_kwitansi_detail;

class DispPAinvTotalPriceController20250825 extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $invoice_no = urldecode($request->invId);
        $payment_type_id = $request->payment_type_id;
        $query = [];
        $qInv = [];

        if (strpos("invoice-".$invoice_no,env('P_INVOICE'))>0 && $payment_type_id=='P'){
            $totLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_invoices AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
            ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
            ->where([
                'tx_payment_receipt_invoices.is_vat'=>'Y',
                'tx_payment_receipt_invoices.active'=>'Y',
                'tx_payment_receipt_invoices.invoice_no'=>$invoice_no,
                'tx.active'=>'Y',
            ])
            // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
            ->sum('tx_payment_receipt_invoices.total_payment');

            // is_full_payment: jika Y maka jgn ditampilkan
            $query = Tx_invoice::selectRaw('do_total-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
            ->where('invoice_no','=',$invoice_no)
            ->whereNotIn('invoice_no', function ($q01) {
                $q01->select('invoice_no')
                ->from('tx_payment_receipt_invoices')
                ->where([
                    'is_full_payment'=>'Y',
                    'is_vat'=>'Y',
                    'active'=>'Y',
                ]);
            })
            ->first();

            $qInv = Tx_invoice_detail::leftjoin('tx_delivery_orders as fk','tx_invoice_details.fk_id','=','fk.id')
            ->leftJoin('tx_nota_returs as nr','fk.id','=','nr.delivery_order_id')
            ->leftJoin('tx_invoices AS inv','tx_invoice_details.invoice_id','=','inv.id')
            ->select(
                'fk.delivery_order_no',
                'nr.nota_retur_no',
                'nr.total_before_vat',
            )
            // ->whereRaw('nr.approved_by IS NOT NULL')
            ->where([
                'inv.invoice_no'=>$invoice_no,
                'fk.active'=>'Y',
                'tx_invoice_details.active'=>'Y',
            ])
            ->get();
        }

        if (strpos("invoice-".$invoice_no,env('P_KWITANSI'))>0 && $payment_type_id=='N'){
            $totLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_kwitansis AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
            ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
            ->where([
                'tx_payment_receipt_invoices.is_vat'=>'N',
                'tx_payment_receipt_invoices.active'=>'Y',
                'tx_payment_receipt_invoices.invoice_no'=>$invoice_no,
                'tx.active'=>'Y',
            ])
            // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
            ->sum('tx_payment_receipt_invoices.total_payment');

            // is_full_payment: jika Y maka jgn ditampilkan
            $query = Tx_kwitansi::selectRaw('np_total-'.$totLastPaymentInv.' AS total_price')
            ->where('kwitansi_no','=',$invoice_no)
            ->whereNotIn('kwitansi_no', function ($q01) {
                $q01->select('invoice_no')
                ->from('tx_payment_receipt_invoices')
                ->where([
                    'is_full_payment'=>'Y',
                    'is_vat'=>'N',
                    'active'=>'Y',
                ]);
            })
            ->first();

            $qInv = Tx_kwitansi_detail::leftjoin('tx_delivery_order_non_taxes as np','tx_kwitansi_details.np_id','=','np.id')
            ->leftJoin('tx_nota_retur_non_taxes as nr','np.id','=','nr.delivery_order_id')
            ->leftJoin('tx_kwitansis AS inv','tx_kwitansi_details.kwitansi_id','=','inv.id')
            ->select(
                'np.delivery_order_no',
                'nr.nota_retur_no',
                'nr.total_price as total_before_vat',
            )
            // ->whereRaw('nr.approved_by IS NOT NULL')
            ->where([
                'inv.kwitansi_no'=>$invoice_no,
                'np.active'=>'Y',
                'tx_kwitansi_details.active'=>'Y',
            ])
            ->get();
        }

        $data = [
            // 'x' => $totLastPaymentInv,
            'inv' => ($query?$query->toArray():[]),
            'fk_info' => ($qInv?$qInv->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
