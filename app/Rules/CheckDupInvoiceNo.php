<?php

namespace App\Rules;

use App\Models\Tx_receipt_order;
use Illuminate\Contracts\Validation\InvokableRule;

class CheckDupInvoiceNo implements InvokableRule
{
    protected $supplier_id;
    protected $invoice_no;
    protected $ro_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($supplier_id,$invoice_no,$ro_id)
    {
        $this->supplier_id = $supplier_id;
        $this->invoice_no = $invoice_no;
        $this->ro_id = $ro_id;
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
        if($this->ro_id==0){
            $qDup = Tx_receipt_order::where([
                'supplier_id' => $this->supplier_id,
                'invoice_no' => $this->invoice_no,
            ])
            ->first();
        }else{
            $qDup = Tx_receipt_order::where('id','<>',$this->ro_id)
            ->where([
                'supplier_id' => $this->supplier_id,
                'invoice_no' => $this->invoice_no,
            ])
            ->first();
        }
        if($qDup){
            $fail('The Invoice number is already in use.');
        }
    }
}
