<?php

namespace App\Rules;

use App\Models\Tx_acceptance_plan;
use Illuminate\Contracts\Validation\InvokableRule;

class AcceptMonthCheck implements InvokableRule
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
        $qPlan = Tx_acceptance_plan::whereRaw('DATE_FORMAT(acceptance_month, "%Y-%m")=\''.$ym[2].'-'.$ym[1].'\'')
        ->where([
            'active' => 'Y',
        ])
        ->first();
        if (!$qPlan) {
            $fail('Payment Month is not avalaible.');
        }
    }
}
