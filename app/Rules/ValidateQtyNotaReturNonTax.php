<?php

namespace App\Rules;

use App\Models\Tx_surat_jalan_part;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateQtyNotaReturNonTax implements InvokableRule
{
    protected $soPartId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($soPartId)
    {
        $this->soPartId = $soPartId;
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
        $query = Tx_surat_jalan_part::where('id','=',$this->soPartId)
        ->first();
        if($query){
            if((int)$query->qty<(int)$value){
                $fail('The number of parts returned must be equal to or less than the number of parts on the sales order part.');
            }
        }
    }
}
