<?php

namespace App\Rules;

use App\Models\Tx_invoice_part;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateQtyRetur implements InvokableRule
{
    protected $invPartId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($invPartId)
    {
        $this->invPartId = $invPartId;
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
        $query = Tx_invoice_part::where('id','=',$this->invPartId)
        ->first();
        if($query){
            if((int)$query->qty<(int)$value){
                $fail('The number of parts returned must be equal to or less than the number of parts on the invoice.');
            }
        }
    }
}
