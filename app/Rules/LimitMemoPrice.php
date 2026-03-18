<?php

namespace App\Rules;

use App\Models\Mst_global;
use Illuminate\Contracts\Validation\InvokableRule;

class LimitMemoPrice implements InvokableRule
{
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
        $amountToValidate = str_replace(",","",$value);
        $query = Mst_global::where('data_cat','=','memo-limit')
        ->where('active','=','Y')
        ->first();
        if($query){
            if($query->numeric_val<$amountToValidate){
                $fail('Maximum allowed price limit is Rp'.number_format($query->numeric_val,0,'.',',').'.');
            }
        }else{
            $fail('The maximum limit of the total price has not been determined.');
        }
    }
}
