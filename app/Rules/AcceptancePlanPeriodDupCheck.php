<?php

namespace App\Rules;

use App\Models\Tx_acceptance_plan;
use Illuminate\Contracts\Validation\InvokableRule;

class AcceptancePlanPeriodDupCheck implements InvokableRule
{
    protected $id;
    protected $year_month_period;
    protected $bank_id;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($id, $year_month_period, $bank_id)
    {
        $this->id = $id;
        $this->year_month_period = $year_month_period;
        $this->bank_id = $bank_id;
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
        $qAcceptancePlan = Tx_acceptance_plan::when($this->id>0, function($query) {
            return $query->where('id', '<>', $this->id);
        })
        ->whereRaw('DATE_FORMAT(acceptance_month, "%Y-%c")=\''.$this->year_month_period.'\'')
        ->where([
            'bank_id'=> $this->bank_id,
        ])
        ->first();
        if($qAcceptancePlan){
            $fail('Period has already been taken.');
        }
    }
}
