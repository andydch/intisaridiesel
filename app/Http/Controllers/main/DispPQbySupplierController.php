<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_quotation;
use Illuminate\Database\Query\Builder;

class DispPQbySupplierController extends Controller
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

        $query = Tx_purchase_quotation::leftJoin('userdetails AS usr','tx_purchase_quotations.created_by','=','usr.user_id')
        ->select(
            'tx_purchase_quotations.id AS pq_id',
            'tx_purchase_quotations.quotation_no'
        )
        ->whereNotIn('tx_purchase_quotations.id', function (Builder $queryQ) {
            $queryQ->select('tx_order.quotation_id')
            ->from('tx_purchase_orders as tx_order')
            ->where('tx_order.quotation_id','<>',null)
            ->where('tx_order.active','=','Y');
            // ->where('tx_order.id','<>',$this->idQ);
        })
        ->where([
            'tx_purchase_quotations.is_draft' => 'N',
            'tx_purchase_quotations.active' => 'Y',
        ])
        ->where('tx_purchase_quotations.supplier_id','=',$request->supplier_id)
        ->where('usr.branch_id','=',$userLogin->branch_id)
        ->orderBy('tx_purchase_quotations.created_at','DESC')
        ->get();

        $data = [
            'qPQ' => $query->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
