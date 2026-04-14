<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

use App\Models\Tx_receipt_order;

class ValdROforTagihanSupplierRules implements InvokableRule
{
    protected $tx_ts_ignore_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($tx_ts_ignore_id)
    {
        $this->tx_ts_ignore_id = $tx_ts_ignore_id;
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
        $ro_inv_all = explode(",", $value);
        foreach ($ro_inv_all as $ro_inv) {
            if ($ro_inv!=''){
                $sNoTmp = str_replace("(Non VAT)", "", $ro_inv);
                $sNoTmp = str_replace("(VAT)", "", $sNoTmp);
                $ro = explode(' / ', $sNoTmp);
    
                $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
                ->whereNotIn('id', function($q1) {
                    $q1->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->when($this->tx_ts_ignore_id!=0, function($q){
                        $q->where('tx_ts.id', '!=', $this->tx_ts_ignore_id);
                    })
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->whereNotIn('id', function($q1){
                    $q1->select('tx_pvi.receipt_order_id')
                    ->from('tx_payment_voucher_invoices AS tx_pvi')
                    ->leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pvi.payment_voucher_id', '=', 'tx_pv.id')
                    ->where([
                        'tx_pvi.is_full_payment' => 'Y',
                        'tx_pvi.active' => 'Y',
                        'tx_pv.active' => 'Y',
                    ]);
                })
                ->where([
                    'receipt_no' => trim(trim($ro[count($ro)-1], " ")),
                    'active' => 'Y',
                ])
                ->first();
                if (!$qRO){
                    $fail('INV No '.rtrim(trim($ro[0], " ")).' / RO No '.rtrim($ro[count($ro)-1]).' is not available.');
                    break;
                }
            }
        }
    }
}
