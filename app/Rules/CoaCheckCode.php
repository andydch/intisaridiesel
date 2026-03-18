<?php

namespace App\Rules;

use App\Models\Mst_coa;
use Illuminate\Contracts\Validation\InvokableRule;

class CoaCheckCode implements InvokableRule
{
    protected $coa_code_complete;

    public function  __construct($coa_code_complete)
    {
        $this->coa_code_complete = $coa_code_complete;
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
        $q = Mst_coa::where([
            'coa_code_complete' => $this->coa_code_complete,
            'active' => 'Y'
        ])
        ->first();
        if($q){
            $fail('The :attribute has been taken. Please, use another code.');
        }
    }
}
