<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order;

class DispSObyDOController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_order = Tx_delivery_order::where('id','=',$request->delivery_order_id)
        ->where('active','=','Y')
        ->first();

        $data = [
            'delivery_order' => $delivery_order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
