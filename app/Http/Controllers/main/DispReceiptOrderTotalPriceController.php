<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_purchase_retur;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
// use App\Models\Tx_receipt_order_part;

class DispReceiptOrderTotalPriceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $totLastPaymentInv = 0;
        $totLastPaymentInv = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers AS tx','tx_payment_voucher_invoices.payment_voucher_id','=','tx.id')
        ->when($request->pv_id!=null, function($q) use($request) {
            $q->whereRaw('tx_payment_voucher_invoices.payment_voucher_id<>'.$request->pv_id);
        })
        ->where('tx_payment_voucher_invoices.receipt_order_id','=',$request->roid)
        ->where('tx_payment_voucher_invoices.active','=','Y')
        // ->where('tx.payment_voucher_plan_no','NOT LIKE','%Draft%')
        ->where('tx.active','=','Y')
        ->sum('tx_payment_voucher_invoices.total_payment');

        // is_full_payment: jika Y maka jgn ditampilkan
        $query = Tx_receipt_order::select(
            'id',
            'supplier_type_id',
            'receipt_no',
            'exchange_rate',
            'exc_rate_for_vat',
            'vat_val',
        )
        ->selectRaw('DATE_FORMAT(receipt_date,\'%d/%m/%Y\') AS receipt_date_01')
        // total yg harus dibayar setelah dikurangi pembayaran sebelumnya jika ada dg RO yg sama
        // ->selectRaw('total_before_vat-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
        ->selectRaw('CASE 
            WHEN supplier_type_id=10 THEN total_before_vat_rp-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' 
            WHEN supplier_type_id=11 THEN total_before_vat-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' 
            ELSE 0 
            END AS total_price')
        ->selectRaw('CASE 
            WHEN supplier_type_id=10 THEN total_before_vat_rp 
            WHEN supplier_type_id=11 THEN total_before_vat 
            ELSE 0 
            END AS total_price_before_vat')
        ->selectRaw('CASE 
            WHEN supplier_type_id=10 THEN total_vat_rp 
            WHEN supplier_type_id=11 THEN total_vat 
            ELSE 0 
            END AS total_vat')
        ->where('id','=',$request->roid)
        ->whereNotIn('id', function ($q01) use($request) {
            $q01->select('receipt_order_id')
            ->from('tx_payment_voucher_invoices')
            ->when($request->pv_id, function($q) use($request) {
                $q->whereRaw('payment_voucher_id<>'.$request->pv_id);
            })
            ->where('is_full_payment','=','Y')
            ->where('active','=','Y');
        })
        ->get();

        // purchase retur
        $qPurchaseRetur = [];
        foreach($query as $qRo){
            // mengambil semua nilai retur pembelian yg sudah di approve
            $qPurchaseRetur = Tx_purchase_retur::where('receipt_order_id','=',$qRo->id)
            ->whereRaw('approved_by IS NOT NULL')
            ->get();
        }

        $data = [
            'receipt_orders' => $query->toArray(),
            'purchase_returs' => collect($qPurchaseRetur)->toArray(),
            // 'purchase_returs' => $qPurchaseRetur->toArray(),
            'totLastPaymentInv'=>$totLastPaymentInv,
        ];
        return response()->json([
            $data
        ], 200);
    }
}
