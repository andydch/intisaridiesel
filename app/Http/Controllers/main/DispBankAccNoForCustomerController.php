<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use App\Models\Mst_customer;
use Illuminate\Http\Request;

class DispBankAccNoForCustomerController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $customer_id = $request->customer_id;
        $payment_mode_id = $request->payment_mode_id;
        $payment_group = $request->payment_group;
        $branch_id = 0;

        $qCustomers = Mst_customer::where([
            'id'=>$customer_id,
            'active'=>'Y',
        ])
        ->first();
        if($qCustomers){
            $branch_id = $qCustomers->branch_id;
        }

        $query = Mst_coa::select(
            'id',
            'coa_name'
        )
        ->where(function($q) use($payment_mode_id, $payment_group, $branch_id){
            $q->whereIn('id', function($q1) use($payment_mode_id, $payment_group, $branch_id){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_details')
                ->where([
                    'auto_journal_id'=>$payment_group,
                    'method_id'=>$payment_mode_id,  // 1:cash/2:bank/3:customer deposit
                    'branch_id'=>$branch_id, // cabang
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`) IN (\'bank\',\'cash\',\'customer deposit\')');
            })
            ->orWhereIn('id', function($q2) use($payment_mode_id, $payment_group, $branch_id){
                $q2->select('coa_code_id')
                ->from('mst_automatic_journal_detail_exts')
                ->where([
                    'auto_journal_id'=>$payment_group,
                    'method_id'=>$payment_mode_id, // 1:cash/2:bank/3:customer deposit
                    'branch_id'=>$branch_id, // cabang
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`) IN (\'bank\',\'cash\',\'customer deposit\')');
            });
        })
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('coa_name','ASC')
        ->get();

        $data = [
            'bankaccno' => ($query?$query->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
