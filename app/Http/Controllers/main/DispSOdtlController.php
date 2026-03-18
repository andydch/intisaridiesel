<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tx_sales_order;

class DispSOdtlController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $so = Tx_sales_order::where('sales_order_no','=',$request->so_no)
        ->first();

        $data = [
            'sales_order' => $so->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
