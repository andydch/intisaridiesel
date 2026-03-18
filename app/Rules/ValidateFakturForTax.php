<?php

namespace App\Rules;

use App\Models\Tx_nota_retur;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateFakturForTax implements InvokableRule
{
    protected $allCusts;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($allCusts)
    {
        $this->allCusts = $allCusts;
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
        if($this->allCusts==''){
            $fail('Choose at least 1 customer.');
        }
    }
}
