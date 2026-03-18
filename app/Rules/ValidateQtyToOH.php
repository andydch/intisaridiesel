<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_qty_part;

class ValidateQtyToOH implements InvokableRule
{
    protected $OHori;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($OHori)
    {
        $this->OHori = $OHori;
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
        if(is_numeric($value)){
            if(($value+$this->OHori)<0){
                $fail('The new OH value cannot be less than 0');
            }
        }else{
            $fail('The qty field must be numeric');
        }
    }
}
