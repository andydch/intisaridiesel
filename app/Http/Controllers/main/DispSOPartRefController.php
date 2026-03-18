<?php

namespace App\Http\Controllers\main;

use App\Models\Mst_part;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order_part;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;

class DispSOPartRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $so_id = $request->so_id;
        $part_id = $request->part_id;
        $branch_id = $request->branch_id;

        $query = Mst_part::leftJoin('tx_qty_parts', 'mst_parts.id', '=', 'tx_qty_parts.part_id')
        ->leftJoin('mst_globals as mg01', 'mst_parts.part_type_id', '=', 'mg01.id')
        ->leftJoin('mst_globals as mg02', 'mst_parts.quantity_type_id', '=', 'mg02.id')
        ->leftJoin('mst_globals as mg03', 'mst_parts.brand_id', '=', 'mg03.id')
        ->select(
            'mst_parts.id as part_id',
            'mst_parts.part_number',
            'mst_parts.part_name',
            'mst_parts.price_list',
            'mst_parts.final_cost',
            'mst_parts.final_price',
            'mst_parts.avg_cost',
            'tx_qty_parts.qty as total_qty',
            'mg01.title_ind as part_type_name',
            'mg02.title_ind as part_unit_name',
            'mg03.title_ind as brand_name',
        )
        ->selectRaw('(SELECT avg_cost 
            FROM v_log_avg_cost 
            WHERE part_id='.$part_id.' 
            AND updated_at<(SELECT created_at FROM tx_sales_orders WHERE id='.$so_id.') 
            ORDER BY updated_at DESC 
            LIMIT 1) as last_avg_cost')
        ->where([
            'mst_parts.id' => $part_id,
            'mst_parts.active' => 'Y',
        ])
        ->when(!is_null($branch_id), function($q) use($branch_id) {
            $q->where('tx_qty_parts.branch_id','=', $branch_id);
        })
        ->get();

        // sales order
        $qtySO = Tx_sales_order_part::leftJoin('tx_sales_orders AS txso','tx_sales_order_parts.order_id','=','txso.id')
        ->leftJoin('userdetails AS usr','tx_sales_order_parts.created_by','=','usr.user_id')
        ->whereNotIn('txso.id',function (Builder $query) {
            $query->select('tx_do_parts.sales_order_id')
            ->from('tx_delivery_order_parts as tx_do_parts')
            ->leftJoin('tx_delivery_orders as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
            ->where('tx_do_parts.active','=','Y')
            ->where('tx_do.active','=','Y');
        })
        ->where('tx_sales_order_parts.part_id','=',$part_id)
        ->where('tx_sales_order_parts.active','=','Y')
        ->where('txso.sales_order_no','NOT LIKE','%Draft%')
        // ->where('txso.need_approval','=','N')
        ->where('txso.active','=','Y')
        ->when($branch_id!=null, function($q) use($branch_id) {
            $q->whereRaw('((usr.branch_id='.$branch_id.' AND txso.branch_id IS null) OR txso.branch_id='.$branch_id.')');
        })
        ->sum('tx_sales_order_parts.qty');

        // surat jalan
        $qtySJ = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id','=','txsj.id')
        ->leftJoin('userdetails AS usr','tx_surat_jalan_parts.created_by','=','usr.user_id')
        ->whereNotIn('txsj.id',function (\Illuminate\Database\Query\Builder $query) {
            $query->select('tx_do_parts.sales_order_id')
            ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
            ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_do_parts.delivery_order_id','=','tx_do.id')
            ->where('tx_do_parts.active','=','Y')
            ->where('tx_do.active','=','Y');
        })
        ->where('tx_surat_jalan_parts.part_id','=',$part_id)
        ->where('tx_surat_jalan_parts.active','=','Y')
        ->where('txsj.surat_jalan_no','NOT LIKE','%Draft%')
        // ->where('txsj.need_approval','=','N')
        ->where('txsj.active','=','Y')
        ->when($branch_id!=null, function($q) use($branch_id) {
            $q->whereRaw('((usr.branch_id='.$branch_id.' AND txsj.branch_id IS null) OR txsj.branch_id='.$branch_id.')');
        })
        ->sum('tx_surat_jalan_parts.qty');

        $data = [
            'parts' => $query->toArray(),
            'soQty' => $qtySO+$qtySJ,
        ];
        return response()->json([
            $data
        ], 200);
    }
}
