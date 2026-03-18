<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;

class QtyOnPOorMO implements InvokableRule
{
    protected $pono_or_mono;
    protected $part_id;
    protected $qty;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($pono_or_mono,$part_id,$qty)
    {
        $this->pono_or_mono = $pono_or_mono;
        $this->part_id = $part_id;
        $this->qty = $qty;
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
        $queryPart = [];
        $po_mo_no = 'part-'.$this->pono_or_mono;
        if(strpos($po_mo_no,'MO')>0 || strpos($po_mo_no,'MP')>0){
            $queryPart = Tx_purchase_memo_part::leftJoin('tx_purchase_memos','tx_purchase_memo_parts.memo_id','=','tx_purchase_memos.id')
            ->leftJoin('mst_parts','tx_purchase_memo_parts.part_id','=','mst_parts.id')
            ->select(
                'tx_purchase_memo_parts.qty',
            )
            ->addSelect(['last_qty_total' => Tx_receipt_order_part::selectRaw('SUM(qty)')
                ->where('tx_receipt_order_parts.po_mo_no','=',$this->pono_or_mono)
                ->whereColumn('tx_receipt_order_parts.part_id','tx_purchase_memo_parts.part_id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            // ->whereNotIn('tx_purchase_memo_parts.part_id',
            //     function (Builder $queryY) use ($request) {
            //     $queryY->select('tx_ro.part_id')
            //     ->from('tx_receipt_order_parts as tx_ro')
            //     ->where([
            //         'tx_ro.po_mo_no' => $this->pono_or_mono,
            //         'tx_ro.is_partial_received' => 'N'
            //     ])
            //     ->where('tx_ro.active','=','Y');
            // })
            ->where([
                'tx_purchase_memos.memo_no' => $this->pono_or_mono,
                'tx_purchase_memo_parts.part_id' => $this->part_id,
                'tx_purchase_memo_parts.active' => 'Y'
            ])
            ->get();
        }
        if(strpos($po_mo_no,'PO')>0){
            $queryPart = Tx_purchase_order_part::leftJoin('tx_purchase_orders','tx_purchase_order_parts.order_id','=','tx_purchase_orders.id')
            ->leftJoin('mst_parts','tx_purchase_order_parts.part_id','=','mst_parts.id')
            ->leftJoin('mst_globals','tx_purchase_orders.currency_id','=','mst_globals.id')
            ->select(
                'tx_purchase_order_parts.qty',
            )
            ->addSelect(['last_qty_total' => Tx_receipt_order_part::selectRaw('SUM(qty)')
                ->where('tx_receipt_order_parts.po_mo_no','=',$this->pono_or_mono)
                ->whereColumn('tx_receipt_order_parts.part_id','tx_purchase_order_parts.part_id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            // ->whereNotIn('tx_purchase_order_parts.part_id',
            //     function (Builder $queryY) use ($request) {
            //     $queryY->select('tx_ro.part_id')
            //     ->from('tx_receipt_order_parts as tx_ro')
            //     ->where([
            //         'tx_ro.po_mo_no' => $this->pono_or_mono,
            //         'tx_ro.is_partial_received' => 'N'
            //     ])
            //     ->where('tx_ro.active','=','Y');
            // })
            ->where([
                'tx_purchase_orders.purchase_no' => $this->pono_or_mono,
                'tx_purchase_order_parts.part_id' => $this->part_id,
                'tx_purchase_order_parts.active' => 'Y'
            ])
            ->get();
        }

        if($queryPart){
            if(($queryPart->qty-$queryPart->last_qty_total)<$this->qty){
                $fail('The qty must be less than '.$queryPart->last_qty_total.'.');
            }
        }else{
            $fail('Invalid Qty');
        }
    }
}
