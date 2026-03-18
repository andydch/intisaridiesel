<?php

namespace App\Rules;

use App\Models\Tx_invoice;
use App\Models\Tx_kwitansi;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_payment_receipt_invoice;
use Illuminate\Contracts\Validation\InvokableRule;

class CheckRemainingPaymentReceipt20250825 implements InvokableRule
{
    protected $invoice_id;
    protected $payment_receipt_inv_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($invoice_id,$payment_receipt_inv_id)
    {
        $this->invoice_id = $invoice_id;
        $this->payment_receipt_inv_id = $payment_receipt_inv_id;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        // cari jumlah total pembayaran terakhir
        $totLastPaymentInv = 0;
        $qTotLastPaymentInv = [];
        if($this->payment_receipt_inv_id!=0){
            // edit
            if (strpos("invoice-".$this->invoice_id,env('P_INVOICE'))>0){
                $qTotLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_invoices AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
                // ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
                ->selectRaw('SUM(tx_payment_receipt_invoices.total_payment) AS totLastPaymentInv')
                ->where('tx_payment_receipt_invoices.id','<>',$this->payment_receipt_inv_id)
                ->where('tx_payment_receipt_invoices.is_vat','=','Y')
                ->where('tx_payment_receipt_invoices.active','=','Y')
                ->where('inv.invoice_no','=',$this->invoice_id)
                // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
                ->first();
            }

            if (strpos("invoice-".$this->invoice_id,env('P_KWITANSI'))>0){
                $qTotLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_kwitansis AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
                // ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
                ->selectRaw('SUM(tx_payment_receipt_invoices.total_payment) AS totLastPaymentInv')
                ->where('tx_payment_receipt_invoices.id','<>',$this->payment_receipt_inv_id)
                ->where('tx_payment_receipt_invoices.is_vat','=','N')
                ->where('tx_payment_receipt_invoices.active','=','Y')
                ->where('inv.kwitansi_no','=',$this->invoice_id)
                // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
                ->first();
            }

        }else{
            // new
            // payment receipt belum terbentuk

            if (strpos("invoice-".$this->invoice_id,env('P_INVOICE'))>0){
                $qTotLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_invoices AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
                // ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
                ->selectRaw('SUM(tx_payment_receipt_invoices.total_payment) AS totLastPaymentInv')
                ->where('tx_payment_receipt_invoices.is_vat','=','Y')
                ->where('tx_payment_receipt_invoices.active','=','Y')
                ->where('inv.invoice_no','=',$this->invoice_id)
                // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
                ->first();
            }

            if (strpos("invoice-".$this->invoice_id,env('P_KWITANSI'))>0){
                $qTotLastPaymentInv = Tx_payment_receipt_invoice::leftJoin('tx_kwitansis AS inv','tx_payment_receipt_invoices.invoice_id','=','inv.id')
                // ->leftJoin('tx_payment_receipts AS tx','tx_payment_receipt_invoices.payment_receipt_id','=','tx.id')
                ->selectRaw('SUM(tx_payment_receipt_invoices.total_payment) AS totLastPaymentInv')
                ->where('tx_payment_receipt_invoices.is_vat','=','N')
                ->where('tx_payment_receipt_invoices.active','=','Y')
                ->where('inv.kwitansi_no','=',$this->invoice_id)
                // ->where('tx.payment_receipt_no','NOT LIKE','%Draft%')
                ->first();
            }

        }
        if($qTotLastPaymentInv){
            $totLastPaymentInv = $qTotLastPaymentInv->totLastPaymentInv;
        }

        $remainingTotalPrice = 0;
        $payment_receipt_inv_id = $this->payment_receipt_inv_id;
        $invoice_id = $this->invoice_id;
        $qDO = [];
        if($payment_receipt_inv_id!=0){
            // edit
            if (strpos("invoice-".$this->invoice_id,env('P_INVOICE'))>0){
                $qDO = Tx_invoice::selectRaw('do_grandtotal_vat-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
                ->where('invoice_no','=',$invoice_id)
                ->whereNotIn('id', function ($q01) use($payment_receipt_inv_id) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->whereColumn('tx_payment_receipt_invoices.invoice_id','<>','tx_invoices.id')
                    ->where('id','<>',$payment_receipt_inv_id)
                    ->where('is_full_payment','=','Y')
                    ->where('is_vat','=','Y');
                })
                ->first();
            }


            if (strpos("invoice-".$this->invoice_id,env('P_KWITANSI'))>0){
                $qDO = Tx_kwitansi::selectRaw('np_total-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
                ->where('kwitansi_no','=',$invoice_id)
                ->whereNotIn('id', function ($q01) use($payment_receipt_inv_id) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->whereColumn('tx_payment_receipt_invoices.invoice_id','<>','tx_kwitansis.id')
                    ->where('id','<>',$payment_receipt_inv_id)
                    ->where('is_full_payment','=','Y')
                    ->where('is_vat','=','N');
                })
                ->first();
            }
        }else{
            // new
            if (strpos("invoice-".$this->invoice_id,env('P_INVOICE'))>0){
                $qDO = Tx_invoice::selectRaw('do_grandtotal_vat-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
                ->where('invoice_no','=',$invoice_id)
                ->whereNotIn('id', function ($q01) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->whereColumn('tx_payment_receipt_invoices.invoice_id','<>','tx_invoices.id')
                    ->where('is_full_payment','=','Y');
                })
                ->first();
            }

            if (strpos("invoice-".$this->invoice_id,env('P_KWITANSI'))>0){
                $qDO = Tx_kwitansi::selectRaw('np_total-'.(!is_null($totLastPaymentInv)?$totLastPaymentInv:0).' AS total_price')
                ->where('kwitansi_no','=',$invoice_id)
                ->whereNotIn('id', function ($q01) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->whereColumn('tx_payment_receipt_invoices.invoice_id','<>','tx_kwitansis.id')
                    ->where('is_full_payment','=','Y');
                })
                ->first();
            }
        }
        if($qDO){
            $remainingTotalPrice = (!is_null($qDO)?$qDO->total_price:0);
        }

        if(floor(GlobalFuncHelper::moneyValidate($value))>floor($remainingTotalPrice)){
            $fail('The payment value entered exceeds the remaining payment value (Rp '.number_format($remainingTotalPrice,2,",",".").').');
        }
    }
}
