<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_supplier_bank_information;

class DispSupplierBankByIdController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_supplier_bank_information::select(
            'bank_name',
            'bank_address',
            'account_name',
            'account_no',
        )
        ->where([
            'supplier_id'=>$request->id,
        ])
        ->orderBy('bank_name','ASC')
        ->get();
        $data = [
            'banks' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
