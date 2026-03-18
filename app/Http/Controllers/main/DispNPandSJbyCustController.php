<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Database\Query\Builder;

class DispNPandSJbyCustController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qFK = Tx_delivery_order_non_tax::select(
            'id',
            'delivery_order_no'
        )
        ->where('delivery_order_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function (Builder $query) {
            $query->select('delivery_order_id')
            ->from('tx_nota_retur_non_taxes')
            ->where('active','=','Y');
        })
        ->where('customer_id','=',$request->cust_id)
        ->where('active','=','Y')
        ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
        ->orderBy('delivery_order_no','ASC')
        ->get();

        $data = [
            'notapenjualans' => $qFK->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
