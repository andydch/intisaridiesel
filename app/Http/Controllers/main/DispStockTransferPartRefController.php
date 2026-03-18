<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_part;

class DispStockTransferPartRefController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_part::leftJoin('tx_qty_parts', 'mst_parts.id', '=', 'tx_qty_parts.part_id')
            ->leftJoin('mst_globals as mg01', 'mst_parts.part_type_id', '=', 'mg01.id')
            ->leftJoin('mst_globals as mg02', 'mst_parts.quantity_type_id', '=', 'mg02.id')
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
            )
            ->where([
                'mst_parts.id' => $request->part_id,
                // 'tx_qty_parts.branch_id' => $request->branch_id,
                'mst_parts.active' => 'Y'
            ])
            ->when(request()->has('branch_id'), function($q) use($request) {
                $q->where('tx_qty_parts.branch_id','=', $request->branch_id);
            })
            ->get();
        $data = [
            'parts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
