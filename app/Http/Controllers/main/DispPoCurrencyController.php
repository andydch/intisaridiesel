<?php

namespace App\Http\Controllers\main;

use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use App\Http\Controllers\Controller;

class DispPoCurrencyController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        // ->first();

        $order = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
        ->leftJoin('mst_globals','tx_purchase_orders.currency_id','=','mst_globals.id')
        ->leftJoin('mst_branches','tx_purchase_orders.branch_id','=','mst_branches.id')
        ->select(
            'tx_purchase_orders.currency_id AS curr_id',
            // 'tx_purchase_orders.courier_id',
            'mst_globals.title_ind',
            'mst_globals.string_val AS currency_symbol',
            'mst_branches.name AS shipto_name',
            'mst_branches.id AS shipto_id',
            )
        ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
        ->where([
            'tx_purchase_orders.purchase_no' => $request->po_no,
            'tx_purchase_orders.active' => 'Y'
        ])
        // ->where('usr.branch_id','=',$userLogin->branch_id)
        ->first();
        $data = [
            'curr' => (is_null($order)?[]:$order->toArray())
        ];
        return response()->json([
            $data
        ], 200);
    }
}
