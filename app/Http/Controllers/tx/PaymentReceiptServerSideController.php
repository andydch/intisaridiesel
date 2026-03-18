<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_payment_receipt;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_lokal_journal_detail;
use App\Models\Tx_kwitansi;
use Illuminate\Support\Facades\Auth;
use App\Rules\SameTotPaymentAsTotInv;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Mst_menu_user;
use App\Rules\CheckRemainingPaymentReceipt;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PaymentReceiptServerSideController extends Controller
{
    protected $title = 'Penerimaan Customer';
    protected $folder = 'payment-receipt';
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
        $this->payment_mode_string = env('METHOD_TERIMA_CUST_NAME');
        $this->payment_mode_id = env('METHOD_TERIMA_CUST_ID');
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

        // cek status user yg login
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($request->ajax()){
            // daftar payment receipt
            $query = Tx_payment_receipt::leftJoin('userdetails AS usr','tx_payment_receipts.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_payment_receipts.customer_id','=','mst_customers.id')
            ->leftJoin('users','tx_payment_receipts.created_by','=','users.id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_payment_receipts.id AS tx_id',
                'tx_payment_receipts.payment_receipt_no',
                'tx_payment_receipts.payment_receipt_plan_no',
                'tx_payment_receipts.payment_date',
                'tx_payment_receipts.payment_total',
                'tx_payment_receipts.pr_created_at',
                'tx_payment_receipts.ps_created_at',
                'tx_payment_receipts.active as pr_active',
                'tx_payment_receipts.created_by as createdby',
                'tx_payment_receipts.created_at as createdat',
                'mst_customers.name AS customer_name',
                'mst_customers.customer_unique_code',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'ety_type.title_ind as ety_type_name',
            )
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('tx_payment_receipts.created_by','=',$userLogin->user_id)
                ->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->where([
                'tx_payment_receipts.active' => 'Y',
            ])
            ->orderBy('tx_payment_receipts.created_at','DESC')
            ->orderBy('tx_payment_receipts.pr_created_at','DESC')
            ->orderBy('tx_payment_receipts.payment_receipt_no','DESC');

            return DataTables::of($query)
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_customers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_customers.customer_unique_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('customer_name', function ($query) {
                return $query->customer_unique_code.' - '.$query->ety_type_name.' '.$query->customer_name;
            })
            ->filterColumn('doc_created_at', function($query, $keyword) {
                $query->where(function($q) use($keyword) {
                    $q->whereRaw('DATE_FORMAT(DATE_ADD(tx_payment_receipts.pr_created_at, INTERVAL '.env('WAKTU_ID').' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(DATE_ADD(tx_payment_receipts.ps_created_at, INTERVAL '.env('WAKTU_ID').' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(DATE_ADD(tx_payment_receipts.created_at, INTERVAL '.env('WAKTU_ID').' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
                });
            })
            ->editColumn('doc_created_at', function ($query) {
                $doc_created_at = null;
                if (!is_null($query->payment_receipt_no)){
                    $doc_created_at=date_create($query->pr_created_at);
                    date_add($doc_created_at,date_interval_create_from_date_string(env('WAKTU_ID')." hours"));
                    return date_format($doc_created_at,"d/m/Y");
                }
                if (!is_null($query->payment_receipt_plan_no)){
                    $doc_created_at=date_create($query->ps_created_at);
                    date_add($doc_created_at,date_interval_create_from_date_string(env('WAKTU_ID')." hours"));
                    return date_format($doc_created_at,"d/m/Y");
                }
                $doc_created_at=date_create($query->createdat);
                date_add($doc_created_at,date_interval_create_from_date_string(env('WAKTU_ID')." hours"));
                return date_format($doc_created_at,"d/m/Y");
            })
            ->filterColumn('journal_date_at', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_payment_receipts.payment_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('journal_date_at', function ($query) {
                $journal_date_at=date_create($query->payment_date);
                date_add($journal_date_at,date_interval_create_from_date_string(env('WAKTU_ID')." hours"));
                return date_format($journal_date_at,"d/m/Y");
            })
            ->filterColumn('journal_no', function($query, $keyword) {
                $query->where(function($q1) use($keyword){
                    $q1->whereIn('tx_payment_receipts.payment_receipt_no', function($q2) use($keyword) {
                        $q2->select('module_no')
                        ->from('tx_general_journals')
                        ->whereRaw('general_journal_no LIKE ?', ["%{$keyword}%"])
                        ->where([
                            'active' => 'Y',
                        ]);
                    });
                })
                ->orWhere(function($q3) use($keyword) {
                    $q3->whereIn('tx_payment_receipts.payment_receipt_no', function($q4) use($keyword) {
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
                if(!is_null($query->payment_receipt_no)){
                    $qGJ = Tx_general_journal::select('general_journal_no')
                    ->where([
                        'module_no'=>$query->payment_receipt_no,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qGJ){
                        return $qGJ->general_journal_no;
                    }
                    $qLJ = Tx_lokal_journal::select('general_journal_no')
                    ->where([
                        'module_no'=>$query->payment_receipt_no,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qLJ){
                        return $qLJ->general_journal_no;
                    }
                }
                return '';
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y' || Auth::user()->id==1) && $query->pr_active=='Y'){
                    if (is_null($query->payment_receipt_no)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.(!is_null($query->payment_receipt_no)?
                            urlencode($query->payment_receipt_no):urlencode($query->payment_receipt_plan_no)).'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.(!is_null($query->payment_receipt_no)?
                            urlencode($query->payment_receipt_no):urlencode($query->payment_receipt_plan_no))).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.(!is_null($query->payment_receipt_no)?
                            urlencode($query->payment_receipt_no):urlencode($query->payment_receipt_plan_no))).'" style="text-decoration: underline;">View</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.(!is_null($query->payment_receipt_no)?
                        urlencode($query->payment_receipt_no):urlencode($query->payment_receipt_plan_no))).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if (strpos($query->payment_receipt_plan_no,"Draft")>0){
                    return 'Draft';
                }
                if (strpos($query->payment_receipt_plan_no,"Draft")==0 && is_null($query->payment_receipt_no)){
                    return 'Plan';
                }
                if (!is_null($query->payment_receipt_no)){
                    return 'Created';
                }
            })
            ->rawColumns(['customer_name','doc_created_at','journal_date_at','journal_no','action','status'])
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

        $customers = Mst_customer::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $invoices = [];
        $queryCoa = [];
        if (old('customer_id')){
            // PPN
            $invoices = Tx_invoice::select(
                'id',
                'invoice_no',
            )
            ->where('invoice_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) {
                $q01->select('invoice_id')
                ->from('tx_payment_receipt_invoices')
                ->where([
                    'is_full_payment' => 'Y',
                    'is_vat' => 'Y',
                    'active' => 'Y',
                ]);
            })
            ->where([
                'customer_id'=>old('customer_id'),
                'payment_to_id'=>old('coa_id'),
                'active' => 'Y',
            ])
            ->orderBy('invoice_no','ASC');

            // Non PPN
            $invoices = Tx_kwitansi::select(
                'id',
                'kwitansi_no as invoice_no',
            )
            ->where('kwitansi_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) {
                $q01->select('invoice_id')
                ->from('tx_payment_receipt_invoices')
                ->where([
                    'is_full_payment' => 'Y',
                    'is_vat' => 'N',
                    'active' => 'Y',
                ]);
            })
            ->where([
                'customer_id'=>old('customer_id'),
                'payment_to_id'=>old('coa_id'),
                'active' => 'Y',
            ])
            ->orderBy('kwitansi_no','ASC')
            ->union($invoices)
            ->get();
        }

        $qVat = Mst_global::where([
            'data_cat'=>'vat',
            'active'=>'Y',
        ])
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'customers' => $customers,
            'invoices' => $invoices,
            'qCurrency' => $qCurrency,
            'payment_mode_string'=>explode("|",$this->payment_mode_string),
            'payment_mode_id'=>explode("|",$this->payment_mode_id),
            'payment_type'=>explode(",",$this->payment_type),
            'qVat'=>$qVat,
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
            'menu_id' => 54,
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

        $total = $request->grand_tot_terbayar_val;
        $validateInput = [
            'customer_id' => 'required|numeric',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id'=>'required|numeric',
            // 'coa_id'=>'required_if:payment_mode_id,2',
            'ref_id' => 'required_if:payment_mode_id,1,2',
            // 'ref_id' => 'required|numeric',
            'reference_no'=>'required_if:payment_mode_id,2|max:255',
            'reference_date' => 'required',
            'payment_date' => 'required',
            'payment_type_id' => 'required',
        ];
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'coa_id.numeric' => 'Please select a valid account',
            'coa_id.required_if'=>'Please select a valid account number',
            'ref_id.numeric' => 'Please select a valid reference',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
            'reference_no.required_if'=>'Transaction / Giro No is required when Payment Method is Bank',
            'reference_date.required'=>'Transaction / Giro Date is required',
            'payment_date.required'=>'Journal Date is required',
            'payment_type_id.required'=>'Please select a valid payment type',
        ];
        if ($request->totalRow > 0) {
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
                        'invoice_no_'.$i => 'required|'.str_replace('invoice_no_'.$i,"",$different_rule),
                        'total_inv_'.$i => ['required', new NumericCustom('Total'), new CheckRemainingPaymentReceipt($request['invoice_no_'.$i],0)],
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.different' => 'Please select a valid invoice no',
                        'invoice_no_'.$i.'.required' => 'Please select a valid invoice no',
                        'total_inv_'.$i.'.required' => 'The total field is required',
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
            $payment_receipt_plan_no = null;
            $payment_receipt_no = null;

            $identityName = 'tx_payment_receipts-draft';
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
                $payment_receipt_plan_no = env('P_PAYMENT_RECEIPT_PLAN').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_payment_receipts-plan';
            if($request->is_draft=='P'){
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
                $payment_receipt_plan_no = env('P_PAYMENT_RECEIPT_PLAN').date('y').'-'.$zero.strval($newInc);
            }

            $identityName = 'tx_payment_receipts';
            if($request->is_draft=='N'){
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
                $payment_receipt_no = env('P_PAYMENT_RECEIPT').date('y').'-'.$zero.strval($newInc);
            }

            $vat_val = 1;
            $qVat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($qVat){
                $vat_val = $qVat->numeric_val;
            }

            $payment_date = explode("/",$request->payment_date);
            $reference_date = explode("/",$request->reference_date);
            $ins = Tx_payment_receipt::create([
                'payment_receipt_no'=>$payment_receipt_no,
                'payment_receipt_plan_no'=>$payment_receipt_plan_no,
                'customer_id'=>$request->customer_id,
                'payment_type_id'=>$request->payment_type_id,
                'payment_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],     // journal date
                'payment_total'=>0,
                'payment_mode'=>$request->payment_mode_id,
                'coa_id'=>$request->coa_id,
                'payment_reference_id'=>($request->ref_id=='#'?null:$request->ref_id),
                'reference_no'=>$request->reference_no,
                'reference_date'=>(!is_null($request->reference_date)?$reference_date[2].'-'.$reference_date[1].'-'.$reference_date[0]:null),
                'diskon_pembelian'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                'admin_bank'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                'biaya_kirim'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                'penerimaan_lainnya'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                'remark'=>$request->remark,
                'pr_created_at'=>(!is_null($payment_receipt_no)?now():null),
                'ps_created_at'=>(!is_null($payment_receipt_plan_no)?now():null),
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $branch_id = '';
            $qCustomers = Mst_customer::where([
                'id'=>$request->customer_id,
                'active'=>'Y',
            ])
            ->first();
            if($qCustomers){
                $branch_id = $qCustomers->branch_id;
            }

            $isFullPayment = 'Y';
            $isVATforAutoJournal = '';
            $payment_total_before_tax = 0;
            if ($request->totalRow > 0) {
                $is_full_payment = '';
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['invoice_no_'.$i]) {
                        $is_full_payment = ((floor(GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]))<floor(GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i])))?'N':'Y');
                        $payment_total_per_inv = (floor(GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]))>floor(GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?
                            GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]):
                            GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]));
                        $payment_total_full_per_inv = GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]);

                        if (strpos("invoice-".$request['invoice_no_'.$i],env('P_INVOICE'))>0){
                            $isVATforAutoJournal = 'Y';
                            $billingProcess = Tx_invoice::where('invoice_no','=',urldecode($request['invoice_no_'.$i]))
                            ->first();
                            if ($billingProcess){
                                $insInv = Tx_payment_receipt_invoice::create([
                                    'payment_receipt_id' => $maxId,
                                    'invoice_id' => $billingProcess->id,
                                    'invoice_no' => $billingProcess->invoice_no,
                                    'description' => $request['desc_'.$i],
                                    'total_payment' => $payment_total_per_inv,
                                    'total_payment_after_vat' => ($payment_total_per_inv+(($payment_total_per_inv*$vat_val)/100)),
                                    'total_payment_full' => $payment_total_full_per_inv,
                                    'total_payment_full_after_vat' => $payment_total_full_per_inv+(($payment_total_full_per_inv*$vat_val)/100),
                                    'is_full_payment' => $is_full_payment,
                                    'is_vat' => 'Y',
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }

                        if (strpos("invoice-".$request['invoice_no_'.$i],env('P_KWITANSI'))>0){
                            $isVATforAutoJournal = 'N';
                            $prosesTagihan = Tx_kwitansi::where('kwitansi_no','=',urldecode($request['invoice_no_'.$i]))
                            ->first();
                            if ($prosesTagihan){
                                $insInv = Tx_payment_receipt_invoice::create([
                                    'payment_receipt_id' => $maxId,
                                    'invoice_id' => $prosesTagihan->id,
                                    'invoice_no' => $prosesTagihan->kwitansi_no,
                                    'description' => $request['desc_'.$i],
                                    'total_payment' => $payment_total_per_inv,
                                    'total_payment_after_vat' => $payment_total_per_inv,
                                    'total_payment_full' => $payment_total_full_per_inv,
                                    'total_payment_full_after_vat' => $payment_total_full_per_inv,
                                    'is_full_payment' => $is_full_payment,
                                    'is_vat' => 'N',
                                    'active' => 'Y',
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }

                        $payment_total_before_tax += $payment_total_per_inv;
                        if($isFullPayment=='Y'){
                            $isFullPayment = $is_full_payment;
                        }
                    }
                }
            }

            // update total qty
            $payment_total = GlobalFuncHelper::moneyValidate($request->total_payment);
            $upd = Tx_payment_receipt::where('id','=',$maxId)
            ->update([
                'payment_total' => $payment_total,
                'payment_total_before_vat' => $payment_total_before_tax,
                'payment_total_after_vat' => ($isVATforAutoJournal=='Y'?($payment_total_before_tax+(($payment_total_before_tax*$vat_val)/100)):$payment_total_before_tax),
                'is_full_payment' => $isFullPayment,
            ]);

            $methodNm = '';
            switch ($request->payment_mode_id) {
                case 1:
                    $methodNm = 'Cash';
                    break;
                case 2:
                    $methodNm = 'Bank';
                    break;
                case 3:
                    $methodNm = 'Customer Deposit';
                    break;
                default:
                    $methodNm = 'Cash';
            }

            // cek apakah fitur automatic journal untuk retur sudah tersedia - PPN
            $qAutJournal = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>7,
                'branch_id'=>$branch_id,
                'method_id'=>$request->payment_mode_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournal && $request->is_draft=='N' && $isVATforAutoJournal=='Y'){
                // cash atau bank atau customer deposit
                $qAutJournal_cash = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                // ->whereRaw('LOWER(`desc`)='.($request->payment_mode_id==1?'\'cash\'':'\'bank\''))
                ->first();
                // discount
                $qAutJournal_discount = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'discount\'')
                ->first();
                // admin bank
                $qAutJournal_admin_bank = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                ->first();
                // biaya kirim
                $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                ->first();
                // penerimaan lainnya
                $qAutJournal_penerimaan_lainnya = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                ->first();
                // piutang
                $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'piutang\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_general_journal::where([
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>7,
                    'active'=>'Y',
                ])
                ->first();
                if ($qJournals){
                    // non aktifkan jurnal detail jika ada
                    $updJournalDtl = Tx_general_journal_detail::where('general_journal_id','=',$qJournals->id)
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $yearTemp = substr($payment_date[2],2,2);
                    $monthTemp = (strlen($payment_date[1])==1?'0'.$payment_date[1]:$payment_date[1]);
                    $ymTemp = $yearTemp.$monthTemp;
                    $zero = '';
                    $YearMonth = '';
                    $newInc = 1;
                    $identityName = 'tx_general_journal';

                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    if ($autoInc) {
                        // jika counter sudah terbentuk

                        $date = date_format(date_create($autoInc->updated_at), "n");    // ambil bulan terakhir di counter jurnal terkait
                        $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                        $dateNow = date("ym");
                        if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                            // jika bulan di server sudah berganti

                            // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                            // untuk menghindari duplikasi
                            $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                            ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('general_journal_no', 'DESC')
                            ->first();
                            if ($lastCounterIfAny){
                                $newInc = $lastCounterIfAny->lastCounter+1;
                            }

                        } else {
                            // jika bulan di server sama dengan bulan jurnal yg dipilih
                            $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                            $updInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->update([
                                'id_auto_inc' => $newInc
                            ]);
                        }

                        $YearMonth = $yearTemp.$monthTemp;
                    } else {
                        // jika counter belum pernah terbentuk

                        $dateNow = date("ym");
                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }

                        $insInc = Auto_inc::create([
                            'identity_name'=>$identityName,
                            'id_auto_inc'=>$newInc
                        ]);

                        $YearMonth = date('y').date('m');
                    }
                    
                    for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                        $zero .= '0';
                    }
                    $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_general_journal::create([
                        'general_journal_no'=>$journal_no,
                        'general_journal_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],
                        // 'general_journal_date'=>date("Y-m-d"),
                        'total_debit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'total_kredit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'module_no'=>$payment_receipt_no,
                        'automatic_journal_id'=>7,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                // cash/bank/customer deposit
                $ins_cash = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_cash->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                        ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                        ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                        ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                        ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // discount
                $ins_discount = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // admin bank
                $ins_admin_bank = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_admin_bank->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // biaya kirim
                $ins_biaya_kirim = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // penerimaan lainnya
                $ins_penerimaan_lainnya = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_penerimaan_lainnya->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // piutang
                $ins_piutang = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_piutang->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);
            }

            // cek apakah fitur automatic journal untuk retur sudah tersedia - Non PPN
            $qAutJournal = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>14,
                'branch_id'=>$branch_id,
                'method_id'=>$request->payment_mode_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournal && $request->is_draft=='N' && $isVATforAutoJournal=='N'){
                // cash atau bank
                $qAutJournal_cash = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                // ->whereRaw('LOWER(`desc`)='.($request->payment_mode_id==1?'\'cash\'':'\'bank\''))
                ->first();
                // discount
                $qAutJournal_discount = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'discount\'')
                ->first();
                // admin bank
                $qAutJournal_admin_bank = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                ->first();
                // biaya kirim
                $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                ->first();
                // penerimaan lainnya
                $qAutJournal_penerimaan_lainnya = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                ->first();
                // piutang
                $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'piutang\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_lokal_journal::where([
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>14,
                    'active'=>'Y',
                ])
                ->first();
                if ($qJournals){
                    // non aktifkan jurnal detail jika ada
                    $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $yearTemp = substr($payment_date[2],2,2);
                    $monthTemp = (strlen($payment_date[1])==1?'0'.$payment_date[1]:$payment_date[1]);
                    $ymTemp = $yearTemp.$monthTemp;
                    $zero = '';
                    $YearMonth = '';
                    $newInc = 1;
                    $identityName = 'tx_lokal_journal';

                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    if ($autoInc) {
                        // jika counter sudah terbentuk

                        $date = date_format(date_create($autoInc->updated_at), "n");        // ambil bulan terakhir di counter jurnal terkait
                        $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                        $dateNow = date("ym");
                        if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                            // jika bulan di server sudah berganti

                            // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                            // untuk menghindari duplikasi
                            $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                            ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('general_journal_no', 'DESC')
                            ->first();
                            if ($lastCounterIfAny){
                                $newInc = $lastCounterIfAny->lastCounter+1;
                            }
                            
                        } else {
                            // jika bulan di server sama dengan bulan jurnal yg dipilih

                            $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                            $updInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->update([
                                'id_auto_inc' => $newInc
                            ]);
                        }

                        $YearMonth = $yearTemp.$monthTemp;
                    } else {
                        // jika counter belum pernah terbentuk

                        $dateNow = date("ym");
                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }

                        $insInc = Auto_inc::create([
                            'identity_name'=>$identityName,
                            'id_auto_inc'=>$newInc
                        ]);

                        $YearMonth = date('y').date('m');
                    }
                    $zero = '';
                    for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                        $zero .= '0';
                    }
                    $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_lokal_journal::create([
                        'general_journal_no'=>$journal_no,
                        'general_journal_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],
                        // 'general_journal_date'=>date("Y-m-d"),
                        'total_debit'=>$payment_total_before_tax-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'total_kredit'=>$payment_total_before_tax-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'module_no'=>$payment_receipt_no,
                        'automatic_journal_id'=>14,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                // cash/bank/customer deposit
                $ins_cash = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_cash->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>$payment_total_before_tax-
                        ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                        ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                        ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                        ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // discount
                $ins_discount = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // admin bank
                $ins_admin_bank = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_admin_bank->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // biaya kirim
                $ins_biaya_kirim = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // penerimaan lainnya
                $ins_penerimaan_lainnya = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_penerimaan_lainnya->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // piutang
                $ins_piutang = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_piutang->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>$payment_total_before_tax,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($receipt_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $customers = Mst_customer::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_payment_receipt::where([
            'payment_receipt_no'=>urldecode($receipt_no),
        ])
        ->orWhere([
            'payment_receipt_plan_no'=>urldecode($receipt_no),
        ])
        ->first();
        if($query){
            $queryInv = Tx_payment_receipt_invoice::where('payment_receipt_id','=',$query->id)
            ->where('active','=','Y');

            $paymentInvId = $query->id;
            $invoices = Tx_invoice::select(
                'id',
                'invoice_no',
            )
            ->where('invoice_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('payment_receipt_id')
                ->from('tx_payment_receipt_invoices')
                ->where('payment_receipt_id','<>',$paymentInvId)
                ->where('is_vat','=','Y')
                ->where('is_full_payment','=','Y');
            })
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC');

            $invoices = Tx_kwitansi::select(
                'id',
                'kwitansi_no as invoice_no',
            )
            ->where('kwitansi_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('payment_receipt_id')
                ->from('tx_payment_receipt_invoices')
                ->where('payment_receipt_id','<>',$paymentInvId)
                ->where('is_vat','=','N')
                ->where('is_full_payment','=','Y');
            })
            ->where('active','=','Y')
            ->orderBy('kwitansi_no','ASC')
            ->union($invoices)
            ->get();

            $qVat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInv->count()),
                'customers' => $customers,
                'invoices' => $invoices,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv->get(),
                'qCurrency' => $qCurrency,
                'payment_mode_string'=>explode("|", $this->payment_mode_string),
                'payment_mode_id'=>explode("|", $this->payment_mode_id),
                'payment_type'=>explode(",", $this->payment_type),
                'qVat'=>$qVat,
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
    public function edit($receipt_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $customers = Mst_customer::where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();

        $qVat = Mst_global::where([
            'data_cat'=>'vat',
            'active'=>'Y',
        ])
        ->first();

        $query = Tx_payment_receipt::where([
            'payment_receipt_no'=>urldecode($receipt_no),
        ])
        ->orWhere([
            'payment_receipt_plan_no'=>urldecode($receipt_no),
        ])
        ->first();
        if($query){
            $queryInv = Tx_payment_receipt_invoice::where([
                'payment_receipt_id'=>$query->id,
                'active'=>'Y',
            ]);

            $paymentInvId = $query->id;
            $invoices = Tx_invoice::select(
                'id',
                'invoice_no',
            )
            ->where('invoice_no','NOT LIKE','%Draft%')
            // ->whereIn('id', function ($q01) use ($paymentInvId) {
            //     $q01->select('invoice_id')
            //     ->from('tx_payment_receipt_invoices')
            //     ->where([
            //         'payment_receipt_id'=>$paymentInvId,
            //         // 'is_full_payment'=>'Y',
            //         'is_vat'=>'Y',
            //         'active'=>'Y',
            //     ]);
            // })
            ->when(!old('customer_id'), function($q) use($query, $paymentInvId) {
                $q->whereIn('id', function ($q01) use ($paymentInvId) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->where([
                        'payment_receipt_id'=>$paymentInvId,
                        // 'is_full_payment'=>'Y',
                        'is_vat'=>'Y',
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'customer_id'=>$query->customer_id,
                    'payment_to_id'=>$query->coa_id,
                ]);
            })
            ->when(old('customer_id'), function($q) use($paymentInvId) {
                $q->whereNotIn('id', function ($q01) use ($paymentInvId) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->where('payment_receipt_id', '<>', $paymentInvId)
                    ->where([
                        // 'payment_receipt_id'=>$paymentInvId,
                        // 'is_full_payment'=>'Y',
                        'is_vat'=>'Y',
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'customer_id'=>old('customer_id'),
                    'payment_to_id'=>old('coa_id'),
                ]);
            })
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC');

            $invoices = Tx_kwitansi::select(
                'id',
                'kwitansi_no as invoice_no',
            )
            ->where('kwitansi_no','NOT LIKE','%Draft%')
            // ->whereIn('id', function ($q01) use ($paymentInvId) {
            //     $q01->select('invoice_id')
            //     ->from('tx_payment_receipt_invoices')
            //     ->when(!old('customer_id'), function($q) use($paymentInvId){
            //         $q->where([
            //             'payment_receipt_id'=>$paymentInvId,
            //         ]);
            //     })
            //     ->where([
            //         // 'payment_receipt_id'=>$paymentInvId,
            //         'is_vat'=>'N',
            //         'active'=>'Y',
            //     ]);
            // })
            // ->when(!old('customer_id'), function($q) use($query) {
            //     $q->where([
            //         'customer_id'=>$query->customer_id,
            //         'payment_to_id'=>$query->coa_id,
            //     ]);
            // })
            ->when(!old('customer_id'), function($q) use($query, $paymentInvId) {
                $q->whereNotIn('id', function ($q01) use ($paymentInvId) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->where('payment_receipt_id', '<>', $paymentInvId)
                    ->where([
                        // 'payment_receipt_id'=>$paymentInvId,
                        'is_full_payment'=>'Y',
                        'is_vat'=>'N',
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'customer_id'=>$query->customer_id,
                    'payment_to_id'=>$query->coa_id,
                ]);
            })
            // ->when(old('customer_id'), function($q) {
            //     $q->where([
            //         'customer_id'=>old('customer_id'),
            //         'payment_to_id'=>old('coa_id'),
            //     ]);
            // })
            ->when(old('customer_id'), function($q) use($paymentInvId) {
                $q->whereNotIn('id', function ($q01) use ($paymentInvId) {
                    $q01->select('invoice_id')
                    ->from('tx_payment_receipt_invoices')
                    ->where('payment_receipt_id', '<>', $paymentInvId)
                    ->where([
                        // 'payment_receipt_id'=>$paymentInvId,
                        'is_full_payment'=>'Y',
                        'is_vat'=>'N',
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'customer_id'=>old('customer_id'),
                    'payment_to_id'=>old('coa_id'),
                ]);
            })
            ->where('active','=','Y')
            ->orderBy('kwitansi_no','ASC')
            ->union($invoices)
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInv->count()),
                'customers' => $customers,
                'invoices' => $invoices,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv->get(),
                'qCurrency' => $qCurrency,
                'payment_mode_string'=>explode("|", $this->payment_mode_string),
                'payment_mode_id'=>explode("|", $this->payment_mode_id),
                'payment_type'=>explode(",", $this->payment_type),
                'qVat' => $qVat,
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
    public function update(Request $request, $receipt_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 54,
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
        
        $qPr = Tx_payment_receipt::where([
            'payment_receipt_no'=>urldecode($receipt_no),
        ])
        ->orWhere([
            'payment_receipt_plan_no'=>urldecode($receipt_no),
        ])
        ->first();

        $total = $request->grand_tot_terbayar_val;
        $validateInput = [
            'customer_id' => 'required|numeric',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id'=>'required|numeric',
            // 'coa_id'=>'required_if:payment_mode_id,2',
            'ref_id' => 'required_if:payment_mode_id,1,2',
            // 'ref_id' => 'required|numeric',
            'reference_no'=>'required_if:payment_mode_id,2|max:255',
            'reference_date' => 'required',
            'payment_date' => 'required',
            'payment_type_id' => 'required',
        ];
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'coa_id.numeric' => 'Please select a valid account',
            'coa_id.required_if'=>'Please select a valid account number',
            'ref_id.numeric' => 'Please select a valid reference',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
            'reference_no.required_if'=>'Transaction / Giro No is required when Payment Method is Bank',
            'reference_date.required'=>'Transaction / Giro Date is required',
            'payment_date.required'=>'Journal Date is required',
            'payment_type_id.required'=>'Please select a valid payment type',
        ];
        if ($request->totalRow > 0) {
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
                        'invoice_no_'.$i => 'required|'.str_replace('invoice_no_'.$i,"",$different_rule),
                        'total_inv_'.$i => ['required',new NumericCustom('Total'),new CheckRemainingPaymentReceipt($request['invoice_no_'.$i],$request['payment_receipt_inv_id'.$i])],
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.different' => 'Please select a valid invoice no',
                        'invoice_no_'.$i.'.required' => 'Please select a valid invoice no',
                        'total_inv_'.$i.'.required' => 'The total field is required',
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
            $payment_receipt_plan_no = null;
            $payment_receipt_no = null;

            $payment_receipts = Tx_payment_receipt::where('payment_receipt_plan_no','=',urldecode($receipt_no))
            ->where('payment_receipt_plan_no','LIKE','%Draft%')
            ->first();
            if($payment_receipts){
                // looking for draft payment receipt
                $draft = true;
                $payment_receipt_no = $payment_receipts->payment_receipt_plan_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft=='P' && $draft){
                // promoto to plan
                $identityName = 'tx_payment_receipts-plan';
                $autoInc = Auto_inc::where([
                    'identity_name'=>$identityName
                ])
                ->first();
                $newInc = 1;
                if ($autoInc) {
                    $date = date_format(date_create($autoInc->updated_at), "Y");
                    if ((int)date("Y")>(int)$date) {
                        $updInc = Auto_inc::where([
                            'identity_name'=>$identityName
                        ])
                        ->update([
                            'id_auto_inc'=>1
                        ]);
                    } else {
                        $newInc = (int)($autoInc->id_auto_inc?$autoInc->id_auto_inc:0)+1;
                        $updInc = Auto_inc::where([
                            'identity_name'=>$identityName
                        ])
                        ->update([
                            'id_auto_inc'=>$newInc
                        ]);
                    }
                } else {
                    $insInc = Auto_inc::create([
                        'identity_name'=>$identityName,
                        'id_auto_inc'=>$newInc
                    ]);
                }

                $zero = '';
                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $payment_receipt_plan_no = env('P_PAYMENT_RECEIPT_PLAN').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_payment_receipt::where([
                    'id'=>$qPr->id,
                ])
                ->update([
                    'payment_receipt_plan_no'=>$payment_receipt_plan_no,
                    'is_draft'=>$request->is_draft,
                    'draft_to_created_at'=>now(),
                    'updated_by'=>Auth::user()->id
                ]);
            }

            if($request->is_draft=='N' && is_null($qPr->payment_receipt_no)){
                // promote to created
                $identityName = 'tx_payment_receipts';
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
                $payment_receipt_no = env('P_PAYMENT_RECEIPT').date('y').'-'.$zero.strval($newInc);

                if ($draft){
                    // promote to created from draft
                    $upd = Tx_payment_receipt::where([
                        'id'=>$qPr->id,
                    ])
                    ->update([
                        'payment_receipt_plan_no'=>(strpos("-".$qPr->payment_receipt_plan_no,"Draft")>0?null:$qPr->payment_receipt_plan_no),
                        'payment_receipt_no'=>$payment_receipt_no,
                        'is_draft'=>$request->is_draft,
                        'draft_to_created_at'=>now(),
                        'updated_by'=>Auth::user()->id
                    ]);
                }else{
                    // promote to created from plan
                    $upd = Tx_payment_receipt::where([
                        'id'=>$qPr->id,
                    ])
                    ->update([
                        'payment_receipt_plan_no'=>(strpos("-".$qPr->payment_receipt_plan_no,"Draft")>0?null:$qPr->payment_receipt_plan_no),
                        'payment_receipt_no'=>$payment_receipt_no,
                        'is_draft'=>$request->is_draft,
                        'updated_by'=>Auth::user()->id
                    ]);
                }
            }

            $payment_date = explode("/",$request->payment_date);
            $reference_date = explode("/",$request->reference_date);
            $upd = Tx_payment_receipt::where([
                'id'=>$qPr->id,
            ])
            ->update([
                'customer_id'=>$request->customer_id,
                'payment_type_id'=>$request->payment_type_id,
                'payment_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],     // journal date
                'payment_mode'=>$request->payment_mode_id,
                'coa_id'=>$request->coa_id,
                'payment_reference_id'=>($request->ref_id=='#'?null:$request->ref_id),
                'reference_no'=>$request->reference_no,
                'reference_date'=>(!is_null($request->reference_date)?$reference_date[2].'-'.$reference_date[1].'-'.$reference_date[0]:null),
                'diskon_pembelian'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                'admin_bank'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                'biaya_kirim'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                'penerimaan_lainnya'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                'remark'=>$request->remark,
                'pr_created_at'=>(!is_null($payment_receipt_no)?now():null),
                'ps_created_at'=>(!is_null($payment_receipt_plan_no)?now():null),
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // set not active
            $updPart = Tx_payment_receipt_invoice::where([
                'payment_receipt_id' => $qPr->id
            ])->update([
                'active' => 'N'
            ]);

            $branch_id = '';
            $qCustomers = Mst_customer::where([
                'id'=>$request->customer_id,
                'active'=>'Y',
            ])
            ->first();
            if($qCustomers){
                $branch_id = $qCustomers->branch_id;
            }

            $vat_val = 1;
            $qVat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($qVat){
                $vat_val = $qVat->numeric_val;
            }

            $isFullPayment = 'Y';
            $isVATforAutoJournal = '';
            $payment_total_before_tax = 0;
            if ($request->totalRow > 0) {
                $is_full_payment = '';
                for ($i = 0; $i < $request->totalRow; $i++) {
                    // echo str_replace(",","",$request['total_inv_'.$i]);
                    if ($request['invoice_no_'.$i]) {
                        $is_full_payment = ((floor(GlobalFuncHelper::moneyValidate(str_replace(",","",$request['total_inv_'.$i])))<floor(GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i])))?'N':'Y');
                        $payment_total_per_inv = (floor(GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]))>floor(GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?
                            GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]):GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]));
                        $payment_total_full_per_inv = GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]);

                        if (strpos("invoice-".$request['invoice_no_'.$i],env('P_INVOICE'))>0){
                            $isVATforAutoJournal = 'Y';
                            $billingProcess = Tx_invoice::where('invoice_no','=',urldecode($request['invoice_no_'.$i]))
                            ->first();
                            if ($billingProcess){
                                // $branch_id = $billingProcess->branch_id;
                                $updInv = Tx_payment_receipt_invoice::where([
                                    'id'=>$request['payment_receipt_inv_id'.$i],
                                ])
                                ->first();
                                if ($updInv){
                                    $updInv = Tx_payment_receipt_invoice::where([
                                        'id'=>$request['payment_receipt_inv_id'.$i],
                                    ])
                                    ->update([
                                        'payment_receipt_id' => $qPr->id,
                                        'invoice_id' => $billingProcess->id,
                                        'invoice_no' => $billingProcess->invoice_no,
                                        'description' => $request['desc_'.$i],
                                        'total_payment' => $payment_total_per_inv,
                                        'total_payment_after_vat' => ($payment_total_per_inv+(($payment_total_per_inv*$vat_val)/100)),
                                        'total_payment_full' => $payment_total_full_per_inv,
                                        'total_payment_full_after_vat' => $payment_total_full_per_inv+(($payment_total_full_per_inv*$vat_val)/100),
                                        'is_full_payment' => $is_full_payment,
                                        'is_vat' => 'Y',
                                        'active' => 'Y',
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }else{
                                    $insInv = Tx_payment_receipt_invoice::create([
                                        'payment_receipt_id' => $qPr->id,
                                        'invoice_id' => $billingProcess->id,
                                        'invoice_no' => $billingProcess->invoice_no,
                                        'description' => $request['desc_'.$i],
                                        'total_payment' => $payment_total_per_inv,
                                        'total_payment_after_vat' => ($payment_total_per_inv+(($payment_total_per_inv*$vat_val)/100)),
                                        'total_payment_full' => $payment_total_full_per_inv,
                                        'total_payment_full_after_vat' => $payment_total_full_per_inv+(($payment_total_full_per_inv*$vat_val)/100),
                                        'is_full_payment' => $is_full_payment,
                                        'is_vat' => 'Y',
                                        'active' => 'Y',
                                        'created_by' => Auth::user()->id,
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }
                            }
                        }

                        if (strpos("invoice-".$request['invoice_no_'.$i],env('P_KWITANSI'))>0){
                            $isVATforAutoJournal = 'N';
                            $prosesTagihan = Tx_kwitansi::where('kwitansi_no','=',urldecode($request['invoice_no_'.$i]))
                            ->first();
                            if ($prosesTagihan){
                                $updInv = Tx_payment_receipt_invoice::where([
                                    'id'=>$request['payment_receipt_inv_id'.$i],
                                ])
                                ->first();
                                if ($updInv){
                                    $insInv = Tx_payment_receipt_invoice::where([
                                        'id'=>$request['payment_receipt_inv_id'.$i],
                                    ])
                                    ->update([
                                        'payment_receipt_id' => $qPr->id,
                                        'invoice_id' => $prosesTagihan->id,
                                        'invoice_no' => $prosesTagihan->kwitansi_no,
                                        'description' => $request['desc_'.$i],
                                        'total_payment' => $payment_total_per_inv,
                                        'total_payment_after_vat' => $payment_total_per_inv,
                                        'total_payment_full' => $payment_total_full_per_inv,
                                        'total_payment_full_after_vat' => $payment_total_full_per_inv,
                                        'is_full_payment' => $is_full_payment,
                                        'is_vat' => 'N',
                                        'active' => 'Y',
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }else{
                                    $insInv = Tx_payment_receipt_invoice::create([
                                        'payment_receipt_id' => $qPr->id,
                                        'invoice_id' => $prosesTagihan->id,
                                        'invoice_no' => $prosesTagihan->kwitansi_no,
                                        'description' => $request['desc_'.$i],
                                        'total_payment' => $payment_total_per_inv,
                                        'total_payment_after_vat' => $payment_total_per_inv,
                                        'total_payment_full' => $payment_total_full_per_inv,
                                        'total_payment_full_after_vat' => $payment_total_full_per_inv,
                                        'is_full_payment' => $is_full_payment,
                                        'is_vat' => 'N',
                                        'active' => 'Y',
                                        'created_by' => Auth::user()->id,
                                        'updated_by' => Auth::user()->id,
                                    ]);
                                }
                            }
                        }

                        $payment_total_before_tax += $payment_total_per_inv;
                        if($isFullPayment=='Y'){
                            $isFullPayment = $is_full_payment;
                        }
                    }
                }
            }

            // update total qty
            $payment_total = GlobalFuncHelper::moneyValidate($request->total_payment);
            $upd = Tx_payment_receipt::where('id','=',$qPr->id)
            ->update([
                'payment_total' => $payment_total,
                'payment_total_before_vat' => $payment_total_before_tax,
                'payment_total_after_vat' => ($isVATforAutoJournal=='Y'?($payment_total_before_tax+(($payment_total_before_tax*$vat_val)/100)):$payment_total_before_tax),
                'is_full_payment' => $isFullPayment,
            ]);

            $methodNm = '';
            switch ($request->payment_mode_id) {
                case 1:
                    $methodNm = 'Cash';
                    break;
                case 2:
                    $methodNm = 'Bank';
                    break;
                case 3:
                    $methodNm = 'Customer Deposit';
                    break;
                default:
                    $methodNm = 'Cash';
            }

            // cek apakah fitur automatic journal untuk retur sudah tersedia - PPN
            $qAutJournal = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>7,
                'branch_id'=>$branch_id,
                'method_id'=>$request->payment_mode_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournal && $request->is_draft=='N' && $isVATforAutoJournal=='Y'){
                // cash atau bank
                $qAutJournal_cash = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                // ->whereRaw('LOWER(`desc`)='.($request->payment_mode_id==1?'\'cash\'':'\'bank\''))
                ->first();
                // discount
                $qAutJournal_discount = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'discount\'')
                ->first();
                // admin bank
                $qAutJournal_admin_bank = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                ->first();
                // biaya kirim
                $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                ->first();
                // penerimaan lainnya
                $qAutJournal_penerimaan_lainnya = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                ->first();
                // piutang
                $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>7,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'piutang\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_general_journal::where([
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>7,
                    'active'=>'Y',
                ])
                ->first();
                if ($qJournals){
                    // non aktifkan jurnal detail jika ada
                    $updJournalDtl = Tx_general_journal_detail::where('general_journal_id','=',$qJournals->id)
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $yearTemp = substr($payment_date[2],2,2);
                    $monthTemp = (strlen($payment_date[1])==1?'0'.$payment_date[1]:$payment_date[1]);
                    $ymTemp = $yearTemp.$monthTemp;
                    $zero = '';
                    $YearMonth = '';
                    $newInc = 1;
                    $identityName = 'tx_general_journal';

                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    if ($autoInc) {
                        // jika counter sudah terbentuk

                        $date = date_format(date_create($autoInc->updated_at), "n");        // ambil bulan terakhir di counter jurnal terkait
                        $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                        $dateNow = date("ym");
                        if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                            // jika bulan di server sudah berganti

                            // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                            // untuk menghindari duplikasi
                            $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                            ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('general_journal_no', 'DESC')
                            ->first();
                            if ($lastCounterIfAny){
                                $newInc = $lastCounterIfAny->lastCounter+1;
                            }

                        } else {
                            // jika bulan di server sama dengan bulan jurnal yg dipilih
                            
                            $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                            $updInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->update([
                                'id_auto_inc' => $newInc
                            ]);
                        }

                        $YearMonth = $yearTemp.$monthTemp;
                    } else {
                        // jika counter belum pernah terbentuk

                        $dateNow = date("ym");
                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }

                        $insInc = Auto_inc::create([
                            'identity_name'=>$identityName,
                            'id_auto_inc'=>$newInc
                        ]);

                        $YearMonth = date('y').date('m');
                    }
                    for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                        $zero .= '0';
                    }
                    $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_general_journal::create([
                        'general_journal_no'=>$journal_no,
                        'general_journal_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],
                        // 'general_journal_date'=>date("Y-m-d"),
                        'total_debit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'total_kredit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'module_no'=>$payment_receipt_no,
                        'automatic_journal_id'=>7,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                // cash/bank/customer deposit
                $ins_cash = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_cash->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100)-
                        ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                        ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                        ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                        ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // discount
                $ins_discount = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // admin bank
                $ins_admin_bank = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_admin_bank->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // biaya kirim
                $ins_biaya_kirim = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // penerimaan lainnya
                $ins_penerimaan_lainnya = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_penerimaan_lainnya->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // piutang
                $ins_piutang = Tx_general_journal_detail::create([
                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_piutang->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($payment_total_before_tax+($payment_total_before_tax*$vat_val)/100),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);
            }

            // cek apakah fitur automatic journal untuk retur sudah tersedia - Non PPN
            $qAutJournal = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>14,
                'branch_id'=>$branch_id,
                'method_id'=>$request->payment_mode_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournal && $request->is_draft=='N' && $isVATforAutoJournal=='N'){
                // cash atau bank
                $qAutJournal_cash = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                // ->whereRaw('LOWER(`desc`)='.($request->payment_mode_id==1?'\'cash\'':'\'bank\''))
                ->first();
                // discount
                $qAutJournal_discount = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'discount\'')
                ->first();
                // admin bank
                $qAutJournal_admin_bank = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'admin bank\'')
                ->first();
                // biaya kirim
                $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                ->first();
                // penerimaan lainnya
                $qAutJournal_penerimaan_lainnya = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'method_id'=>$request->payment_mode_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'penerimaan lainnya\'')
                ->first();
                // piutang
                $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>14,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'piutang\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_lokal_journal::where([
                    'module_no'=>$payment_receipt_no,
                    'automatic_journal_id'=>14,
                    'active'=>'Y',
                ])
                ->first();
                if ($qJournals){
                    // non aktifkan jurnal detail jika ada
                    $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $yearTemp = substr($payment_date[2],2,2);
                    $monthTemp = (strlen($payment_date[1])==1?'0'.$payment_date[1]:$payment_date[1]);
                    $ymTemp = $yearTemp.$monthTemp;
                    $zero = '';
                    $YearMonth = '';
                    $newInc = 1;
                    $identityName = 'tx_lokal_journal';

                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    if ($autoInc) {
                        // jika counter sudah terbentuk

                        $date = date_format(date_create($autoInc->updated_at), "n");        // ambil bulan terakhir di counter jurnal terkait
                        $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                        $dateNow = date("ym");
                        if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                            // jika bulan di server sudah berganti

                            // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                            // untuk menghindari duplikasi
                            $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$yearTemp.$monthTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                            ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('general_journal_no', 'DESC')
                            ->first();
                            if ($lastCounterIfAny){
                                $newInc = $lastCounterIfAny->lastCounter+1;
                            }
                            
                        } else {
                            // jika bulan di server sama dengan bulan jurnal yg dipilih

                            $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                            $updInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->update([
                                'id_auto_inc' => $newInc
                            ]);
                        }

                        $YearMonth = $yearTemp.$monthTemp;
                    } else {
                        // jika counter belum pernah terbentuk

                        $dateNow = date("ym");
                        // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                        // untuk menghindari duplikasi
                        $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                        ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('general_journal_no', 'DESC')
                        ->first();
                        if ($lastCounterIfAny){
                            // ambil no urut terakhir dan ditambahkan 1

                            $newInc = $lastCounterIfAny->lastCounter+1;
                        }

                        $insInc = Auto_inc::create([
                            'identity_name'=>$identityName,
                            'id_auto_inc'=>$newInc
                        ]);

                        $YearMonth = date('y').date('m');
                    }
                    for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                        $zero .= '0';
                    }
                    $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_lokal_journal::create([
                        'general_journal_no'=>$journal_no,
                        'general_journal_date'=>$payment_date[2].'-'.$payment_date[1].'-'.$payment_date[0],
                        // 'general_journal_date'=>date("Y-m-d"),
                        'total_debit'=>$payment_total_before_tax-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'total_kredit'=>$payment_total_before_tax-
                            ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                            ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                            ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                            ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                        'module_no'=>$payment_receipt_no,
                        'automatic_journal_id'=>14,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                // cash/bank/customer deposit
                $ins_cash = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_cash->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>$payment_total_before_tax-
                        ($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0)-
                        ($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0)+
                        ($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0)+
                        ($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // discount
                $ins_discount = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->diskon_pembelian?GlobalFuncHelper::moneyValidate($request->diskon_pembelian):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // admin bank
                $ins_admin_bank = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_admin_bank->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>($request->admin_bank?GlobalFuncHelper::moneyValidate($request->admin_bank):0),
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // biaya kirim
                $ins_biaya_kirim = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->biaya_kirim?GlobalFuncHelper::moneyValidate($request->biaya_kirim):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // penerimaan lainnya
                $ins_penerimaan_lainnya = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_penerimaan_lainnya->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>($request->penerimaan_lainnya?GlobalFuncHelper::moneyValidate($request->penerimaan_lainnya):0),
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // piutang
                $ins_piutang = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_piutang->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>null,
                    'debit'=>0,
                    'kredit'=>$payment_total_before_tax,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
