<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_delivery_order;

class IsSOnotConnectedToFK implements InvokableRule
{
    protected $fkId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($fkId)
    {
        $this->fkId = $fkId;
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
        $allSOs = explode(",", $value);
        foreach ($allSOs as $so) {
            if ($so!=''){
                $qFk = Tx_delivery_order::when($this->fkId>0, function($q) {
                    $q->where('id', '<>', $this->fkId);
                })
                ->where('is_draft', '=', 'N')
                ->where('sales_order_no_all', 'LIKE', '%'.$so.'%')
                ->first();
                if ($qFk){
                    $fail('The Sales Order number is already linked to another Faktur number.');
                    break;
                }
            }
        }
    }
}
