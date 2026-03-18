<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax;

class DispDOnonTaxController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $kwitansi_id = (isset($request->kwitansi_id))?$request->kwitansi_id:'';
        $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$request->customer_id)
        ->where('delivery_order_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function($query) use ($kwitansi_id) {
            $query->select('tx_kwitansi_details.np_id')
            ->from('tx_kwitansi_details')
            ->leftJoin('tx_kwitansis','tx_kwitansi_details.kwitansi_id','=','tx_kwitansis.id')
            ->when($kwitansi_id!='', function($q) use ($kwitansi_id) {
                $q->where('tx_kwitansi_details.kwitansi_id','<>', $kwitansi_id);
            })
            ->where('tx_kwitansi_details.active','=','Y')
            ->where('tx_kwitansis.active','=','Y');
        })
        ->where('active','=','Y')
        ->orderBy('delivery_order_date','DESC')
        ->orderBy('created_at','DESC')
        ->get();

        $data = [
            'delivery_order' => $delivery_order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
