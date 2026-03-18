<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_quotation_part;

class DispPartsInPQController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Tx_purchase_quotation_part::where([
            'quotation_id' => $request->qId,
            'active' => 'Y'
        ])
        ->get();
        $data = [
            'qParts' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
