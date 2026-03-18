<?php

namespace App\Rules;

use App\Models\Mst_coa;
use Illuminate\Contracts\Validation\InvokableRule;

class CoaCheckLevel implements InvokableRule
{
    protected $col;

    public function  __construct($coa_level)
    {
        $this->col = $coa_level;
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
        $qCoa = Mst_coa::where('id','=',$value)
        ->first();
        if($qCoa){
            if($qCoa->coa_level>=$this->col){
                $fail('The :attribute level must be lower than coa level.');
            }
        }
        // if ($value >= $this->col) {
        //     if ($value == 1 && $this->col == 1) {
        //         //
        //     } else {
        //         if ($value > 1 && $this->col == 1) {
        //             $fail('The :attribute level must be 1.');
        //         } else {
        //             $fail('The :attribute level must be lower than coa level.');
        //         }
        //     }
        // }
    }
}
