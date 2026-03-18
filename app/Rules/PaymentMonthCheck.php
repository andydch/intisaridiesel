<?php

namespace App\Rules;

use App\Models\Tx_payment_plan;
use Illuminate\Contracts\Validation\InvokableRule;

class PaymentMonthCheck implements InvokableRule
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
        $ym = explode("/", $value);
        $qPlan = Tx_payment_plan::whereRaw('DATE_FORMAT(payment_month, "%Y-%m")=\''.$ym[2].'-'.$ym[1].'\'')
        ->where([
            'active' => 'Y',
        ])
        ->first();
        if (!$qPlan) {
            $fail('Payment Month is not avalaible.');
        }
    }
}
