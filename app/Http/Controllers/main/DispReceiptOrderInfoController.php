<?php

namespace App\Http\Controllers\main;

use App\Http\Controllers\Controller;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_purchase_order;

class DispReceiptOrderInfoController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $p_no = $request->p_no;
        $q = [];
        switch (substr($p_no, 0, 2)) {
            case 'MP':
                $q = Tx_purchase_memo::leftJoin('mst_globals AS supplierType', 'tx_purchase_memos.supplier_type_id', '=', 'supplierType.id')
                    ->leftJoin('mst_globals AS entityType', 'tx_purchase_memos.supplier_entity_type_id', '=', 'entityType.id')
                    ->leftJoin('mst_branches AS branch', 'tx_purchase_memos.branch_id', '=', 'branch.id')
                    ->select(
                        'tx_purchase_memos.supplier_name',
                        'tx_purchase_memos.supplier_id',
                        'supplierType.title_ind AS supplier_type_name',
                        'entityType.title_ind AS entity_type_name',
                        'branch.name AS branch_name',
                        'tx_purchase_memos.branch_address',
                    )
                    ->addSelect([
                        'currency_name' => Mst_global::select('title_ind')
                            ->where('id', '=', function ($query) {
                                $query->select('mst_supplier_bank_information.currency_id')
                                    ->from('mst_supplier_bank_information')
                                    ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                    ->where('mst_supplier_bank_information.active', '=', 'Y')
                                    ->limit(1);
                            })
                    ])
                    ->addSelect([
                        'currency_id' => Mst_global::select('id')
                            ->where('id', '=', function ($query) {
                                $query->select('mst_supplier_bank_information.currency_id')
                                    ->from('mst_supplier_bank_information')
                                    ->whereColumn('mst_supplier_bank_information.supplier_id', 'tx_purchase_memos.supplier_id')
                                    ->where('mst_supplier_bank_information.active', '=', 'Y')
                                    ->limit(1);
                            })
                    ])
                    ->where([
                        'tx_purchase_memos.memo_no' => $p_no
                    ])
                    ->get();
                break;
            case 'PO':
                $q = Tx_purchase_order::leftJoin('mst_globals AS supplierType', 'tx_purchase_orders.supplier_type_id', '=', 'supplierType.id')
                    ->leftJoin('mst_globals AS entityType', 'tx_purchase_orders.supplier_entity_type_id', '=', 'entityType.id')
                    ->leftJoin('mst_globals AS curr', 'tx_purchase_orders.currency_id', '=', 'curr.id')
                    ->leftJoin('mst_branches AS branch', 'tx_purchase_orders.branch_id', '=', 'branch.id')
                    ->select(
                        'tx_purchase_orders.supplier_name',
                        'tx_purchase_orders.supplier_id',
                        'curr.title_ind AS currency_name',
                        'curr.id AS currency_id',
                        'supplierType.title_ind AS supplier_type_name',
                        'entityType.title_ind AS entity_type_name',
                        'branch.name AS branch_name',
                        'tx_purchase_orders.branch_address',
                    )
                    ->where([
                        'tx_purchase_orders.purchase_no' => $p_no
                    ])
                    ->get();
                break;
            default:
                // code to be executed if n is different from all labels;
        }
        $data = [
            'receipt_order' => $q->toArray()
        ];
        return response()->json([
            $data
        ], 200);
    }
}
