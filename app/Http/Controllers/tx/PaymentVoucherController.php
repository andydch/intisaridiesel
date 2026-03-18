<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_coa;
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
use Illuminate\Support\Facades\Auth;
use App\Rules\SameTotPaymentAsTotInv;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_voucher_invoice;
use App\Rules\CheckRemainingPaymentVoucher;
use Illuminate\Validation\ValidationException;

class PaymentVoucherController extends Controller
{
    protected $title = 'Payment Voucher';
    protected $folder = 'payment-voucher';

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
            $query = Tx_payment_voucher::leftJoin('userdetails AS usr','tx_payment_vouchers.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id','=','mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by','=','users.id')
            ->select(
                'tx_payment_vouchers.id AS tx_payment_vouchers_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.created_by',
                'tx_payment_vouchers.active',
                'mst_suppliers.name AS supplier_name',
                'users.name AS created_by_name'
            )
            ->where('tx_payment_vouchers.active','=','Y')
            ->orderBy('tx_payment_vouchers.created_at','DESC');
        }else{
            $query = Tx_payment_voucher::leftJoin('userdetails AS usr','tx_payment_vouchers.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id','=','mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by','=','users.id')
            ->select(
                'tx_payment_vouchers.id AS tx_payment_vouchers_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.created_by',
                'tx_payment_vouchers.active',
                'mst_suppliers.name AS supplier_name',
                'users.name AS created_by_name'
            )
            ->where('tx_payment_vouchers.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_payment_vouchers.created_at','DESC');
        }

        $data = [
            'paymentVouchers' => $query->get(),
            'paymentVouchersCount' => $query->count(),
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
        $suppliers = Mst_supplier::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $condition = 'cash in bank';
        $coaCashInBank = Mst_coa::where('coa_parent','=',function (Builder $q) use ($condition) {
            $q->select('id')
            ->from('mst_coas AS coasLvl3')
            ->where(DB::raw('LOWER(coa_name)'),'=',$condition)
            ->where('coa_level','=',3)
            ->limit(1);
        })
        ->where([
            'coa_level' => 4,
            'active' => 'Y'
        ])
        ->get();

        $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
        ->whereNotIn('id', function ($q01) {
            $q01->select('receipt_order_id')
            ->from('tx_payment_voucher_invoices')
            ->where('is_full_payment','=','Y');
        })
        ->where('approved_by','<>',null)
        ->where('active','=','Y')
        ->orderBy('invoice_no','ASC')
        ->get();

        $paymentRef = Mst_global::where([
            'data_cat' => 'payment-ref',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'suppliers' => $suppliers,
            'coaCashInBank' => $coaCashInBank,
            'receiptOrders' => $receiptOrders,
            'paymentRef' => $paymentRef
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
        $total = 0;
        for ($i = 0; $i < $request->totalRow; $i++) {
            if($request['total_inv_'.$i]){
                $total += GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]);
            }
        }
        $validateInput = [
            'payment_date' => 'required|date',
            'supplier_id' => 'required|numeric',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id' => 'required|numeric',
            'ref_id' => 'required|numeric',
            'reference_no' => 'required|max:255',
            'reference_date' => 'required|date',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'coa_id.numeric' => 'Please select a valid account',
            'ref_id.numeric' => 'Please select a valid reference',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
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
                        'invoice_no_'.$i => 'required|numeric'.str_replace('invoice_no_'.$i,"",$different_rule),
                        'desc_'.$i => 'required',
                        'total_inv_'.$i => ['required',new NumericCustom('Total'),new CheckRemainingPaymentVoucher($request['invoice_no_'.$i],0)],
                        // 'total_inv_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.numeric' => 'Please select a valid invoice no',
                        'desc_'.$i.'.required' => 'The description field is required',
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
                $payment_voucher_no = env('P_PAYMENT_VOUCHER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_payment_vouchers';
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
                $payment_voucher_no = env('P_PAYMENT_VOUCHER').date('y').'-'.$zero.strval($newInc);
            }

            $ins = Tx_payment_voucher::create([
                'payment_voucher_no' => $payment_voucher_no,
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_total' => GlobalFuncHelper::moneyValidate($request->total_payment),
                'coa_id' => $request->coa_id,
                'payment_reference_id' => $request->ref_id,
                'reference_no' => $request->reference_no,
                'reference_date' => $request->reference_date,
                // 'is_full_payment',
                'remark' => $request->remark,
                // 'approved_by',
                // 'approved_at',
                // 'canceled_by',
                // 'canceled_at',
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;
            // $maxId = Tx_payment_voucher::max('id');

            $isFullPayment = 'Y';
            if ($request->totalRow > 0) {
                $qty = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    $total_inv = GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]);
                    $total_inv_o = GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]);

                    if ($request['invoice_no_'.$i]) {
                        $ro = Tx_receipt_order::where('id','=',$request['invoice_no_'.$i])
                        ->first();
                        $insPart = Tx_payment_voucher_invoice::create([
                            'payment_voucher_id' => $maxId,
                            'receipt_order_id' => $request['invoice_no_'.$i],
                            'invoice_no' => $ro->invoice_no,
                            'description' => $request['desc_'.$i],
                            'total_payment' => (($total_inv>$total_inv_o)?$total_inv_o:$total_inv),
                            'is_full_payment' => (($total_inv<$total_inv_o)?'N':'Y'),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qty += (($total_inv>$total_inv_o)?$total_inv_o:$total_inv);
                        if($isFullPayment=='Y'){
                            $isFullPayment = (($total_inv<$total_inv_o)?'N':'Y');
                        }
                    }
                }
            }

            // update total qty
            $upd = Tx_payment_voucher::where('id','=',$maxId)
            ->update([
                'payment_total' => $qty,
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

        $query = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->get();
            $queryInvCount = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->count();

            $paymentInvId = $query->id;
            $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('payment_voucher_id','<>',$paymentInvId)
                ->where('is_full_payment','=','Y');
            })
            ->where('approved_by','<>',null)
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC')
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInvCount),
                // 'suppliers' => $suppliers,
                // 'coaCashInBank' => $coaCashInBank,
                // 'receiptOrders' => $receiptOrders,
                // 'paymentRef' => $paymentRef,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv,
                'qCurrency' => $qCurrency
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
        $suppliers = Mst_supplier::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $condition = 'cash in bank';
        $coaCashInBank = Mst_coa::where('coa_parent','=',function (Builder $q) use ($condition) {
            $q->select('id')
            ->from('mst_coas AS coasLvl3')
            ->where(DB::raw('LOWER(coa_name)'),'=',$condition)
            ->where('coa_level','=',3)
            ->limit(1);
        })
        ->where([
            'coa_level' => 4,
            'active' => 'Y'
        ])
        ->get();

        $paymentRef = Mst_global::where([
            'data_cat' => 'payment-ref',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $query = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->get();
            $queryInvCount = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->count();

            $paymentInvId = $query->id;
            $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('payment_voucher_id','<>',$paymentInvId)
                ->where('is_full_payment','=','Y');
            })
            ->where('approved_by','<>',null)
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC')
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInvCount),
                'suppliers' => $suppliers,
                'coaCashInBank' => $coaCashInBank,
                'receiptOrders' => $receiptOrders,
                'paymentRef' => $paymentRef,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv
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
        $total = 0;
        for ($i = 0; $i < $request->totalRow; $i++) {
            if($request['total_inv_'.$i]){
                $total += GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]);
            }
        }
        $qPv = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
        ->first();

        $validateInput = [
            'payment_date' => 'required|date',
            'supplier_id' => 'required|numeric',
            'total_payment' => ['required',new NumericCustom('Total'),new SameTotPaymentAsTotInv($total)],
            'coa_id' => 'required|numeric',
            'ref_id' => 'required|numeric',
            'reference_no' => 'required|max:255',
            'reference_date' => 'required|date',
        ];
        $errMsg = [
            'supplier_id.numeric' => 'Please select a valid supplier',
            'coa_id.numeric' => 'Please select a valid account',
            'ref_id.numeric' => 'Please select a valid reference',
            'total_payment.required' => 'The total field is required',
            'total_payment.numeric' => 'The total field is must be numeric',
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
                        'invoice_no_'.$i => 'required|numeric'.str_replace('invoice_no_'.$i,"",$different_rule),
                        'desc_'.$i => 'required',
                        'total_inv_'.$i => ['required',new NumericCustom('Total'),new CheckRemainingPaymentVoucher($request['invoice_no_'.$i],$request['inv_id_'.$i])],
                        // 'total_inv_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'invoice_no_'.$i.'.numeric' => 'Please select a valid invoice no',
                        'desc_'.$i.'.required' => 'The description field is required',
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
            $payment_vouchers = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
            ->where('payment_voucher_no','LIKE','%Draft%')
            ->first();
            if($payment_vouchers){
                // looking for draft sales_quotation no
                $draft = true;
                $payment_voucher_no = $payment_vouchers->payment_voucher_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_payment_vouchers';
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
                $payment_voucher_no = env('P_PAYMENT_VOUCHER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
                ->update([
                    'payment_voucher_no' => $payment_voucher_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
            ->update([
                'supplier_id' => $request->supplier_id,
                'payment_date' => $request->payment_date,
                'payment_total' => GlobalFuncHelper::moneyValidate($request->total_payment),
                'coa_id' => $request->coa_id,
                'payment_reference_id' => $request->ref_id,
                'reference_no' => $request->reference_no,
                'reference_date' => $request->reference_date,
                // 'is_full_payment',
                'remark' => $request->remark,
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
            if ($request->totalRow > 0) {
                $qty = 0;

                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['invoice_no_'.$i]) {
                        $ro = Tx_receipt_order::where('id','=',$request['invoice_no_'.$i])
                        ->first();

                        if ($request['inv_id_'.$i]==0) {
                            $insInv = Tx_payment_voucher_invoice::create([
                                'payment_voucher_id' => $qPv->id,
                                'receipt_order_id' => $request['invoice_no_'.$i],
                                'invoice_no' => $ro->invoice_no,
                                'description' => $request['desc_'.$i],
                                'total_payment' => ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])>GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]):GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])),
                                // 'total_payment' => GlobalFuncHelper::moneyValidate(($request['total_inv_'.$i]>$request['total_inv_o_'.$i])?$request['total_inv_o_'.$i]:$request['total_inv_'.$i]),
                                'is_full_payment' => ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])<GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?'N':'Y'),
                                // 'is_full_payment' => (($request['total_inv_'.$i]<$request['total_inv_o_'.$i])?'N':'Y'),
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $updInv = Tx_payment_voucher_invoice::where('id','=',$request['inv_id_'.$i])
                            ->update([
                                'payment_voucher_id' => $qPv->id,
                                'receipt_order_id' => $request['invoice_no_'.$i],
                                'invoice_no' => $ro->invoice_no,
                                'description' => $request['desc_'.$i],
                                'total_payment' => ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])>GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]):GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])),
                                // 'total_payment' => (($request['total_inv_'.$i]>$request['total_inv_o_'.$i])?$request['total_inv_o_'.$i]:$request['total_inv_'.$i]),
                                'is_full_payment' => ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])<GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?'N':'Y'),
                                // 'is_full_payment' => (($request['total_inv_'.$i]<$request['total_inv_o_'.$i])?'N':'Y'),
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                        $qty += ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])>GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]):GlobalFuncHelper::moneyValidate($request['total_inv_'.$i]));
                        // $qty += (($request['total_inv_'.$i]>$request['total_inv_o_'.$i])?$request['total_inv_o_'.$i]:$request['total_inv_'.$i]);
                        if($isFullPayment=='Y'){
                            $isFullPayment = ((GlobalFuncHelper::moneyValidate($request['total_inv_'.$i])<GlobalFuncHelper::moneyValidate($request['total_inv_o_'.$i]))?'N':'Y');
                        }
                    }
                }
            }

            // update total qty
            $upd = Tx_payment_voucher::where('id','=',$qPv->id)
            ->update([
                'payment_total' => $qty,
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
