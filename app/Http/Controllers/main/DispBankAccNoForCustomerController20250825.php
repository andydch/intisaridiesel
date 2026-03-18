<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use App\Models\Mst_customer;
use Illuminate\Http\Request;

class DispBankAccNoForCustomerController20250825 extends Controller
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
        ->where(function($q) use($payment_group,$branch_id){
            $q->whereIn('id', function($q1) use($payment_group,$branch_id){
                $q1->select('coa_code_id')
                ->from('mst_automatic_journal_details')
                ->where([
                    'auto_journal_id'=>$payment_group,
                    'method_id'=>2, // bank
                    'branch_id'=>$branch_id, // cabang
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'bank\'');;
            })
            ->orWhereIn('id', function($q2) use($payment_group,$branch_id){
                $q2->select('coa_code_id')
                ->from('mst_automatic_journal_detail_exts')
                ->where([
                    'auto_journal_id'=>$payment_group,
                    'method_id'=>2, // bank
                    'branch_id'=>$branch_id, // cabang
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'bank\'');;
            });
        })
        ->where([
            // 'coa_level' => 5,
            'active' => 'Y'
        ])
        ->orderBy('coa_name','ASC')
        ->get();


        // $query = Mst_coa::select(
        //     'id',
        //     'coa_name'
        // )
        // ->where(function($q) use($payment_group,$customer_id){
        //     $q->whereIn('id', function($q1) use($payment_group,$customer_id){
        //         $q1->select('coa_code_id')
        //         ->from('mst_automatic_journal_details')
        //         ->where([
        //             'auto_journal_id'=>$payment_group,
        //             'method_id'=>2, // bank
        //             'active'=>'Y',
        //         ])
        //         ->where(function($q2) use($customer_id) {
        //             $q2->whereIn('branch_id', function($q3) use($customer_id) {
        //                 $q3->select('branch_id')
        //                 ->from('tx_delivery_orders')
        //                 ->where([
        //                     'customer_id'=>$customer_id,
        //                     'active'=>'Y',
        //                 ]);
        //             })
        //             ->orWhereIn('branch_id', function($q3) use($customer_id) {
        //                 $q3->select('branch_id')
        //                 ->from('tx_delivery_order_non_taxes')
        //                 ->where([
        //                     'customer_id'=>$customer_id,
        //                     'active'=>'Y',
        //                 ]);
        //             });
        //         })
        //         ->whereRaw('LOWER(`desc`)=\'bank\'');
        //     })
        //     ->orWhereIn('id', function($q1) use($payment_group,$customer_id){
        //         $q1->select('coa_code_id')
        //         ->from('mst_automatic_journal_detail_exts')
        //         ->where([
        //             'auto_journal_id'=>$payment_group,
        //             'method_id'=>2, // bank
        //             'active'=>'Y',
        //         ])
        //         ->where(function($q2) use($customer_id) {
        //             $q2->whereIn('branch_id', function($q3) use($customer_id) {
        //                 $q3->select('branch_id')
        //                 ->from('tx_delivery_orders')
        //                 ->where([
        //                     'customer_id'=>$customer_id,
        //                     'active'=>'Y',
        //                 ]);
        //             })
        //             ->orWhereIn('branch_id', function($q3) use($customer_id) {
        //                 $q3->select('branch_id')
        //                 ->from('tx_delivery_order_non_taxes')
        //                 ->where([
        //                     'customer_id'=>$customer_id,
        //                     'active'=>'Y',
        //                 ]);
        //             });
        //         })
        //         ->whereRaw('LOWER(`desc`)=\'bank\'');
        //     });
        // })
        // ->where([
        //     'coa_level' => 5,
        //     'active' => 'Y'
        // ])
        // ->orderBy('coa_name','ASC')
        // ->get();

        $data = [
            'bankaccno' => ($query?$query->toArray():[]),
        ];
        return response()->json([
            $data
        ], 200);
    }
}
