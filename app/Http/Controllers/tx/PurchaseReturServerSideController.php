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
use App\Models\Mst_menu_user;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_retur;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_retur_part;
use App\Rules\CheckOHforPurchaseRetur;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PurchaseReturServerSideController extends Controller
{
    protected $title = 'Purchase Retur';
    protected $folder = 'purchase-retur';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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

        if ($request->ajax()){
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_purchase_returs.supplier_id','=','mst_suppliers.id')
            ->leftJoin('tx_receipt_orders','tx_purchase_returs.receipt_order_id','=','tx_receipt_orders.id')
            ->leftJoin('mst_globals as ent','mst_suppliers.entity_type_id','=','ent.id')
            ->select(
                'tx_purchase_returs.id as tx_id',
                'tx_purchase_returs.purchase_retur_no',
                'tx_purchase_returs.purchase_retur_date',
                'tx_purchase_returs.receipt_order_id',
                'tx_purchase_returs.active as pr_active',
                'tx_purchase_returs.created_by as createdBy',
                'tx_purchase_returs.approved_by',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_suppliers.name as supplier_name',
                'mst_suppliers.supplier_code',
                'tx_receipt_orders.receipt_no',
                'tx_receipt_orders.invoice_no',
                'ent.title_ind as supplier_entity_type_name',
            )
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_purchase_returs.created_at','DESC')
            ->orderBy('tx_purchase_returs.purchase_retur_no','DESC');

            return DataTables::of($query)
            ->filterColumn('receipt_no', function($query, $keyword) {
                $query->whereRaw('tx_receipt_orders.receipt_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('receipt_no', function ($query) {
                if(!is_null($query->receipt_no)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->receipt_order_id).'" target="_new" '.
                        'style="text-decoration: underline;">'.$query->receipt_no.'</a>';
                }
                return '';
            })
            ->filterColumn('purchase_retur_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_purchase_returs.purchase_retur_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->addColumn('purchase_retur_date', function ($query) {
                return date_format(date_create($query->purchase_retur_date),"d/m/Y");
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

                $links = '';
                if((($query->createdBy==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->pr_active=='Y')){
                    if(is_null($query->approved_by)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-retur/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-retur/'.$query->tx_id).'" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if (!is_null($query->approved_by) && $query->pr_active=='Y'){
                    return 'Approved';
                }
                if (is_null($query->approved_by) && $query->pr_active=='Y' && strpos($query->purchase_retur_no,'Draft')==0){
                    return 'Waiting for Approval';
                }
                if (is_null($query->approved_by) && $query->pr_active=='Y' && strpos($query->purchase_retur_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->pr_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['receipt_no','purchase_retur_date','supplier_name','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
        ];
        return view('tx.'.$this->folder.'.index-server-side', $data);
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 37,
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

        $qReceiptOrderTmp = Tx_receipt_order::where('id', '=', $request->ro_id)
        ->first();

        $validateInput = [
            'supplier_id' => 'required|numeric',
            'ro_id' => 'required|numeric',
            'courier_id' => 'required_if:courier_type,3'
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'ro_id.numeric' => 'Please select a valid invoice no',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => ['required', 'numeric', 'lte:qty'.$i, new CheckOHforPurchaseRetur($request['part_id'.$i], $qReceiptOrderTmp->branch_id)],
                        // 'qty_retur'.$i => 'required|numeric|lte:qty'.$i,
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

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();
            $qReceiptOrder = Tx_receipt_order::where('id', '=', $request->ro_id)
            ->first();

            $ins = Tx_purchase_retur::create([
                'purchase_retur_no' => $order_no,
                'purchase_retur_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'receipt_order_id' => $request->ro_id,
                'currency_id' => $qReceiptOrder->currency_id,
                'exc_rate' => $qReceiptOrder->exchange_rate,
                'vat_val' => $qReceiptOrder->vat_val,
                'branch_id' => $qReceiptOrder->branch_id,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
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

            $isThereSomePart = 0;
            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if(isset($request['qty_retur'.$iRow])){
                    $isThereSomePart++;

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

            if ($isThereSomePart<1){
                DB::rollback();
                
                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            // // get active VAT
            // $vat = ENV('VAT');
            // $qVat = Mst_global::where([
            //     'data_cat' => 'vat',
            //     'active' => 'Y'
            // ])
            //     ->first();
            // if ($qVat) {
            //     $vat = $qVat->numeric_val;
            // }

            $updRO = Tx_purchase_retur::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_after_vat' => $totalPrice+($totalPrice*$qReceiptOrder->vat_val/100),
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 37,
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

        $qPv = Tx_purchase_retur::where('id', '=', $id)
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'The document status cannot be changed because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }

        $qReceiptOrderTmp = Tx_receipt_order::where('id', '=', $request->ro_id)
        ->first();
        
        $validateInput = [
            'supplier_id' => 'required|numeric',
            'ro_id' => 'required|numeric',
            'courier_id' => 'required_if:courier_type,3'
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'ro_id.numeric' => 'Please select a valid invoice no',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => ['required', 'numeric', 'lte:qty'.$i, new CheckOHforPurchaseRetur($request['part_id'.$i], $qReceiptOrderTmp->branch_id)],
                        // 'qty_retur'.$i => 'required|numeric|lte:qty'.$i,
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
                    'purchase_retur_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
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

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();
            $qReceiptOrder = Tx_receipt_order::where('id', '=', $request->ro_id)
            ->first();

            $upd = Tx_purchase_retur::where('id','=',$id)
            ->update([
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'receipt_order_id' => $request->ro_id,
                'currency_id' => $qReceiptOrder->currency_id,
                'exc_rate' => $qReceiptOrder->exchange_rate,
                'vat_val' => $qReceiptOrder->vat_val,
                'branch_id' => $qReceiptOrder->branch_id,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
                'remark' => $request->remark,
                'updated_by' => Auth::user()->id,
            ]);

            // set non active untuk part yang tidak masuk retur
            $updPart = Tx_purchase_retur_part::where('purchase_retur_id','=',$id)
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);

            $isThereSomePart = 0;
            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if(isset($request['qty_retur'.$iRow])){
                    $isThereSomePart++; // memastikan ada part yg dibawa

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

            if ($isThereSomePart<1){
                DB::rollback();

                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            // // get active VAT
            // $vat = ENV('VAT');
            // $qVat = Mst_global::where([
            //     'data_cat' => 'vat',
            //     'active' => 'Y'
            // ])
            // ->first();
            // if ($qVat) {
            //     $vat = $qVat->numeric_val;
            // }

            $updRO = Tx_purchase_retur::where('id','=',$id)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_after_vat' => $totalPrice+($totalPrice*$qReceiptOrder->vat_val/100),
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
            throw $e;

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
