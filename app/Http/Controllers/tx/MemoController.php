<?php

namespace App\Http\Controllers\tx;

use \Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Rules\LimitMemoPrice;
use App\Models\Tx_purchase_memo;
use App\Helpers\GlobalFuncHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemoController extends Controller
{
    protected $title = 'Purchase Memo';
    protected $folder = 'memo';

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
        if($userLogin->is_director=='Y'){
            $query = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
            ->select('tx_purchase_memos.*')
            ->addSelect(['total_price' => Tx_purchase_memo_part::selectRaw('SUM(qty*price)')
                ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
                ->where('tx_purchase_memo_parts.active','=','Y')
            ])
            // ->where('tx_purchase_memos.active','=','Y')
            ->where(function($q){
                $q->where('tx_purchase_memos.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_purchase_memos.active','N')
                    ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%');
                });
            })
            ->orderBy('tx_purchase_memos.memo_no', 'DESC')
            ->orderBy('tx_purchase_memos.created_at', 'DESC');
        }else{
            $query = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
            ->select('tx_purchase_memos.*')
            ->addSelect(['total_price' => Tx_purchase_memo_part::selectRaw('SUM(qty*price)')
                ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
                ->where('tx_purchase_memo_parts.active','=','Y')
            ])
            // ->where('tx_purchase_memos.active','=','Y')
            ->where(function($q){
                $q->where('tx_purchase_memos.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_purchase_memos.active','N')
                    ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%');
                });
            })
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_purchase_memos.memo_no', 'DESC')
            ->orderBy('tx_purchase_memos.created_at', 'DESC');
        }

        $data = [
            'memos' => $query->get(),
            'memosCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
            'user_branch_id' => $userLogin->branch_id,
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

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_number', 'ASC')
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
            'parts' => $parts,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'userLogin' => $userLogin,
            'qCurrency' => $qCurrency
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
                        // 'price'.$i => 'required|numeric',
                        // 'price'.$i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
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
            if ($request->vat == 'on') {
                $is_vat = 'Y';
            }

            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $ins = Tx_purchase_memo::create([
                'memo_no' => $memo_no,
                'memo_date' => date("Y-m-d"),
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
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'remark' => $request->remark_txt,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;
            // $maxId = Tx_purchase_memo::max('id');

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

            $qty = 0;
            $totalPriceBeforeVAT = 0;
            $totalPriceAfterVAT = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
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
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) * $vat) / 100);

                        $qUsr = Userdetail::where('user_id','=',Auth::user()->id)
                        ->first();

                        $qTxQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                            // 'branch_id' => $qUsr->branch_id,
                        ])
                        ->first();
                        if(!$qTxQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $qUsr->branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }
                }
            }

            // update total qty
            $upd = Tx_purchase_memo::where('id', '=', $maxId)
            ->update([
                'total_qty' => $qty,
                'total_before_vat' => $totalPriceBeforeVAT,
                'total_after_vat' => $totalPriceAfterVAT,
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
     * @param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
     * @return \Illuminate\Http\Response
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
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
                        // 'price'.$i => 'required|numeric',
                        // 'price'.$i => 'required|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
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
                    'memo_date' => date("Y-m-d"),
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

            $qty = 0;
            $totalPriceBeforeVAT = 0;
            $totalPriceAfterVAT = 0;
            if ($request->totalRow > 0) {
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
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
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) * $vat) / 100);

                        $qUsr = Userdetail::where('user_id','=',Auth::user()->id)
                        ->first();

                        $qTxQty = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$i],
                            'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                            // 'branch_id' => $qUsr->branch_id,
                        ])
                        ->first();
                        if(!$qTxQty){
                            $insQty = Tx_qty_part::create([
                                'part_id' => $request['part_id'.$i],
                                'qty' => 0,
                                'branch_id' => ($request->is_director=='Y'?$request->branch_id:$userLogin->branch_id),
                                // 'branch_id' => $qUsr->branch_id,
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
                    'total_after_vat' => $totalPriceAfterVAT,
                ]);
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_memo  $tx_purchase_memo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_memo $tx_purchase_memo)
    {
        //
    }
}
