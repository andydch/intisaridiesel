<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Mst_coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Rules\AcceptancePlanPeriodDupCheck;
use App\Models\Tx_acceptance_plan;
use App\Models\Tx_acceptance_plan_per_invoice;
use App\Models\Tx_payment_receipt;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\V_invoice;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;

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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
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

        $query = Tx_acceptance_plan::where([
            'id'=>$id,
        ])
        ->first();
        if ($query) {
            if ($request->ajax()){
                $q = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
                ->leftJoin('mst_globals as ent','cust.entity_type_id','=','ent.id')
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
                    'ent.title_ind as customer_entity_type_name',
                    DB::raw('DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY) AS due_date_acceptance'),
                )
                ->whereRaw('DATE_FORMAT(DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY), "%c-%Y")=\''.date_format(date_create($query->acceptance_month),"n-Y").'\'')
                ->where([
                    'v_invoices.payment_to_id'=>$query->bank_id,
                ])
                ->orderBy('v_invoices.invoice_date','DESC');

                return DataTables::of($q)
                ->addColumn('invoice_no', function ($q) {
                    if (strpos('i-'.$q->invoice_no,env('P_INVOICE'))>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$q->inv_id).'" target="_new"
                            style="text-decoration: underline;">'.$q->invoice_no.'</a>';
                    }
                    if (strpos('i-'.$q->invoice_no,env('P_KWITANSI'))>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$q->inv_id).'" target="_new"
                            style="text-decoration: underline;">'.$q->invoice_no.'</a>';
                    }
                    return $links;
                })
                ->addColumn('tagihan', function ($q) {
                    return number_format($q->tagihan,0,".",",");
                })
                ->filterColumn('customer_identity', function($q, $keyword) {
                    $q->where('cust.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%")
                    ->orWhere('cust.customer_unique_code', 'LIKE', "%{$keyword}%");
                })
                ->editColumn('customer_identity', function ($q) {
                    return $q->customer_unique_code.' - '.$q->customer_entity_type_name.' '.$q->cust_name;
                })
                ->addColumn('plan_date', function ($q) use($query) {
                    $plan_date = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'inv_or_kwi_id'=>$q->inv_id,
                        'inv_or_kwi'=>$q->inv_identity,
                        'active'=>'Y',
                    ])
                    ->orderBy('plan_date','DESC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $plan_date .= date_format(date_create($p->plan_date),"d/m/Y").'<br/>';
                    }
                    return $plan_date;
                })
                ->addColumn('paid_date', function ($q) use($query) {
                    $paid_date = '';
                    $is_full_payment = 'N';
                    $Pr = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as pr','tx_payment_receipt_invoices.payment_receipt_id','=','pr.id')
                    ->select(
                        'pr.is_full_payment',
                    )
                    ->selectRaw('DATE_FORMAT(pr.payment_date, "%d/%c/%Y") as paid_date')
                    ->whereRaw('pr.payment_receipt_no IS NOT NULL')
                    ->where([
                        'tx_payment_receipt_invoices.invoice_no'=>$q->invoice_no,
                        'tx_payment_receipt_invoices.active'=>'Y',
                        'pr.active'=>'Y',
                    ])
                    ->get();
                    if ($Pr){
                        foreach ($Pr as $p) {
                            $paid_date .= $p->paid_date.'<br/>';
                            $is_full_payment = $p->is_full_payment;
                        }
                    }
                    return $paid_date;
                })
                ->addColumn('bayar_tagihan', function ($q) use($query) {
                    $paid_val = '';
                    $Pr = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as pr','tx_payment_receipt_invoices.payment_receipt_id','=','pr.id')
                    ->selectRaw('tx_payment_receipt_invoices.total_payment_after_vat as bayar_tagihan')
                    ->whereRaw('pr.payment_receipt_no IS NOT NULL')
                    ->where([
                        'tx_payment_receipt_invoices.invoice_no'=>$q->invoice_no,
                        'tx_payment_receipt_invoices.active'=>'Y',
                        'pr.active'=>'Y',
                    ])
                    ->get();
                    if ($Pr){
                        foreach ($Pr as $p) {
                            $paid_val .= number_format($p->bayar_tagihan,0,".",",").'<br/>';
                            // $paid_val .= ($q->vat_val>0?
                            //     number_format($p->bayar_tagihan+($p->bayar_tagihan*$q->vat_val/100),0,".",","):
                            //     number_format($p->bayar_tagihan,0,".",",")).'<br/>';
                        }
                    }
                    return $paid_val;
                })
                ->addColumn('rencana_bayar_tagihan', function ($q) use($query) {
                    $paid_num_str = '';
                    $qPayPerInv = Tx_acceptance_plan_per_invoice::where([
                        'inv_or_kwi_id'=>$q->inv_id,
                        'inv_or_kwi'=>$q->inv_identity,
                        'active'=>'Y',
                    ])
                    ->orderBy('plan_date','DESC')
                    ->get();
                    foreach ($qPayPerInv as $p) {
                        $paid_num_str .= $p->plan_accept>0?number_format($p->plan_accept,0,".",",").'<br/>':'';
                    }
                    return $paid_num_str;
                })
                ->addColumn('payment_receipt_no', function ($q) {
                    $payment_receipt_no = '';
                    $qPyV = Tx_payment_receipt::leftJoin('tx_payment_receipt_invoices as pri','tx_payment_receipts.id','=','pri.payment_receipt_id')
                    ->select('tx_payment_receipts.payment_receipt_no')
                    ->where([
                        'pri.invoice_no'=>$q->invoice_no,
                        'pri.active'=>'Y',
                        'tx_payment_receipts.active'=>'Y',
                    ])
                    ->get();
                    foreach ($qPyV as $p) {
                        $payment_receipt_no .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-receipt/'.urlencode($p->payment_receipt_no)).'" target="_new"
                            style="text-decoration: underline;">'.$p->payment_receipt_no.'</a><br/>';
                    }
                    return $payment_receipt_no;
                })
                ->addColumn('action', function ($q) use($query, $id) {
                    $qPyV = Tx_payment_receipt::leftJoin('tx_payment_receipt_invoices as pri','tx_payment_receipts.id','=','pri.payment_receipt_id')
                    ->select('tx_payment_receipts.payment_receipt_no')
                    ->where([
                        'pri.invoice_no'=>$q->invoice_no,
                        'pri.active'=>'Y',
                        'tx_payment_receipts.active'=>'Y',
                    ])
                    ->get();
                    if (count($qPyV)>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'?am='.
                            urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'/edit?am='.
                            urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">Edit</a>
                            | <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder_per_inv.'/'.urlencode($q->invoice_no).'?am='.
                            urlencode(date_format(date_create($query->acceptance_month),"n-Y")).'&ap='.$id.'&b_id='.$query->bank_id).'" style="text-decoration: underline;">View</a>';
                    }
                    return $links;
                })
                ->rawColumns(['invoice_no','tagihan','plan_date','customer_identity','paid_date','bayar_tagihan','rencana_bayar_tagihan','payment_receipt_no','action'])
                ->toJson();
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'bank_name'=>$query->bank->coa_name,
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
