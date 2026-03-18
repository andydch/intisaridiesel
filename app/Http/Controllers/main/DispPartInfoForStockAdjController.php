<?php

namespace App\Http\Controllers\main;

use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order_part;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispPartInfoForStockAdjController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Tx_qty_part::leftJoin('mst_parts AS msp','tx_qty_parts.part_id','=','msp.id')
        ->leftJoin('mst_globals AS mg_unit','msp.quantity_type_id','=','mg_unit.id')
        ->leftJoin('mst_globals AS mg_part_type','msp.part_type_id','=','mg_part_type.id')
        ->select(
            'msp.part_number',
            'msp.part_name',
            'msp.avg_cost',
            'tx_qty_parts.qty AS OH_qty',
            'mg_unit.string_val AS unit_name',
            'mg_part_type.string_val AS part_type_name',
            )
        ->addSelect(['SO_qty' => Tx_sales_order_part::selectRaw('IFNULL(SUM(tx_sales_order_parts.qty),0)')
            ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
            ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
            ->whereColumn('tx_sales_order_parts.part_id','tx_qty_parts.part_id')
            ->whereColumn('usr.branch_id','tx_qty_parts.branch_id')
            ->where('tx_sales_order_parts.active','=','Y')
            ->where('txso.active','=','Y')
            ->whereIn('tx_sales_order_parts.id', function (Builder $query) {
                $query->select('sales_order_part_id')
                ->from('tx_delivery_order_parts')
                ->where('active','=','Y');
            })
        ])
        ->where([
            'tx_qty_parts.part_id' => $request->part_id,
            'tx_qty_parts.branch_id' => $request->branch_id,
        ])
        ->first();
        $data = [
            'parts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
