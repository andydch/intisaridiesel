<?php

namespace App\Rules;

use App\Helpers\GlobalFuncHelper;
use App\Models\Mst_supplier;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateExchangeRateForSupplierType implements InvokableRule
{
    protected $supplier_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($supplier_id)
    {
        $this->supplier_id = $supplier_id;
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
        // 10 : international
        // 11 : lokal

        $qSupplier = Mst_supplier::where('id','=',$this->supplier_id)
        ->first();
        if($qSupplier->supplier_type_id==10 && GlobalFuncHelper::moneyValidate($value)==0){
            $fail('Exchange Rate is required if Supplier Type is International!');
        }
    }
}
