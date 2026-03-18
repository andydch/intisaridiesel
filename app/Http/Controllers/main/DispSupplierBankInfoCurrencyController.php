<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mst_supplier_bank_information;

class DispSupplierBankInfoCurrencyController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $query = Mst_supplier_bank_information::leftJoin('mst_globals AS currency', 'mst_supplier_bank_information.currency_id', '=', 'currency.id')
            ->select(
                'mst_supplier_bank_information.currency_id',
                'currency.title_ind AS currencyName'
            )
            ->where([
                'supplier_id' => $request->supplier_id,
                'mst_supplier_bank_information.active' => 'Y'
            ])
            ->orderBy('currency.title_ind', 'ASC')
            ->get();
        $data = [
            'supplier_currency' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
