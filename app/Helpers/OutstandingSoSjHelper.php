<?php

namespace App\Helpers;

use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;

class OutstandingSoSjHelper
{
    // helper ini digunakan utk mengetahui total outstanding Qty suatu part di SO dan SJ
    
    public static function getOutstandingSoSj($partId){
        $allSoSjQty = 0;

        $qtySOo = Tx_sales_order_part::leftJoin('tx_sales_orders AS tso','tx_sales_order_parts.order_id','=','tso.id')
        ->where([
            'tx_sales_order_parts.part_id' => $partId,
            'tx_sales_order_parts.active' => 'Y',
            'tso.active' => 'Y',
        ])
        ->whereRaw('tso.sales_order_no NOT LIKE \'%Draft%\'')
        ->whereNotIn('tso.id', function($q) {
            $q->select('sales_order_id')
            ->from('tx_delivery_order_parts as tx_dop')
            ->leftJoin('tx_delivery_orders as tx_do', 'tx_dop.delivery_order_id', '=', 'tx_do.id')
            ->where('tx_dop.active', '=', 'Y')
            ->where('tx_do.active', '=', 'Y');
        })
        ->where('tso.sales_quotation_id', '=', null)
        ->sum('tx_sales_order_parts.qty');

        $qtySJ = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id', '=', 'txsj.id')
        ->whereNotIn('txsj.id', function ($query) {
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
            'tx_surat_jalan_parts.part_id' => $partId,
            'tx_surat_jalan_parts.active' => 'Y',
            'txsj.active' => 'Y',
        ])
        ->sum('tx_surat_jalan_parts.qty');

        $allSoSjQty = $qtySOo+$qtySJ;
        return $allSoSjQty;
    }
}
