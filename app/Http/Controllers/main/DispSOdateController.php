<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Database\Query\Builder;

class DispSOdateController extends Controller
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

        $sales_order = Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
        ->selectRaw('DISTINCT DATE_FORMAT(sales_order_date, "%d/%m/%Y") as sales_order_date')
        ->where('tx_sales_orders.sales_order_no','NOT LIKE','%Draft%')
        ->whereNotIn('tx_sales_orders.id', function (Builder $queryQ) {
            $queryQ->select('tx_do_part.sales_order_id')
                ->from('tx_delivery_order_parts as tx_do_part')
                ->where('tx_do_part.active','=','Y');
        })
        ->where(function($query) {
            $query->where('tx_sales_orders.approved_by','<>',null)
                ->orWhere(function($queryA) {
                $queryA->where('tx_sales_orders.approved_by','=',null)
                    ->where('tx_sales_orders.need_approval','=','N');
            });
        })
        ->where([
            'tx_sales_orders.customer_id' => $request->customer_id,
            'tx_sales_orders.is_vat' => 'Y',
            'tx_sales_orders.active' => 'Y',
        ])
        ->when($userLogin->is_director=='N', function($query) use($userLogin) {
            $query->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->get();

        $data = [
            'sales_order_date' => $sales_order->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
