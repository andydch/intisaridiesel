<?php

namespace App\Rules;

use App\Models\Tx_purchase_order;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_receipt_order;
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
    public function __construct($purchase_order_id, $purchase_order_part_id, $part_id)
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
        // pastikan apakah status di RO adalah partial received atau belum terhubung di RO
        $isPartialRcv = Tx_receipt_order_part::whereIn('receipt_order_id', function($q) {
            $q->select('id')
            ->from('tx_receipt_orders')
            ->where('active', 'Y');
        })
        ->where('po_mo_id', $this->purchase_order_part_id)
        ->where('part_id', $this->part_id)
        ->whereRaw('qty<>'.$value)
        ->where('is_partial_received', 'N')
        ->where('active', 'Y')
        ->first();
        if ($isPartialRcv){
            $msg = 'Data can no longer be changed';
            $fail($msg);
        }
        
        if ($value == 0){
            // qty tidak boleh diisi 0 jika terhubung ke lebih dari 1 RO
            $countRO = Tx_receipt_order::whereIn('id', function($q) {
                $q->select('receipt_order_id')
                ->from('tx_receipt_order_parts')
                ->whereIn('po_mo_no', function($q1) {
                    $q1->select('purchase_no')
                    ->from('tx_purchase_orders')
                    ->where('id', $this->purchase_order_id)
                    ->where('active', 'Y');
                })
                ->whereIn('po_mo_id', function($q1) {
                    $q1->select('id')
                    ->from('tx_purchase_order_parts')
                    ->where('id', $this->purchase_order_part_id)
                    ->whereIn('order_id', function($q2) {
                        $q2->select('id')
                        ->from('tx_purchase_orders')
                        ->where('id', $this->purchase_order_id)
                        ->where('active', 'Y');
                    })
                    ->where('part_id', $this->part_id)
                    ->where('active', 'Y');
                })
                ->where('is_partial_received', 'Y')
                ->where('active', 'Y');
            })
            ->where('active', 'Y')
            ->count();

            if ($countRO>1){
                $msg = 'The qty field is not allowed to be filled with 0';
                $fail($msg);
            }
        }else{
            $approvedBy = '';
            $isDraft = 'Y';
            $qPOstatus = Tx_purchase_order::where('id', $this->purchase_order_id)
            ->where('active', 'Y')
            ->first();
            if ($qPOstatus){
                $approvedBy = $qPOstatus->approved_by;
                $isDraft = $qPOstatus->is_draft;
            }
    
            if ($approvedBy==null || $isDraft=='Y'){
                // lewat
            }else{
                $isPOpartInRO = Tx_receipt_order_part::whereIn('receipt_order_id', function($q) {
                    $q->select('id')
                    ->from('tx_receipt_orders')
                    ->where('active', 'Y');
                })
                ->where('po_mo_id', $this->purchase_order_part_id)
                ->where('part_id', $this->part_id)
                ->where('active', 'Y')
                ->first();
                if ($isPOpartInRO){
                    // tampilkan QTY yg sudah masuk RO dan berstatus active
                    $sumQtyRO = Tx_receipt_order_part::whereIn('receipt_order_id', function($q) {
                        $q->select('id')
                        ->from('tx_receipt_orders')
                        ->where('active', 'Y');
                    })
                    ->where('po_mo_id', $this->purchase_order_part_id)
                    ->where('part_id', $this->part_id)
                    ->where('active', 'Y')
                    ->sum('qty');
            
                    $lastQtyOnPO = 0;
                    $qLastQtyOnPO = Tx_purchase_order_part::where('id', $this->purchase_order_part_id)
                    ->where('active', 'Y')
                    ->first();
                    if ($qLastQtyOnPO){
                        $lastQtyOnPO = $qLastQtyOnPO->qty;
                    }
            
                    // nilai max
                    $maxValue = ($lastQtyOnPO-$sumQtyRO)+$sumQtyRO;
                    // nilai min
                    $minValue = ($sumQtyRO<=0?1:$sumQtyRO);
            
                    if ($maxValue<$value){
                        $msg = 'The qty should not be more than '.$maxValue;
                        $fail($msg);
                    }
                    if ($minValue>$value){
                        $msg = 'The qty cannot be less than '.$minValue;
                        $fail($msg);
                    }
                }
            }
        }

        // if ($value <= 0) {
        // $msg = 'max: '.$maxValue.' min: '.$minValue;
        // $fail($msg);
        // }

        // quantity cannot be less than 5
        // quantity should not be more than 5
    }
}
