<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_invoice;

class DispInvoicesPerCustController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $date = date_create(date("Y-m-d"));
        date_add($date,date_interval_create_from_date_string("-12 months"));

        $invoices = Tx_invoice::where('tax_invoice_no','<>',null)
        ->where('customer_id','=',$request->customer_id)
        ->where('approved_by','<>',null)
        ->where('canceled_by','=',null)
        ->where('active','=','Y')
        ->where('created_at','>',date_format($date,"Y-m-d"))
        ->get();
        $data = [
            'invoices' => $invoices->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
