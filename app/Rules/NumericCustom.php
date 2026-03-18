<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class NumericCustom implements InvokableRule
{
    protected $field_name;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($field_name)
    {
        $this->field_name = $field_name;
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
        if ($value==''){
            $fail('The field '.$this->field_name.' must be numeric.');
        }
        $validateChars = '0123456789,.';
        $amountToValidate = str_replace(",","",$value);
        for($i=0;$i<strlen($amountToValidate);$i++){
            if (strpos('v'.$validateChars,substr($amountToValidate,$i,1))==false){
                $fail('The field '.$this->field_name.' must be numeric.');
                break;
            }
        }

        if($amountToValidate>99999999999999999){
            $fail('The field '.$this->field_name.' must be less than 99999999999999999.');
        }
    }
}
