<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Tx_receipt_order;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_purchase_order;
use App\Models\Tx_qty_part;
use App\Models\Mst_part;
use App\Models\Mst_supplier;
use App\Models\Mst_global;
use App\Models\Mst_courier;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptOrderApprovalController extends Controller
{
    protected $title = 'Receipt Order - Approval';
    protected $folder = 'receipt-order-approval';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userLogin->is_director=='Y'){
            $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->select('tx_receipt_orders.*')
            ->addSelect(['total_price' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty*tx_receipt_order_parts.part_price)')
                ->whereColumn('tx_receipt_order_parts.receipt_order_id','tx_receipt_orders.id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where('tx_receipt_orders.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_receipt_orders.active','=','Y')
            ->orderBy('tx_receipt_orders.receipt_date', 'DESC')
            ->orderBy('tx_receipt_orders.created_at', 'DESC')
            ->get();
        }else{
            $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->select('tx_receipt_orders.*')
            ->addSelect(['total_price' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty*tx_receipt_order_parts.part_price)')
                ->whereColumn('tx_receipt_order_parts.receipt_order_id','tx_receipt_orders.id')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where('tx_receipt_orders.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_receipt_orders.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_receipt_orders.receipt_date', 'DESC')
            ->orderBy('tx_receipt_orders.created_at', 'DESC')
            ->get();
        }
        $data = [
            'orders' => $query,
            'title' => $this->title,
            'folder' => $this->folder
        ];
        return view('tx.'.$this->folder.'.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();
        $querySupplier = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
            ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $query = Tx_receipt_order::where('id', '=', $id)
            ->first();
        if ($query) {

            $order = [];
            if(old('supplier_id')){
                $memo = Tx_purchase_memo::select('memo_no AS order_no')
                    ->where('memo_no','NOT LIKE','%Draft%')
                    ->where([
                        'supplier_id' => old('supplier_id'),
                        'active' => 'Y'
                    ]);
                $order = Tx_purchase_order::select('purchase_no AS order_no')
                    ->where('purchase_no','NOT LIKE','%Draft%')
                    ->where([
                        'supplier_id' => old('supplier_id'),
                        'active' => 'Y'
                    ])
                    ->union($memo)
                    ->get();
            }else{
                $memo = Tx_purchase_memo::select('memo_no AS order_no')
                    ->where('memo_no','NOT LIKE','%Draft%')
                    ->where([
                        'supplier_id' => $query->supplier_id,
                        'active' => 'Y'
                    ]);
                $order = Tx_purchase_order::select('purchase_no AS order_no')
                    ->where('purchase_no','NOT LIKE','%Draft%')
                    ->where([
                        'supplier_id' => $query->supplier_id,
                        'active' => 'Y'
                    ])
                    ->union($memo)
                    ->get();
            }

            $query_part = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
                ->orderBy('created_at','ASC')
                ->get();
            $query_part_count = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
                ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'weighttype' => $weighttype,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $query_part_count),
                'vat' => $vat,
                'get_po_pm_no' => $order,
                'ro' => $query,
                'ro_part' => $query_part,
                'qCurrency' => $qCurrency,
            ];

            return view('tx.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // ambil info user termasuk cabang dari user pembuat RO
        $qUser = Tx_receipt_order::leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
        ->select('userdetails.branch_id AS user_branch_id')
        ->where('tx_receipt_orders.id','=',$id)
        ->first();

        if($request->order_appr == 'A'){
            $checkAppr = Tx_receipt_order::where([
                'id' => $id
            ])
            ->where('approved_by','=',null)
            ->first();
            if($checkAppr){
                // set received for purchase memo
                $memos = explode(",",$checkAppr->po_or_pm_no);
                foreach($memos as $memo){
                    if(strpos('cek'.$memo,"MO")>0){
                        $updMemo = Tx_purchase_memo::where('memo_no','=',$memo)
                        ->update([
                            'is_received' => 'Y',
                            'received_at' => now(),
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }

                $queryPart = Tx_receipt_order_part::where([
                    'receipt_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();
                if ($queryPart){
                    $updAppr = Tx_receipt_order::where([
                        'id' => $id
                    ])
                    ->where('approved_by','=',null)
                    ->update([
                        'approved_by' => Auth::user()->id,
                        'approved_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null
                    ]);

                    foreach($queryPart as $qP){
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.avg_cost',
                            'mst_parts.price_list',
                            'tx_qty_parts.qty'
                        )
                        ->where([
                            'mst_parts.id' => $qP->part_id
                        ])
                        ->first();
                        if($queryMstPart){
                            $branch_id_set_by_director = null;

                            // ambil branch_id dari purchase memo yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_memo::where('memo_no','=',$qP->po_mo_no)
                            ->where('branch_id','<>',null)
                            ->where('active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id;
                            }

                            // ambil branch_id dari purchase order yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_order::where('purchase_no','=',$qP->po_mo_no)
                            ->where('active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id;
                            }

                            // cek keberadaan part di master part
                            $lastQty = is_null($queryMstPart->qty)?0:$queryMstPart->qty;
                            $lastPrice = ($queryMstPart->avg_cost==0)?$queryMstPart->price_list:$queryMstPart->avg_cost;
                            if(($lastQty+$qP->qty)>0){
                                $avg_cost = (($lastQty*$lastPrice)+($qP->qty*$qP->part_price))/($lastQty+$qP->qty);
                            }

                            // update RO parts
                            $updTxRoPart = Tx_receipt_order_part::where('id','=',$qP->id)
                            ->update([
                                'avg_cost' => $avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master part
                            $upqMstPart = Mst_part::where([
                                'id' => $queryMstPart->part_id_tmp
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'initial_cost' => ($queryMstPart->initial_cost==0)?$qP->part_price:$queryMstPart->initial_cost,
                                'final_cost' => $qP->part_price,
                                'total_cost' => ($lastQty+$qP->qty)*$avg_cost,
                                // 'total_sales' => ($lastQty+$qP->qty)*$queryMstPart->final_price,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master qty
                            $qtyPart = Tx_qty_part::where([
                                'part_id' => $queryMstPart->part_id_tmp,
                                'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director
                            ])
                            ->first();
                            if($qtyPart){
                                $updQty = Tx_qty_part::where([
                                    'part_id' => $queryMstPart->part_id_tmp,
                                    'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director
                                ])
                                ->update([
                                    'qty' => $lastQty+$qP->qty,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }else{
                                // insert
                                $qtyPartIns = Tx_qty_part::create([
                                    'part_id' => $queryMstPart->part_id_tmp,
                                    'qty' => $qP->qty,
                                    'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director,
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }
                        }
                    }
                }
            }
        }

        if($request->order_appr == 'R'){
            $q = Tx_receipt_order::where([
                'id' => $id
            ])
            ->where('approved_by','<>',null)
            ->first();
            if($q){
                // set received for purchase memo
                $memos = explode(",",$q->po_or_pm_no);
                foreach($memos as $memo){
                    if(strpos('cek'.$memo,"MO")>0){
                        $updMemo = Tx_purchase_memo::where('memo_no','=',$memo)
                        ->update([
                            'is_received' => 'N',
                            'received_at' => null,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }

                // cek apakah RO sudah approved sebelumnya
                $queryPart = Tx_receipt_order_part::where([
                    'receipt_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();
                if ($queryPart){
                    foreach($queryPart as $qP){
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.price_list',
                            'tx_qty_parts.qty'
                        )
                        ->where([
                            'mst_parts.id' => $qP->part_id
                        ])
                        ->first();
                        if($queryMstPart){
                            $branch_id_set_by_director = null;

                            // ambil branch_id dari purchase memo yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_memo::where('memo_no','=',$qP->po_mo_no)
                            ->where('branch_id','<>',null)
                            ->where('active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id;
                            }

                            // ambil branch_id dari purchase order yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_order::where('purchase_no','=',$qP->po_mo_no)
                            ->where('active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id;
                            }

                            $lastQty = is_null($queryMstPart->qty)?0:$queryMstPart->qty;

                            $qtyPart = Tx_qty_part::where([
                                'part_id' => $queryMstPart->part_id_tmp,
                                'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director
                            ])
                                ->first();
                            if($qtyPart){
                                $updQty = Tx_qty_part::where([
                                    'part_id' => $queryMstPart->part_id_tmp,
                                    'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director
                                ])
                                ->update([
                                    'qty' => $lastQty-$qP->qty,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }
                        }
                    }
                }
            }

            $updAppr = Tx_receipt_order::where([
                'id' => $id
            ])
            ->where('canceled_by','=',null)
            ->update([
                'approved_by' => null,
                'approved_at' => null,
                'canceled_by' => Auth::user()->id,
                'canceled_at' => now()
            ]);
        }

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_receipt_order)
    {
        //
    }
}
