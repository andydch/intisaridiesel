<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Mst_coa;
use App\Models\Userdetail;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_payment_plan;
use App\Models\Tx_receipt_order;
use App\Helpers\GlobalFuncHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Rules\PaymentPlanPeriodDupCheck;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_payment_plan_per_rc_order;
use App\Models\Tx_payment_voucher;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_tagihan_supplier;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

            // cek setiap RO yg memiliki jatuh tempo sesuai pilihan periode
            $q = Tx_receipt_order::leftJoin('mst_suppliers as sp','tx_receipt_orders.supplier_id','=','sp.id')
            ->leftJoin('tx_tagihan_suppliers as tx_ts','sp.id','=','tx_ts.supplier_id')
            ->select(
                'tx_receipt_orders.id as ro_id',
                'tx_receipt_orders.receipt_date',
                // 'tx_receipt_orders.total_after_vat',
                DB::raw('IF(ISNULL(tx_receipt_orders.total_after_vat_rp), tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as total_after_vat'),
                // 'sp.payment_from_id as bank_id',
                'sp.top as supplier_top',
                DB::raw('DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY) AS due_date_payment'),
                'tx_ts.bank_id as bank_id',
            )
            ->whereRaw('DATE_FORMAT(DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY), "%c-%Y")=\''.$request->month_id.'-'.$request->year_id.'\'')
            ->where('tx_receipt_orders.receipt_no','NOT LIKE','%Draft%')
            ->where([
                'tx_receipt_orders.active'=>'Y',
                'tx_ts.bank_id'=>$request->bank_id,
                // 'sp.payment_from_id'=>$request->bank_id,
            ])
            ->orderBy('tx_receipt_orders.receipt_date','DESC')
            ->get();
            foreach($q as $ro){
                $inDtls = Tx_payment_plan_per_rc_order::create([
                    'payment_plan_id'=>$ins->id,
                    'plan_date'=>$ro->due_date_payment,
                    'plan_pay'=>$ro->total_after_vat,
                    'receipt_order_id'=>$ro->ro_id,
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

        $query = Tx_payment_plan::where([
            'id'=>$id,
        ])
        ->first();
        if ($query) {
            if ($request->ajax()){
                $q = Tx_tagihan_supplier::leftJoin('mst_suppliers as sp','tx_tagihan_suppliers.supplier_id','=','sp.id')
                ->leftJoin('mst_globals as gb','sp.entity_type_id','=','gb.id')
                ->select(
                    'tx_tagihan_suppliers.id as ts_id',
                    'tx_tagihan_suppliers.tagihan_supplier_no',
                    'tx_tagihan_suppliers.tagihan_supplier_date',
                    'tx_tagihan_suppliers.total_price',
                    'tx_tagihan_suppliers.total_price_vat',
                    'tx_tagihan_suppliers.grandtotal_price',
                    'tx_tagihan_suppliers.is_vat',
                    'sp.name as supplier_name',
                    'sp.supplier_code',
                    'tx_tagihan_suppliers.bank_id as bank_id',
                    // 'sp.payment_from_id as bank_id',
                )
                ->where([
                    'tx_tagihan_suppliers.bank_id' => $query->bank_id,
                    'tx_tagihan_suppliers.active' => 'Y',
                ])
                ->whereRaw('DATE_FORMAT(tx_tagihan_suppliers.tagihan_supplier_date, "%c-%Y")=\''.date_format(date_create($query->payment_month),"n-Y").'\'')
                ->orderBy('tx_tagihan_suppliers.tagihan_supplier_date','DESC')
                ->orderBy('tx_tagihan_suppliers.id','DESC');

                return DataTables::of($q)
                ->filterColumn('plan_date', function($q, $keyword) {
                    $q->whereRaw('DATE_FORMAT(tx_tagihan_suppliers.tagihan_supplier_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
                })
                ->editColumn('plan_date', function ($q) {
                    $plan_date = date_create($q->tagihan_supplier_date);
                    return date_format($plan_date,"d/m/Y");
                })
                ->filterColumn('supplier_name', function($q, $keyword) {
                    $q->where(function($q) use ($keyword) {
                        $q->where('sp.name', 'like', "%{$keyword}%")
                        ->orWhere('sp.supplier_code', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('supplier_name', function ($q) {
                    return $q->supplier_code.' - '.$q->title_ind.' '.$q->supplier_name;
                })
                ->filterColumn('receipt_orders_no', function ($q, $keyword) {
                    $q->whereIn('tx_tagihan_suppliers.id', function($q) use($keyword) {
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
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                        ->where([
                            'tx_tsd.active' => 'Y',
                            'tx_ts.id' => $q->ts_id,
                            'tx_ts.active' => 'Y',
                        ]);
                    })
                    ->where('receipt_no', 'NOT LIKE', '%Draft%')
                    ->where([
                        'active' => 'Y',
                    ])
                    ->orderBy('receipt_no', 'desc')
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
                    $q->whereIn('tx_tagihan_suppliers.id', function($qTsuDtl) use($keyword) {
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
                    ->whereIn('id', function($qRO) use($q){
                        $qRO->select('tx_tsd.receipt_order_id')
                        ->from('tx_tagihan_supplier_details as tx_tsd')
                        ->leftJoin('tx_tagihan_suppliers as tx_ts', 'tx_tsd.tagihan_supplier_id', '=', 'tx_ts.id')
                        ->where([
                            'tx_tsd.active' => 'Y',
                            'tx_ts.id' => $q->ts_id,
                            'tx_ts.active' => 'Y',
                        ]);
                    })
                    ->where('receipt_no', 'NOT LIKE', '%Draft%')
                    ->where([
                        'active' => 'Y',
                    ])
                    ->orderBy('receipt_no', 'desc')
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
                ->filterColumn('paid_date', function ($q, $keyword) {
                    $q->whereIn('tx_tagihan_suppliers.id', function($q1) use($keyword) {
                        $q1->select('tagihan_supplier_id')
                        ->from('tx_payment_vouchers')
                        ->whereRaw('DATE_FORMAT(payment_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                        ->whereRaw('payment_voucher_no IS NOT NULL')
                        ->where([
                            'active' => 'Y',
                        ]);
                    });
                })
                ->editColumn('paid_date', function ($q) {
                    $qPv = Tx_payment_voucher::select(
                        'payment_date',
                    )
                    ->whereRaw('payment_voucher_no IS NOT NULL')
                    ->where([
                        'tagihan_supplier_id' => $q->ts_id,
                        'active' => 'Y',
                    ])
                    ->first();
                    if ($qPv){
                        $date = date_create($qPv->reference_date);
                        return date_format($date, "d/m/Y");
                    }
                    return '';
                })
                ->addColumn('paid_value', function ($q) {
                    $qPv = Tx_payment_voucher::select(
                        'payment_total_after_vat',
                        'admin_bank',
                        'biaya_asuransi',
                        'biaya_kirim',
                        'diskon_pembelian',
                        'vat_num',
                    )
                    ->whereRaw('payment_voucher_no IS NOT NULL')
                    ->where([
                        'tagihan_supplier_id' => $q->ts_id,
                        'active' => 'Y',
                    ])
                    ->first();
                    if ($qPv){
                        return number_format($qPv->payment_total_after_vat+
                            $qPv->admin_bank+
                            $qPv->biaya_asuransi+
                            $qPv->biaya_kirim-
                            $qPv->diskon_pembelian, 0, ".", ",");
                    }
                    return '';
                })
                ->filterColumn('pv_no', function ($q, $keyword) {
                    $q->whereIn('tx_tagihan_suppliers.id', function($q1) use($keyword) {
                        $q1->select('tx_pv.tagihan_supplier_id')
                        ->from('tx_payment_vouchers as tx_pv')
                        ->where('tx_pv.payment_voucher_no', 'LIKE', "%{$keyword}%")
                        ->whereRaw('tx_pv.payment_voucher_no IS NOT NULL')
                        ->where([
                            'tx_pv.active' => 'Y',
                        ]);
                    });
                })
                ->editColumn('pv_no', function ($q) {
                    $qPv = Tx_payment_voucher::select(
                        'payment_voucher_no',
                    )
                    ->whereRaw('payment_voucher_no IS NOT NULL')
                    ->where([
                        'tagihan_supplier_id' => $q->ts_id,
                        'active' => 'Y',
                    ])
                    ->first();
                    if ($qPv){
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher/'.urlencode($qPv->payment_voucher_no)).'" target="_new" '.
                            'style="text-decoration: underline;">'.$qPv->payment_voucher_no.'</a>';
                    }
                    return '';
                })
                ->rawColumns([
                    'plan_date',
                    'supplier_name',
                    'receipt_orders_no',
                    'receipt_orders_invoices',
                    'paid_date',
                    'paid_value',
                    'pv_no',
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
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

            // cek setiap RO yg memiliki jatuh tempo sesuai pilihan periode
            $q = Tx_receipt_order::leftJoin('mst_suppliers as sp','tx_receipt_orders.supplier_id','=','sp.id')
            ->leftJoin('tx_tagihan_suppliers as tx_ts','sp.id','=','tx_ts.supplier_id')
            ->select(
                'tx_receipt_orders.id as ro_id',
                'tx_receipt_orders.receipt_date',
                // 'tx_receipt_orders.total_after_vat',
                DB::raw('IF(ISNULL(tx_receipt_orders.total_after_vat_rp), tx_receipt_orders.total_after_vat, tx_receipt_orders.total_after_vat_rp) as total_after_vat'),
                // 'sp.payment_from_id as bank_id',
                'sp.top as supplier_top',
                DB::raw('DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY) AS due_date_payment'),
                'tx_ts.bank_id as bank_id',
            )
            ->whereRaw('DATE_FORMAT(DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY), "%c-%Y")=\''.$request->month_id.'-'.$request->year_id.'\'')
            ->where('tx_receipt_orders.receipt_no','NOT LIKE','%Draft%')
            ->where([
                'tx_receipt_orders.active'=>'Y',
                'tx_ts.bank_id'=>$request->bank_id,
                // 'sp.payment_from_id'=>$request->bank_id,
            ])
            ->orderBy('tx_receipt_orders.receipt_date','DESC')
            ->get();
            foreach($q as $ro){
                $qDtl = Tx_payment_plan_per_rc_order::where([
                    'payment_plan_id'=>$id,
                    'plan_date'=>$ro->due_date_payment,
                    'receipt_order_id'=>$ro->ro_id,
                ])
                ->first();
                if (!$qDtl){
                    $ins = Tx_payment_plan_per_rc_order::create([
                        'payment_plan_id'=>$id,
                        'plan_date'=>$ro->due_date_payment,
                        'plan_pay'=>$ro->total_after_vat,
                        'receipt_order_id'=>$ro->ro_id,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
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
