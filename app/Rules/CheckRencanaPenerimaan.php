<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\DB;

class CheckRencanaPenerimaan implements InvokableRule
{
    protected $isDraft;
    protected $periodDate;
    protected $coaID;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($isDraft, $periodDate, $coaID)
    {
        $this->isDraft = $isDraft;
        $this->periodDate = $periodDate;
        $this->coaID = $coaID;
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
        if ($this->isDraft=='N'){
            $invoice_date = explode('/', $this->periodDate);
    
            // cek di rencana penerimaan
            $qPa = DB::table('tx_acceptance_plans AS tx_ap')
            ->where('acceptance_month', $invoice_date[2].'-'.$invoice_date[1].'-01')
            ->where('bank_id', $this->coaID)
            ->where('is_draft', 'N')
            ->first();
            if (!$qPa){
                $fail('No corresponding period was found in Rencana Penerimaan.');
            }
        }
    }
}
