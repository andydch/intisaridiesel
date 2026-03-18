<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Tx_sales_order;
use Illuminate\Http\Request;

class DispSalesOrderInfoByIdController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $qSO = Tx_sales_order::where('id','=',$request->so_id)
        ->where('active','=','Y')
        ->first();

        $data = [
            'salesorders' => $qSO->toArray(),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
