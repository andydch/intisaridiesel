<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_receipt_order;
use App\Rules\CheckDupInvoiceNo;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_order;
use App\Rules\ApprovalCheckingRO;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
// use Illuminate\Database\Query\Builder;
use App\Models\Tx_purchase_order_part;
use App\Rules\CheckAmountEqualWithTotal;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Rules\ValidateExchangeRateForSupplierType;

class ReceiptOrderController extends Controller
{
    protected $title = 'Receipt Order';
    protected $folder = 'receipt-order';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
        ->select('tx_receipt_orders.*')
        ->addSelect(['total_price' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty*tx_receipt_order_parts.part_price)')
            ->whereColumn('tx_receipt_order_parts.receipt_order_id','tx_receipt_orders.id')
            ->where('tx_receipt_order_parts.active','=','Y')
        ])
        ->where('tx_receipt_orders.active','=','Y')
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_receipt_orders.receipt_no', 'DESC')
        ->orderBy('tx_receipt_orders.created_at', 'DESC');

        $data = [
            'orders' => $query->get(),
            'ordersCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
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
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
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

        $order = [];
        if(old('supplier_id')){
            $memo = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
            ->select('tx_purchase_memos.memo_no AS order_no')
            ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%')
            ->addSelect(['memo_po_qty' => Tx_purchase_memo_part::selectRaw('SUM(tx_purchase_memo_parts.qty)')
                ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
                ->where('tx_purchase_memo_parts.active','=','Y')
            ])
            ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
                ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_memos.memo_no')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where([
                'tx_purchase_memos.supplier_id' => old('supplier_id'),
                'tx_purchase_memos.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            });

            $order = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
            ->select('tx_purchase_orders.purchase_no AS order_no')
            ->addSelect(['memo_po_qty' => Tx_purchase_order_part::selectRaw('SUM(tx_purchase_order_parts.qty)')
                ->whereColumn('tx_purchase_order_parts.order_id','tx_purchase_orders.id')
                ->where('tx_purchase_order_parts.active','=','Y')
            ])
            ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
                ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_orders.purchase_no')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
            ->where('tx_purchase_orders.approved_by','<>',null)
            ->where([
                'tx_purchase_orders.supplier_id' => old('supplier_id'),
                'tx_purchase_orders.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->union($memo)
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'querySupplier' => $querySupplier,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'vat' => $vat,
            'get_po_pm_no' => $order,
            'qCurrency' => $qCurrency,
        ];

        return view('tx.'.$this->folder.'.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validateInput = [
            'supplier_id' => 'required|numeric',
            'invoice_no' => ['required',new CheckDupInvoiceNo($request->supplier_id,$request->invoice_no,0)],
            // 'invoice_no' => 'required|unique:App\Models\Tx_receipt_order,invoice_no',
            'invoice_amount' => ['required', new NumericCustom('Invoice Amount'), new CheckAmountEqualWithTotal($request->lastTotalAmountTmp)],
            'exc_rate' => [new NumericCustom('Exchange Rate'), new ValidateExchangeRateForSupplierType($request->supplier_id),'nullable'],
            'bl_no' => 'required',
            'gross_weight' => [new NumericCustom('Gross Weight'),'nullable'],
            'measurement' => [new NumericCustom('Measurement'),'nullable'],
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'bl_no.required' => 'The B/L No field is required.',
            'weight_type_id01.numeric' => 'Please select a valid weight type',
            'weight_type_id02.numeric' => 'Please select a valid weight type',
            'courier_id.numeric' => 'Please select a valid ship by',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty'.$i => 'required|numeric|lte:qty_on_po'.$i.'|min:0',
                    ];
                    $errShipmentMsg = [
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'qty'.$i.'.lte' => 'The qty must be less than '.$request['qty_on_po'.$i].'.',
                        'qty'.$i.'.min' => 'The qty must be at least 0.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $draft_at = null;
            $draft_to_created_at = null;
            $identityName = 'tx_receipt_orders-draft';
            if($request->is_draft=='Y'){
                $draft_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }
                $order_no = ENV('P_RECEIPT_ORDER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_receipt_orders';
            if($request->is_draft!='Y'){
                $draft_to_created_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $order_no = ENV('P_RECEIPT_ORDER').date('y').'-'.$zero.strval($newInc);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $ins = Tx_receipt_order::create([
                'receipt_no' => $order_no,
                'receipt_date' => date("Y-m-d"),
                'po_or_pm_no' => $request->po_pm_no_all,
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'currency_id' => $request->currency_id,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'branch_id' => $request->shipto_id,
                'courier_id' => is_numeric($request->courier_id)?$request->courier_id:null,
                'invoice_no' => $request->invoice_no,
                'invoice_amount' => GlobalFuncHelper::moneyValidate($request->invoice_amount),
                'exchange_rate' => ($request->exc_rate!='')?GlobalFuncHelper::moneyValidate($request->exc_rate):null,
                'bl_no' => $request->bl_no,
                'vessel_no' => $request->vessel_no,
                'weight_type_id01' => is_numeric($request->weight_type_id01)?$request->weight_type_id01:null,
                'weight_type_id02' => is_numeric($request->weight_type_id02)?$request->weight_type_id02:null,
                'gross_weight' => !is_numeric($request->gross_weight)?null:GlobalFuncHelper::moneyValidate($request->gross_weight),
                'measurement' => !is_numeric($request->measurement)?null:GlobalFuncHelper::moneyValidate($request->measurement),
                'remark' => $request->remark,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            // ambil info user termasuk cabang dari user pembuat RO
            $qUser = Tx_receipt_order::leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
            ->select('userdetails.branch_id AS user_branch_id')
            ->where('tx_receipt_orders.id','=',$maxId)
            ->first();

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

            $totalQty = 0;
            $totalPrice = 0;
            $is_partial_received_last = 'N';
            for($lastIdx=0;$lastIdx<$request->totalRow;$lastIdx++){
                if($request['qty'.$lastIdx]){
                    $price_fob = 0;
                    $price_local = 0;
                    $total_fob = 0;
                    $total_local = 0;
                    if($qSupplier->supplier_type_id==10){
                        // international - update price
                        $price_fob = $request['price_fob_val'.$lastIdx];
                        $price_local = $request['price_fob_val'.$lastIdx]*GlobalFuncHelper::moneyValidate($request->exc_rate);
                        $total_fob = $request['qty'.$lastIdx]*$price_fob;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_fob;
                    }
                    if($qSupplier->supplier_type_id==11){
                        // lokal - update price
                        $price_fob = 0;
                        $price_local = $request['price_local_val'.$lastIdx];
                        $total_fob = 0;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_local;
                    }

                    $is_partial_received = 'N';
                    if($request['qty_on_po'.$lastIdx]>$request['qty'.$lastIdx]){
                        $is_partial_received = 'Y';
                        $is_partial_received_last = 'Y';
                    }

                    $totalQty += $request['qty'.$lastIdx];
                    $insPart = Tx_receipt_order_part::create([
                        'receipt_order_id' => $maxId,
                        'po_mo_no' => $request['po_mo_no'.$lastIdx],
                        'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                        'part_id' => $request['part_id'.$lastIdx],
                        'qty' => $request['qty'.$lastIdx],
                        'qty_on_po' => $request['qty_on_po'.$lastIdx],
                        'part_price' => $price_local,
                        'final_fob' => $price_fob,
                        'final_cost' => $price_local,
                        'total_fob_price' => $total_fob,
                        'total_price' => $total_local,
                        'is_partial_received' => $is_partial_received,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);

                    if(strpos($order_no,"Draft")==0){
                        // update qty and all price jika bukan DRAFT
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.avg_cost',
                            'mst_parts.price_list',
                            // 'tx_qty_parts.qty',
                            'tx_qty_parts.branch_id',
                        )
                        ->addSelect([
                            'qty' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->where('tx_qty_parts.branch_id','=',$qUser->user_branch_id)
                            ->limit(1)
                        ])
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_id'.$lastIdx],
                            'tx_qty_parts.branch_id' => $qUser->user_branch_id,
                        ])
                        ->first();
                        if($queryMstPart){
                            // ambil informasi part yang masuk
                            // cek keberadaan part di master part
                            $lastQty = is_null($queryMstPart->qty_total)?0:$queryMstPart->qty_total;
                            $lastQtyPerBranch = is_null($queryMstPart->qty)?0:$queryMstPart->qty;
                            $lastPrice = ($queryMstPart->avg_cost==0)?$queryMstPart->price_list:$queryMstPart->avg_cost;
                            $avg_cost = (($lastQty*$lastPrice)+($request['qty'.$lastIdx]*$price_local))/($lastQty+$request['qty'.$lastIdx]);

                            // branch - start
                            $branch_id_set_by_director = null;
                            // ambil branch_id dari purchase memo yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
                            ->select(
                                'usr.branch_id as branch_id_tmp'
                            )
                            ->where('tx_purchase_memos.memo_no','=',$request['po_mo_no'.$lastIdx])
                            ->where('tx_purchase_memos.active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id_tmp;
                            }
                            // ambil branch_id dari purchase order yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
                            ->select(
                                'usr.branch_id as branch_id_tmp'
                            )
                            ->where('tx_purchase_orders.purchase_no','=',$request['po_mo_no'.$lastIdx])
                            ->where('tx_purchase_orders.active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id_tmp;
                            }
                            // branch - end

                            // update avg cost di part RO
                            $updTxRoPart = Tx_receipt_order_part::where([
                                'receipt_order_id' => $maxId,
                                'po_mo_no' => $request['po_mo_no'.$lastIdx],
                                'part_id' => $request['part_id'.$lastIdx],
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master qty utk masing2 part
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
                                    'qty' => $lastQtyPerBranch+$request['qty'.$lastIdx],
                                    'updated_by' => Auth::user()->id
                                ]);
                            }else{
                                // insert
                                $qtyPartIns = Tx_qty_part::create([
                                    'part_id' => $queryMstPart->part_id_tmp,
                                    'qty' => $request['qty'.$lastIdx],
                                    'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director,
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }

                            // update master part terhadap part terkait
                            $upqMstPart = Mst_part::where([
                                'id' => $queryMstPart->part_id_tmp
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'initial_cost' => $queryMstPart->avg_cost,
                                // 'initial_cost' => ($queryMstPart->initial_cost==0)?$price_local:$queryMstPart->initial_cost,
                                'final_cost' => $price_local,
                                'total_cost' => ($lastQty+$request['qty'.$lastIdx])*$avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                }
            }

            if($is_partial_received_last=='Y'){
                // jika salah satu part ber status partial received (Y)
                // maka part yang lain ber status partial received (Y) juga
                $updPartialReceived = Tx_receipt_order_part::where([
                    'receipt_order_id' => $maxId,
                    'active' => 'Y'
                ])
                ->update([
                    'is_partial_received' => 'Y',
                    'updated_by' => Auth::user()->id
                ]);
            }

            $updRO = Tx_receipt_order::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_after_vat' => $totalPrice+($totalPrice*$vat/100),
                'updated_by' => Auth::user()->id
            ]);

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
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
                'ro' => $query,
                'ro_part' => $query_part,
                'qCurrency' => $qCurrency
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

            $qSupplierSelected = Mst_supplier::where('id','=',$query->supplier_id)
            ->first();

            $order = [];
            if(old('supplier_id')){
                $memo = Tx_purchase_memo::select('memo_no AS order_no')
                ->where('memo_no','NOT LIKE','%Draft%')
                // ->whereIn('memo_no',explode(",",$query->po_or_pm_no))
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ]);
                $order = Tx_purchase_order::select('purchase_no AS order_no')
                ->where('purchase_no','NOT LIKE','%Draft%')
                // ->whereIn('purchase_no',explode(",",$query->po_or_pm_no))
                ->where('approved_by','<>',null)
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->union($memo)
                ->get();
            }else{
                $memo = Tx_purchase_memo::select('memo_no AS order_no')
                ->where('memo_no','NOT LIKE','%Draft%')
                // ->whereIn('memo_no',explode(",",$query->po_or_pm_no))
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ]);
                $order = Tx_purchase_order::select('purchase_no AS order_no')
                ->where('purchase_no','NOT LIKE','%Draft%')
                // ->whereIn('purchase_no',explode(",",$query->po_or_pm_no))
                ->where('approved_by','<>',null)
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
                'qSupplierSelected' => $qSupplierSelected,
                'parts' => $parts,
                'weighttype' => $weighttype,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $query_part_count),
                'vat' => $vat,
                'get_po_pm_no' => $order,
                'ro' => $query,
                'ro_part' => $query_part,
                'qCurrency' => $qCurrency
            ];

            return view('tx.'.$this->folder.'.edit', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
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
        $validateInput = [
            'supplier_id' => 'required|numeric',
            'invoice_no' => ['required',new CheckDupInvoiceNo($request->supplier_id,$request->invoice_no,$id)],
            // 'invoice_no' => 'required|unique:App\Models\Tx_receipt_order,invoice_no,'.$id,
            'invoice_amount' => ['required', new NumericCustom('Invoice Amount'), new CheckAmountEqualWithTotal($request->lastTotalAmountTmp)],
            'exc_rate' => [new NumericCustom('Exchange Rate'), new ValidateExchangeRateForSupplierType($request->supplier_id),'nullable'],
            'bl_no' => 'required',
            'gross_weight' => [new NumericCustom('Gross Weight'),'nullable'],
            'measurement' => [new NumericCustom('Measurement'),'nullable'],
            'receipt_no' => [new ApprovalCheckingRO],
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'invoice_amount.regex' => 'Must have exacly 2 decimal places (9,99)',
            'exc_rate.regex' => 'Must have exacly 2 decimal places (9,99)',
            'bl_no.required' => 'The B/L No field is required.',
            'weight_type_id01.numeric' => 'Please select a valid weight type',
            'weight_type_id02.numeric' => 'Please select a valid weight type',
            // 'po_pm_no.numeric' => 'Please select a valid po/pm number',
            'courier_id.numeric' => 'Please select a valid ship by',
            'gross_weight.regex' => 'Must have exacly 2 decimal places (9,99)',
            'measurement.regex' => 'Must have exacly 2 decimal places (9,99)',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty'.$i => 'required|numeric|lte:qty_on_po'.$i.'|min:0',
                    ];
                    $errShipmentMsg = [
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'qty'.$i.'.lte' => 'The qty must be less than '.$request['qty_on_po'.$i].'.',
                        'qty'.$i.'.min' => 'The qty must be at least 0.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            // ambil info user termasuk cabang dari user pembuat RO jika bukan direktur
            $qUser = Tx_receipt_order::leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
            ->select(
                'userdetails.branch_id AS user_branch_id',
                'tx_receipt_orders.receipt_no AS last_receipt_no'
            )
            ->where('tx_receipt_orders.id','=',$id)
            ->first();

            $receipt_no = '';
            $draft = false;
            $orders = Tx_receipt_order::where('id', '=', $id)
            ->where('receipt_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $receipt_no = $orders->receipt_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_receipt_order';
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y") > (int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => $newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name' => $identityName,
                        'id_auto_inc' => $newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $receipt_no = ENV('P_RECEIPT_ORDER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_receipt_order::where('id', '=', $id)
                ->update([
                    'receipt_no' => $receipt_no,
                    'receipt_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_receipt_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qSupplier = Mst_supplier::where('id','=',$request->supplier_id)
            ->first();

            $updRO = Tx_receipt_order::where('id','=',$id)
            ->update([
                'po_or_pm_no' => $request->po_pm_no_all,
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'currency_id' => $request->currency_id,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'branch_id' => $request->shipto_id,
                'courier_id' => is_numeric($request->courier_id)?$request->courier_id:null,
                'invoice_no' => $request->invoice_no,
                'invoice_amount' => GlobalFuncHelper::moneyValidate($request->invoice_amount),
                'exchange_rate' => ($request->exc_rate!='')?GlobalFuncHelper::moneyValidate($request->exc_rate):null,
                'bl_no' => $request->bl_no,
                'vessel_no' => $request->vessel_no,
                'weight_type_id01' => is_numeric($request->weight_type_id01)?$request->weight_type_id01:null,
                'weight_type_id02' => is_numeric($request->weight_type_id02)?$request->weight_type_id02:null,
                'gross_weight' => !is_numeric($request->gross_weight)?null:GlobalFuncHelper::moneyValidate($request->gross_weight),
                'measurement' => !is_numeric($request->measurement)?null:GlobalFuncHelper::moneyValidate($request->measurement),
                'remark' => $request->remark,
                'updated_by' => Auth::user()->id,
            ]);

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

            // set not active utk memisahkan part lama dan part baru karena perubahan supplier dan PO/MO
            $updNotActivePart = Tx_receipt_order_part::where([
                'receipt_order_id' => $id,
            ])
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id
            ]);

            $totalQty = 0;
            $totalPrice = 0;
            $is_partial_received_last = 'N';
            for($lastIdx=0;$lastIdx<$request->totalRow;$lastIdx++){
                if($request['qty'.$lastIdx]){
                    $price_fob = 0;
                    $price_local = 0;
                    $total_fob = 0;
                    $total_local = 0;
                    if($qSupplier->supplier_type_id==10){
                        // international - update price
                        $price_fob = $request['price_fob_val'.$lastIdx];
                        $price_local = $request['price_fob_val'.$lastIdx]*GlobalFuncHelper::moneyValidate($request->exc_rate);
                        $total_fob = $request['qty'.$lastIdx]*$price_fob;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_fob;
                    }
                    if($qSupplier->supplier_type_id==11){
                        // lokal - update price
                        $price_fob = 0;
                        $price_local = $request['price_local_val'.$lastIdx];
                        $total_fob = 0;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_local;
                    }

                    $is_partial_received = 'N';
                    if($request['qty_on_po'.$lastIdx]>$request['qty'.$lastIdx]){
                        $is_partial_received = 'Y';
                        $is_partial_received_last = 'Y';
                    }

                    $totalQty += $request['qty'.$lastIdx];
                    $qPart = Tx_receipt_order_part::where([
                        'id' => $request['ro_part_id'.$lastIdx],
                        'receipt_order_id' => $id,
                    ])
                    ->first();
                    if($qPart){
                        $updPart = Tx_receipt_order_part::where([
                            'id' => $request['ro_part_id'.$lastIdx],
                            'receipt_order_id' => $id,
                        ])
                        ->update([
                            'po_mo_no' => $request['po_mo_no'.$lastIdx],
                            'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                            'part_id' => $request['part_id'.$lastIdx],
                            'qty' => $request['qty'.$lastIdx],
                            'qty_on_po' => $request['qty_on_po'.$lastIdx],
                            'part_price' => $request['price_local_val'.$lastIdx],
                            'final_fob' => $price_fob,
                            'final_cost' => $price_local,
                            'total_fob_price' => $total_fob,
                            'total_price' => $total_local,
                            'is_partial_received' => $is_partial_received,
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id
                        ]);
                    }else{
                        $insPart = Tx_receipt_order_part::create([
                            'receipt_order_id' => $id,
                            'po_mo_no' => $request['po_mo_no'.$lastIdx],
                            'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                            'part_id' => $request['part_id'.$lastIdx],
                            'qty' => $request['qty'.$lastIdx],
                            'qty_on_po' => $request['qty_on_po'.$lastIdx],
                            'part_price' => $request['price_local_val'.$lastIdx],
                            'final_fob' => $price_fob,
                            'final_cost' => $price_local,
                            'total_fob_price' => $total_fob,
                            'total_price' => $total_local,
                            'is_partial_received' => $is_partial_received,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }

                    if(strpos($receipt_no,"Draft")==0){
                        // update qty and all price jika bukan DRAFT
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.avg_cost',
                            'mst_parts.price_list',
                            // 'tx_qty_parts.qty',
                            'tx_qty_parts.branch_id',
                        )
                        ->addSelect([
                            'qty' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->where('tx_qty_parts.branch_id','=',$qUser->user_branch_id)
                            ->limit(1)
                        ])
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_id'.$lastIdx],
                            'tx_qty_parts.branch_id' => $qUser->user_branch_id,
                        ])
                        ->first();

                        if($queryMstPart){
                            // ambil informasi part yang masuk
                            // cek keberadaan part di master part
                            $lastQty = is_null($queryMstPart->qty_total)?0:$queryMstPart->qty_total;
                            $lastQtyPerBranch = is_null($queryMstPart->qty)?0:$queryMstPart->qty;
                            $lastPrice = ($queryMstPart->avg_cost==0)?$queryMstPart->price_list:$queryMstPart->avg_cost;
                            $avg_cost = (($lastQty*$lastPrice)+($request['qty'.$lastIdx]*$price_local))/($lastQty+$request['qty'.$lastIdx]);

                            $branch_id_set_by_director = null;
                            // ambil branch_id dari purchase memo yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
                            ->select(
                                'usr.branch_id as branch_id_tmp'
                            )
                            ->where('tx_purchase_memos.memo_no','=',$request['po_mo_no'.$lastIdx])
                            ->where('tx_purchase_memos.active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id_tmp;
                            }

                            // ambil branch_id dari purchase order yg dibuat oleh direktur
                            $qBranchId = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
                            ->select(
                                'usr.branch_id as branch_id_tmp'
                            )
                            ->where('tx_purchase_orders.purchase_no','=',$request['po_mo_no'.$lastIdx])
                            ->where('tx_purchase_orders.active','=','Y')
                            ->first();
                            if($qBranchId){
                                $branch_id_set_by_director = $qBranchId->branch_id_tmp;
                            }

                            // update avg cost di part RO
                            $updTxRoPart = Tx_receipt_order_part::where([
                                'receipt_order_id' => $id,
                                'po_mo_no' => $request['po_mo_no'.$lastIdx],
                                'part_id' => $request['part_id'.$lastIdx],
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master qty utk masing2 part
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
                                    'qty' => $lastQtyPerBranch+$request['qty'.$lastIdx],
                                    'updated_by' => Auth::user()->id
                                ]);
                            }else{
                                // insert
                                $qtyPartIns = Tx_qty_part::create([
                                    'part_id' => $queryMstPart->part_id_tmp,
                                    'qty' => $request['qty'.$lastIdx],
                                    'branch_id' => is_null($branch_id_set_by_director)?$qUser->user_branch_id:$branch_id_set_by_director,
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }

                            // update master part terhadap part terkait
                            $upqMstPart = Mst_part::where([
                                'id' => $queryMstPart->part_id_tmp
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'initial_cost' => $queryMstPart->avg_cost,
                                // 'initial_cost' => ($queryMstPart->initial_cost==0)?$price_local:$queryMstPart->initial_cost,
                                'final_cost' => $price_local,
                                'total_cost' => ($lastQty+$request['qty'.$lastIdx])*$avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                }
            }

            if($is_partial_received_last=='Y'){
                // jika salah satu part ber status partial received (Y)
                // maka part yang lain ber status partial received (Y) juga
                $updPartialReceived = Tx_receipt_order_part::where([
                    'receipt_order_id' => $id,
                    'active' => 'Y'
                ])
                ->update([
                    'is_partial_received' => 'Y',
                    'updated_by' => Auth::user()->id
                ]);
            }

            $updRO = Tx_receipt_order::where('id','=',$id)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_after_vat' => $totalPrice+($totalPrice*$vat/100),
                'updated_by' => Auth::user()->id
            ]);

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        } catch(Exception $e){
            DB::rollback();
            // throw $e;

            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        // If we reach here, then
        // data is valid and working.
        // Commit the queries!
        DB::commit();

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
