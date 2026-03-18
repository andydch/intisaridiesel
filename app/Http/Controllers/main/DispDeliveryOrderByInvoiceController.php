<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tx_invoice;

class DispDeliveryOrderByInvoiceController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $delivery_orders = Tx_invoice::leftJoin('tx_delivery_orders AS tx_do','tx_invoices.delivery_order_id','=','tx_do.id')
        ->select(
            'tx_do.id AS tx_do_id',
            'tx_do.delivery_order_no',
        )
        ->where('tx_invoices.id','=',$request->invoice_id)
        ->where('tx_invoices.active','=','Y')
        ->where('tx_do.active','=','Y')
        ->get();
        $data = [
            'delivery_orders' => $delivery_orders->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
