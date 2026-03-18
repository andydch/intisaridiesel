<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Mst_customer;
use App\Models\Tx_delivery_order;

class DispCustPerFKController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $start_date = explode("/", $request->start_date);
        $end_date = explode("/", $request->end_date);

        $custs = Mst_customer::whereIn('id', function($q) use($start_date,$end_date){
            $q->select('customer_id')
            ->from('tx_delivery_orders')
            ->whereRaw('delivery_order_no NOT LIKE \'%Draft%\'')
            ->whereRaw('delivery_order_date>=\''.$start_date[2].'-'.$start_date[1].'-'.$start_date[0].'\'
                AND delivery_order_date<=\''.$end_date[2].'-'.$end_date[1].'-'.$end_date[0].'\'')
            ->whereRaw('faktur_dl_date IS NULL')
            ->where([
                'active'=>'Y',
            ])
            ->orderBy('delivery_order_date','DESC')
            ->orderBy('created_at','DESC');
        })
        ->where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'custs' => $custs->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
