<?php

namespace App\Rules;

use App\Models\Tx_purchase_order;
use App\Models\Tx_receipt_order_part;
use Illuminate\Contracts\Validation\InvokableRule;

class VatCheckingPO implements InvokableRule
{
    protected $po_no;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($vat)
    {
        $this->vat = $vat;
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
        $is_vat = $this->vat == 'on' ? 'Y' : 'N';

        $qRo = Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
        ->where([
            'tx_receipt_order_parts.po_mo_no' => $value,
            'tx_receipt_order_parts.active' => 'Y',
            'tx_ro.is_draft' => 'N',
            'tx_ro.active' => 'Y',
        ])
        ->first();

        $qPo = Tx_purchase_order::select('is_vat')
        ->where([
            'purchase_no' => $value,
            'active' => 'Y',
        ])
        ->first();

        if($qPo){
            if ($qPo->is_vat != $is_vat && $qRo) {
                $fail('VAT status cannot be changed because there are already Receipt Orders created for this PO.');
            }
        }else{
            $fail('PO not found.');
        }
    }
}
