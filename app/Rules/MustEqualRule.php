<?php

namespace App\Rules;

use App\Models\Tx_acceptance_plan;
use Illuminate\Contracts\Validation\InvokableRule;

class MustEqualRule implements InvokableRule
{
    protected $totRencanaPenerimaan = 0;
    protected $totTagihan = 0;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($totRencanaPenerimaan, $totTagihan)
    {
        $this->totRencanaPenerimaan = $totRencanaPenerimaan;
        $this->totTagihan = $totTagihan;
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
        if ((floatval($this->totRencanaPenerimaan) != floatval($this->totTagihan))) {
            $fail('Total Rencana Penerimaan and Total Tagihan must be equal.');
            // $fail('Total Rencana Penerimaan and Total Tagihan must be equal.'.$this->totRencanaPenerimaan.' != '.$this->totTagihan);
        }
    }
}
