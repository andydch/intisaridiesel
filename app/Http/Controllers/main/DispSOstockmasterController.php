<?php

namespace App\Http\Controllers\main;

use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispSOstockmasterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // sales order
        $qSO = Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
        ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
        ->leftJoin('mst_customers AS cust','txso.customer_id','=','cust.id')
        ->select(
            'txso.id',
            'txso.sales_order_no',
            'txso.sales_order_date',
            // 'FORMAT(tx_sales_order_parts.price,2) AS price',
            'cust.name AS cust_name',
        )
        ->selectRaw('FORMAT(SUM(tx_sales_order_parts.price),0) AS price')
        ->addSelect(['so_qty' => Tx_sales_order_part::selectRaw('SUM(qty)')
            ->whereColumn('order_id','txso.id')
            ->where('part_id','=',$request->part_id)
            ->where('active','=','Y')
        ])
        ->whereNotIn('txso.id',function (Builder $query) {
            $query->select('tx_do_parts.sales_order_id')
            ->from('tx_delivery_order_parts as tx_do_parts')
            ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
            ->where('tx_do_parts.active','=','Y')
            ->where('tx_do.active','=','Y');
        })
        ->where('tx_sales_order_parts.part_id','=',$request->part_id)
        ->where('tx_sales_order_parts.active','=','Y')
        ->where('txso.sales_order_no','NOT LIKE','%Draft%')
        ->where('txso.need_approval','=','N')
        ->where('txso.active','=','Y')
        ->when($request->branch_id!=null, function($q) use($request) {
            $q->whereRaw('((usr.branch_id='.$request->branch_id.' AND txso.branch_id IS null) OR txso.branch_id='.$request->branch_id.')');
        })
        ->when($request->branch_id==null, function($q) use($request) {
            $q->where('usr.branch_id','=',$request->branch_id);
        })
        ->groupBy('txso.id')
        ->groupBy('txso.sales_order_no')
        ->groupBy('txso.sales_order_date')
        // ->groupBy('tx_sales_order_parts.price')
        ->groupBy('cust.name')
        ->get();

        // surat jalan
        $qSJ = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
        ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
        ->leftJoin('mst_customers AS cust','txsj.customer_id','=','cust.id')
        ->select(
            'txsj.id',
            'txsj.surat_jalan_no',
            'txsj.surat_jalan_date',
            // 'FORMAT(tx_surat_jalan_parts.price,0) AS price',
            'cust.name AS cust_name',
        )
        ->selectRaw('FORMAT(SUM(tx_surat_jalan_parts.price),0) AS price')
        ->addSelect(['sj_qty' => Tx_surat_jalan_part::selectRaw('SUM(qty)')
            ->whereColumn('surat_jalan_id','txsj.id')
            ->where('part_id','=',$request->part_id)
            ->where('active','=','Y')
        ])
        ->whereNotIn('txsj.id',function (Builder $query) {
            $query->select('tx_do_parts.sales_order_id')
            ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
            ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
            ->where('tx_do_parts.active','=','Y')
            ->where('tx_do.active','=','Y');
        })
        ->where('tx_surat_jalan_parts.part_id','=',$request->part_id)
        ->where('tx_surat_jalan_parts.active','=','Y')
        ->where('txsj.surat_jalan_no','NOT LIKE','%Draft%')
        ->where('txsj.need_approval','=','N')
        ->where('txsj.active','=','Y')
        ->when($request->branch_id!=null, function($q) use($request) {
            $q->whereRaw('((usr.branch_id='.$request->branch_id.' AND txsj.branch_id IS null) OR txsj.branch_id='.$request->branch_id.')');
        })
        ->when($request->branch_id==null, function($q) use($request) {
            $q->where('usr.branch_id','=',$request->branch_id);
        })
        ->groupBy('txsj.id')
        ->groupBy('txsj.surat_jalan_no')
        ->groupBy('txsj.surat_jalan_date')
        // ->groupBy('tx_surat_jalan_parts.price')
        ->groupBy('cust.name')
        ->get();

        $data = [
            'sales_orders' => $qSO->toArray(),
            'surat_jalans' => $qSJ->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
