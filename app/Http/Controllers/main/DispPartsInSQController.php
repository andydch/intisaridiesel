<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_sales_quotation_part;

class DispPartsInSQController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Tx_sales_quotation_part::where([
            'sales_quotation_id' => $request->sId,
            'active' => 'Y'
        ])
        ->get();
        $data = [
            'sParts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
