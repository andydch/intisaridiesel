<?php

namespace App\Rules;

use App\Helpers\GlobalFuncHelper;
use Illuminate\Contracts\Validation\InvokableRule;

class SameTotPaymentAsTotInv implements InvokableRule
{
    protected $totInv;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($totInv)
    {
        $this->totInv = $totInv;
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
        if (is_numeric(GlobalFuncHelper::moneyValidate($this->totInv))){
            if(floor(GlobalFuncHelper::moneyValidate($value))!=round(GlobalFuncHelper::moneyValidate($this->totInv))){
                $fail('The total payment is not the same as the total invoice.');
                // $fail('The total payment is not the same as the total invoice.['.$value.']['.round(GlobalFuncHelper::moneyValidate($this->totInv)).']');
            }
        }
    }
}
