<?php

namespace App\Rules;

use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_payment_voucher_invoice;
use Illuminate\Contracts\Validation\InvokableRule;

class CheckRemainingPaymentVoucher implements InvokableRule
{
    protected $roid;
    protected $pv_inv_id;
    protected $pv_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($roid,$pv_inv_id,$pv_id)
    {
        $this->roid = $roid;
        $this->pv_inv_id = $pv_inv_id;
        $this->pv_id = $pv_id;
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
        $remainingTotalPrice = 0;
        $amountPaid = 0;
        $sumReturTotBeforeVat = \App\Models\Tx_purchase_retur::where('receipt_order_id','=',$this->roid)
        ->whereRaw('approved_by IS NOT NULL')
        ->sum('total_before_vat');

        $total_before_vat = 0;
        $qLast = Tx_receipt_order::where([
            'id'=>$this->roid,
        ])
        ->first();
        if ($qLast){
            $total_before_vat = ($qLast->supplier_type_id==11?$qLast->total_before_vat:$qLast->total_before_vat_rp);
            // $total_before_vat = ($qLast->total_before_vat_rp==null || $qLast->total_before_vat_rp==0?$qLast->total_before_vat:$qLast->total_before_vat_rp);
        }

        // cek jika ada pembayaran sebagian baik itu sudah approved atau belum approved
        if ($this->pv_inv_id==0){
            $sumDibayar = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers AS tx_pv','tx_pv.id','=','tx_payment_voucher_invoices.payment_voucher_id')
            ->whereNotIn('tx_payment_voucher_invoices.payment_voucher_id', function ($q){
                $q->select('id')
                ->from('tx_payment_vouchers AS tx_pv')
                ->whereRaw('payment_voucher_plan_no LIKE \'%Draft%\'')
                ->where('active', '=', 'Y');
            })
            ->where([
                'tx_payment_voucher_invoices.receipt_order_id' => $this->roid,
                'tx_payment_voucher_invoices.active' => 'Y',
                'tx_pv.active' => 'Y',
            ])
            ->sum('tx_payment_voucher_invoices.total_payment');

            $remainingTotalPrice = $total_before_vat-$sumReturTotBeforeVat-$amountPaid;
        }else{
            $sumDibayar = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers AS tx_pv','tx_pv.id','=','tx_payment_voucher_invoices.payment_voucher_id')
            ->whereNotIn('tx_payment_voucher_invoices.payment_voucher_id', function ($q){
                $q->select('id')
                ->from('tx_payment_vouchers AS tx_pv')
                ->whereRaw('payment_voucher_plan_no LIKE \'%Draft%\'')
                ->where('active', '=', 'Y');
            })
            ->where('tx_payment_voucher_invoices.id', '<>', $this->pv_inv_id)
            ->where([
                'tx_payment_voucher_invoices.receipt_order_id' => $this->roid,
                'tx_payment_voucher_invoices.active' => 'Y',
                'tx_pv.active' => 'Y',
            ])
            ->sum('tx_payment_voucher_invoices.total_payment');

            $remainingTotalPrice = $total_before_vat-$sumReturTotBeforeVat-$sumDibayar;
        }

        if(floor(GlobalFuncHelper::moneyValidate($value))>floor(number_format($remainingTotalPrice,0,"",""))){
            $fail('The payment value entered exceeds the remaining payment value.');
        }
    }
}
