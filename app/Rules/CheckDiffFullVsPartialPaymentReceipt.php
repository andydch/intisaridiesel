<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class CheckDiffFullVsPartialPaymentReceipt implements InvokableRule
{
    protected $full_payment;
    protected $partial_payment;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($full_payment, $partial_payment)
    {
        $this->full_payment = $full_payment;
        $this->partial_payment = $partial_payment;
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
        // rule ini digunakan di PA dan PV
        if ((float)$this->full_payment <> (float)$this->partial_payment && $value=='') {
            $fail('Due to partial payment, the next planned date must be filled in.');
        }
    }
}
