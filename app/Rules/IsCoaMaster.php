<?php

namespace App\Rules;

use App\Models\Mst_coa;
use Illuminate\Contracts\Validation\InvokableRule;

class IsCoaMaster implements InvokableRule
{
    // protected $field_name;
    // /**
    //  * Create a new rule instance.
    //  *
    //  * @return void
    //  */
    // public function __construct($field_name)
    // {
    //     $this->field_name = $field_name;
    // }

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
        $qCoa = Mst_coa::where([
            'id'=>$value,
            'is_master_coa'=>'Y',
        ])
        ->first();
        if ($qCoa) {
            $fail('This COA codes cannot be used in journals.');
        }
    }
}
