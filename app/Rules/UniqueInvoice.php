<?php

namespace App\Rules;

use App\Models\Tx_invoice;
use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_receipt_order;
use App\Models\Tx_receipt_order_part;

class UniqueInvoice implements InvokableRule
{
    protected $invoice_id;
    protected $do_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($invoice_id,$do_id)
    {
        $this->invoice_id = $invoice_id;
        $this->do_id = $do_id;
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
        $query = Tx_invoice::where([
            'delivery_order_id' => $this->do_id,
            'active' => 'Y'
        ])
        ->where('id','<>',$this->invoice_id)
        ->first();

        if($query){
            $fail('The delivery order number has already been taken.');
        }
    }
}
