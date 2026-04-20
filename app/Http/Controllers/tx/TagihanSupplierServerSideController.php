<?php

namespace App\Http\Controllers\tx;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\ValidationException;
use App\Models\Mst_coa;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_branch;
// use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use App\Models\Tx_receipt_order;
use App\Models\Tx_tagihan_supplier;
use App\Models\Tx_tagihan_supplier_detail;
use App\Models\Tx_payment_voucher;
use App\Models\Mst_menu_user;
use App\Models\Tx_purchase_retur;
use App\Rules\ValdROforTagihanSupplierRules;

class TagihanSupplierServerSideController extends Controller
{
    protected $title = 'Collection Tagihan Supplier';
    protected $folder = 'tagihan-supplier';
    protected $uri = 'tagihan-supplier';

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

        // branch
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        if ($request->ajax()){
            $qTagihanSupplier = Tx_tagihan_supplier::leftJoin('users','tx_tagihan_suppliers.created_by','=','users.id')
            ->leftJoin('userdetails','tx_tagihan_suppliers.created_by','=','userdetails.user_id')
            ->leftJoin('mst_suppliers as sp','tx_tagihan_suppliers.supplier_id','=','sp.id')
            ->leftJoin('mst_globals as gb','sp.entity_type_id','=','gb.id')
            ->select(
                'tx_tagihan_suppliers.id as ts_id',
                'tx_tagihan_suppliers.tagihan_supplier_no',
                'tx_tagihan_suppliers.tagihan_supplier_date',
                'tx_tagihan_suppliers.total_price',
                'tx_tagihan_suppliers.total_price_vat',
                'tx_tagihan_suppliers.grandtotal_price',
                'tx_tagihan_suppliers.is_vat',
                'users.name as createdBy',
                'userdetails.initial',
                'sp.name as supplier_name',
                'sp.supplier_code',
                'gb.title_ind',
            )
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('tx_tagihan_suppliers.created_by', '=', Auth::user()->id);
            })
            ->where('tx_tagihan_suppliers.active', '=', 'Y')
            ->orderBy('tx_tagihan_suppliers.tagihan_supplier_no', 'DESC')
            ->orderBy('tx_tagihan_suppliers.created_at', 'DESC');

            return DataTables::of($qTagihanSupplier)
            ->filterColumn('tagihan_supplier_date', function($qTagihanSupplier, $keyword) {
                $qTagihanSupplier->whereRaw('DATE_FORMAT(tx_tagihan_suppliers.tagihan_supplier_date, "%e/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('tagihan_supplier_date', function ($qTagihanSupplier) {
                return date_format(date_create($qTagihanSupplier->tagihan_supplier_date),"d/m/Y");
            })
            ->filterColumn('supplier_name', function($qTagihanSupplier, $keyword) {
                $qTagihanSupplier->where(function($q) use ($keyword) {
                    $q->where('sp.name', 'like', "%{$keyword}%")
                    ->orWhere('sp.supplier_code', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($qTagihanSupplier) {
                return $qTagihanSupplier->supplier_code.' - '.$qTagihanSupplier->title_ind.' '.$qTagihanSupplier->supplier_name;
            })
            ->filterColumn('receipt_orders_no', function ($qTagihanSupplier, $keyword) {
                $qTagihanSupplier->whereIn('tx_tagihan_suppliers.id', function($q) use($keyword) {
                    $q->select('tx_tsd.tagihan_supplier_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_receipt_orders as tx_ro', 'tx_tsd.receipt_order_id', '=', 'tx_ro.id')
                    ->where('tx_ro.receipt_no', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('receipt_orders_no', function ($qTagihanSupplier) {
                $ro_numbers = '';
                $qRO = Tx_receipt_order::leftJoin('tx_purchase_returs as tx_pr', 'tx_receipt_orders.id', '=', 'tx_pr.receipt_order_id')
                ->select(
                    'tx_receipt_orders.id as ro_id',                    
                    'tx_receipt_orders.receipt_no',                    
                )
                ->whereIn('tx_receipt_orders.id', function($q) use($qTagihanSupplier){
                    $q->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.id' => $qTagihanSupplier->ts_id,
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                ->where([
                    'tx_receipt_orders.active' => 'Y',
                ])
                ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                ->get();
                if ($qRO){
                    foreach($qRO as $ro){
                        $ro_numbers .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$ro->ro_id).'" target="_new" '.
                            'style="text-decoration: underline;">'.$ro->receipt_no.'</a>,';
                    }
                    if ($ro_numbers!=''){
                        $ro_numbers = substr($ro_numbers, 0, strlen($ro_numbers)-1);
                    }
                }
                return str_replace(",","<br/>",$ro_numbers);
            })
            ->filterColumn('receipt_orders_invoices', function ($qTagihanSupplier, $keyword) {
                $qTagihanSupplier->whereIn('tx_tagihan_suppliers.id', function($q) use($keyword) {
                    $q->select('tx_tsd.tagihan_supplier_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_receipt_orders as tx_ro', 'tx_tsd.receipt_order_id', '=', 'tx_ro.id')
                    ->where('tx_ro.invoice_no', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('receipt_orders_invoices', function ($qTagihanSupplier) {
                $inv_numbers = '';
                $qRO = Tx_receipt_order::leftJoin('tx_purchase_returs as tx_pr', 'tx_receipt_orders.id', '=', 'tx_pr.receipt_order_id')
                ->select(
                    'tx_receipt_orders.invoice_no',                    
                )
                ->whereIn('tx_receipt_orders.id', function($q) use($qTagihanSupplier){
                    $q->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.id' => $qTagihanSupplier->ts_id,
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                ->where([
                    'tx_receipt_orders.active' => 'Y',
                ])
                ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                ->get();
                if ($qRO){
                    foreach($qRO as $ro){
                        $inv_numbers .= $ro->invoice_no.',';
                    }
                    if ($inv_numbers!=''){
                        $inv_numbers = substr($inv_numbers, 0, strlen($inv_numbers)-1);
                    }
                }
                return str_replace(",","<br/>",$inv_numbers);
            })
            ->filterColumn('purchase_returs_no', function ($qTagihanSupplier, $keyword) {
                $qTagihanSupplier->whereIn('tx_tagihan_suppliers.id', function($q) use($keyword) {
                    $q->select('tx_tsd.tagihan_supplier_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_receipt_orders as tx_ro', 'tx_tsd.receipt_order_id', '=', 'tx_ro.id')
                    ->leftJoin('tx_purchase_returs as tx_pr', 'tx_ro.id', '=', 'tx_pr.receipt_order_id')
                    ->where('tx_pr.purchase_retur_no', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('purchase_returs_no', function ($qTagihanSupplier) {
                $pr_numbers = '';
                $qRO = Tx_receipt_order::leftJoin('tx_purchase_returs as tx_pr', 'tx_receipt_orders.id', '=', 'tx_pr.receipt_order_id')
                ->select(
                    'tx_pr.id as pr_id',
                    'tx_pr.purchase_retur_no',
                )
                ->whereIn('tx_receipt_orders.id', function($q) use($qTagihanSupplier){
                    $q->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.id' => $qTagihanSupplier->ts_id,
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                ->where([
                    'tx_receipt_orders.active' => 'Y',
                ])
                ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                ->get();
                if ($qRO){
                    foreach($qRO as $pr){
                        if (strpos($pr->purchase_retur_no, "Draft")<0){
                            $pr_numbers .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur/'.$pr->pr_id).'" target="_new" '.
                                'style="text-decoration: underline;">'.$pr->purchase_retur_no.'</a>,';
                        }else{
                            $pr_numbers .= '';
                        }
                    }
                    if ($pr_numbers!=''){
                        $pr_numbers = substr($pr_numbers, 0, strlen($pr_numbers)-1);
                    }
                }
                return str_replace(",","<br/>", $pr_numbers);
            })
            ->addColumn('createdBy', function ($qTagihanSupplier) {
                return $qTagihanSupplier->initial;
            })
            ->addColumn('action', function ($qTagihanSupplier) {
                $qPv = Tx_payment_voucher::where('tagihan_supplier_id', '=', $qTagihanSupplier->ts_id)
                ->where('active', '=', 'Y')
                ->first();
                if (!$qPv){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri.'/'.urlencode($qTagihanSupplier->tagihan_supplier_no).'/edit').'" 
                        style="text-decoration: underline;">Edit</a> |
                        <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri.'/'.urlencode($qTagihanSupplier->tagihan_supplier_no)).'" 
                        style="text-decoration: underline;">View</a>';
                    return $links;
                }
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri.'/'.urlencode($qTagihanSupplier->tagihan_supplier_no)).'" 
                    style="text-decoration: underline;">View</a>';
            })
            ->addColumn('status', function ($qTagihanSupplier) {
                $qPv = Tx_payment_voucher::where('tagihan_supplier_id', '=', $qTagihanSupplier->ts_id)
                ->where('active', '=', 'Y')
                ->orderBy('id', 'DESC')
                ->first();
                if ($qPv){
                    if ($qPv->is_full_payment=='N'){
                        return 'Partial';
                    }else{
                        if ($qPv->approved_by==null && $qPv->is_draft=='N'){
                            return 'PV';
                        }
                        if ($qPv->approved_by!=null){
                            return 'Paid';
                        }
                    }
                }
                return 'Created';
            })
            ->rawColumns(['tagihan_supplier_date', 'supplier_name', 'receipt_orders_no', 'receipt_orders_invoices', 'purchase_returs_no', 'createdBy', 'action', 'status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
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
        $qSuppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $coas = Mst_coa::where(function($q){
            $q->where('coa_code_complete','LIKE','111%')
            ->orWhere('coa_code_complete','LIKE','112%')
            ->orWhere('coa_code_complete','LIKE','116%');
        })
        ->where('is_master_coa', '=', 'N')
        ->when(old('vat_val')=='(VAT)', function($q) {
            $q->whereIn('local', ['A', 'P']);
        })
        ->when(old('vat_val')=='(Non VAT)', function($q) {
            $q->whereIn('local', ['A', 'N']);
        })
        ->when(old('vat_val')!='(VAT)' && old('vat_val')!='(Non VAT)', function($q) {
            $q->where([
                'local' => 'X',
            ]);
        })
        ->where('active', '=', 'Y')
        ->orderBy('coa_name', 'ASC')
        ->get();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qRO = [];
        if (old('supplier_id')){
            $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
            ->whereNotIn('id', function($q1) {
                $q1->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                ->where([
                    'tx_tsd.active' => 'Y',
                    'tx_ts.active' => 'Y',
                ]);
            })
            ->whereNotIn('id', function($q1){
                $q1->select('tx_pvi.receipt_order_id')
                ->from('tx_payment_voucher_invoices AS tx_pvi')
                ->leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pvi.payment_voucher_id', '=', 'tx_pv.id')
                ->where([
                    'tx_pvi.is_full_payment' => 'Y',
                    'tx_pvi.active' => 'Y',
                    'tx_pv.active' => 'Y',
                ]);
            })
            ->where([
                'supplier_id' => old('supplier_id'),
                'active' => 'Y',
            ])
            ->orderBy('receipt_no', 'desc')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qSuppliers' => $qSuppliers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'vat' => 1,
            'qCurrency' => $qCurrency,
            'coas' => $coas,
            'qRO' => $qRO,
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
            'menu_id' => 121,
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
            'receipt_order_no_all' => ['required', new ValdROforTagihanSupplierRules(0)],
            'payment_plan_date' => 'required',
            'bank_id' => 'required|numeric',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'receipt_order_no_all.required' => 'Please select a valid RO No / INV No',
            'payment_plan_date.required' => 'Payment Plan Date is required',
            'bank_id.numeric' => 'Please select a valid bank',
        ];
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
            $identityName = 'tx_tagihan_suppliers-draft';
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
                $tagihan_supplier_no = ENV('P_TAGIHAN_SUPPLIER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_tagihan_suppliers';
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
                $tagihan_supplier_no = ENV('P_TAGIHAN_SUPPLIER').date('y').'-'.$zero.strval($newInc);
            }

            $ro_date_arr = explode("/", $request->payment_plan_date);
            $tagihan_supplier_date = date_create($ro_date_arr[2].'-'.$ro_date_arr[1].'-'.$ro_date_arr[0]);

            $ins = Tx_tagihan_supplier::create([
                'tagihan_supplier_no' => $tagihan_supplier_no,
                'tagihan_supplier_date' => $tagihan_supplier_date,
                'supplier_id' => $request->supplier_id,
                'bank_id' => $request->bank_id,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $is_vat = '';
            $vat_percent = 0;
            $total_vat_val = 0;
            $totalAmount = 0;
            for ($iRow=0;$iRow<$request->totalRowRO;$iRow++){
                if ($request['receipt_order_id_dtl_'.$iRow]){
                    $total_price_per_ro = 0;
                    $qRO = Tx_receipt_order::select(
                        'tx_receipt_orders.id as ro_id',
                        'tx_receipt_orders.receipt_no',
                        'tx_receipt_orders.invoice_no',
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
                        'tx_receipt_orders.vat_val',
                        'tx_receipt_orders.journal_type_id',
                        'tx_receipt_orders.active as ro_active',
                    )
                    ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                    ->where([
                        'tx_receipt_orders.id' => $request['receipt_order_id_dtl_'.$iRow],
                        'tx_receipt_orders.active' => 'Y',
                    ])
                    ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                    ->first();
                    if ($qRO){
                        $pr_total_before_vat = Tx_purchase_retur::where('receipt_order_id', $request['receipt_order_id_dtl_'.$iRow])
                        ->whereRaw('approved_by IS NOT NULL')
                        ->where('is_draft', 'N')
                        ->where('active', 'Y')
                        ->sum('total_before_vat');
                        $pr_total_vat = 0;
                        $qTotalVat = Tx_purchase_retur::selectRaw('SUM(total_after_vat-total_before_vat) AS pr_total_vat')
                        ->whereRaw('approved_by IS NOT NULL')
                        ->where('receipt_order_id', $request['receipt_order_id_dtl_'.$iRow])
                        ->where('is_draft', 'N')
                        ->where('active', 'Y')
                        ->first();
                        if ($qTotalVat){
                            $pr_total_vat = $qTotalVat->pr_total_vat;
                        }

                        $total_price_per_ro = $qRO->ro_total_before_vat-$pr_total_before_vat;
                        $totalAmount += $total_price_per_ro;
                        $is_vat = $qRO->journal_type_id!='N'?'Y':'N';
                        $vat_percent = $qRO->vat_val;
                        $total_vat_val += ($qRO->ro_total_vat-$pr_total_vat);
                    }
                    $insDtl = Tx_tagihan_supplier_detail::create([
                        'tagihan_supplier_id' => $maxId,
                        'receipt_order_id' => $request['receipt_order_id_dtl_'.$iRow],
                        'total_price_per_ro' => $total_price_per_ro,
                        'is_vat_per_ro' => $is_vat,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }

            // update total tagihan supplier
            $upd = Tx_tagihan_supplier::where('id','=',$maxId)
            ->update([
                'total_price' => $totalAmount,
                'total_price_vat' => $total_vat_val,
                // 'total_price_vat' => ($totalAmount*$vat_percent/100),
                'grandtotal_price' => $totalAmount+$total_vat_val,
                // 'grandtotal_price' => $totalAmount+($totalAmount*$vat_percent/100),
                'is_vat' => $is_vat,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_tagihan_supplier  $Tx_tagihan_supplier
     * @return \Illuminate\Http\Response
     */
    public function show($ts_no)
    {
        $qSuppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $coas = Mst_coa::where('coa_code_complete','LIKE','112%')
        ->whereIn('local', ['A','N'])
        ->where([
            'coa_level' => 5,
            'active' => 'Y',
        ])
        ->get();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qTS = Tx_tagihan_supplier::where([
            'tagihan_supplier_no' => $ts_no,
        ])
        ->first();
        if ($qTS){
            $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
            ->whereNotIn('id', function($q) use($qTS){
                $q->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                ->where('tx_tsd.tagihan_supplier_id','<>',$qTS->id)
                ->where([
                    'tx_tsd.active' => 'Y',
                    'tx_ts.active' => 'Y',
                ]);
            })
            ->whereNotIn('id', function($q){
                $q->select('tx_tpv.receipt_order_id')
                ->from('tx_payment_voucher_invoices as tx_tpv')
                ->leftJoin('tx_payment_vouchers as tx_tv', 'tx_tpv.payment_voucher_id', '=', 'tx_tv.id')
                ->where([
                    'tx_tpv.is_full_payment' => 'Y',
                    'tx_tpv.active' => 'Y',
                    'tx_tv.active' => 'Y',
                ]);
            })
            ->where([
                'supplier_id' => $qTS->supplier_id,
                'active' => 'Y',
            ])
            ->orderBy('receipt_no', 'desc')
            ->get();

            $qRO_selected = Tx_tagihan_supplier_detail::leftJoin('tx_receipt_orders as tx_ro','tx_tagihan_supplier_details.receipt_order_id','=','tx_ro.id')
            ->select(
                'tx_tagihan_supplier_details.id as qTS_dtl_id',
                'tx_ro.id as ro_id',
                'tx_ro.receipt_no',
                'tx_ro.receipt_date',
                'tx_ro.invoice_no',
                'tx_ro.vat_val',
                'tx_ro.journal_type_id',
            )
            ->where([
                'tx_tagihan_supplier_details.tagihan_supplier_id' => $qTS->id,
                'tx_tagihan_supplier_details.active' => 'Y',
            ])
            ->orderBy('tx_ro.receipt_no','asc');
    
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'uri' => $this->uri,
                'qTS' => $qTS,
                'qRO_selected' => $qRO_selected,
                'qSuppliers' => $qSuppliers,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => 1,
                'qCurrency' => $qCurrency,
                'coas' => $coas,
                'qRO' => $qRO,
            ];
    
            return view('tx.'.$this->folder.'.show', $data);
        }else{         
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);   
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_tagihan_supplier  $Tx_tagihan_supplier
     * @return \Illuminate\Http\Response
     */
    public function edit($ts_no)
    {
        $qSuppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qTS = Tx_tagihan_supplier::where([
            'tagihan_supplier_no' => urldecode($ts_no),
        ])
        ->first();
        if ($qTS){
            $coas = Mst_coa::where(function($q){
                $q->where('coa_code_complete','LIKE','111%')
                ->orWhere('coa_code_complete','LIKE','112%')
                ->orWhere('coa_code_complete','LIKE','116%');
            })
            ->where('is_master_coa', '=', 'N')
            ->when(old('vat_val')=='(VAT)' || $qTS->is_vat=='Y', function($q) {
                $q->whereIn('local', ['A', 'P']);
            })
            ->when(old('vat_val')=='(Non VAT)' || $qTS->is_vat=='N', function($q) {
                $q->whereIn('local', ['A', 'N']);
            })
            ->when(old('vat_val')!='(VAT)' && old('vat_val')!='(Non VAT)' && $qTS->is_vat!='Y' && $qTS->is_vat!='N', function($q) {
                $q->where('local', '=', 'X');
            })
            ->where('active', '=', 'Y')
            ->orderBy('coa_name', 'ASC')
            ->get();

            $qRO = [];
            $qRO_selected = [];
            if (old('supplier_id')){
                $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
                ->whereNotIn('id', function($q1) use($qTS){
                    $q1->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->where('tx_tsd.tagihan_supplier_id','<>',$qTS->id)
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->whereNotIn('id', function($q1){
                    $q1->select('tx_pvi.receipt_order_id')
                    ->from('tx_payment_voucher_invoices AS tx_pvi')
                    ->leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pvi.payment_voucher_id', '=', 'tx_pv.id')
                    ->where([
                        'tx_pvi.is_full_payment' => 'Y',
                        'tx_pvi.active' => 'Y',
                        'tx_pv.active' => 'Y',
                    ]);
                })
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y',
                ])
                ->orderBy('receipt_no', 'desc')
                ->get();
            }else{
                $qRO = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
                ->whereNotIn('id', function($q1) use($qTS){
                    $q1->select('tx_tsd.receipt_order_id')
                    ->from('tx_tagihan_supplier_details as tx_tsd')
                    ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                    ->where('tx_tsd.tagihan_supplier_id','<>',$qTS->id)
                    ->where([
                        'tx_tsd.active' => 'Y',
                        'tx_ts.active' => 'Y',
                    ]);
                })
                ->whereNotIn('id', function($q1){
                    $q1->select('tx_pvi.receipt_order_id')
                    ->from('tx_payment_voucher_invoices AS tx_pvi')
                    ->leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pvi.payment_voucher_id', '=', 'tx_pv.id')
                    ->where([
                        'tx_pvi.is_full_payment' => 'Y',
                        'tx_pvi.active' => 'Y',
                        'tx_pv.active' => 'Y',
                    ]);
                })
                ->where([
                    'supplier_id' => $qTS->supplier_id,
                    'active' => 'Y',
                ])
                ->orderBy('receipt_no', 'desc')
                ->get();

                $qRO_selected = Tx_tagihan_supplier_detail::leftJoin('tx_receipt_orders as tx_ro','tx_tagihan_supplier_details.receipt_order_id','=','tx_ro.id')
                ->select(
                    'tx_tagihan_supplier_details.id as qTS_dtl_id',
                    'tx_ro.id as ro_id',
                    'tx_ro.receipt_no',
                    'tx_ro.receipt_date',
                    'tx_ro.invoice_no',
                    'tx_ro.vat_val',
                    'tx_ro.journal_type_id',
                )
                ->where([
                    'tx_tagihan_supplier_details.tagihan_supplier_id' => $qTS->id,
                    'tx_tagihan_supplier_details.active' => 'Y',
                ])
                ->orderBy('tx_ro.receipt_no','asc');
            }
    
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'uri' => $this->uri,
                'qTS' => $qTS,
                'qRO_selected' => $qRO_selected,
                'qSuppliers' => $qSuppliers,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => 1,
                'qCurrency' => $qCurrency,
                'coas' => $coas,
                'qRO' => $qRO,
            ];
    
            return view('tx.'.$this->folder.'.edit', $data);
        }else{         
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
     * @param  \App\Models\Tx_tagihan_supplier  $Tx_tagihan_supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $ts_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 121,
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

        // get last ID
        $maxId = 0;
        $qTS = Tx_tagihan_supplier::where([
            'tagihan_supplier_no' => $ts_no,
        ])
        ->first();
        if ($qTS){                
            $maxId = $qTS->id;
        }

        $validateInput = [
            'supplier_id' => 'required|numeric',
            'receipt_order_no_all' => ['required', new ValdROforTagihanSupplierRules($maxId)],
            'payment_plan_date' => 'required',
            'bank_id' => 'required|numeric',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'receipt_order_no_all.required' => 'Please select a valid RO No / INV No',
            'payment_plan_date.required' => 'Payment Plan Date is required',
            'bank_id.numeric' => 'Please select a valid bank',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {
            $ro_date_arr = explode("/", $request->payment_plan_date);
            $tagihan_supplier_date = date_create($ro_date_arr[2].'-'.$ro_date_arr[1].'-'.$ro_date_arr[0]);

            $upd = Tx_tagihan_supplier::where([
                'tagihan_supplier_no' => $ts_no,
            ])
            ->update([
                'tagihan_supplier_date' => $tagihan_supplier_date,
                'supplier_id' => $request->supplier_id,
                'bank_id' => $request->bank_id,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // reset detail
            $updDtl = Tx_tagihan_supplier_detail::where([
                'tagihan_supplier_id' => $maxId,
            ])
            ->update([
                'active' => 'N',
            ]);

            $is_vat = '';
            $vat_percent = 0;
            $totalAmount = 0;
            $total_vat_val = 0;
            for ($iRow=0;$iRow<$request->totalRowRO;$iRow++){
                if ($request['receipt_order_id_dtl_'.$iRow]){
                    $total_price_per_ro = 0;
                    $qRO = Tx_receipt_order::leftJoin('tx_purchase_returs as tx_pr', 'tx_receipt_orders.id', '=', 'tx_pr.receipt_order_id')
                    ->select(
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_before_vat, tx_receipt_orders.total_before_vat_rp) as ro_total_before_vat'),
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_vat, tx_receipt_orders.total_vat_rp) as ro_total_vat'),
                        DB::raw('IF(tx_receipt_orders.supplier_type_id=11, 
                            tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as ro_total_after_vat'),
                        'tx_receipt_orders.vat_val',
                        'tx_receipt_orders.journal_type_id',
                        DB::raw('IF(tx_pr.total_before_vat IS NULL, 0, tx_pr.total_before_vat) as pr_total_before_vat'),
                    )
                    ->where('tx_receipt_orders.receipt_no', 'NOT LIKE', '%Draft%')
                    ->where([
                        'tx_receipt_orders.id' => $request['receipt_order_id_dtl_'.$iRow],
                        'tx_receipt_orders.active' => 'Y',
                    ])
                    ->orderBy('tx_receipt_orders.receipt_no', 'desc')
                    ->first();
                    if ($qRO){
                        $pr_total_before_vat = Tx_purchase_retur::where('receipt_order_id', $request['receipt_order_id_dtl_'.$iRow])
                        ->whereRaw('approved_by IS NOT NULL')
                        ->where('is_draft', 'N')
                        ->where('active', 'Y')
                        ->sum('total_before_vat');
                        $pr_total_vat = 0;
                        $qTotalVat = Tx_purchase_retur::selectRaw('SUM(total_after_vat-total_before_vat) AS pr_total_vat')
                        ->whereRaw('approved_by IS NOT NULL')
                        ->where('receipt_order_id', $request['receipt_order_id_dtl_'.$iRow])
                        ->where('is_draft', 'N')
                        ->where('active', 'Y')
                        ->first();
                        if ($qTotalVat){
                            $pr_total_vat = $qTotalVat->pr_total_vat;
                        }

                        $total_price_per_ro = $qRO->ro_total_before_vat-$pr_total_before_vat;
                        $totalAmount += $total_price_per_ro;
                        $is_vat = $qRO->journal_type_id!='N'?'Y':'N';
                        $vat_percent = $qRO->vat_val;
                        $total_vat_val += ($qRO->ro_total_vat-$pr_total_vat);
                    }

                    $qCheckDtl = Tx_tagihan_supplier_detail::where([
                        'tagihan_supplier_id' => $maxId,
                        'receipt_order_id' => $request['receipt_order_id_dtl_'.$iRow],
                        'active' => 'N',
                    ])
                    ->first();
                    if ($qCheckDtl){
                        $updDtl = Tx_tagihan_supplier_detail::where([
                            'tagihan_supplier_id' => $maxId,
                            'receipt_order_id' => $request['receipt_order_id_dtl_'.$iRow],
                            'active' => 'N',
                        ])
                        ->update([
                            'total_price_per_ro' => $total_price_per_ro,
                            'is_vat_per_ro' => $is_vat,
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id,
                        ]);
                    }else{
                        $insDtl = Tx_tagihan_supplier_detail::create([
                            'tagihan_supplier_id' => $maxId,
                            'receipt_order_id' => $request['receipt_order_id_dtl_'.$iRow],
                            'total_price_per_ro' => $total_price_per_ro,
                            'is_vat_per_ro' => $is_vat,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
            }

            // update total tagihan supplier
            $upd = Tx_tagihan_supplier::where('id','=',$maxId)
            ->update([
                'total_price' => $totalAmount,
                'total_price_vat' => $total_vat_val,
                'grandtotal_price' => $totalAmount+$total_vat_val,
                'is_vat' => $is_vat,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_tagihan_supplier  $Tx_tagihan_supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_tagihan_supplier $Tx_tagihan_supplier)
    {
        //
    }

    public function downloadRpt(Request $request)
    {
        $validateInput = [
            'branch_id' => 'required|numeric',
            'start_date' => 'required',
            'end_date' => 'required',
        ];
        $errMsg = [
            'branch_id.required' => 'Branch is required',
            'branch_id.numeric' => 'Branch is required',
            'start_date.required' => 'Start Date is required',
            'end_date.required' => 'End Date is required',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        return redirect(env('TRANSACTION_FOLDER_NAME').'/tagihan-supplier-xlsx/'.
            urlencode($request->branch_id).'/'.
            urlencode($request->start_date).'/'.
            urlencode($request->end_date));
    }

    public function rmTagihanSupplier(Request $request)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 121,
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
        
        // Start transaction!
        DB::beginTransaction();

        try {

            $qTS = Tx_tagihan_supplier::where([
                'tagihan_supplier_no' => urldecode($request->no),
            ])
            ->first();
            if($qTS){
                $d1 = Tx_tagihan_supplier::where('id','=',$qTS->id)
                ->where('active', '=', 'Y')
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
                ]);

                $updDtl = Tx_tagihan_supplier_detail::where([
                    'tagihan_supplier_id' => $qTS->id,
                ])
                ->where('active', '=', 'Y')
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id,
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

        session()->flash('status', urldecode($request->no).' has been deleted.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);

    }
}
