<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Mst_coa;
use App\Models\Mst_global;
use App\Models\Mst_menu_user;
use App\Models\Tx_acceptance_plan_per_invoice;
use App\Models\Tx_acceptance_plan;
use App\Models\Tx_invoice;
use App\Models\Tx_kwitansi;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\V_invoice;
use App\Rules\AcceptancePlanPeriodDupCheck;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class AcceptancePlanServerSideController extends Controller
{
    protected $title = 'Rencana Penerimaan';
    protected $folder = 'acceptance-plan';
    protected $folder_per_inv = 'acceptance-plan-inv';
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
            $query = Tx_acceptance_plan::select(
                'id as accept_id',
                'bank_id',
                DB::raw('DATE_FORMAT(acceptance_month, "%M %Y") as acceptance_month_f'),
                'is_draft',
            )
            ->orderBy('acceptance_month','DESC');

            return DataTables::of($query)
            ->filterColumn('acceptance_month_f', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(acceptance_month, "%M %Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('acceptance_month_f', function ($query) {
                return $query->acceptance_month_f;
            })
            ->filterColumn('coa_name', function($query, $keyword) {
                $query->whereIn('bank_id', function($q) use($keyword) {
                    $q->select('id')
                    ->from('mst_coas')
                    ->where('coa_name', 'LIKE', "%{$keyword}%");
                });
            })
            ->addColumn('coa_name', function ($query) {
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
                $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$query->accept_id.'/edit').'" style="text-decoration: underline;">Edit</a>
                    | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$query->accept_id).'" style="text-decoration: underline;">View</a>';
                return $links;
            })
            ->addColumn('status', function ($query) {
                $status = $query->is_draft=='Y'?'Draft':'Created';
                return $status;
            })
            ->rawColumns(['acceptance_month_f','coa_name','action','status'])
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

        if (old('month_id')){
            $month_id = old('month_id');
            $year_id = old('year_id');
            $coas = Mst_coa::whereIn('id', function($q) use($year_id, $month_id){
                $q->select('bank_id')
                ->from('tx_payment_plans')
                ->whereRaw('payment_month=\''.$year_id.'-'.$month_id.'-01\'')
                ->where([
                    'is_draft' => 'N',
                    'active' => 'Y',
                ]);
            })
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('coa_name', 'ASC')
            ->get();
        }else{
            $coas = [];
        }

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
            'menu_id' => 117,
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
            'year_id'=>['required', 'numeric', new AcceptancePlanPeriodDupCheck(0,$request->year_id.'-'.$request->month_id,$request->bank_id)],
            'bank_id'=>'required|numeric',
        ];
        $errMsg = [
            'month_id.required'=>'The month field is required.',
            'month_id.numeric'=>'The month field is required.',
            'year_id.required'=>'The year field is required.',
            'year_id.numeric'=>'The year field is required.',
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
            $ins = Tx_acceptance_plan::create([
                'acceptance_month'=>$request->year_id.'-'.$request->month_id.'-01',
                'bank_id'=>$request->bank_id,
                'is_draft'=>$request->is_draft,
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'active'=>'Y',
                'created_by'=>Auth::user()->id,
                'updated_by'=>Auth::user()->id,
            ]);

            $qPerInv = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
            ->select(
                'v_invoices.inv_id',
                'v_invoices.invoice_no',
                'v_invoices.customer_id',
                'v_invoices.invoice_date',
                'v_invoices.tagihan',
                'v_invoices.inv_identity',
                'v_invoices.vat_val',
                'v_invoices.payment_to_id',
                'cust.id as cust_id',
                'cust.name as cust_name',
                'cust.customer_unique_code',
                'cust.top as cust_top',
                DB::raw('CONCAT(cust.customer_unique_code, " - ", cust.name) AS customer_identity'),
                DB::raw('DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY) AS due_date_acceptance'),
            )
            ->whereRaw('DATE_FORMAT(DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY), "%c-%Y")=\''.$request->month_id.'-'.$request->year_id.'\'')
            ->where([
                'v_invoices.payment_to_id'=>$request->bank_id,
            ])
            ->orderBy('v_invoices.invoice_date','DESC')
            ->get();
            foreach ($qPerInv as $qPI){
                $insDtl = Tx_acceptance_plan_per_invoice::create([
                    'acceptance_plan_id'=>$ins->id,
                    'plan_date'=>$qPI->due_date_acceptance,
                    'plan_accept'=>$qPI->tagihan,
                    'inv_or_kwi_id'=>$qPI->inv_id,
                    'inv_or_kwi'=>$qPI->inv_identity,
                    'customer_id'=>$qPI->cust_id,
                    'invoice_no'=>$qPI->invoice_no,
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

        $query = Tx_acceptance_plan::where('id', $id)
        ->first();
        if ($query) {
            if ($request->ajax()){
                $txAppiSubquery = Tx_acceptance_plan_per_invoice::select('acceptance_plan_id', 'customer_id', 'invoice_no')
                ->where('active', 'Y')
                ->groupBy('acceptance_plan_id', 'customer_id', 'invoice_no');
                $q = Tx_acceptance_plan::leftJoinSub($txAppiSubquery, 'sub', function ($join) use($id) {
                    $join->on('tx_acceptance_plans.id', '=', 'sub.acceptance_plan_id')
                    ->where('tx_acceptance_plans.id', '=', $id)
                    ->where('tx_acceptance_plans.active', 'Y');
                })
                ->leftJoin('mst_customers as cust', function($join) {
                    $join->on('sub.customer_id', '=', 'cust.id')
                    ->where('cust.active', 'Y');
                })
                ->leftJoin('mst_globals as ent', function($join) {
                    $join->on('cust.entity_type_id', '=', 'ent.id')
                    ->where('ent.data_cat', 'entity_type')
                    ->where('ent.active', 'Y');
                })
                ->select(
                    'sub.invoice_no as invoice_no',
                    'cust.id as cust_id',
                    'cust.name as cust_name',
                    'cust.customer_unique_code',
                    'cust.top as cust_top',
                    'ent.title_ind as customer_entity_type_name',
                )
                ->where([
                    'sub.acceptance_plan_id'=>$id,
                ])
                ->orderBy('sub.invoice_no','DESC');

                return DataTables::of($q)
                ->filterColumn('invoice_no_tmp', function($q, $keyword) {
                    $q->where('sub.invoice_no', 'LIKE', "%{$keyword}%");
                })
                ->editColumn('invoice_no_tmp', function ($q) {
                    $links = '';
                    // Log::debug([$q->invoice_no, $q->customer_unique_code]);
                    if (strpos('i-'.$q->invoice_no, env('P_INVOICE'))>0){
                        $qInv = Tx_invoice::where('invoice_no', $q->invoice_no)
                        ->select('id', 'invoice_no')
                        ->first();
                        if ($qInv){
                            $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$qInv->id).'" target="_new"
                                style="text-decoration: underline;">'.$qInv->invoice_no.'</a>';
                        }
                    }
                    if (strpos('k-'.$q->invoice_no, env('P_KWITANSI'))>0){
                        $qInv = Tx_kwitansi::where('kwitansi_no', $q->invoice_no)
                        ->select('id', 'kwitansi_no')
                        ->first();
                        if ($qInv){
                            $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$qInv->id).'" target="_new"
                                style="text-decoration: underline;">'.$qInv->kwitansi_no.'</a>';
                        }
                    }
                    return $links;
                })
                ->filterColumn('customer_identity', function($q, $keyword) {
                    $q->where('cust.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%")
                    ->orWhere('cust.customer_unique_code', 'LIKE', "%{$keyword}%");
                })
                ->editColumn('customer_identity', function ($q) {
                    return $q->customer_unique_code.' - '.$q->customer_entity_type_name.' '.$q->cust_name;
                })
                ->addColumn('tagihan', function ($q) {
                    
                    if (strpos('i-'.$q->invoice_no, env('P_INVOICE'))>0){
                        $qInv = Tx_invoice::where('invoice_no', $q->invoice_no)
                        ->select('do_grandtotal_vat')
                        ->first();
                        if ($qInv){
                            return number_format($qInv->do_grandtotal_vat,0,".",",");
                        }
                    }
                    if (strpos('i-'.$q->invoice_no, env('P_KWITANSI'))>0){
                        $qInv = Tx_kwitansi::where('kwitansi_no', $q->invoice_no)
                        ->select('np_total')
                        ->first();
                        if ($qInv){
                            return number_format($qInv->np_total,0,".",",");
                        }
                    }
                })
                ->addColumn('plan_date', function ($q) use($query) {
                    $plan_date = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'invoice_no' => $q->invoice_no,
                        'active' => 'Y',
                    ])
                    ->select('plan_date')
                    ->orderBy('plan_date','ASC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $plan_date .= date_format(date_create($p->plan_date),"d/m/Y").'<br/>';
                    }
                    return $plan_date;
                })
                ->addColumn('paid_date', function ($q) use($query) {
                    $paid_date = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'invoice_no' => $q->invoice_no,
                        'active' => 'Y',
                    ])
                    ->selectRaw('DATE_FORMAT(payment_date, "%d/%c/%Y") as paid_date')
                    ->orderBy('plan_date', 'ASC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $paid_date .= $p->paid_date.'<br/>';
                    }
                    return $paid_date;
                })
                ->addColumn('bayar_tagihan', function ($q) use($query) {
                    $paid_val = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'invoice_no' => $q->invoice_no,
                        'active' => 'Y',
                    ])
                    ->select('payment_total')
                    ->orderBy('plan_date', 'ASC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $paid_val .= number_format($p->payment_total, 0, ".", ",").'<br/>';
                    }
                    return $paid_val;
                })
                ->addColumn('rencana_bayar_tagihan', function ($q) use($query) {
                    $paid_num_str = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'invoice_no' => $q->invoice_no,
                        'active' => 'Y',
                    ])
                    ->select('plan_accept')
                    ->orderBy('plan_date', 'ASC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $paid_num_str .= $p->plan_accept>0?number_format($p->plan_accept, 0, ".", ",").'<br/>':'';
                    }
                    return $paid_num_str;
                })
                ->addColumn('payment_receipt_no', function ($q) {
                    $payment_receipt_no = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'invoice_no' => $q->invoice_no,
                        'active' => 'Y',
                    ])
                    ->select('payment_receipt_no')
                    ->orderBy('plan_date', 'ASC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $payment_receipt_no .= $p->payment_receipt_no ? '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.urlencode($p->payment_receipt_no)).'" 
                            target="_new" style="text-decoration: underline;">'.$p->payment_receipt_no.'</a><br/>' : '<br/>';
                    }
                    return $payment_receipt_no;
                })
                ->addColumn('action', function ($q) use($query, $id) {
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'?am='.
                        urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">View</a>';
                    // $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'/edit?am='.
                    //     urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">Edit</a>
                    //     | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'?am='.
                    //     urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">View</a>';
                    return $links;
                })
                ->rawColumns(['invoice_no_tmp', 'tagihan', 'plan_date', 'customer_identity', 'paid_date', 'bayar_tagihan', 'rencana_bayar_tagihan', 'payment_receipt_no', 'action'])
                ->toJson();
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'bank_name'=>$query->bank?$query->bank->coa_name:null,
                'qCurrency'=>$qCurrency,
                'qPlans'=>$query,
            ];
            return view('tx.'.$this->folder.'.index-pr-server-side', $data);

        } else {
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

        $query = Tx_acceptance_plan::where([
            'id'=>$id,
        ])
        ->first();
        if ($query) {
            if (old('month_id')){
                $month_id = old('month_id');
                $year_id = old('year_id');
            }else{
                $month_id = date_format(date_create($query->acceptance_month), "m");
                $year_id = date_format(date_create($query->acceptance_month), "Y");
            }
            $coas = Mst_coa::whereIn('id', function($q) use($year_id, $month_id){
                $q->select('bank_id')
                ->from('tx_payment_plans')
                ->whereRaw('payment_month=\''.$year_id.'-'.$month_id.'-01\'')
                ->where([
                    'is_draft' => 'N',
                    'active' => 'Y',
                ]);
            })
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('coa_name', 'ASC')
            ->get();

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
            'menu_id' => 117,
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
            'year_id'=>['required', 'numeric', new AcceptancePlanPeriodDupCheck($id,$request->year_id.'-'.$request->month_id,$request->bank_id)],
            'bank_id'=>'required|numeric',
        ];
        $errMsg = [
            'month_id.required'=>'The month field is required.',
            'month_id.numeric'=>'The month field is required.',
            'year_id.required'=>'The year field is required.',
            'year_id.numeric'=>'The year field is required.',
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
            $ins = Tx_acceptance_plan::where([
                'id'=>$id,
            ])
            ->update([
                'acceptance_month'=>$request->year_id.'-'.$request->month_id.'-01',
                'bank_id'=>$request->bank_id,
                'is_draft'=>$request->is_draft,
                'draft_at'=>$draft_at,
                'draft_to_created_at'=>$draft_to_created_at,
                'active'=>'Y',
                'updated_by'=>Auth::user()->id,
            ]);

            $qPerInv = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
            ->select(
                'v_invoices.inv_id',
                'v_invoices.invoice_no',
                'v_invoices.customer_id',
                'v_invoices.invoice_date',
                'v_invoices.tagihan',
                'v_invoices.inv_identity',
                'v_invoices.vat_val',
                'v_invoices.payment_to_id',
                'cust.id as cust_id',
                'cust.name as cust_name',
                'cust.customer_unique_code',
                'cust.top as cust_top',
                DB::raw('CONCAT(cust.customer_unique_code, " - ", cust.name) AS customer_identity'),
                DB::raw('DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY) AS due_date_acceptance'),
            )
            ->whereRaw('DATE_FORMAT(DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY), "%c-%Y")=\''.$request->month_id.'-'.$request->year_id.'\'')
            ->where([
                'v_invoices.payment_to_id'=>$request->bank_id,
            ])
            ->orderBy('v_invoices.invoice_date','DESC')
            ->get();
            foreach ($qPerInv as $qPI){
                $qDtl = Tx_acceptance_plan_per_invoice::where([
                    'acceptance_plan_id'=>$id,
                    'plan_date'=>$qPI->due_date_acceptance,
                    'inv_or_kwi_id'=>$qPI->inv_id,
                    'inv_or_kwi'=>$qPI->inv_identity,
                    'customer_id'=>$qPI->cust_id,
                    'invoice_no'=>$qPI->invoice_no,
                ])
                ->first();
                if (!$qDtl){
                    $insDtl = Tx_acceptance_plan_per_invoice::create([
                        'acceptance_plan_id'=>$id,
                        'plan_date'=>$qPI->due_date_acceptance,
                        'plan_accept'=>$qPI->tagihan,
                        'inv_or_kwi_id'=>$qPI->inv_id,
                        'inv_or_kwi'=>$qPI->inv_identity,
                        'customer_id'=>$qPI->cust_id,
                        'invoice_no'=>$qPI->invoice_no,
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();
            // throw $e;

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

            $qPlan = Tx_acceptance_plan::whereRaw('DATE_FORMAT(acceptance_month, "%Y-%m")=\''.$date.'\'')
            ->where('bank_id', $bank_id)
            ->where('is_draft', 'N')
            ->where('active', 'Y')
            ->first();
            if ($qPlan){
                // sinkronisasikan ID plan detail terhadap ID plan induk
                $qPlanDtl = Tx_acceptance_plan_per_invoice::leftJoin('v_invoices AS v_inv', 'tx_acceptance_plan_per_invoices.invoice_no', '=', 'v_inv.invoice_no')
                ->select(
                    'tx_acceptance_plan_per_invoices.id AS tx_appi_id',
                    'v_inv.invoice_no',
                    'v_inv.payment_to_id',
                )
                ->where('tx_acceptance_plan_per_invoices.acceptance_plan_id', '<>', $qPlan->id)
                ->whereRaw('DATE_FORMAT(tx_acceptance_plan_per_invoices.plan_date, "%Y-%m")=\''.$date.'\'')
                ->where('tx_acceptance_plan_per_invoices.active', 'Y')
                ->where('v_inv.payment_to_id', $bank_id)
                ->get();
                foreach($qPlanDtl as $qPDtl){
                    $updPlanDtl = Tx_acceptance_plan_per_invoice::where('id', $qPDtl->tx_ppro_id)
                    ->update([
                        'payment_plan_id' => $qPlan->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
                // sinkronisasikan ID plan detail terhadap ID plan induk

                // cari data plan detail yg memiliki perbedaan periode dengan periode di plan induk dan perbedaan kode bank
                $qPlanDtl = Tx_acceptance_plan_per_invoice::leftJoin('v_invoices AS v_inv', 'tx_acceptance_plan_per_invoices.invoice_no', '=', 'v_inv.invoice_no')
                ->select(
                    'tx_acceptance_plan_per_invoices.id AS tx_appi_id',
                    'tx_acceptance_plan_per_invoices.plan_date',
                    'v_inv.invoice_no',
                    'v_inv.payment_to_id',
                )
                ->where('tx_acceptance_plan_per_invoices.acceptance_plan_id', $qPlan->id)
                ->whereRaw('DATE_FORMAT(tx_acceptance_plan_per_invoices.plan_date, "%Y-%m")=\''.$date.'\'')
                ->where('tx_acceptance_plan_per_invoices.active', 'Y')
                ->where('v_inv.payment_to_id', '<>', $bank_id)
                ->get();
                foreach($qPlanDtl as $qPDtl){
                    $qPlanA = Tx_acceptance_plan::whereRaw('DATE_FORMAT(acceptance_month, "%Y-%m")=\''.date_format(date_create($qPDtl->plan_date),"Y-m").'\'')
                    ->where('bank_id', $qPDtl->payment_to_id)
                    ->where('is_draft', 'N')
                    ->where('active', 'Y')
                    ->first();
                    if ($qPlanA){
                        Log::debug([$qPDtl->invoice_no, $qPDtl->payment_to_id, $qPlanA->id]);
                        $updPlanDtlA = Tx_acceptance_plan_per_invoice::where('id', $qPDtl->tx_appi_id)
                        ->update([
                            'acceptance_plan_id' => $qPlanA->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
                // cari data plan detail yg memiliki perbedaan periode dengan periode di plan induk
            }

            // kumpulkan billing process dan proses tagihan
            $qPerInv = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
            ->select(
                'v_invoices.inv_id',
                'v_invoices.invoice_no',
                'v_invoices.customer_id',
                'v_invoices.invoice_date',
                'v_invoices.tagihan',
                'v_invoices.inv_identity',
                'v_invoices.vat_val',
                'v_invoices.payment_to_id',
                'cust.id as cust_id',
                'cust.name as cust_name',
                'cust.customer_unique_code',
                'cust.top as cust_top',
                DB::raw('CONCAT(cust.customer_unique_code, " - ", cust.name) AS customer_identity'),
                DB::raw('v_invoices.invoice_date AS due_date_acceptance'),
            )
            ->whereRaw('DATE_FORMAT(v_invoices.invoice_date, "%Y-%m")=\''.$date.'\'')
            ->where('v_invoices.payment_to_id', $bank_id)
            ->whereNotIn('v_invoices.invoice_no', function($query) use($id) {
                $query->select('invoice_no')
                ->from('tx_acceptance_plan_per_invoices')
                ->where('acceptance_plan_id', $id)
                ->where('active', 'Y');
            })
            ->orderBy('v_invoices.invoice_date','DESC')
            ->get();
            foreach ($qPerInv as $qPI){
                $qDtl = Tx_acceptance_plan_per_invoice::where([
                    'invoice_no'=>$qPI->invoice_no,
                ]);
                if (!$qDtl->first()){
                    $insDtl = Tx_acceptance_plan_per_invoice::create([
                        'acceptance_plan_id'=>$id,
                        'plan_date'=>$qPI->due_date_acceptance,
                        'plan_accept'=>$qPI->tagihan,
                        'inv_or_kwi_id'=>$qPI->inv_id,
                        'inv_or_kwi'=>$qPI->inv_identity,
                        'customer_id'=>$qPI->cust_id,
                        'invoice_no'=>$qPI->invoice_no,
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }else{
                    $qDtl->update([
                        'plan_date'=>$qPI->due_date_acceptance,
                        'plan_accept'=>$qPI->tagihan,
                        'active'=>'Y',
                        'updated_by'=>Auth::user()->id,
                    ]);
                }
            }
            // kumpulkan billing process dan proses tagihan

            // reset
            $qUpdDtlSetZero = Tx_acceptance_plan_per_invoice::where('acceptance_plan_id', ($qPlan?$qPlan->id:0))
            ->update([
                'payment_receipt_no' => null,
                'payment_date' => null,
                'payment_total' => 0,
                'updated_by' => Auth::user()->id,
            ]);
            // reset

            // tambahkan data PA terkait dg billing process/proses tagihan jika ada
            $qDtl = Tx_acceptance_plan_per_invoice::whereRaw('DATE_FORMAT(plan_date, "%Y-%m")=\''.$date.'\'')
            // whereRaw('payment_receipt_no IS NULL')
            // ->where('invoice_no', 'KWM26-00037')
            ->where('active', 'Y')
            ->orderBy('id', 'ASC')
            ->get();
            foreach ($qDtl as $qD){
                // $invoiceNo = 'INM26-00335';
                $invoiceNo = $qD->invoice_no;
                $qPA = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as pr', function($join) {
                    $join->on('tx_payment_receipt_invoices.payment_receipt_id', '=', 'pr.id')
                    ->whereRaw('pr.payment_receipt_no IS NOT NULL')
                    ->where('pr.is_draft', 'N')
                    ->where('pr.active', 'Y');
                })
                ->select(
                    'pr.payment_receipt_no',
                    'pr.payment_date',
                    'tx_payment_receipt_invoices.id AS tx_pri_id',
                    'tx_payment_receipt_invoices.total_payment_after_vat',
                )
                ->whereNotIn('pr.payment_receipt_no', function($q) use($invoiceNo){
                    // cari yg belum memiliki payment plan dan actual payment
                    $q->select('payment_receipt_no')
                    ->from('tx_acceptance_plan_per_invoices')
                    ->where('invoice_no', $invoiceNo)
                    ->whereRaw('payment_receipt_no IS NOT null')
                    // ->where('payment_receipt_no', 'pr.payment_receipt_no')
                    ->where('active', 'Y');
                })
                ->where([
                    'tx_payment_receipt_invoices.invoice_no' => $invoiceNo,
                    'tx_payment_receipt_invoices.active' => 'Y',
                ])
                ->orderBy('pr.created_at', 'ASC')
                ->first();
                if ($qPA){
                    $qDtl = Tx_acceptance_plan_per_invoice::where('id', $qD->id)
                    ->update([
                        'payment_receipt_no' => $qPA->payment_receipt_no,
                        'payment_date' => $qPA->payment_date,
                        'payment_total' => $qPA->total_payment_after_vat,
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $qDtl = Tx_acceptance_plan_per_invoice::where('id', $qD->id)
                    ->update([
                        'payment_receipt_no' => null,
                        'payment_date' => null,
                        'payment_total' => 0,
                        'updated_by' => Auth::user()->id,
                    ]);
                }
            }
            // tambahkan data PA terkait dg billing process/proses tagihan jika ada

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();
            // throw $e;

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

        session()->flash('status', 'Synchronization has been completed.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$id);
    }
}
