<?php

namespace App\Rules;

use App\Models\Tx_purchase_memo;
use App\Models\Tx_receipt_order_part;
use Illuminate\Contracts\Validation\InvokableRule;

class IsMemoTiedWithRO_Rule implements InvokableRule
{
    protected $memo_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($memo_id)
    {
        $this->memo_id = $memo_id;
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
        $memo = Tx_purchase_memo::where('id','=',$this->memo_id)
        ->first();
        if($memo){
            $ro_part = Tx_receipt_order_part::where('po_mo_no','=',$memo->memo_no)
            ->where('active','=','Y')
            ->first();
            if($ro_part){
                $fail('Purchase Memo '.$memo->memo_no.' is tied to Receipt Order.');
            }
        }
    }
}
