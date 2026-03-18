<?php

namespace App\Rules;

use App\Models\Mst_customer;
use Illuminate\Contracts\Validation\InvokableRule;

class CustCodeUnique implements InvokableRule
{
    protected $custCode;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($custCode)
    {
        $this->custCode = $custCode;
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
        $qCust = Mst_customer::where([
            'customer_unique_code' => urldecode($this->custCode),
            'active' => 'Y'
        ])
        ->first();
        if($qCust){            
            $qCheck = Mst_customer::where([
                'customer_unique_code' => $value,
                'active' => 'Y'
            ])
            ->where('id', '<>', $qCust->id)
            ->first();
            if($qCheck){
                $fail('The customer unique code has already been taken.');
            }
        }
    }
}
