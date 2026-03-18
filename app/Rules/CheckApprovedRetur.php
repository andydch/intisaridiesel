<?php

namespace App\Rules;

use App\Models\Tx_nota_retur;
use Illuminate\Contracts\Validation\InvokableRule;

class CheckApprovedRetur implements InvokableRule
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
        $found = false;
        $fkArr = explode(",", $value);
        foreach($fkArr as $fk){
            if ($fk!=''){
                $nr = Tx_nota_retur::leftJoin('tx_delivery_orders as tx_o','tx_nota_returs.delivery_order_id','=','tx_o.id')
                ->whereRaw('tx_nota_returs.approved_by IS NULL AND tx_nota_returs.canceled_by IS NULL')
                ->where([
                    'tx_o.delivery_order_no'=>$fk,
                    'tx_nota_returs.active'=>'Y',
                ])
                ->first();
                if ($nr){
                    $found = true;
                    break;
                }
            }
        }
        if ($found){
            $fail('There are several Nota Returs that has not been approved.');
        }
    }
}
