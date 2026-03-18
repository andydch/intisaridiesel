<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_delivery_order_non_tax;

class IsSJnotConnectedToNP implements InvokableRule
{
    protected $npId;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($npId)
    {
        $this->npId = $npId;
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
        $allSJs = explode(",", $value);
        foreach ($allSJs as $sj) {
            if ($sj!=''){
                $qFk = Tx_delivery_order_non_tax::when($this->npId>0, function($q) {
                    $q->where('id', '<>', $this->npId);
                })
                ->where('is_draft', '=', 'N')
                ->where('sales_order_no_all', 'LIKE', '%'.$sj.'%')
                ->first();
                if ($qFk){
                    $fail('The Surat Jalan number is already linked to another Nota Penjualan number.');
                    break;
                }
            }
        }
    }
}
