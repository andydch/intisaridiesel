<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_surat_jalan;

class SQnumSuratJalanUnique implements InvokableRule
{
    protected $poId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($poId)
    {
        $this->poId = $poId;
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
        if($this->poId==0){
            $query = Tx_surat_jalan::where('sales_quotation_id','=',$value)
                ->where('active','=','Y')
                ->first();
        }else{
            $query = Tx_surat_jalan::where('id','<>',$this->poId)
                ->where('sales_quotation_id','=',$value)
                ->where('active','=','Y')
                ->first();
        }
        if($query){
            $fail('The Quotation Code has been used by another PO.');
        }
    }
}
