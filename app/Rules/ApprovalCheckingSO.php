<?php

namespace App\Rules;

use App\Models\Tx_sales_order;
use App\Models\Tx_delivery_order_part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\InvokableRule;

class ApprovalCheckingSO implements InvokableRule
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
        $query = Tx_sales_order::where([
            'sales_order_no' => $value
        ])
        ->first();
        if($query){
            // approval checking
            if(!is_null($query->approved_by)){
                $fail('The Sales Order No already approved. Cannot be edited!');
            }
            if(!is_null($query->canceled_by)){
                $fail('The Sales Order No already rejected. Cannot be edited!');
            }
            // approval checking
            
            // faktur checking
            $faktur = Tx_delivery_order_part::where('sales_order_id','=',$query->id)
            ->first();
            if ($faktur){
                $fail('The Faktur has been created. Cannot be edited!');
            }
            // faktur checking

            // deliver checking
            if ($faktur && $query->number_of_prints>0){
                $fail('The Sales Order No has Deliver status. Cannot be edited!');
            }
            // deliver checking
        }

    }
}
