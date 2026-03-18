<?php

namespace App\Rules;

use App\Models\Tx_lokal_journal;
use Illuminate\Contracts\Validation\InvokableRule;

class IsLJoApproved implements InvokableRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        $qIsApproved = Tx_lokal_journal::where([
            'general_journal_no'=>$value,
            'status_appr'=>'Y',
        ])
        ->first();
        if (!$qIsApproved){
            //
        }else{
            $fail('This document cannot be changed because it has already been approved.');
        }
    }
}
