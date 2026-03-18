<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;

class DispSObyCustController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qSO = Tx_sales_order::select(
            'id',
            'sales_order_no'
        )
        ->where('sales_order_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function (Builder $query) {
            $query->select('tsop.order_id')
            ->from('tx_nota_retur_parts')
            ->leftJoin('tx_sales_order_parts AS tsop','tx_nota_retur_parts.sales_order_part_id','=','tsop.id')
            ->where('tx_nota_retur_parts.active','=','Y');
        })
        // ->where('customer_id','=',$request->cust_id)
        ->whereIn('id', function ($q1) use($request) {
            $q1->select('sales_order_id')
            ->from('tx_delivery_order_parts')
            ->where([
                'delivery_order_id'=>$request->fk_id,
                'active'=>'Y',
            ]);
        })
        ->where('active','=','Y')
        ->orderBy('sales_order_date','DESC')
        ->orderBy('created_at','DESC')
        ->get();

        $data = [
            'salesorders' => $qSO->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
