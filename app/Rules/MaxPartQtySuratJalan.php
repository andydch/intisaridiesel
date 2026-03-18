<?php

namespace App\Rules;

use App\Models\Tx_delivery_order;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_qty_part;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;
use Illuminate\Contracts\Validation\InvokableRule;

class MaxPartQtySuratJalan implements InvokableRule
{
    protected $part_id;
    protected $branch_id;
    protected $so_id;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($part_id,$branch_id,$so_id)
    {
        $this->part_id = $part_id;
        $this->branch_id = $branch_id;
        $this->so_id = $so_id;
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
        $qtyOH = 0;
        $qtyOHo = Tx_qty_part::where([
            'part_id' => $this->part_id,
            'branch_id' => $this->branch_id,
        ])
        ->first();
        if($qtyOHo){
            $qtyOH = $qtyOHo->qty;
        }

        $so_in_do = '';
        $DOs = Tx_delivery_order_non_tax::where('active','=','Y')
        ->get();
        foreach($DOs as $do){
            $so_in_do .= $do->sales_order_no_all;
        }

        if($this->so_id>0){
            $qtySOo = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS tso','tx_surat_jalan_parts.surat_jalan_id','=','tso.id')
            ->leftJoin('userdetails AS usr','tso.created_by','=','usr.user_id')
            ->whereRaw('tso.surat_jalan_no NOT LIKE \'%Draft%\'')
            ->where([
                'tx_surat_jalan_parts.part_id' => $this->part_id,
                'usr.branch_id' => $this->branch_id,
                'tx_surat_jalan_parts.active' => 'Y',
                'tso.active' => 'Y',
            ])
            ->where('tso.id','<>',$this->so_id)
            ->where('tso.sales_quotation_id','=',null)
            ->whereNotIn('tso.surat_jalan_no', explode(",",$so_in_do))
            ->sum('tx_surat_jalan_parts.qty');
        }else{
            $qtySOo = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS tso','tx_surat_jalan_parts.surat_jalan_id','=','tso.id')
            ->leftJoin('userdetails AS usr','tso.created_by','=','usr.user_id')
            ->whereRaw('tso.surat_jalan_no NOT LIKE \'%Draft%\'')
            ->where([
                'tx_surat_jalan_parts.part_id' => $this->part_id,
                'usr.branch_id' => $this->branch_id,
                'tx_surat_jalan_parts.active' => 'Y',
                'tso.active' => 'Y',
            ])
            ->where('tso.sales_quotation_id','=',null)
            ->whereNotIn('tso.surat_jalan_no', explode(",",$so_in_do))
            ->sum('tx_surat_jalan_parts.qty');
        }

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

        if((int)$value>($qtyOH-($qtySOo+$qtySO))){
            $fail('The qty must not be greater than '.(($qtyOH>$qtySOo)?($qtyOH-($qtySOo+$qtySO)):0).'.');
            // $fail('The qty must not be greater than '.(($qtyOH>$qtySOo)?($qtyOH-$qtySOo):0).'.'.$this->so_id);
        }
    }
}
