<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_receipt_order;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_tagihan_supplier_detail;

class ApprovalCheckingRO implements InvokableRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $query = Tx_receipt_order::where([
            'receipt_no' => $value
        ])
        ->first();
        if($query){
            $qTagihanSupplier = Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tx_ts','tx_tagihan_supplier_details.tagihan_supplier_id','=','tx_ts.id')
            ->select(
                'tx_tagihan_supplier_details.receipt_order_id',
            )
            ->where([
                'tx_tagihan_supplier_details.receipt_order_id' => $query->id,
                'tx_tagihan_supplier_details.active'=>'Y',
                'tx_ts.active'=>'Y',
            ])
            ->first();

            $qPySupplier = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as tx_pv','tx_payment_voucher_invoices.payment_voucher_id','=','tx_pv.id')
            ->select(
                'tx_payment_voucher_invoices.is_full_payment',
            )
            ->whereIn('tx_payment_voucher_invoices.is_full_payment', ['Y', 'N'])
            ->where([
                'tx_payment_voucher_invoices.receipt_order_id' => $query->id,
                'tx_payment_voucher_invoices.active' => 'Y',
                'tx_pv.active' => 'Y',
            ])
            ->orderBy('tx_pv.created_at','DESC')
            ->first();
            if ($qPySupplier || $qTagihanSupplier){
                $fail('The Receipt Order No can not be updated!');
            }
        }

    }
}
