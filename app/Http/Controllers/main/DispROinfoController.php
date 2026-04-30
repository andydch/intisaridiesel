<?php

namespace App\Http\Controllers\main;

use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_receipt_order_part;
// use Illuminate\Database\Query\Builder;

class DispROinfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)
        ->first();
        if ($userLogin){
            $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->leftJoin('mst_branches','usr.branch_id','=','mst_branches.id')
            ->select(
                'tx_receipt_orders.receipt_no',
                'tx_receipt_orders.journal_type_id',
                'mst_branches.name AS branch_name'
            )
            ->where([
                'tx_receipt_orders.id' => $request->ro_id,
                'tx_receipt_orders.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin){
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->first();

            $queryPart = Tx_receipt_order_part::leftJoin('userdetails AS usr','tx_receipt_order_parts.created_by','=','usr.user_id')
            ->leftJoin('mst_parts','tx_receipt_order_parts.part_id','=','mst_parts.id')
            ->select(
                'tx_receipt_order_parts.po_mo_no',
                'tx_receipt_order_parts.part_id',
                // 'tx_receipt_order_parts.qty',
                DB::raw('SUM(tx_receipt_order_parts.qty) as qty'),
                'tx_receipt_order_parts.final_cost',
                'mst_parts.part_number',
                'mst_parts.part_name'
            )
            // ->whereNotIn('part_id', function($q) use($request){
            //     $q->select('part_id')
            //     ->from('tx_purchase_retur_parts')
            //     ->whereIn('purchase_retur_id', function($q1) use($request){
            //         $q1->select('id')
            //         ->from('tx_purchase_returs')
            //         ->where('receipt_order_id', '=', $request->ro_id);
            //     })
            //     ->where('active', '=', 'Y');
            // })
            ->where([
                'tx_receipt_order_parts.receipt_order_id' => $request->ro_id,
                'tx_receipt_order_parts.active' => 'Y'
            ])
            ->groupBy(
                'tx_receipt_order_parts.po_mo_no',
                'tx_receipt_order_parts.part_id',
                'tx_receipt_order_parts.final_cost',
                'mst_parts.part_number',
                'mst_parts.part_name'
            )
            ->get();

            $data = [
                'ro_info' => ($query?$query->toArray():[]),
                'ro_part_info' => ($queryPart?$queryPart->toArray():[]),
            ];
            return response()->json([
                $data
            ], 200);
        }else{
            $data = [
                'ro_info' => [],
                'ro_part_info' => [],
            ];
            return response()->json([
                $data
            ], 200);
        }        
    }
}
