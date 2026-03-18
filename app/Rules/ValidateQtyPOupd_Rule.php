<?php

namespace App\Rules;

use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Contracts\Validation\InvokableRule;

class ValidateQtyPOupd_Rule implements InvokableRule
{
    protected $purchase_order_id;
    protected $purchase_order_part_id;
    protected $part_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($purchase_order_id,$purchase_order_part_id,$part_id)
    {
        $this->purchase_order_id = $purchase_order_id;
        $this->purchase_order_part_id = $purchase_order_part_id;
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


        // $po_no = '';
        // $po_part_id = '';
        // $po_part_qty = 0;
        // $sumRO_per_part = 0;
        // $qPurchaseOrderPart = Tx_purchase_order_part::leftJoin('tx_purchase_orders as tx_po','tx_purchase_order_parts.order_id','=','tx_po.id')
        // ->select(
        //     'tx_purchase_order_parts.id as purchase_order_part_id',
        //     'tx_purchase_order_parts.qty',
        //     'tx_purchase_order_parts.order_id',
        //     'tx_purchase_order_parts.part_id',
        //     'tx_po.purchase_no',
        // )
        // ->where([
        //     'tx_purchase_order_parts.order_id'=>$this->purchase_order_id,
        //     'tx_purchase_order_parts.part_id'=>$this->part_id,
        // ])
        // ->first();
        // if ($qPurchaseOrderPart){
        //     $po_no = $qPurchaseOrderPart->purchase_no;
        //     $po_part_id = $qPurchaseOrderPart->purchase_order_part_id;
        //     $part_id = $qPurchaseOrderPart->part_id;
        //     $po_part_qty = $qPurchaseOrderPart->qty;

        //     $sumRO_per_part = Tx_receipt_order_part::where([
        //         'po_mo_no'=>$po_no,
        //         'po_mo_id'=>$po_part_id,
        //         'part_id'=>$part_id,
        //         'active'=>'Y',
        //     ])
        //     ->sum('qty');

        //     if ($value>$po_part_qty || $value<$sumRO_per_part){
        //         $msg = 'Allowed values: >='.$sumRO_per_part.' and <='.$po_part_qty;
        //         $fail($msg);
        //     }
        // }

        // if ($value>$po_part_qty || $value<$sumRO_per_part){
        //     $msg = 'Allowed values: >='.$sumRO_per_part.' and <='.$po_part_qty;
        //     $fail($msg);
        // }else{
        //     // $fail('lolos');
        // }
    }
}
