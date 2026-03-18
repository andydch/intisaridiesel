<?php

namespace App\Rules;

use App\Helpers\GlobalFuncHelper;
use Illuminate\Contracts\Validation\InvokableRule;

class CheckAmountEqualWithTotal implements InvokableRule
{
    protected $totalAmount;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($totalAmount)
    {
        $this->totalAmount = $totalAmount;
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
        if(str_replace(",", "",$this->totalAmount)!=GlobalFuncHelper::moneyValidate($value)){
            $fail('Invoice Amount must be equal to the Total Price!');
            // $fail('Invoice Amount must be equal to the Total Price!'.str_replace(",", "",$this->totalAmount).'---'.GlobalFuncHelper::moneyValidate($value));
        }
    }
}
