<?php

namespace App\Http\Controllers\tx;

use App\Helpers\GlobalFuncHelper;
use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use App\Models\Tx_payment_plan_per_rc_order;
use App\Models\Tx_payment_plan;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_receipt_order;
use App\Models\Tx_tagihan_supplier;
use App\Models\Userdetail;
use App\Rules\NumericCustom;
use App\Rules\PaymentPlanPeriodDupCheck;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PaymentPlanServerSideController extends Controller
{
    protected $title = 'Rencana Pembayaran';
    protected $folder = 'payment-plan';
    protected $folder_per_ro = 'payment-plan-ro';
    protected $monthList = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        if ($request->ajax()){
            $query = Tx_payment_plan::select(
                'id as plan_id',
                DB::raw('DATE_FORMAT(payment_month, "%M %Y") as payment_month_f'),
                'beginning_balance',
                'bank_id',
                'is_draft',
            )
            ->whereExists(function($q){
                $q->selectRaw(1)
                ->from('mst_coas')
                ->whereColumn('mst_coas.id', 'tx_payment_plans.bank_id')
                ->where('is_cashflow', 'Y')
                ->where('active', 'Y');
            })
            ->where('active', 'Y')
            ->orderBy('payment_month','DESC');

            return DataTables::of($query)
            ->filterColumn('payment_month_f', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(payment_month, "%M %Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('payment_month_f', function ($query) {
                return $query->payment_month_f;
            })
            ->addColumn('beginning_balance_num', function ($query) {
                return number_format($query->beginning_balance,0,".",",");
            })
            ->filterColumn('coa_name', function($query, $keyword) {
                $query->whereIn('bank_id', function($q) use($keyword) {
                    $q->select('id')
                    ->from('mst_coas')
                    ->where('coa_name', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('coa_name', function ($query) {
                $coaName = '';
                $qCoa = Mst_coa::where([
                    'id'=>$query->bank_id,
                ])
                ->first();
                if ($qCoa){
                    $coaName = $qCoa->coa_name;
                }
                return $coaName;
            })
            ->addColumn('action', function ($query) {
                $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$query->plan_id.'/edit').'" style="text-decoration: underline;">Edit</a>
                    | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$query->plan_id).'" style="text-decoration: underline;">View</a>';
                return $links;
            })
            ->addColumn('status', function ($query) {
                $status = $query->is_draft=='Y'?'Draft':'Created';
                return $status;
            })
            ->rawColumns(['payment_month_f','beginning_balance_num','coa_name','action','status'])
            ->toJson();
        }

        $data = [
            'title'=>$this->title,
            'folder'=>$this->folder,
            'qCurrency'=>$qCurrency,
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
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $coas = Mst_coa::where('coa_code_complete','LIKE','112%%')
        ->whereIn('local', ['A','P','N'])
        ->where([
            'coa_level' => 5,
            'is_cashflow' => 'Y',
            'active' => 'Y',
        ])
        ->get();

        $data = [
            'title'=>$this->title,
            'folder'=>$this->folder,
            'qCurrency'=>$qCurrency,
            'coas'=>$coas,
            'monthList'=>$this->monthList,
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
            'menu_id' => 116,
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
            'month_id'=>'required|numeric',
            'year_id'=>['required', 'numeric', new PaymentPlanPeriodDupCheck(0,$request->year_id.'-'.$request->month_id,$request->bank_id)],
            'saldo_awal'=>['required',new NumericCustom('Saldo Awal')],
            'bank_id'=>'required|numeric',
        ];
        $errMsg = [
            'month_id.required'=>'The month field is required.',
            'month_id.numeric'=>'The month field is required.',
            'year_id.required'=>'The year field is required.',
            'year_id.numeric'=>'The year field is required.',
            'saldo_awal.required'=>'Saldo Awal field is required.',
            'month_id.required'=>'Please select a valid Bank Account.',
            'bank_id.numeric'=>'Please select a valid Bank Account.',
            'bank_id.required'=>'Please select a valid Bank Account.',
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

            $draft_at = $request->is_draft=='Y'?now():null;
            $draft_to_created_at = null;
            $ins = Tx_payment_plan::create([
                'payment_month'=>$request->year_id.'-'.$request->month_id.'-01',
                'beginning_balance'=>GlobalFuncHelper::moneyValidate($request->saldo_awal),
                'bank_id'=>$request->bank_id,
                'is_draft'=>$request->is_draft,
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'active'=>'Y',
                'created_by'=>Auth::user()->id,
                'updated_by'=>Auth::user()->id,
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
     * @param  \App\Models\Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $query = Tx_payment_plan::where([
            'id' => $id,
        ])
        ->first();
        if ($query) {
            if ($request->ajax()){
                $txPppRcOSubQuery = Tx_payment_plan_per_rc_order::select('payment_plan_id', 'tagihan_supplier_id', 'tagihan_supplier_no', 'supplier_id')
                ->where('active', 'Y')
                ->groupBy('payment_plan_id', 'tagihan_supplier_id', 'tagihan_supplier_no', 'supplier_id');
                $q = Tx_payment_plan::leftJoinSub($txPppRcOSubQuery, 'sub', function ($join) use($id) {
                    $join->on('tx_payment_plans.id', '=', 'sub.payment_plan_id')
                    ->where('tx_payment_plans.id', '=', $id)
                    ->where('tx_payment_plans.active', 'Y');
                })
                ->leftJoin('mst_suppliers AS msp', function($join) {
                    $join->on('sub.supplier_id', '=', 'msp.id')
                    ->where('msp.active', 'Y');
                })
                ->leftJoin('mst_globals as gb', function($join) {
                    $join->on('msp.entity_type_id', '=', 'gb.id')
                    ->where('gb.active', 'Y');
                })
                ->select(
                    'sub.tagihan_supplier_id AS tagihan_supplier_id',
                    'sub.tagihan_supplier_no AS tagihan_supplier_no',
                    'msp.name',
                    'msp.supplier_code',
                    'msp.name as supplier_name',
                    'gb.title_ind',
                )
                ->where('sub.payment_plan_id', $id);

                return DataTables::of($q)
                ->addColumn('plan_pay', function ($q) {
                    $plan_pay = '';
                    $qSubCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $q->tagihan_supplier_id)
                    ->where('active', 'Y')
                    ->orderBy('id', 'ASC');
                    foreach($qSubCts->get() as $qC){
                        $plan_pay .= number_format($qC->plan_pay, 0, ".", ",").'<br/>';
                    }
                    return $plan_pay;
                })
                ->filterColumn('plan_date', function ($q, $keyword) {
                    $q->whereIn('sub.tagihan_supplier_id', function($q1) use($keyword) {
                        $q1->select('tagihan_supplier_id')
                        ->from('tx_payment_plan_per_rc_orders')
                        ->whereRaw('DATE_FORMAT(plan_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                        ->where('active', 'Y');
                    });
                })
                ->editColumn('plan_date', function ($q) {
                    $plan_date = '';
                    $qSubCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $q->tagihan_supplier_id)
                    ->where('active', 'Y')
                    ->orderBy('id', 'ASC');
                    foreach($qSubCts->get() as $qC){
                        $plan_date .= date_format(date_create($qC->plan_date), "d/m/Y").'<br/>';
                    }
                    return $plan_date;
                })
                ->filterColumn('payment_voucher_no', function ($q, $keyword) {
                    $q->whereIn('sub.tagihan_supplier_id', function($q1) use($keyword) {
                        $q1->select('tagihan_supplier_id')
                        ->from('tx_payment_plan_per_rc_orders')
                        ->whereRaw('payment_voucher_no LIKE ?', ["%{$keyword}%"])
                        // ->where('is_pv_approved', 'Y')
                        ->where('active', 'Y');
                    });
                })
                ->editColumn('payment_voucher_no', function ($q) {
                    $pv_no = '';
                    $qSubCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $q->tagihan_supplier_id)
                    ->where('active', 'Y')
                    ->orderBy('id', 'ASC');
                    foreach($qSubCts->get() as $qC){
                        $pv_no .= $qC->payment_voucher_no.'<br/>';
                        // $pv_no .= $qC->is_pv_approved=='Y'?$qC->payment_voucher_no.'<br/>':'<br/>';
                    }
                    return $pv_no;
                })
                ->addColumn('actual_payment', function ($q) {
                    $actual_payment = '';
                    $qSubCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $q->tagihan_supplier_id)
                    ->where('active', 'Y')
                    ->orderBy('id', 'ASC');
                    foreach($qSubCts->get() as $qC){
                        $actual_payment .= $qC->is_pv_approved=='Y'? number_format($qC->actual_payment, 0, ".", ",").'<br/>' : '<br/>';
                    }
                    return $actual_payment;
                })
                ->filterColumn('actual_date', function ($q, $keyword) {
                    $q->whereIn('sub.tagihan_supplier_id', function($q1) use($keyword) {
                        $q1->select('tagihan_supplier_id')
                        ->from('tx_payment_plan_per_rc_orders')
                        ->whereRaw('DATE_FORMAT(actual_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                        ->where('is_pv_approved', 'Y')
                        ->where('active', 'Y');
                    });
                })
                ->editColumn('actual_date', function ($q) {
                    $actual_date = '';
                    $qSubCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $q->tagihan_supplier_id)
                    ->where('active', 'Y')
                    ->orderBy('id', 'ASC');
                    foreach($qSubCts->get() as $qC){
                        $actual_date .= $qC->is_pv_approved=='Y'?date_format(date_create($qC->actual_date), "d/m/Y").'<br/>':'<br/>';
                    }
                    return $actual_date;
                })
                ->filterColumn('supplier_name', function($q, $keyword) {
                    $q->where(function($q) use ($keyword) {
                        $q->where('msp.name', 'like', "%{$keyword}%")
                        ->orWhere('msp.supplier_code', 'like', "%{$keyword}%")
                        ->orWhere('gb.title_ind', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('supplier_name', function ($q) {
                    return $q->supplier_code.' - '.$q->title_ind.' '.$q->supplier_name;
                })
                ->filterColumn('receipt_orders_no', function ($q, $keyword) {
                    $q->whereIn('sub.tagihan_supplier_id', function($q) use($keyword) {
                        $q->select('tx_tsd.tagihan_supplier_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_receipt_orders as tx_ro', 'tx_tsd.receipt_order_id', '=', 'tx_ro.id')
                        ->where('tx_ro.receipt_no', 'LIKE', "%{$keyword}%");
                    });
                })
                ->editColumn('receipt_orders_no', function ($q) {
                    $ro_numbers = '';
                    $qRO = Tx_receipt_order::select(
                        'id as ro_id',                    
                        'receipt_no',                    
                    )
                    ->whereIn('id', function($qTsuDtl) use($q){
                        $qTsuDtl->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->where([
                            'tx_tsd.active' => 'Y',
                            'tx_tsd.tagihan_supplier_id' => $q->tagihan_supplier_id,
                        ]);
                    })
                    ->where('is_draft', 'N')
                    ->where('active', 'Y')
                    ->orderBy('receipt_no', 'asc')
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
                ->filterColumn('receipt_orders_invoices', function ($q, $keyword) {
                    $q->whereIn('sub.tagihan_supplier_id', function($qTsuDtl) use($keyword) {
                        $qTsuDtl->select('tx_tsd.tagihan_supplier_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_receipt_orders as tx_ro', 'tx_tsd.receipt_order_id', '=', 'tx_ro.id')
                        ->where('tx_ro.invoice_no', 'LIKE', "%{$keyword}%");
                    });
                })
                ->editColumn('receipt_orders_invoices', function ($q) {
                    $inv_numbers = '';
                    $qRO = Tx_receipt_order::select(
                        'invoice_no',                    
                    )
                    ->whereIn('id', function($qTsuDtl) use($q){
                        $qTsuDtl->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->where([
                            'tx_tsd.active' => 'Y',
                            'tx_tsd.tagihan_supplier_id' => $q->tagihan_supplier_id,
                        ]);
                    })
                    ->where('is_draft', 'N')
                    ->where('active', 'Y')
                    ->orderBy('receipt_no', 'asc')
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
                ->addColumn('action', function ($q) use($id){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/vcts/'.urlencode($q->tagihan_supplier_no)).'/?ap='.$id.'" style="text-decoration: underline;">View</a>';
                    return $links;
                })
                ->rawColumns([
                    'plan_pay',
                    'plan_date',
                    'receipt_orders_no',
                    'receipt_orders_invoices',
                    'payment_voucher_no',
                    'actual_payment',
                    'actual_date',
                    'action',
                ])
                ->toJson();
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'bank_name'=>$query->bank->coa_name,
                'qCurrency'=>$qCurrency,
                'qPlans'=>$query,
            ];
            return view('tx.'.$this->folder.'.index-ro-server-side', $data);

        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    public function show_tagihan_supplier(Request $request, $tagihan_supplier_no)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $qCts = Tx_payment_plan_per_rc_order::where('tagihan_supplier_no', $tagihan_supplier_no)
        ->where('active', 'Y')
        ->orderBy('id', 'ASC')
        ->first();
        if ($qCts){
            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                // 'bank_name'=>$query->bank->coa_name,
                'qCurrency'=>$qCurrency,
                'qCts'=>$qCts,
                'ap'=>$request->ap,
            ];
            return view('tx.'.$this->folder.'.show-per-cts', $data);
        }else{
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $coas = Mst_coa::where('coa_code_complete','LIKE','112%%')
        ->whereIn('local', ['A','P','N'])
        ->where([
            'coa_level' => 5,
            'active' => 'Y',
        ])
        ->get();

        $query = Tx_payment_plan::where([
            'id'=>$id,
        ])
        ->first();
        if ($query) {
            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'qCurrency'=>$qCurrency,
                'plans'=>$query,
                'coas'=>$coas,
                'monthList'=>$this->monthList,
            ];
            return view('tx.'.$this->folder.'.edit', $data);
        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 116,
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
            'month_id'=>'required|numeric',
            'year_id'=>['required', 'numeric', new PaymentPlanPeriodDupCheck($id,$request->year_id.'-'.$request->month_id,$request->bank_id)],
            'saldo_awal'=>['required',new NumericCustom('Saldo Awal')],
            'bank_id'=>'required|numeric',
        ];
        $errMsg = [
            'month_id.required'=>'The month field is required.',
            'month_id.numeric'=>'The month field is required.',
            'year_id.required'=>'The year field is required.',
            'year_id.numeric'=>'The year field is required.',
            'saldo_awal.required'=>'Saldo Awal field is required.',
            'bank_id.numeric'=>'Please select a valid Bank Account.',
            'bank_id.required'=>'Please select a valid Bank Account.',
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

            $draft_at = $request->is_draft=='Y'?now():null;
            $draft_to_created_at = $request->is_draft=='N'?now():null;
            $ins = Tx_payment_plan::where([
                'id'=>$id,
            ])
            ->update([
                'payment_month'=>$request->year_id.'-'.$request->month_id.'-01',
                'beginning_balance'=>GlobalFuncHelper::moneyValidate($request->saldo_awal),
                'bank_id'=>$request->bank_id,
                'is_draft'=>$request->is_draft,
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'active'=>'Y',
                'updated_by'=>Auth::user()->id,
            ]);

            // // cek setiap RO yg memiliki jatuh tempo sesuai pilihan periode
            // $q = Tx_receipt_order::leftJoin('mst_suppliers as sp','tx_receipt_orders.supplier_id','=','sp.id')
            // ->leftJoin('tx_tagihan_suppliers as tx_ts','sp.id','=','tx_ts.supplier_id')
            // ->select(
            //     'tx_receipt_orders.id as ro_id',
            //     'tx_receipt_orders.receipt_date',
            //     // 'tx_receipt_orders.total_after_vat',
            //     DB::raw('IF(ISNULL(tx_receipt_orders.total_after_vat_rp), tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as total_after_vat'),
            //     // 'sp.payment_from_id as bank_id',
            //     'sp.top as supplier_top',
            //     DB::raw('DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY) AS due_date_payment'),
            //     'tx_ts.bank_id as bank_id',
            // )
            // ->whereRaw('DATE_FORMAT(DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY), "%c-%Y")=\''.$request->month_id.'-'.$request->year_id.'\'')
            // ->where('tx_receipt_orders.is_draft', 'N')
            // ->where('tx_receipt_orders.active', 'Y')
            // ->where('tx_ts.bank_id', $request->bank_id)
            // ->orderBy('tx_receipt_orders.receipt_date','DESC')
            // ->get();
            // foreach($q as $ro){
            //     $qDtl = Tx_payment_plan_per_rc_order::where([
            //         'payment_plan_id'=>$id,
            //         'plan_date'=>$ro->due_date_payment,
            //         'receipt_order_id'=>$ro->ro_id,
            //     ])
            //     ->first();
            //     if (!$qDtl){
            //         $ins = Tx_payment_plan_per_rc_order::create([
            //             'payment_plan_id'=>$id,
            //             'plan_date'=>$ro->due_date_payment,
            //             'plan_pay'=>$ro->total_after_vat,
            //             'receipt_order_id'=>$ro->ro_id,
            //             'active'=>'Y',
            //             'created_by'=>Auth::user()->id,
            //             'updated_by'=>Auth::user()->id,
            //         ]);
            //     }
            // }

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
     * @param  \App\Models\Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function sync_doc($id, $date, $bank_id)
    {
        // Start transaction!
        DB::beginTransaction();

        try {
            $qPlan = Tx_payment_plan::whereRaw('DATE_FORMAT(payment_month, "%Y-%m")=\''.$date.'\'')
            ->where('bank_id', $bank_id)
            ->where('is_draft', 'N')
            ->where('active', 'Y')
            ->first();
            if ($qPlan){
                // sinkronisasikan ID plan detail terhadap ID plan induk
                $qPlanDtl = Tx_payment_plan_per_rc_order::leftJoin('tx_tagihan_suppliers AS tx_ts', 'tx_payment_plan_per_rc_orders.tagihan_supplier_id', '=', 'tx_ts.id')
                ->select(
                    'tx_payment_plan_per_rc_orders.id AS tx_ppro_id',
                    'tx_ts.tagihan_supplier_no',
                    'tx_ts.bank_id AS tx_ts_bank_id',
                )
                ->where('tx_payment_plan_per_rc_orders.payment_plan_id', '<>', $qPlan->id)
                ->whereRaw('DATE_FORMAT(tx_payment_plan_per_rc_orders.plan_date, "%Y-%m")=\''.$date.'\'')
                ->where('tx_payment_plan_per_rc_orders.active', 'Y')
                ->where('tx_ts.bank_id', $bank_id)
                ->get();
                foreach($qPlanDtl as $qPDtl){
                    $updPlanDtl = Tx_payment_plan_per_rc_order::where('id', $qPDtl->tx_ppro_id)
                    ->update([
                        'payment_plan_id' => $qPlan->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
                // sinkronisasikan ID plan detail terhadap ID plan induk

                // cari data plan detail yg memiliki perbedaan periode dengan periode di plan induk dan perbedaan kode bank
                $qPlanDtl = Tx_payment_plan_per_rc_order::leftJoin('tx_tagihan_suppliers AS tx_ts', 'tx_payment_plan_per_rc_orders.tagihan_supplier_id', '=', 'tx_ts.id')
                ->select(
                    'tx_payment_plan_per_rc_orders.id AS tx_ppro_id',
                    'tx_payment_plan_per_rc_orders.plan_date',
                    'tx_ts.tagihan_supplier_no',
                    'tx_ts.bank_id AS tx_ts_bank_id',
                )
                ->where('tx_payment_plan_per_rc_orders.payment_plan_id', $qPlan->id)
                ->whereRaw('DATE_FORMAT(tx_payment_plan_per_rc_orders.plan_date, "%Y-%m")=\''.$date.'\'')
                ->where('tx_payment_plan_per_rc_orders.active', 'Y')
                ->where('tx_ts.bank_id', '<>', $bank_id)
                ->get();
                foreach($qPlanDtl as $qPDtl){
                    $qPlanA = Tx_payment_plan::whereRaw('DATE_FORMAT(payment_month, "%Y-%m")=\''.date_format(date_create($qPDtl->plan_date),"Y-m").'\'')
                    ->where('bank_id', $qPDtl->tx_ts_bank_id)
                    ->where('is_draft', 'N')
                    ->where('active', 'Y')
                    ->first();
                    if ($qPlanA){
                        $updPlanDtlA = Tx_payment_plan_per_rc_order::where('id', $qPDtl->tx_ppro_id)
                        ->update([
                            'payment_plan_id' => $qPlanA->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
                // cari data plan detail yg memiliki perbedaan periode dengan periode di plan induk
            }

            // kumpulkan semua CTS aktif yang belum pernah dibuatkan plan pembayarannya
            // $qCts = Tx_tagihan_supplier::whereNotIn('id', function($q1) {
            //     $q1->select('tagihan_supplier_id')
            //     ->from('tx_payment_plan_per_rc_orders')
            //     ->where('active', 'Y');
            // })
            // ->whereIn('id', function($q1) {
            //     $q1->select('tagihan_supplier_id')
            //     ->from('tx_payment_vouchers')
            //     ->whereRaw('payment_voucher_no IS NOT null')
            //     ->whereRaw('tagihan_supplier_id IS NOT null')
            //     ->where('is_draft', 'N')
            //     ->where('active', 'Y');
            // })
            // ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$date.'\'')
            // ->where('bank_id', $bank_id)
            // ->where('active', 'Y')
            // ->orderBy('id', 'ASC')
            // ->get();

            $qCts = Tx_tagihan_supplier::leftJoin('tx_payment_vouchers AS tx_pv', 'tx_pv.tagihan_supplier_id', '=', 'tx_tagihan_suppliers.id')
            ->select(
                'tx_tagihan_suppliers.id AS tx_ts_id',
                'tx_tagihan_suppliers.supplier_id',
                'tx_tagihan_suppliers.tagihan_supplier_no',
                'tx_tagihan_suppliers.tagihan_supplier_date',
                'tx_tagihan_suppliers.grandtotal_price',
                'tx_pv.id AS payment_voucher_id',
                'tx_pv.payment_voucher_no',
                'tx_pv.payment_date',
                'tx_pv.payment_total_after_vat',
                'tx_pv.approved_by',
            )
            ->addSelect([
                'next_plan_date' => Tx_payment_voucher::select('next_plan_date')
                    ->whereRaw('id < tx_pv.id')
                    ->whereNotNull('payment_voucher_no')
                    ->where('payment_voucher_no', '<>', 'tx_pv.payment_voucher_no')
                    ->where('tagihan_supplier_id', 'tx_tagihan_suppliers.id')
                    ->where('active', 'Y')
                    ->orderBy('id', 'desc')
                    ->limit(1)
            ])
            ->addSelect([
                'last_plan_payment' => Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat)')
                    ->whereRaw('id < tx_pv.id')
                    ->whereNotNull('payment_voucher_no')
                    ->where('payment_voucher_no', '<>', 'tx_pv.payment_voucher_no')
                    ->where('tagihan_supplier_id', 'tx_tagihan_suppliers.id')
                    ->where('active', 'Y')
            ])
            ->where(function($q) use($id){
                $q->whereNotIn('tx_tagihan_suppliers.id', function($q1) use($id){
                    $q1->select('tagihan_supplier_id')
                    ->from('tx_payment_plan_per_rc_orders')
                    ->where('payment_plan_id', $id)
                    ->where('active', 'Y');
                })
                ->orWhereNotIn('tx_pv.payment_voucher_no', function($q1) use($id){
                    $q1->select('payment_voucher_no')
                    ->from('tx_payment_plan_per_rc_orders')
                    ->where('payment_plan_id', $id)
                    ->whereNotNull('payment_voucher_no')
                    ->where('active', 'Y');
                });
            })
            ->whereRaw('DATE_FORMAT(tx_tagihan_suppliers.tagihan_supplier_date, "%Y-%m")=\''.$date.'\'')
            ->where('tx_tagihan_suppliers.bank_id', $bank_id)
            ->where('tx_tagihan_suppliers.active', 'Y')
            ->where('tx_pv.active', 'Y')
            ->orderBy('tx_tagihan_suppliers.tagihan_supplier_date', 'ASC')
            ->get();

            $lastTagihanSupplierNo = '';
            $lastTagihanSupplierId = 0;
            $lastSupplierId = 0;
            $last_plan_pay = 0;
            $last_plan_payment = null;
            $lastPaymentVoucherNo = '';
            $lastPaymentDate = '';
            foreach($qCts as $qC){
                // Log::debug([
                //     'payment_voucher_no' => $qC->payment_voucher_no,
                //     'grandtotal_price' => $qC->grandtotal_price,
                //     'last_plan_payment' => $qC->last_plan_payment?$qC->last_plan_payment:777,
                //     'last_plan_pay' => $qC->last_plan_payment?$qC->grandtotal_price-$qC->last_plan_payment:$qC->grandtotal_price,
                // ]);
                if ($qC->tagihan_supplier_no!=$lastTagihanSupplierNo && $lastTagihanSupplierNo!='' && $last_plan_pay>0 && $lastPaymentVoucherNo!='' && $last_plan_payment){
                    // cari pembayaran supplier terakhir dengan pembayaran sebagian, maka buatkan plan pembayaran berikutnya
                    $ins = Tx_payment_plan_per_rc_order::create([
                        'payment_plan_id' => $id,
                        'supplier_id' => $lastSupplierId,
                        'tagihan_supplier_id' => $lastTagihanSupplierId,
                        'tagihan_supplier_no' => $lastTagihanSupplierNo,
                        'plan_date' => $lastPaymentDate,
                        'plan_pay' => $last_plan_pay,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);

                    $updPV = Tx_payment_voucher::where('payment_voucher_no', $lastPaymentVoucherNo)
                    ->where('active', 'Y')
                    ->update([
                        'next_plan_date' => date_format(date_add(date_create($lastPaymentDate), date_interval_create_from_date_string("1 days")), "Y-m-d"),
                        'next_plan_date_status' => 'Y',
                        'updated_by' => Auth::user()->id,
                    ]);
                }
                $lastTagihanSupplierNo = $qC->tagihan_supplier_no;
                $lastTagihanSupplierId = $qC->tx_ts_id;
                $lastSupplierId = $qC->supplier_id;
                $last_plan_pay = $qC->last_plan_payment?$qC->grandtotal_price-$qC->last_plan_payment:$qC->grandtotal_price;
                $last_plan_payment = $qC->last_plan_payment;
                $lastPaymentVoucherNo = $qC->payment_voucher_no;
                $lastPaymentDate = $qC->payment_date;

                $qDtl = Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $qC->tx_ts_id)
                ->whereNull('payment_voucher_no')
                ->where('active', 'Y')
                ->orderBy('id', 'asc')
                ->first();
                if ($qDtl){
                    $upd = Tx_payment_plan_per_rc_order::where('id', $qDtl->id)
                    ->update([
                        'payment_voucher_id' => $qC->payment_voucher_id,
                        'payment_voucher_no' => $qC->payment_voucher_no,
                        'actual_date' => $qC->payment_date,
                        'actual_payment' => $qC->payment_total_after_vat,
                        'is_pv_approved' => $qC->approved_by?'Y':'N',
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $ins = Tx_payment_plan_per_rc_order::create([
                        'payment_plan_id' => $id,
                        'supplier_id' => $qC->supplier_id,
                        'tagihan_supplier_id' => $qC->tx_ts_id,
                        'tagihan_supplier_no' => $qC->tagihan_supplier_no,
                        'plan_date' => $qC->tagihan_supplier_date,
                        'plan_pay' => $qC->last_plan_payment?$qC->grandtotal_price-$qC->last_plan_payment:$qC->grandtotal_price,
                        'payment_voucher_id' => $qC->payment_voucher_id,
                        'payment_voucher_no' => $qC->payment_voucher_no,
                        'actual_date' => $qC->payment_date,
                        'actual_payment' => $qC->payment_total_after_vat,
                        'is_pv_approved' => $qC->approved_by?'Y':'N',
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }
            // kumpulkan semua CTS aktif yang belum pernah dibuatkan plan pembayarannya

            // // reset
            // $qUpdDtlSetZero = Tx_payment_plan_per_rc_order::where('payment_plan_id', ($qPlan?$qPlan->id:0))
            // ->update([
            //     'payment_voucher_id' => null,
            //     'payment_voucher_no' => null,
            //     'actual_date' => null,
            //     'actual_payment' => 0,
            //     'is_pv_approved' => 'N',
            //     'updated_by' => Auth::user()->id,
            // ]);
            // // reset

            // kumpulkan CTS yg sudah terbentuk plan nya tapi PV nya masih kosong di rencana pembayaran
            $qCts = Tx_tagihan_supplier::whereIn('id', function($q1) {
                $q1->select('tagihan_supplier_id')
                ->from('tx_payment_plan_per_rc_orders')
                ->whereRaw('payment_voucher_id IS null')
                ->where('active', 'Y');
            })
            ->whereRaw('DATE_FORMAT(tagihan_supplier_date, "%Y-%m")=\''.$date.'\'')
            ->where('bank_id', $bank_id)
            ->where('active', 'Y')
            ->orderBy('id', 'ASC')
            ->get();
            foreach($qCts as $qC){
                $qPvCts = Tx_payment_voucher::where('tagihan_supplier_id', $qC->id)
                ->whereRaw('payment_voucher_no IS NOT null')
                ->where('active', 'Y')
                ->orderBy('id', 'asc')
                ->first();
                if ($qPvCts){
                    // Log::debug($qPvCts->tagihan_supplier_id);
                    $upd = Tx_payment_plan_per_rc_order::where('tagihan_supplier_no', $qC->tagihan_supplier_no)
                    ->update([
                        'payment_voucher_id' => $qPvCts->pv_id,
                        'payment_voucher_no' => $qPvCts->payment_voucher_no,
                        'actual_date' => $qPvCts->payment_date,
                        'actual_payment' => $qPvCts->payment_total_after_vat,
                        'is_pv_approved' => $qPvCts->approved_by!=null?'Y':'N',
                        'active' => 'Y',
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }
            // kumpulkan CTS yg sudah terbentuk plan nya tapi PV nya masih kosong di rencana pembayaran

            $next_cts_payment = 0;
            $next_cts_id = 0;
            $next_pv_payment = 0;
            // kumpulkan semua PV aktif yang memiliki ID dari tagihan supplier
            // PV boleh berstatus approved atau belum
            // PV yg dibaca adalah yang belum pernah masuk ke rencana pembayaran
            $qPvCts = Tx_payment_voucher::leftJoin('tx_tagihan_suppliers AS tx_ts', function($join) use($bank_id){
                $join->on('tx_ts.id', '=', 'tx_payment_vouchers.tagihan_supplier_id')
                ->where('tx_ts.bank_id', $bank_id)
                ->where('tx_ts.active', 'Y');
            })
            ->select(
                'tx_payment_vouchers.id AS pv_id', 
                'tx_payment_vouchers.payment_voucher_no', 
                'tx_payment_vouchers.payment_date', 
                'tx_payment_vouchers.payment_total_after_vat', 
                'tx_payment_vouchers.coa_id',
                'tx_payment_vouchers.tagihan_supplier_id',
                'tx_payment_vouchers.supplier_id',
                'tx_payment_vouchers.approved_by',
                'tx_ts.id AS cts_id',
                'tx_ts.tagihan_supplier_no',
                'tx_ts.tagihan_supplier_date',
                'tx_ts.grandtotal_price',
                'tx_ts.bank_id',
            )
            ->whereNotIn('tx_payment_vouchers.id', function($q1) {
                $q1->select('payment_voucher_id')
                ->from('tx_payment_plan_per_rc_orders')
                ->where('active', 'Y');
            })
            ->where('tx_payment_vouchers.coa_id', $bank_id)
            ->where('tx_payment_vouchers.active', 'Y')
            ->whereRaw('DATE_FORMAT(tx_ts.tagihan_supplier_date, "%Y-%m")=\''.$date.'\'')
            ->orderBy('tx_ts.id', 'ASC')
            ->orderBy('tx_payment_vouchers.id', 'ASC')
            ->get();
            foreach($qPvCts as $qPC){
                if ($next_cts_id==$qPC->cts_id){
                    // ID cts masih sama
                    $ins = Tx_payment_plan_per_rc_order::create([
                        'payment_plan_id' => $id,
                        'supplier_id' => $qPC->supplier_id,
                        'tagihan_supplier_id' => $qPC->cts_id,
                        'tagihan_supplier_no' => $qPC->tagihan_supplier_no,
                        'plan_date' => $qPC->payment_date,
                        'plan_pay' => $next_cts_payment,
                        'payment_voucher_id' => $qPC->pv_id,
                        'payment_voucher_no' => $qPC->payment_voucher_no,
                        'actual_date' => $qPC->payment_date,
                        'actual_payment' => $qPC->payment_total_after_vat,
                        'is_pv_approved' => $qPC->approved_by!=null?'Y':'N',
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);

                    $next_cts_payment = $next_cts_payment - $qPC->payment_total_after_vat;
                }else{
                    // ID cts berbeda, ambil ID baru
                    $next_cts_id = $qPC->cts_id;
                    $next_cts_payment = $qPC->grandtotal_price - $qPC->payment_total_after_vat;
                    $next_pv_payment = $qPC->payment_total_after_vat;

                    $ins = Tx_payment_plan_per_rc_order::create([
                        'payment_plan_id' => $id,
                        'supplier_id' => $qPC->supplier_id,
                        'tagihan_supplier_id' => $qPC->cts_id,
                        'tagihan_supplier_no' => $qPC->tagihan_supplier_no,
                        'plan_date' => $qPC->tagihan_supplier_date,
                        'plan_pay' => $qPC->grandtotal_price,
                        'payment_voucher_id' => $qPC->pv_id,
                        'payment_voucher_no' => $qPC->payment_voucher_no,
                        'actual_date' => $qPC->payment_date,
                        'actual_payment' => $qPC->payment_total_after_vat,
                        'is_pv_approved' => $qPC->approved_by!=null?'Y':'N',
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
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

        session()->flash('status', 'Synchronization has been completed.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$id);
    }
}
