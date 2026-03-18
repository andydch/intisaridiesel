<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_retur;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_retur_part;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PurchaseReturController extends Controller
{
    protected $title = 'Purchase Retur';
    protected $folder = 'purchase-retur';

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
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->select('tx_purchase_returs.*')
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            // ->where('tx_purchase_returs.active','=','Y')
            ->orderBy('tx_purchase_returs.created_at','DESC')
            ->orderBy('tx_purchase_returs.purchase_retur_no','DESC');
        }else{
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->select('tx_purchase_returs.*')
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            // ->where('tx_purchase_returs.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_purchase_returs.created_at','DESC')
            ->orderBy('tx_purchase_returs.purchase_retur_no','DESC');
        }

        $data = [
            'returs' => $query->get(),
            'retursCount' => $query->count(),
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

        $invoice_no = [];
        if(old('supplier_id')){
            $invoice_no = Tx_receipt_order::where(function ($query) {
                $query->where('tx_receipt_orders.approved_by','IS NOT',null)
                ->orWhereNotIn('tx_receipt_orders.id',
                function($query){
                    $query->select('receipt_order_id')
                    ->from('tx_receipt_order_parts')
                    ->where('is_partial_received','=','Y')
                    ->where('active','=','Y')
                    ->whereRaw('tx_receipt_order_parts.receipt_order_id=tx_receipt_orders.id');
                });
            })
            ->where([
                'supplier_id' => old('supplier_id'),
                'active' => 'Y'
            ])
            ->orderBy('receipt_no','ASC')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'querySupplier' => $querySupplier,
            'parts' => $parts,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'invoice_no' => $invoice_no,
            'vat' => $vat,
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
            'ro_id' => 'required|numeric',
            'courier_id' => 'required|numeric',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'ro_id.numeric' => 'Please select a valid invoice no',
            'courier_id.numeric' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => 'required|numeric|lte:qty'.$i,
                    ];
                    $errShipmentMsg = [
                        'qty_retur'.$i.'.required' => 'The qty retur field is required',
                        'qty_retur'.$i.'.numeric' => 'The qty retur field must be numeric',
                        'qty_retur'.$i.'.lte' => 'The qty retur field must be less than qty field',
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
            $identityName = 'tx_purchase_returs-draft';
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
                $order_no = ENV('P_PURCHASE_RETUR').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_purchase_returs';
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
                $order_no = ENV('P_PURCHASE_RETUR').date('y').'-'.$zero.strval($newInc);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
            $qReceiptOrder = Tx_receipt_order::where('id', '=', $request->ro_id)->first();

            $ins = Tx_purchase_retur::create([
                'purchase_retur_no' => $order_no,
                'purchase_retur_date' => date("Y-m-d"),
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'receipt_order_id' => $request->ro_id,
                'currency_id' => $qReceiptOrder->currency_id,
                'exc_rate' => $qReceiptOrder->exchange_rate,
                'branch_id' => $qReceiptOrder->branch_id,
                'courier_id' => $request->courier_id,
                'remark' => $request->remark,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;
            // $maxId = Tx_purchase_retur::max('id');

            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if(isset($request['qty_retur'.$iRow])){
                    $insPart = Tx_purchase_retur_part::create([
                        'purchase_retur_id' => $maxId,
                        'part_id' => $request['part_id'.$iRow],
                        'qty' => $request['qty'.$iRow],
                        'qty_retur' => $request['qty_retur'.$iRow],
                        'final_cost' => GlobalFuncHelper::moneyValidate($request['price'.$iRow]),
                        'total_retur' => ($request['qty_retur'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                        'total_price' => ($request['qty'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                        'description' => $request['desc_part'.$iRow],
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                    $totalQty += $request['qty_retur'.$iRow];
                    $totalPrice += ($request['qty_retur'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow]));
                }
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

            $updRO = Tx_purchase_retur::where('id','=',$maxId)
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

        $query = Tx_purchase_retur::where('id', '=', $id)->first();
        if ($query) {
            $invoice_no = [];
            if(old('supplier_id')){
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }else{
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }

            $queryPart = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'invoice_no' => $invoice_no,
                'vat' => $vat,
                'qRo' => $query,
                'qRoPart' => $queryPart,
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

        $query = Tx_purchase_retur::where('id', '=', $id)
        ->first();
        if ($query) {
            $qRoBranch = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->leftJoin('mst_branches','usr.branch_id','=','mst_branches.id')
            ->select('mst_branches.name AS branch_name')
            ->where([
                'tx_receipt_orders.id' => $query->receipt_order_id
            ])
            ->first();

            $invoice_no = [];
            if(old('supplier_id')){
                $invoice_no = Tx_receipt_order::where(function ($query) {
                    $query->where('tx_receipt_orders.approved_by','IS NOT',null)
                    ->orWhereNotIn('tx_receipt_orders.id',
                    function($query){
                        $query->select('receipt_order_id')
                        ->from('tx_receipt_order_parts')
                        ->where('is_partial_received','=','Y')
                        ->where('active','=','Y')
                        ->whereRaw('tx_receipt_order_parts.receipt_order_id=tx_receipt_orders.id');
                    });
                })
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }else{
                $invoice_no = Tx_receipt_order::where(function ($query) {
                    $query->where('tx_receipt_orders.approved_by','IS NOT',null)
                    ->orWhereNotIn('tx_receipt_orders.id',
                    function($query){
                        $query->select('receipt_order_id')
                        ->from('tx_receipt_order_parts')
                        ->where('is_partial_received','=','Y')
                        ->where('active','=','Y')
                        ->whereRaw('tx_receipt_order_parts.receipt_order_id=tx_receipt_orders.id');
                    });
                })
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }

            $queryPart = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'invoice_no' => $invoice_no,
                'vat' => $vat,
                'qRo' => $query,
                'qRoPart' => $queryPart,
                'qCurrency' => $qCurrency,
                'qRoBranch' => $qRoBranch
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
            'ro_id' => 'required|numeric',
            'courier_id' => 'required|numeric',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'ro_id.numeric' => 'Please select a valid invoice no',
            'courier_id.numeric' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => 'required|numeric|lte:qty'.$i,
                    ];
                    $errShipmentMsg = [
                        'qty_retur'.$i.'.required' => 'The qty retur field is required',
                        'qty_retur'.$i.'.numeric' => 'The qty retur field must be numeric',
                        'qty_retur'.$i.'.lte' => 'The qty retur field must be less than qty field',
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
            $orders = Tx_purchase_retur::where('id', '=', $id)
            ->where('purchase_retur_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $purchase_retur_no = $orders->purchase_retur_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_purchase_returs';
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
                $purchase_retur_no = ENV('P_PURCHASE_RETUR').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_purchase_retur::where('id', '=', $id)
                ->update([
                    'purchase_retur_no' => $purchase_retur_no,
                    'purchase_retur_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_purchase_retur::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)->first();
            $qReceiptOrder = Tx_receipt_order::where('id', '=', $request->ro_id)->first();

            $upd = Tx_purchase_retur::where('id','=',$id)
            ->update([
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'receipt_order_id' => $request->ro_id,
                'currency_id' => $qReceiptOrder->currency_id,
                'exc_rate' => $qReceiptOrder->exchange_rate,
                'branch_id' => $qReceiptOrder->branch_id,
                'courier_id' => $request->courier_id,
                'remark' => $request->remark,
                'updated_by' => Auth::user()->id,
            ]);

            // set non active untuk part yang tidak masuk retur
            $updPart = Tx_purchase_retur_part::where('purchase_retur_id','=',$id)
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);

            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if(isset($request['qty_retur'.$iRow])){
                    $qPurchaseReturPart = Tx_purchase_retur_part::where('id','=',$request['row_part_id_'.$iRow])->first();
                    if($qPurchaseReturPart){

                        $insPart = Tx_purchase_retur_part::where('id','=',$request['row_part_id_'.$iRow])
                        ->update([
                            'purchase_retur_id' => $id,
                            'part_id' => $request['part_id'.$iRow],
                            'qty' => $request['qty'.$iRow],
                            'qty_retur' => $request['qty_retur'.$iRow],
                            'final_cost' => GlobalFuncHelper::moneyValidate($request['price'.$iRow]),
                            'total_retur' => ((int)$request['qty_retur'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                            'total_price' => ($request['qty'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                            'description' => $request['desc_part'.$iRow],
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id,
                        ]);

                    }else{

                        if($request['part_id'.$iRow]){
                            $insPart = Tx_purchase_retur_part::create([
                                'purchase_retur_id' => $id,
                                'part_id' => $request['part_id'.$iRow],
                                'qty' => $request['qty'.$iRow],
                                'qty_retur' => $request['qty_retur'.$iRow],
                                'final_cost' => GlobalFuncHelper::moneyValidate($request['price'.$iRow]),
                                'total_retur' => ($request['qty_retur'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                                'total_price' => ($request['qty'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow])),
                                'description' => $request['desc_part'.$iRow],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                    }
                }

                if($request['qty_retur'.$iRow]){
                    $totalQty += $request['qty_retur'.$iRow];
                    $totalPrice += ($request['qty_retur'.$iRow]*GlobalFuncHelper::moneyValidate($request['price'.$iRow]));
                }
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

            $updRO = Tx_purchase_retur::where('id','=',$id)
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
