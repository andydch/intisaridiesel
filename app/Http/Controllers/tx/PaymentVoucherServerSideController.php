<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_payment_voucher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_general_journal;
use App\Models\Tx_lokal_journal;
use Illuminate\Support\Facades\Auth;
use App\Rules\SameTotPaymentAsTotInv;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Mst_menu_user;
use App\Rules\CheckRemainingPaymentVoucher;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PaymentVoucherServerSideController extends Controller
{
    protected $title = 'Pembayaran Supplier';
    protected $folder = 'payment-voucher';
    protected $payment_mode_string = 'Cash,Bank';
    protected $payment_mode_id = '1,2';
    protected $payment_type = 'P,N';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->payment_mode_string = env('METHOD_BAYAR_SUPPLIER_NAME');
        $this->payment_mode_id = env('METHOD_BAYAR_SUPPLIER_ID');
    }

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

        $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)
        ->first();

        if ($request->ajax()){
            $query = Tx_payment_voucher::leftJoin('userdetails AS usr','tx_payment_vouchers.created_by', '=', 'usr.user_id')
            ->leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id', '=', 'mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by', '=', 'users.id')
            ->leftJoin('mst_globals AS ety_type','mst_suppliers.entity_type_id', '=', 'ety_type.id')
            ->leftJoin('tx_tagihan_suppliers AS cts','tx_payment_vouchers.tagihan_supplier_id', '=', 'cts.id')
            ->select(
                'tx_payment_vouchers.id AS tx_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_voucher_plan_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_type_id',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.payment_total_after_vat',
                'tx_payment_vouchers.admin_bank',
                'tx_payment_vouchers.biaya_asuransi',
                'tx_payment_vouchers.biaya_kirim',
                'tx_payment_vouchers.biaya_lainnya',
                'tx_payment_vouchers.diskon_pembelian',
                'tx_payment_vouchers.vat_num',
                'tx_payment_vouchers.pv_created_at',
                'tx_payment_vouchers.ps_created_at',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.created_by as createdby',
                'tx_payment_vouchers.created_at as createdat',
                'tx_payment_vouchers.active as pv_active',
                'mst_suppliers.name AS supplier_name',
                'mst_suppliers.supplier_code',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'ety_type.title_ind as ety_type_name',
                'cts.tagihan_supplier_no',
            )
            ->where('tx_payment_vouchers.active', '=', 'Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('tx_payment_vouchers.created_by', '=', $userLogin->user_id)
                ->where('usr.branch_id', '=', $userLogin->branch_id);
            })
            ->orderBy('tx_payment_vouchers.payment_voucher_plan_no','DESC')
            ->orderBy('tx_payment_vouchers.created_at','DESC');

            return DataTables::of($query)
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_suppliers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_suppliers.supplier_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($query) {
                return $query->supplier_code.' - '.$query->ety_type_name.' '.$query->supplier_name;
            })
            ->filterColumn('doc_created_at', function($query, $keyword) {
                $query->where(function($q) use($keyword) {
                    $q->whereRaw('DATE_FORMAT(tx_payment_vouchers.pv_created_at, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(tx_payment_vouchers.ps_created_at, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(tx_payment_vouchers.created_at, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
                });
            })
            ->editColumn('doc_created_at', function ($query) {
                $doc_created_at=date_create($query->createdat);
                return date_format($doc_created_at,"d/m/Y");
            })
            ->filterColumn('journal_date_at', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_payment_vouchers.payment_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('journal_date_at', function ($query) {
                $journal_date_at=date_create($query->payment_date);
                return date_format($journal_date_at,"d/m/Y");
            })
            ->addColumn('payment_total', function ($query) {
                $tot = $query->payment_total_after_vat+
                    $query->admin_bank+
                    $query->biaya_kirim+
                    $query->biaya_lainnya+
                    $query->biaya_asuransi-
                    $query->diskon_pembelian;
                return $tot;
            })
            ->filterColumn('journal_no', function($query, $keyword) {
                $query->where(function($q1) use($keyword){
                    $q1->whereIn('tx_payment_vouchers.payment_voucher_no', function($q2) use($keyword) {
                        $q2->select('module_no')
                        ->from('tx_general_journals')
                        ->whereRaw('general_journal_no LIKE ?', ["%{$keyword}%"])
                        ->where([
                            'active' => 'Y',
                        ]);
                    });
                })
                ->orWhere(function($q3) use($keyword) {
                    $q3->whereIn('tx_payment_vouchers.payment_voucher_no', function($q4) use($keyword) {
                        $q4->select('module_no')
                        ->from('tx_lokal_journals')
                        ->whereRaw('general_journal_no LIKE ?', ["%{$keyword}%"])
                        ->where([
                            'active' => 'Y',
                        ]);
                    });
                });
            })
            ->editColumn('journal_no', function ($query) {
                if(!is_null($query->payment_voucher_no)){
                    $qGJ = Tx_general_journal::select('general_journal_no')
                    ->where([
                        'module_no' => $query->payment_voucher_no,
                        'active' => 'Y',
                    ])
                    ->first();
                    if ($qGJ){
                        return $qGJ->general_journal_no;
                    }
                    $qLJ = Tx_lokal_journal::select('general_journal_no')
                    ->where([
                        'module_no' => $query->payment_voucher_no,
                        'active' => 'Y',
                    ])
                    ->first();
                    if ($qLJ){
                        return $qLJ->general_journal_no;
                    }
                }
                return '';
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id', '=', Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->pv_active=='Y'){
                    if (!is_null($query->approved_by)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode(!is_null($query->payment_voucher_no)?
                            $query->payment_voucher_no:$query->payment_voucher_plan_no)).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode(!is_null($query->payment_voucher_no)?
                            $query->payment_voucher_no:$query->payment_voucher_plan_no).'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode(!is_null($query->payment_voucher_no)?
                            $query->payment_voucher_no:$query->payment_voucher_plan_no)).'" style="text-decoration: underline;">View</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode(!is_null($query->payment_voucher_no)?
                        $query->payment_voucher_no:$query->payment_voucher_plan_no)).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if (strpos($query->payment_voucher_plan_no,"Draft")>0){
                    return 'Draft';
                }
                if (strpos($query->payment_voucher_plan_no,"Draft")==0 && is_null($query->payment_voucher_no)){
                    return 'Plan';
                }
                if (!is_null($query->payment_voucher_no) && $query->approved_by==null){
                    return 'Waiting For Approval';
                }
                if ($query->approved_by!=null){
                    return 'Created';
                }
            })
            ->rawColumns(['supplier_name','doc_created_at','journal_date_at','journal_no','payment_total','action','status'])
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

        $suppliers = Mst_supplier::where('active', '=', 'Y')
        ->orderBy('name','ASC')
        ->get();

        $receiptOrders = Tx_receipt_order::where('receipt_no', 'NOT LIKE', '%Draft%')
        ->whereNotIn('id', function ($q01) {
            $q01->select('receipt_order_id')
            ->from('tx_payment_voucher_invoices')
            ->where([
                'is_full_payment' => 'Y',
                'active' => 'Y',
            ]);
        })
        ->when(old('supplier_id'), function($q){
            $q->where([
                'supplier_id' => old('supplier_id'),
            ]);
        })
        ->when(old('tagihan_supplier_id')=='#', function($q) {
            $q->whereNotIn('id', function ($q1) {
                $q1->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                ->where([
                    'tx_tsd.active'=>'Y',
                    'tx_ts.active'=>'Y',
                ]);
            });
        })
        ->when(old('tagihan_supplier_id')!='#', function($q) {
            $q->whereIn('id', function ($q1) {
                $q1->select('tx_tsd.receipt_order_id')
                ->from('tx_tagihan_supplier_details as tx_tsd')
                ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                ->where([
                    'tx_tsd.active'=>'Y',
                    'tx_ts.id'=>old('tagihan_supplier_id'),
                    'tx_ts.active'=>'Y',
                ]);
            });
        })
        ->when(old('journal_type_id'), function($q) {
            $q->where([
                'journal_type_id' => old('journal_type_id'),
            ]);
        })
        // ->when(old('payment_type_id') && old('journal_type_id'), function($q) {
        //     if (old('payment_type_id')=='P' && old('journal_type_id')=='P'){
        //         $q->whereRaw('vat_val>0');
        //     }
        //     if (old('payment_type_id')=='N' && old('journal_type_id')=='N'){
        //         $q->whereRaw('vat_val=0');
        //     }
        //     if (old('payment_type_id')=='N' && old('journal_type_id')=='P'){
        //         $q->where([
        //             'journal_type_id' => 'P',
        //             'vat_val' => 0,
        //         ]);
        //     }
        // })
        ->where('active', '=', 'Y')
        ->orderBy('invoice_no','ASC')
        ->get();

        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y',
        ])
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'totalRow' => (old('totalRow')?old('totalRow'):0),
            'suppliers' => $suppliers,
            'receiptOrders' => $receiptOrders,
            'payment_mode_string' => explode("|", $this->payment_mode_string),
            'payment_mode_id' => explode("|", $this->payment_mode_id),
            'payment_type' => explode(",", $this->payment_type),
            'qVat' => $qVat,
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
            'menu_id' => 50,
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

        $total = GlobalFuncHelper::moneyValidate($request['grandTotalTerbayar']);
        $validateInput = [
            'payment_mode_id' => 'numeric',
            'tagihan_supplier_id' => 'required|numeric',
            'supplier_id' => 'required|numeric',
            'payment_type_id' => 'required',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id' => 'required_if:payment_mode_id,2',
            'ref_id' => 'required_if:payment_mode_id,1,2',
            'reference_date' => 'required',
            'payment_date' => 'required',
            'reference_no' => 'required_if:payment_mode_id,2|max:255',
            'admin_bank' => [new NumericCustom('Admin Bank')],
            'biaya_asuransi' => [new NumericCustom('Biaya Asuransi')],
            'biaya_kirim' => [new NumericCustom('Biaya Kirim')],
            'diskon_pembelian' => [new NumericCustom('Diskon Pembelian')],
        ];
        $errMsg = [
            'payment_mode_id.numeric' => 'Please select a valid Payment Method',
            'tagihan_supplier_id.numeric' => 'Please select a valid No Tagihan Supplier',
            'tagihan_supplier_id.required' => 'Please select a valid No Tagihan Supplier',
            'supplier_id.numeric' => 'Please select a valid supplier',
            'payment_type_id.required' => 'Please select a valid payment type',
            'coa_id.required_if' => 'Please select a valid account number',
            'coa_id.numeric' => 'Please select a valid account number',
            'ref_id.required_if' => 'Please select a valid reference',
            'reference_no.required_if' => 'Transaction / Giro No is required when Payment Method is Bank',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
            'reference_date.required' => 'Transaction / Giro Date is required',
            'payment_date.required' => 'Journal Date is required',
        ];
        if ($request->totalRow>0) {
            $different = '';
            $different_rule = '';
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['invoice_no_'.$i]) {
                    if($i>=1){
                        $different .= ',invoice_no_'.$i;
                        $different_rule = '|different:'.$different;
                    }
                }
            }
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['invoice_no_'.$i]) {
                    $validateShipmentInput = [
                        'invoice_no_'.$i=>'required|numeric'.str_replace('invoice_no_'.$i,"", $different_rule),
                        'total_inv_'.$i=>['required',new NumericCustom('Total'),new CheckRemainingPaymentVoucher($request['invoice_no_'.$i],0,0)],
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.different' => 'You cannot choose the same invoice number',
                        'invoice_no_'.$i.'.required' => 'Please select a valid invoice no',
                        'invoice_no_'.$i.'.numeric' => 'Please select a valid invoice no',
                        'total_inv_'.$i.'.required' => 'The total field is required',
                        'total_inv_'.$i.'.numeric' => 'The total field is must be numeric',
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
            $payment_voucher_plan_no = null;
            $payment_voucher_no = null;

            $identityName = 'tx_payment_vouchers-draft';
            if($request->is_draft=='Y'){
                $draft_at = now();
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
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
                $payment_voucher_plan_no = env('P_PAYMENT_VOUCHER_PLAN').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_payment_vouchers-plan';
            if($request->is_draft=='P'){
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
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
                $payment_voucher_plan_no = env('P_PAYMENT_VOUCHER_PLAN').date('y').'-'.$zero.strval($newInc);
            }

            $identityName = 'tx_payment_vouchers';
            if($request->is_draft=='N'){
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
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
                $payment_voucher_no = env('P_PAYMENT_VOUCHER').date('y').'-'.$zero.strval($newInc);
            }

            $qVat = Mst_global::where([
                'data_cat' => 'vat',
                'active' => 'Y',
            ])
            ->first();

            $payment_date = explode("/", $request->payment_date);
            $reference_date = explode("/", $request->reference_date);
            $ins = Tx_payment_voucher::create([
                'payment_voucher_no' => $payment_voucher_no,
                'payment_voucher_plan_no' => $payment_voucher_plan_no,
                'supplier_id' => $request->supplier_id,
                'payment_type_id' => $request->payment_type_id,
                'journal_type_id' => $request->journal_type_id,
                'payment_date' => (!is_null($request->payment_date)?$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0]:null),
                'payment_total' => GlobalFuncHelper::moneyValidate($request->total_payment),
                'payment_mode' => $request->payment_mode_id,
                'coa_id' => $request->coa_id,
                'payment_reference_id' => (is_numeric($request->ref_id)?$request->ref_id:null),
                'tagihan_supplier_id' => (is_numeric($request->tagihan_supplier_id)?$request->tagihan_supplier_id:null),
                'reference_no' => ($request->reference_no!=''?$request->reference_no:null),
                'reference_date' => (!is_null($request->reference_date)?$reference_date[2].'-'.$reference_date[1].'-'.$reference_date[0]:null),
                'remark' => $request->remark,
                'admin_bank' => ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                'biaya_asuransi' => ($request->biaya_asuransi?GlobalFuncHelper::moneyValidate($request->biaya_asuransi):0),
                'biaya_kirim' => ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                'biaya_lainnya' => ($request->biaya_lainnya?GlobalFuncHelper::moneyValidate($request->biaya_lainnya):0),
                'diskon_pembelian' => ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                'pv_created_at' => (!is_null($payment_voucher_no)?now():null),
                'ps_created_at' => (!is_null($payment_voucher_plan_no)?(strpos($payment_voucher_plan_no,"Draft")==0?now():null):null),
                'vat_num' => ($request->payment_type_id=='P'?$qVat->numeric_val:0),
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $isFullPayment = 'Y';
            $vat_impor_final = 0;
            if ($request->totalRow>0) {
                $paymentTotal = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    $total_inv = GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]);
                    $total_inv_o = GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]);
                    $total_inv_before_retur = GlobalFuncHelper::moneyValidate($request['total_inv_before_retur_'.$i]);

                    if ($request['invoice_no_'.$i]) {
                        $ro = Tx_receipt_order::where('id', '=', $request['invoice_no_'.$i])
                        ->first();
                        if($ro){
                            $total_inv = is_numeric($total_inv)?$total_inv:0;
                            $total_inv_o = is_numeric($total_inv_o)?$total_inv_o:0;
                            $total_inv_before_retur = is_numeric($total_inv_before_retur)?$total_inv_before_retur:0;

                            $total_payment_after_vat = (float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)+
                                ((float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)*($request->ro_vat/100));
                            $total_payment_before_retur_after_vat = (float)$total_inv_before_retur+
                                ((float)$total_inv_before_retur*($request->ro_vat/100));

                            $vat_impor = 0;
                            if ($ro->supplier_type_id==10){
                                $vat_impor = ($ro->total_vat_rp*(($total_inv/$ro->total_before_vat_rp)*100))/100;
                                $vat_impor_final += $vat_impor;
                            }

                            $insPart = Tx_payment_voucher_invoice::create([
                                'payment_voucher_id' => $maxId,
                                'receipt_order_id' => $request['invoice_no_'.$i],
                                'invoice_no' => $ro->invoice_no,
                                'description' => $request['desc_'.$i],
                                'total_payment' => ((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv),
                                'total_payment_after_vat' => $total_payment_after_vat,
                                'total_payment_before_retur' => $total_inv_before_retur,
                                'total_payment_before_retur_after_vat' => $total_payment_before_retur_after_vat,
                                'is_full_payment' => ((floor($total_inv)<floor($total_inv_o))?'N':'Y'),
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        $paymentTotal += ((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv);
                        if($isFullPayment=='Y'){
                            $isFullPayment = ((floor($total_inv)<floor($total_inv_o))?'N':'Y');
                        }
                    }
                }
            }

            // cek apakah ada salah satu PV detil ber status isFullPayment=N
            $qPvDtl = Tx_payment_voucher_invoice::where('payment_voucher_id', '=', $maxId)
            ->where('is_full_payment', '=', 'N')
            ->where('active', '=', 'Y')
            ->first();
            if ($qPvDtl){
                $isFullPayment = 'N';
            }

            // update total qty
            $upd = Tx_payment_voucher::where('id', '=', $maxId)
            ->update([
                'payment_total' => $paymentTotal,
                'payment_total_after_vat' => $ro->supplier_type_id==10?$paymentTotal+$vat_impor_final:
                    ($paymentTotal+($paymentTotal*($request->ro_vat/100))),
                'is_full_payment' => $isFullPayment,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($voucher_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y',
        ])
        ->first();

        $query = Tx_payment_voucher::where('payment_voucher_no', '=', urldecode($voucher_no))
        ->orWhere('payment_voucher_plan_no', '=', urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id', '=', $query->id)
            ->where('active', '=', 'Y');

            $paymentInvId = $query->id;
            $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('payment_voucher_id','<>', $paymentInvId)
                ->where('is_full_payment', '=', 'Y')
                ->where('active', '=', 'Y');
            })
            ->where('active', '=', 'Y')
            ->orderBy('invoice_no','ASC')
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInv->count()),
                'qPaymentInv' => $query,
                'queryInv' => $queryInv->get(),
                'qCurrency' => $qCurrency,
                'payment_mode_string' => explode("|", $this->payment_mode_string),
                'payment_mode_id' => explode("|", $this->payment_mode_id),
                'payment_type' => explode(",", $this->payment_type),
                'qVat' => $qVat,                
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($voucher_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $suppliers = Mst_supplier::where('active', '=', 'Y')
        ->orderBy('name','ASC')
        ->get();

        $paymentRef = Mst_global::where([
            'data_cat' => 'payment-ref',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y',
        ])
        ->first();

        $query = Tx_payment_voucher::where('payment_voucher_no', '=', urldecode($voucher_no))
        ->orWhere('payment_voucher_plan_no', '=', urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id', '=', $query->id)
            ->where('active', '=', 'Y');

            $paymentInvId = $query->id;
            if (old('supplier_id')){
                $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                // ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                //     $q01->select('receipt_order_id')
                //     ->from('tx_payment_voucher_invoices')
                //     ->where('payment_voucher_id','<>', $paymentInvId)
                //     ->where([
                //         'is_full_payment' => 'Y',
                //         'active' => 'Y',
                //     ]);
                // })
                ->when(old('supplier_id'), function($q){
                    $q->where([
                        'supplier_id' => old('supplier_id'),
                    ]);
                })
                ->when(old('tagihan_supplier_id')!='#', function($q) {
                    $q->whereIn('id', function ($q1) {
                        $q1->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                        ->where([
                            'tx_tsd.active'=>'Y',
                            'tx_ts.id'=>old('tagihan_supplier_id'),
                            'tx_ts.active'=>'Y',
                        ]);
                    });
                })
                ->when(old('tagihan_supplier_id')=='#', function($q) {
                    $q->whereNotIn('id', function ($q1) {
                        $q1->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                        ->where([
                            'tx_tsd.active'=>'Y',
                            'tx_ts.active'=>'Y',
                        ]);
                    });
                })
                ->when(old('journal_type_id'), function($q) {
                    $q->where([
                        'journal_type_id' => old('journal_type_id'),
                    ]);
                })
                ->where('active', '=', 'Y')
                ->orderBy('invoice_no','ASC')
                ->get();

            }else{
                $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                // ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                //     $q01->select('receipt_order_id')
                //     ->from('tx_payment_voucher_invoices')
                //     ->where('payment_voucher_id','<>', $paymentInvId)
                //     ->where([
                //         'is_full_payment' => 'Y',
                //         'active' => 'Y',
                //     ]);
                // })
                ->when($query->tagihan_supplier_id!=null, function($q) use($query){
                    $q->whereIn('id', function ($q1) use($query){
                        $q1->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                        ->where([
                            'tx_tsd.active'=>'Y',
                            'tx_ts.id'=>$query->tagihan_supplier_id,
                            'tx_ts.active'=>'Y',
                        ]);
                    });
                })
                ->when($query->tagihan_supplier_id==null, function($q) {
                    $q->whereNotIn('id', function ($q1) {
                        $q1->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_ts.id', '=', 'tx_tsd.tagihan_supplier_id')
                        ->where([
                            'tx_tsd.active'=>'Y',
                            'tx_ts.active'=>'Y',
                        ]);
                    });
                })
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y',
                ])
                ->when($query->journal_type_id!=null, function($q) use($query) {
                    $q->where([
                        'journal_type_id' => $query->journal_type_id,
                    ]);
                })
                // ->when($query->payment_type_id=='P' && $query->journal_type_id=='P', function($q){
                //     $q->whereRaw('vat_val>0');
                // })
                // ->when($query->payment_type_id=='N' && $query->journal_type_id=='N', function($q){
                //     $q->whereRaw('vat_val=0');
                // })
                // ->when($query->payment_type_id=='N' && $query->journal_type_id=='P', function($q){
                //     $q->where([
                //         'journal_type_id' => 'P',
                //         'vat_val' => 0,
                //     ]);
                // })
                ->orderBy('invoice_no','ASC')
                ->get();
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCurrency' => $qCurrency,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInv->count()),
                'suppliers' => $suppliers,
                'receiptOrders' => $receiptOrders,
                'paymentRef' => $paymentRef,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv->get(),
                'payment_mode_string' => explode("|", $this->payment_mode_string),
                'payment_mode_id' => explode("|", $this->payment_mode_id),
                'payment_type' => explode(",", $this->payment_type),
                'qVat' => $qVat,
                'pvId' => $query->id,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $voucher_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 50,
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
        
        $total = GlobalFuncHelper::moneyValidate($request['grandTotalTerbayar']);
        $qPv = Tx_payment_voucher::where('payment_voucher_no', '=', urldecode($voucher_no))
        ->orWhere('payment_voucher_plan_no', '=', urldecode($voucher_no))
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'Existing data cannot be updated because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }

        $validateInput = [
            'payment_mode_id' => 'numeric',
            'tagihan_supplier_id' => 'required|numeric',
            'supplier_id' => 'required|numeric',
            'payment_type_id' => 'required',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id' => 'required_if:payment_mode_id,2',
            'ref_id' => 'required_if:payment_mode_id,1,2',
            'reference_date' => 'required',
            'payment_date' => 'required',
            'reference_no' => 'required_if:payment_mode_id,2|max:255',
            'admin_bank' => [new NumericCustom('Admin Bank')],
            'biaya_asuransi' => [new NumericCustom('Biaya Asuransi')],
            'biaya_kirim' => [new NumericCustom('Biaya Kirim')],
            'diskon_pembelian' => [new NumericCustom('Diskon Pembelian')],
        ];
        $errMsg = [
            'payment_mode_id.numeric' => 'Please select a valid Payment Method',
            'tagihan_supplier_id.numeric' => 'Please select a valid No Tagihan Supplier',
            'tagihan_supplier_id.required' => 'Please select a valid No Tagihan Supplier',
            'supplier_id.numeric' => 'Please select a valid supplier',
            'payment_type_id.required' => 'Please select a valid payment type',
            'coa_id.required_if' => 'Please select a valid account number',
            'coa_id.numeric' => 'Please select a valid account number',
            'ref_id.required_if' => 'Please select a valid reference',
            'reference_no.required_if' => 'Transaction / Giro No is required when Payment Method is Bank',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
            'reference_date.required' => 'Transaction / Giro Date is required',
            'payment_date.required' => 'Journal Date is required',
        ];
        if ($request->totalRow>0) {
            $different = '';
            $different_rule = '';
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['invoice_no_'.$i]) {
                    if($i>=1){
                        $different .= ',invoice_no_'.$i;
                        $different_rule = '|different:'.$different;
                    }
                }
            }
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['invoice_no_'.$i]) {
                    $validateShipmentInput = [
                        'invoice_no_'.$i=>'required|numeric'.str_replace('invoice_no_'.$i,"", $different_rule),
                        'total_inv_'.$i=>['required',new NumericCustom('Total'),new CheckRemainingPaymentVoucher($request['invoice_no_'.$i], $request['inv_id_'.$i], $qPv->id)],
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.different' => 'You cannot choose the same invoice number',
                        'invoice_no_'.$i.'.required' => 'Please select a valid invoice no',
                        'invoice_no_'.$i.'.numeric' => 'Please select a valid invoice no',
                        'total_inv_'.$i.'.required' => 'The total field is required',
                        'total_inv_'.$i.'.numeric' => 'The total field is must be numeric',
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
            $payment_voucher_plan_no = null;
            $payment_voucher_no = null;

            $payment_vouchers = Tx_payment_voucher::where('payment_voucher_plan_no', '=', urldecode($voucher_no))
            ->where('payment_voucher_plan_no','LIKE','%Draft%')
            ->first();
            if($payment_vouchers){
                // looking for draft no
                $draft = true;
                $payment_voucher_no = $payment_vouchers->payment_voucher_plan_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft=='P' && $draft){
                // promoto to plan
                $identityName = 'tx_payment_vouchers-plan';
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
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
                $payment_voucher_plan_no = env('P_PAYMENT_VOUCHER_PLAN').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_payment_voucher::where([
                    'id' => $qPv->id,
                ])
                ->update([
                    'payment_voucher_plan_no' => $payment_voucher_plan_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft=='N' && is_null($qPv->payment_voucher_no)){
                $identityName = 'tx_payment_vouchers';
                $autoInc = Auto_inc::where([
                    'identity_name' => $identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name' => $identityName
                        ])
                        ->update([
                            'id_auto_inc' => 1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
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
                $payment_voucher_no = env('P_PAYMENT_VOUCHER').date('y').'-'.$zero.strval($newInc);

                if ($draft){
                    // promote to created from draft
                    $upd = Tx_payment_voucher::where([
                        'id' => $qPv->id,
                    ])
                    ->update([
                        'payment_voucher_plan_no' => (strpos("-".$qPv->payment_voucher_plan_no,"Draft")>0?null:$qPv->payment_voucher_plan_no),
                        'payment_voucher_no' => $payment_voucher_no,
                        'is_draft' => $request->is_draft,
                        'draft_to_created_at' => now(),
                        'updated_by' => Auth::user()->id
                    ]);
                }else{
                    // promote to created from plan
                    $upd = Tx_payment_voucher::where([
                        'id' => $qPv->id,
                    ])
                    ->update([
                        'payment_voucher_plan_no' => (strpos("-".$qPv->payment_voucher_plan_no,"Draft")>0?null:$qPv->payment_voucher_plan_no),
                        'payment_voucher_no' => $payment_voucher_no,
                        'is_draft' => $request->is_draft,
                        'updated_by' => Auth::user()->id
                    ]);
                }
            }

            $qVat = Mst_global::where([
                'data_cat' => 'vat',
                'active' => 'Y',
            ])
            ->first();

            $payment_date = explode("/", $request->payment_date);
            $reference_date = explode("/", $request->reference_date);
            $upd = Tx_payment_voucher::where([
                'id' => $qPv->id,
            ])
            ->update([
                'supplier_id' => $request->supplier_id,
                'payment_type_id' => $request->payment_type_id,
                'journal_type_id' => $request->journal_type_id,
                'payment_date' => (!is_null($request->payment_date)?$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0]:null),
                'payment_total' => GlobalFuncHelper::moneyValidate($request->total_payment),
                'payment_mode' => $request->payment_mode_id,
                'coa_id' => $request->coa_id,
                'payment_reference_id' => (is_numeric($request->ref_id)?$request->ref_id:null),
                'tagihan_supplier_id' => (is_numeric($request->tagihan_supplier_id)?$request->tagihan_supplier_id:null),
                'reference_no' => ($request->reference_no!=''?$request->reference_no:null),
                'reference_date' => (!is_null($request->reference_date)?$reference_date[2].'-'.$reference_date[1].'-'.$reference_date[0]:null),
                'remark' => $request->remark,
                'admin_bank' => ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                'biaya_asuransi' => ($request->biaya_asuransi?GlobalFuncHelper::moneyValidate($request->biaya_asuransi):0),
                'biaya_kirim' => ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                'diskon_pembelian' => ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                'pv_created_at' => (!is_null($payment_voucher_no)?now():null),
                'ps_created_at' => (!is_null($payment_voucher_plan_no)?(strpos($payment_voucher_plan_no,"Draft")==0?now():null):null),
                'vat_num' => ($request->payment_type_id=='P'?$qVat->numeric_val:0),
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // set not active
            $updPart = Tx_payment_voucher_invoice::where([
                'payment_voucher_id' => $qPv->id
            ])
            ->update([
                'active' => 'N'
            ]);

            $isFullPayment='Y';
            $vat_impor_final = 0;
            if ($request->totalRow>0) {
                $paymentTotal = 0;

                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['invoice_no_'.$i]) {
                        $total_inv = GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]);
                        $total_inv_o = GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]);
                        $total_inv_before_retur = GlobalFuncHelper::moneyValidate($request['total_inv_before_retur_'.$i]);

                        $ro = Tx_receipt_order::where('id', '=', $request['invoice_no_'.$i])
                        ->first();
                        if($ro){
                            $total_inv = is_numeric($total_inv)?$total_inv:0;
                            $total_inv_o = is_numeric($total_inv_o)?$total_inv_o:0;
                            $total_inv_before_retur = is_numeric($total_inv_before_retur)?$total_inv_before_retur:0;

                            $total_payment_after_vat = $total_inv+($total_inv*($request->ro_vat/100));
                            $total_payment_before_retur_after_vat = (float)$total_inv+((float)$total_inv*($request->ro_vat/100));

                            $vat_impor = 0;
                            if ($ro->supplier_type_id==10){
                                $vat_impor = ($ro->total_vat_rp*(($total_inv/$ro->total_before_vat_rp)*100))/100;
                                $vat_impor_final += $vat_impor;
                            }

                            if ($request['inv_id_'.$i]==0) {
                                $insInv = Tx_payment_voucher_invoice::create([
                                    'payment_voucher_id' => $qPv->id,
                                    'receipt_order_id' => $request['invoice_no_'.$i],
                                    'invoice_no' => $ro->invoice_no,
                                    'description' => $request['desc_'.$i],
                                    'total_payment' => $total_inv,
                                    'total_payment_after_vat' => $total_payment_after_vat,
                                    'total_payment_before_retur' => $total_inv,
                                    'total_payment_before_retur_after_vat' => $total_payment_before_retur_after_vat,
                                    // 'total_payment' => (($total_inv>$total_inv_o)?$total_inv_o:$total_inv),
                                    // 'total_payment_after_vat' => (float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)+
                                    //     ((float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)*($request->payment_type_id=='P'?$qVat->numeric_val/100:0)),
                                    // 'total_payment_before_retur' => $total_inv_before_retur,
                                    // 'total_payment_before_retur_after_vat' => (float)$total_inv_before_retur+
                                    //     ((float)$total_inv_before_retur*($request->payment_type_id=='P'?$qVat->numeric_val/100:0)),
                                    'is_full_payment' => (($total_inv<$total_inv_o)?'N':'Y'),
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }else{
                                $updInv = Tx_payment_voucher_invoice::where('id', '=', $request['inv_id_'.$i])
                                ->update([
                                    'payment_voucher_id' => $qPv->id,
                                    'receipt_order_id' => $request['invoice_no_'.$i],
                                    'invoice_no' => $ro->invoice_no,
                                    'description' => $request['desc_'.$i],
                                    'total_payment' => $total_inv,
                                    'total_payment_after_vat' => $total_payment_after_vat,
                                    'total_payment_before_retur' => $total_inv,
                                    'total_payment_before_retur_after_vat' => $total_payment_before_retur_after_vat,
                                    // 'total_payment' => (($total_inv>$total_inv_o)?$total_inv_o:$total_inv),
                                    // 'total_payment_after_vat' => (float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)+
                                    //     ((float)((floor($total_inv)>floor($total_inv_o))?$total_inv_o:$total_inv)*($request->payment_type_id=='P'?$qVat->numeric_val/100:0)),
                                    // 'total_payment_before_retur' => $total_inv_before_retur,
                                    // 'total_payment_before_retur_after_vat' => (float)$total_inv_before_retur+
                                    //     ((float)$total_inv_before_retur*($request->payment_type_id=='P'?$qVat->numeric_val/100:0)),
                                    'is_full_payment' => (($total_inv<$total_inv_o)?'N':'Y'),
                                    'active' => 'Y',
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }

                        $paymentTotal += $total_inv;
                        // $paymentTotal += (($total_inv>$total_inv_o)?$total_inv_o:$total_inv);
                        if($isFullPayment=='Y'){
                            $isFullPayment = (($total_inv<$total_inv_o)?'N':'Y');
                        }
                    }
                }
            }

            // cek apakah ada salah satu PV detil ber status isFullPayment=Y
            $qPvDtl = Tx_payment_voucher_invoice::where('payment_voucher_id', '=', $qPv->id)
            ->where('is_full_payment', '=', 'N')
            ->where('active', '=', 'Y')
            ->first();
            if ($qPvDtl){
                $isFullPayment = 'N';
            }

            // update payment total
            $upd = Tx_payment_voucher::where('id', '=', $qPv->id)
            ->update([
                'payment_total' => $paymentTotal,
                'payment_total_after_vat' => $ro->supplier_type_id==10?$paymentTotal+$vat_impor_final:
                    ($paymentTotal+($paymentTotal*($request->ro_vat/100))),
                'is_full_payment' => $isFullPayment,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
