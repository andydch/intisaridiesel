<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class SupplierType implements InvokableRule
{
    protected $countryId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($country_id)
    {
        $this->countryId = $country_id;
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
        // 10: international; 11: lokal
        if ($value == 10 && $this->countryId == 9999) {
            $fail('The Supplier Type must be Lokal.');
        }
        if ($value == 11 && $this->countryId != 9999) {
            $fail('The Supplier Type must be International.');
        }
    }
}
