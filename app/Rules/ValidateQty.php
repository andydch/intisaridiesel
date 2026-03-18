<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use App\Models\Tx_qty_part;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;

class ValidateQty implements InvokableRule
{
    protected $part_id;
    protected $branch_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($part_id,$branch_id)
    {
        $this->part_id = $part_id;
        $this->branch_id = $branch_id;
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
        if($this->part_id!='#'){
            $qtySO = Tx_sales_order_part::leftJoin('tx_sales_orders AS txso', 'tx_sales_order_parts.order_id', '=', 'txso.id')
            ->whereNotIn('txso.id',function ($query) {
                $query->select('tx_do_parts.sales_order_id')
                ->from('tx_delivery_order_parts as tx_do_parts')
                ->leftJoin('tx_delivery_orders as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                ->where([
                    'tx_do_parts.active' => 'Y',
                    'tx_do.active' => 'Y',
                ]);
            })
            ->whereRaw('txso.sales_order_no NOT LIKE \'%Draft%\'')
            ->where([
                'tx_sales_order_parts.part_id' => $this->part_id,
                'tx_sales_order_parts.active' => 'Y',
                // 'txso.need_approval'=>'N',
                'txso.branch_id' => $this->branch_id,
                'txso.active' => 'Y',
            ])
            ->sum('tx_sales_order_parts.qty');

            $qtySJ = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id', '=', 'txsj.id')
            ->whereNotIn('txsj.id',function ($query) {
                $query->select('tx_do_parts.sales_order_id')
                ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                ->leftJoin('tx_delivery_order_non_taxes as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                ->where([
                    'tx_do_parts.active' => 'Y',
                    'tx_do.active' => 'Y',
                ]);
            })
            ->whereRaw('txsj.surat_jalan_no NOT LIKE \'%Draft%\'')
            ->where([
                'tx_surat_jalan_parts.part_id' => $this->part_id,
                'tx_surat_jalan_parts.active' => 'Y',
                // 'txsj.need_approval'=>'N',
                'txsj.branch_id' => $this->branch_id,
                'txsj.active' => 'Y',
            ])
            ->sum('tx_surat_jalan_parts.qty');

            $oh = 0;
            $qtyOH = Tx_qty_part::where([
                'part_id' => $this->part_id,
                'branch_id' => $this->branch_id,
            ])
            ->first();
            if ($qtyOH){
                $oh = $qtyOH->qty;
            }

            $availableOH = $oh-($qtySO+$qtySJ);
            if ($availableOH<$value){
                $fail('The quantity entered is greater than the available stock. (OH: '.$availableOH.')');
            }


            // $queryQty = Tx_qty_part::where('part_id','=', $this->part_id)
            // ->where('qty','>=',$value)
            // ->where('branch_id','=',$this->branch_id)
            // ->first();
            // if(!$queryQty){
            //     $fail('The quantity entered is greater than the available stock.');
            // }
        }
    }
}
