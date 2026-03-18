<?php

namespace App\Rules;

use App\Models\Tx_purchase_order;
use App\Models\Tx_receipt_order_part;
use Illuminate\Contracts\Validation\InvokableRule;

class IsPOrderTiedWithRO_Rule implements InvokableRule
{
    protected $order_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($order_id)
    {
        $this->order_id = $order_id;
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
        $p_order = Tx_purchase_order::where('id','=',$this->order_id)
        ->first();
        if($p_order){
            $ro_part = Tx_receipt_order_part::where('po_mo_no','=',$p_order->purchase_no)
            ->where('active','=','Y')
            ->first();
            if($ro_part){
                $fail('Purchase Order '.$p_order->purchase_no.' is tied to Receipt Order.');
            }
        }
    }
}
