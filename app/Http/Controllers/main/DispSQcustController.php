<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Models\Tx_sales_quotation;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;

class DispSQcustController extends Controller
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

        // $queryC = Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
        // ->where('tx_sales_orders.customer_id','=',$request->cid)
        // ->where('usr.branch_id','=',$userLogin->branch_id)
        // ->first();
        // if($queryC){
        //     $query = Tx_sales_quotation::leftJoin('userdetails AS usr','tx_sales_quotations.created_by','=','usr.user_id')
        //     ->select('tx_sales_quotations.*')
        //     ->whereNotIn('tx_sales_quotations.id', function (Builder $query) use ($queryC) {
        //         $query->select('sales_quotation_id')
        //             ->from('tx_sales_orders')
        //             ->where('sales_quotation_id','<>',$queryC->sales_quotation_id)
        //             ->where('sales_quotation_id','<>',null);
        //     })
        //     ->where('tx_sales_quotations.sales_quotation_no','NOT LIKE','%Draft%')
        //     ->where([
        //         'tx_sales_quotations.customer_id' => $request->cid,
        //         'tx_sales_quotations.active' => 'Y'
        //     ])
        //     ->where('usr.branch_id','=',$userLogin->branch_id)
        //     ->get();
        // }else{
        //     $query = Tx_sales_quotation::leftJoin('userdetails AS usr','tx_sales_quotations.created_by','=','usr.user_id')
        //     ->select('tx_sales_quotations.*')
        //     ->whereNotIn('tx_sales_quotations.id', function (Builder $query) {
        //         $query->select('sales_quotation_id')
        //             ->from('tx_sales_orders')
        //             ->where('sales_quotation_id','<>',null);
        //     })
        //     ->where('tx_sales_quotations.sales_quotation_no','NOT LIKE','%Draft%')
        //     ->where([
        //         'tx_sales_quotations.customer_id' => $request->cid,
        //         'tx_sales_quotations.active' => 'Y'
        //     ])
        //     ->where('usr.branch_id','=',$userLogin->branch_id)
        //     ->get();
        // }

        $query = Tx_sales_quotation::leftJoin('userdetails AS usr','tx_sales_quotations.created_by','=','usr.user_id')
        ->select('tx_sales_quotations.*')
        ->whereNotIn('tx_sales_quotations.id', function (Builder $query) {
            $query->select('sales_quotation_id')
                ->from('tx_sales_orders')
                ->where('sales_quotation_id','<>',null);
        })
        ->where('tx_sales_quotations.sales_quotation_no','NOT LIKE','%Draft%')
        ->where([
            'tx_sales_quotations.customer_id' => $request->cid,
            'tx_sales_quotations.active' => 'Y'
        ])
        // ->where('usr.branch_id','=',$userLogin->branch_id)
        ->get();

        $data = [
            'sq' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
