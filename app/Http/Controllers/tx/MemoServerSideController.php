<?php

namespace App\Http\Controllers\tx;

use \Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use App\Models\Tx_receipt_order;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use App\Rules\IsMemoTiedWithRO_Rule;
use App\Rules\ValidateQtyMOupd_Rule;
use Illuminate\Http\Request;
use App\Rules\LimitMemoPrice;
use App\Models\Tx_purchase_memo;
use App\Helpers\GlobalFuncHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class MemoServerSideController extends Controller
{
    protected $title = 'Purchase Memo';
    protected $folder = 'memo';

    /**
    *Display a listing of the resource.
     *
    *@return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        if ($request->ajax()) {
            $query = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_purchase_memos.supplier_id','=','mst_suppliers.id')
            ->leftJoin('mst_globals as ent','mst_suppliers.entity_type_id','=','ent.id')
            ->select(
                'tx_purchase_memos.id as tx_id',
                'tx_purchase_memos.memo_no',
                'tx_purchase_memos.memo_date',
                'tx_purchase_memos.active as memo_active',
                'tx_purchase_memos.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_suppliers.name as supplier_name',
                'mst_suppliers.supplier_code',
                'ent.title_ind as supplier_entity_type_name',
                )
            ->addSelect(['total_price' => Tx_purchase_memo_part::selectRaw('SUM(qty*price)')
                ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
                ->where('tx_purchase_memo_parts.active','=','Y')
            ])
            ->where(function($q){
                $q->where('tx_purchase_memos.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_purchase_memos.active','N')
                    ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director=='N' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_purchase_memos.memo_no', 'DESC')
            ->orderBy('tx_purchase_memos.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('memo_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_purchase_memos.memo_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('memo_date', function ($query) {
                return date_format(date_create($query->memo_date), "d/m/Y");
            })
            ->filterColumn('receipt_order_no', function($query, $keyword) {
                $query->whereIn('memo_no', function($q) use($keyword) {
                    $q->select('tx_rop.po_mo_no')
                    ->from('tx_receipt_order_parts as tx_rop')
                    ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                    ->where("tx_ro.receipt_no", "LIKE", "%{$keyword}%")
                    ->where([
                        'tx_rop.active' => 'Y',
                        'tx_ro.active' => 'Y',
                    ]);
                });
            })
            ->editColumn('receipt_order_no', function ($query) {
                $receipt_order_id = '';
                $receipt_order_no = '';
                $qRO = Tx_receipt_order::where('po_or_pm_no','LIKE','%'.$query->memo_no.'%')
                ->where('active','=','Y')
                ->first();
                if($qRO){
                    $receipt_order_id = $qRO->id;
                    $receipt_order_no = $qRO->receipt_no;
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$receipt_order_id).'" target="_new"
                        style="text-decoration: underline;">'.$receipt_order_no.'</a>';
                }
                return '';
            })
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_suppliers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_suppliers.supplier_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($query) {
                return $query->supplier_code.' - '.$query->supplier_entity_type_name.' '.$query->supplier_name;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $qRO = Tx_receipt_order_part::where('po_mo_no','=',$query->memo_no)
                ->where('active','=','Y')
                ->first();

                if(($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->memo_active=='Y'){
                    if(strpos($query->memo_no,"Draft")>0 || !$qRO){
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$query->tx_id.'/edit').'"
                            style="text-decoration: underline;">Edit</a> | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$query->tx_id).'"
                            style="text-decoration: underline;">View</a> | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$query->tx_id).'"
                            style="text-decoration: underline;" target="_new">Print</a> | <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$query->tx_id).'"
                            style="text-decoration: underline;">Download</a>';
                    }else{
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$query->tx_id).'"
                            style="text-decoration: underline;">View</a> | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$query->tx_id).'"
                            style="text-decoration: underline;" target="_new">Print</a> | <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$query->tx_id).'"
                            style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
            })
            ->addColumn('status', function ($query) {
                $qRO = Tx_receipt_order::where('po_or_pm_no','LIKE','%'.$query->memo_no.'%')
                ->where('active','=','Y')
                ->first();

                if(strpos($query->memo_no,"Draft")>0 && $query->memo_active=='Y'){
                    return 'Draft';
                }
                if(strpos($query->memo_no,"Draft")==0 && $query->memo_active=='Y' && !$qRO){
                    return 'Created';
                }
                if($query->memo_active=='N'){
                    return 'Cancel';
                }
                if(strpos($query->memo_no,"Draft")==0 && $query->memo_active=='Y' && $qRO){
                    return 'Received';
                }
            })
            ->rawColumns(['memo_date','receipt_order_no','supplier_name','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
            'user_branch_id' => $userLogin->branch_id,
        ];

        return view('tx.'.$this->folder.'.index-server-side', $data);
    }

    /**
    *Show the form for creating a new resource.
     *
    *@return \Illuminate\Http\Response
     */
    public function create()
    {
        ini_set('memory_limit', '64M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $suppliers = Mst_supplier::where([
            'supplier_type_id' => 11,
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();

        $branches = Mst_branch::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();

        $supplierPic = [];
        if (old('supplier_id')) {
            $supplierPic = Mst_supplier::where([
                'id' => old('supplier_id'),
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
        }
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'suppliers' => $suppliers,
            'supplierPics' => $supplierPic,
            'branches' => $branches,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'userLogin' => $userLogin,
            'qCurrency' => $qCurrency
        ];

        return view('tx.'.$this->folder.'.create', $data);
    }

    /**
    *Store a newly created resource in storage.
     *
    *@param  \Illuminate\Http\Request  $request
    *@return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 25,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }

        $validateInput = [
            'supplier_id' => 'required|numeric',
            'supplier_pic' => 'required|numeric',
            'branch_id' => 'required_if:is_director,Y',
            'is_draft' => 'in:Y,N',
            'total_price' => ['required',new NumericCustom('Total Price'),new LimitMemoPrice()]
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'branch_id.required_if' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                        'price'.$i => ['required',new NumericCustom('Price')],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'price'.$i.'.numeric' => 'The price must be numeric.',
                        'price'.$i.'.required' => 'The price field is required.',
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
            $identityName = 'tx_purchase_memos-draft';
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
                $memo_no = ENV('P_PURCHASE_MEMO').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_purchase_memos';
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
                $memo_no = ENV('P_PURCHASE_MEMO').date('y').'-'.$zero.strval($newInc);
            }

            $is_vat = 'N';
            $vat_val = 0;
            if ($request->vat == 'on') {
                $is_vat = 'Y';
            }
            $vat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vat){
                $vat_val = $vat->numeric_val;
            }

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $ins = Tx_purchase_memo::create([
                'memo_no' => $memo_no,
                'memo_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'supplier_office_address' => $qSupplier->office_address,
                'supplier_country_id' => $qSupplier->country_id,
                'supplier_province_id' => $qSupplier->province_id,
                'supplier_city_id' => $qSupplier->city_id,
                'supplier_district_id' => $qSupplier->district_id,
                'supplier_sub_district_id' => $qSupplier->sub_district_id,
                'supplier_post_code' => $qSupplier->post_code,
                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                'pic_idx' => $request->supplier_pic,
                'is_draft' => $request->is_draft,
                'is_vat' => $is_vat,
                'vat_val' => $vat_val,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'remark' => $request->remark_txt,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;

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

            $isThereSomePart = 0;
            $qty = 0;
            $totalPriceBeforeVAT = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $isThereSomePart++;

                        $insPart = Tx_purchase_memo_part::create([
                            'memo_id' => $maxId,
                            'part_id' => $request['part_id'.$i],
                            'qty' => $request['qty'.$i],
                            'price' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                            'description' => $request['desc_part'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);

                        $qty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i]*GlobalFuncHelper::moneyValidate($request['price'.$i]));

                        $qTxQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if(!$qTxQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }
            }

            if ($isThereSomePart<1){
                DB::rollback();

                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            // update total qty
            $upd = Tx_purchase_memo::where('id', '=', $maxId)
            ->update([
                'total_qty' => $qty,
                'total_before_vat' => $totalPriceBeforeVAT,
                'total_after_vat' => $is_vat=='Y'?$totalPriceBeforeVAT+(($totalPriceBeforeVAT*$vat)/100):$totalPriceBeforeVAT,
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
    *Display the specified resource.
     *
    *@param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
    *@return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_purchase_memo::where('id','=',$id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $supplierPic = Mst_supplier::where([
                'id' => $query->supplier_id,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $queryMemoPart = Tx_purchase_memo_part::where([
                'memo_id' => $query->id,
                'active' => 'Y'
            ]);
            $data = [
                'memos' => $query,
                'memoParts' => $queryMemoPart->get(),
                'title' => $this->title,
                'folder' => $this->folder,
                // 'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                // 'branches' => $branches,
                // 'parts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryMemoPart->count()),
                'userLogin' => $userLogin,
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
    *Show the form for editing the specified resource.
     *
    *@param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
    *@return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_purchase_memo::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $suppliers = Mst_supplier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_number', 'ASC')
            ->get();
            if (old('supplier_id')) {
                $supplierPic = Mst_supplier::where([
                    'id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }
            $queryMemoPart = Tx_purchase_memo_part::where([
                'memo_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryMemoPartCount = Tx_purchase_memo_part::where([
                'memo_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $queryMemoPartSUM = Tx_purchase_memo_part::select(DB::raw('SUM(qty*price) AS tot'))
            ->where([
                'memo_id' => $query->id,
                'active' => 'Y'
            ])
            ->first();
            $data = [
                'memos' => $query,
                'memoParts' => $queryMemoPart,
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'branches' => $branches,
                'parts' => $parts,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryMemoPartCount),
                'queryMemoPartSUM' => $queryMemoPartSUM,
                'userLogin' => $userLogin,
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
    *Update the specified resource in storage.
     *
    *@param  \Illuminate\Http\Request  $request
    *@param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
    *@return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 25,
            'user_id' => Auth::user()->id,
            'user_access_read' => 'Y',
        ])
        ->first();
        if (!$qCheckPriv){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error', ENV('ERR_MSG_02')?ENV('ERR_MSG_02'):'You are not allowed to access this page!');
        }
        
        $validateInput = [
            'supplier_id' => ['required','numeric'],
            'supplier_pic' => 'required|numeric',
            'branch_id' => 'required_if:is_director,Y',
            'is_draft' => 'in:Y,N',
            'total_price' => ['required',new NumericCustom('Total Price'),new LimitMemoPrice()],
            // 'memo_no_tmp' => [new IsMemoTiedWithRO_Rule($id)],
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'supplier_pic.numeric' => 'Please select a valid supplier pic',
            'branch_id.required_if' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => ['required', 'numeric', new ValidateQtyMOupd_Rule($id,$request['memo_part_id_'.$i],$request['part_id'.$i])],
                        // 'qty'.$i => 'required|numeric',
                        'price'.$i => ['required',new NumericCustom('Price')],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'price'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
                        'price'.$i.'.numeric' => 'The price must be numeric.',
                        'price'.$i.'.required' => 'The price field is required.',
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

            $draft = false;
            $memos = Tx_purchase_memo::where('id', '=', $id)
                ->where('memo_no','LIKE','%Draft%')
                ->first();
            if($memos){
                // looking for draft memo no
                $draft = true;
                $memo_no = $memos->memo_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_purchase_memos';
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
                $memo_no = ENV('P_PURCHASE_MEMO').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_purchase_memo::where('id', '=', $id)
                ->update([
                    'memo_no' => $memo_no,
                    'memo_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_purchase_memo::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $is_vat = 'N';
            if ($request->vat == 'on') {
                $is_vat = 'Y';
            }

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

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();

            $upd = Tx_purchase_memo::where('id', '=', $id)
            ->update([
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'supplier_office_address' => $qSupplier->office_address,
                'supplier_country_id' => $qSupplier->country_id,
                'supplier_province_id' => $qSupplier->province_id,
                'supplier_city_id' => $qSupplier->city_id,
                'supplier_district_id' => $qSupplier->district_id,
                'supplier_sub_district_id' => $qSupplier->sub_district_id,
                'supplier_post_code' => $qSupplier->post_code,
                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                'pic_idx' => $request->supplier_pic,
                'is_vat' => $is_vat,
                'remark' => $request->remark_txt,
                'updated_by' => Auth::user()->id
            ]);

            // set not active
            $updPart = Tx_purchase_memo_part::where([
                'memo_id' => $id
            ])
            ->update([
                'active' => 'N'
            ]);

            $isThereSomePart = 0;
            $qty = 0;
            $totalPriceBeforeVAT = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $isThereSomePart++;

                        $qty += $request['qty'.$i];
                        if ($request['memo_part_id_'.$i] > 0) {
                            $insPart = Tx_purchase_memo_part::where('id', '=', $request['memo_part_id_'.$i])
                            ->update([
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                        } else {
                            $insPart = Tx_purchase_memo_part::create([
                                'memo_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }

                        $qty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i]*GlobalFuncHelper::moneyValidate($request['price'.$i]));

                        $qTxQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                        ])
                        ->first();
                        if(!$qTxQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }

                // update total qty
                $upd = Tx_purchase_memo::where('id', '=', $id)
                ->update([
                    'total_qty' => $qty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $is_vat=='Y'?$totalPriceBeforeVAT+(($totalPriceBeforeVAT*$vat)/100):$totalPriceBeforeVAT,
                ]);
            }

            if ($isThereSomePart<1){
                DB::rollback();
                
                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

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
    *Remove the specified resource from storage.
     *
    *@param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
    *@return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_memo $tx_purchase_memo)
    {
        //
    }
}
