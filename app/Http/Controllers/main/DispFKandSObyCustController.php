<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Models\Tx_delivery_order;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispFKandSObyCustController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qFK = Tx_delivery_order::select(
            'id',
            'delivery_order_no'
        )
        ->where('delivery_order_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function (Builder $query) {
            $query->select('delivery_order_id')
            ->from('tx_nota_returs')
            ->where('active','=','Y');
        })
        ->where('customer_id','=',$request->cust_id)
        ->where('active','=','Y')
        ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
        ->orderBy('delivery_order_date','DESC')
        ->orderBy('created_at','DESC')
        ->get();

        // $qSO = Tx_sales_order::select(
        //     'id',
        //     'sales_order_no'
        // )
        // ->where('sales_order_no','NOT LIKE','%Draft%')
        // ->whereNotIn('id', function (Builder $query) {
        //     $query->select('tsop.order_id')
        //     ->from('tx_nota_retur_parts')
        //     ->leftJoin('tx_sales_order_parts AS tsop','tx_nota_retur_parts.sales_order_part_id','=','tsop.id')
        //     ->where('tx_nota_retur_parts.active','=','Y');
        // })
        // ->where('customer_id','=',$request->cust_id)
        // ->where('active','=','Y')
        // ->orderBy('sales_order_date','DESC')
        // ->orderBy('created_at','DESC')
        // ->get();

        $data = [
            'fakturs' => $qFK->toArray(),
            // 'salesorders' => $qSO->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
