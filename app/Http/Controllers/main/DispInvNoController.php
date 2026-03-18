<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Query\Builder;

class DispInvNoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->select(
                'tx_receipt_orders.id',
                'tx_receipt_orders.invoice_no',
            )
            ->where('tx_receipt_orders.receipt_no','NOT LIKE','%Draft%')
            ->where([
                'tx_receipt_orders.supplier_id' => $request->supplier_id,
                'tx_receipt_orders.active' => 'Y'
            ])
            ->whereRaw('tx_receipt_orders.created_at >= DATE_ADD(tx_receipt_orders.created_at, INTERVAL -12 MONTH)')    // part yg boleh di retur dg RO 12 bulan terakhir
            ->when($request->old_inv_no!='', function($q) use($request){
                $q->where('tx_receipt_orders.invoice_no','<>',$request->old_inv_no);
            })
            ->when($userLogin->is_director!='Y', function($q) use($userLogin){
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_receipt_orders.receipt_no','ASC')
            ->get();

            $data = [
                'invoice_no' => $query->toArray(),
            ];
            return response()->json([
                $data
            ], 200);
        }else{
            $data = [
                'invoice_no' => [],
            ];
            return response()->json([
                $data
            ], 200);
        }


        
    }
}
