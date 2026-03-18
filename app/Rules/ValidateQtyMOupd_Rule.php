<?php

namespace App\Rules;

use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateQtyMOupd_Rule implements InvokableRule
{
    protected $purchase_memo_id;
    protected $purchase_memo_part_id;
    protected $part_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($purchase_memo_id,$purchase_memo_part_id,$part_id)
    {
        $this->purchase_memo_id = $purchase_memo_id;
        $this->purchase_memo_part_id = $purchase_memo_part_id;
        $this->part_id = $part_id;
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
        if ($value <= 0) {
            $msg = 'Qty cannot be less than or equal 0';
            $fail($msg);
        }
        
        // $mo_no = '';
        // $mo_part_id = '';
        // $mo_part_qty = '';
        // $qPurchaseOrderPart = Tx_purchase_memo_part::leftJoin('tx_purchase_memos as tx_mo','tx_purchase_memo_parts.memo_id','=','tx_mo.id')
        // ->select(
        //     'tx_purchase_memo_parts.id as purchase_memo_part_id',
        //     'tx_purchase_memo_parts.qty',
        //     'tx_purchase_memo_parts.memo_id',
        //     'tx_purchase_memo_parts.part_id',
        //     'tx_mo.memo_no',
        // )
        // ->where([
        //     'tx_purchase_memo_parts.memo_id'=>$this->purchase_memo_id,
        //     'tx_purchase_memo_parts.part_id'=>$this->part_id,
        // ])
        // ->first();
        // if ($qPurchaseOrderPart){
        //     $mo_no = $qPurchaseOrderPart->memo_no;
        //     $mo_part_id = $qPurchaseOrderPart->purchase_memo_part_id;
        //     $part_id = $qPurchaseOrderPart->part_id;
        //     $mo_part_qty = $qPurchaseOrderPart->qty;
        // }

        // $sumRO_per_part = Tx_receipt_order_part::where([
        //     'po_mo_no'=>$mo_no,
        //     'po_mo_id'=>$mo_part_id,
        //     'part_id'=>$part_id,
        //     'active'=>'Y',
        // ])
        // ->sum('qty');

        // if ($value>$mo_part_qty || $value<$sumRO_per_part){
        //     $msg = 'Allowed values: >='.$sumRO_per_part.' and <='.$mo_part_qty;
        //     $fail($msg);
        // }else{
        //     // $fail('lolos');
        // }
    }
}
