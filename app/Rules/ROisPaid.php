<?php

namespace App\Rules;

use App\Models\Tx_payment_voucher;
use App\Models\Tx_tagihan_supplier_detail;
use Illuminate\Contracts\Validation\InvokableRule;

class ROisPaid implements InvokableRule
{
    // digunakan untuk mem-validasi apakah RO sudah dibayar

    protected $ro_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($ro_id)
    {
        $this->ro_id = $ro_id;
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
        $qTagihanSupplier = Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tx_ts','tx_tagihan_supplier_details.tagihan_supplier_id','=','tx_ts.id')
        ->select(
            'tx_tagihan_supplier_details.receipt_order_id',
        )
        ->where([
            'tx_tagihan_supplier_details.receipt_order_id' => $this->ro_id,
            'tx_tagihan_supplier_details.active'=>'Y',
            'tx_ts.active'=>'Y',
        ])
        ->first();

        $qPySupplier = Tx_payment_voucher::select('payment_voucher_no')
        ->whereIn('id', function($q) {
            $q->select('payment_voucher_id')
            ->from('tx_payment_voucher_invoices')
            ->where([
                'receipt_order_id' => $this->ro_id,
                'active' => 'Y',
            ]);
        })
        // ->whereRaw('approved_by IS NOT NULL')
        ->where([
            'active' => 'Y',
        ])
        ->first();
        if ($qPySupplier || $qTagihanSupplier){
            // diketahui bahwa RO sudah dibayar maka Journal Type-nya tidak boleh diubah
            $fail('Journal Type cannot be changed if RO status is Paid or RO status is TS.');
        }
    }
}
