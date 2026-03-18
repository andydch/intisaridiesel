<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_delivery_order_non_tax_part;

class ApprovalCheckingSJ implements InvokableRule
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
        $query = Tx_surat_jalan::where([
            'surat_jalan_no' => $value
        ])
        ->first();
        if($query){
            // approval checking
            if(!is_null($query->approved_by)){
                $fail('The Surat Jalan No already approved. Cannot be edited!');
            }
            if(!is_null($query->canceled_by)){
                $fail('The Surat Jalan No already rejected. Cannot be edited!');
            }
            // approval checking

            // nota penjualan checking
            $qNPpart = Tx_delivery_order_non_tax_part::where([
                'sales_order_id'=>$query->id,
            ])
            ->first();
            if ($qNPpart){
                $fail('The Nota Penjualan has been created. Cannot be edited!');
            }
            // nota penjualan checking

            // deliver checking
            if ($qNPpart && $query->number_of_prints>0){
                $fail('The Surat Jalan No has Deliver status. Cannot be edited!');
            }
            // deliver checking
        }
    }
}
