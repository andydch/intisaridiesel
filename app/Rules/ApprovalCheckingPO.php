<?php

namespace App\Rules;

use App\Models\Tx_purchase_order;
use App\Models\Userdetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\InvokableRule;

class ApprovalCheckingPO implements InvokableRule
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
        $userDirector = Userdetail::where([
            'user_id'=>Auth::user()->id,
            'is_director'=>'Y',
            'active'=>'Y',
        ])
        ->first();
        $query = Tx_purchase_order::where([
            'purchase_no' => $value
        ])
        ->first();
        if($query){
            if (!$userDirector){
                // selain direktur tidak boleh edit data
                if(!is_null($query->approved_by)){
                    $fail('The Purchase Order No: '.$value.' already approved. Cannot be edited!');
                }
                if(!is_null($query->canceled_by)){
                    $fail('The Purchase Order No: '.$value.' already rejected. Cannot be edited!');
                }
            }else{
                // direktur boleh edit data
            }
        }
    }
}
