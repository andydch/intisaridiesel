<?php

namespace App\Http\Controllers\main;

use App\Models\Tx_stock_transfer_part;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DispITstockmasterController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qIT = Tx_stock_transfer_part::leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
        ->leftJoin('mst_branches AS br_from','tx_stock.branch_from_id','=','br_from.id')
        ->leftJoin('mst_branches AS br_to','tx_stock.branch_to_id','=','br_to.id')
        ->select(
            'tx_stock.stock_transfer_no',
            'tx_stock.stock_transfer_date',
            'br_from.name AS branch_from',
            'br_to.name AS branch_to',
        )
        ->addSelect(['it_qty' => Tx_stock_transfer_part::selectRaw('SUM(qty)')
            ->whereColumn('stock_transfer_id','tx_stock.id')
            ->where('part_id','=',$request->part_id)
            ->where('active','=','Y')
        ])
        ->where('tx_stock_transfer_parts.part_id','=',$request->part_id)
        ->where('tx_stock.branch_to_id','=',$request->branch_id)
        ->where('tx_stock_transfer_parts.active','=','Y')
        ->where('tx_stock.approved_by','<>',null)
        ->where('tx_stock.received_by','=',null)
        ->where('tx_stock.active','=','Y')
        ->groupBy('tx_stock.id')
        ->groupBy('tx_stock.stock_transfer_no')
        ->groupBy('tx_stock.stock_transfer_date')
        ->groupBy('br_from.name')
        ->groupBy('br_to.name')
        ->get();

        $data = [
            'in_transits' => $qIT->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
