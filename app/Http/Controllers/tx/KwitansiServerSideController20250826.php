<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Mst_coa;
use App\Models\Tx_kwitansi;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_kwitansi_detail;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Tx_nota_retur_non_tax;
use App\Models\Tx_payment_receipt_invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class KwitansiServerSideController20250826 extends Controller
{
    protected $title = 'Proses Tagihan';
    protected $folder = 'kwitansi';

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
            $query = Tx_kwitansi::leftJoin('userdetails AS usr','tx_kwitansis.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_kwitansis.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_kwitansis.id as tx_id',
                'tx_kwitansis.kwitansi_no',
                'tx_kwitansis.kwitansi_date',
                'tx_kwitansis.np_total',
                'tx_kwitansis.approved_by',
                'tx_kwitansis.canceled_by',
                'tx_kwitansis.active as kw_active',
                'tx_kwitansis.created_by as createdby',
                'tx_kwitansis.created_at as createdat',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'ety_type.title_ind as ety_type_name',
            )
            ->when($userLogin->is_director!='Y', function($q) use ($userLogin) {
                $q->where('usr.branch_id','=', $userLogin->branch_id);
            })
            ->orderBy('tx_kwitansis.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('kwitansi_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_kwitansis.kwitansi_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('kwitansi_date', function ($query) {
                return date_format(date_create($query->kwitansi_date),"d/m/Y");
            })
            ->addColumn('createdat', function ($query) {
                return date_format(date_create($query->createdat),"d/m/Y");
            })
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_customers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_customers.customer_unique_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('customer_name', function ($query) {
                return $query->customer_unique_code.' - '.$query->ety_type_name.' '.$query->cust_name;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->kw_active=='Y'){
                    if (strpos($query->kwitansi_no,"Draft")>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                            <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                    }else{
                        if ($userLogin->is_director=='Y'){
                            $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                                <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                                <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                                <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                        }else{
                            $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                                <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                                <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                        }
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if ($query->kw_active=='Y' && strpos($query->kwitansi_no,'Draft')==0 && is_null($query->approved_by) && is_null($query->canceled_by)){
                    $links = 'Created';

                    // cek status di penerimaan customer
                    $qPyReceipt = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr','tx_payment_receipt_invoices.payment_receipt_id','=','tx_pr.id')
                    ->select(
                        'tx_payment_receipt_invoices.is_full_payment',
                    )
                    ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                    ->where([
                        'tx_payment_receipt_invoices.invoice_no'=>$query->kwitansi_no,
                        'tx_payment_receipt_invoices.active'=>'Y',
                        'tx_pr.active'=>'Y',
                    ])
                    ->orderBy('tx_pr.id','DESC')
                    ->first();
                    if ($qPyReceipt){
                        if ($qPyReceipt->is_full_payment=='Y'){
                            $links = 'Paid';
                        }
                        if ($qPyReceipt->is_full_payment=='N'){
                            $links = 'Partial';
                        }
                    }

                    return $links;
                }
                if ($query->kw_active=='Y' && strpos($query->kwitansi_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->kw_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['kwitansi_date','createdat','customer_name','action','status'])
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
        $is_director = '';
        $branch_id = '';
        $finance_admin_id = 0;
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
            $finance_admin_id = $userLogin->section_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $delivery_order = [];
        if(old('customer_id')){
            $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',old('customer_id'))
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->orderBy('delivery_order_date','DESC')
            ->orderBy('created_at','DESC')
            ->get();
        }

        $qPaymentTo = Mst_coa::select(
            'id',
            'coa_code',
            'coa_code_complete',
            'coa_name',
        )
        ->where('coa_code_complete','LIKE','112%%')
        ->where([
            'coa_level' => 5,
            'local' => 'N',
            'active' => 'Y',
        ])
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCust' => $queryCustomer,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'deliveryOrders' => $delivery_order,
            'qCurrency' => $qCurrency,
            'userLogin' => $userLogin,
            'branches' => $branches,
            'qPaymentTo' => $qPaymentTo,
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
            'customer_id' => 'required|numeric',
            'all_selected_NP' => 'required',
            'kwitansi_date' => 'required',
            'payment_to_id' => 'required|numeric',
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid supplier',
            'customer_id.numeric' => 'Please select a valid supplier',
            'all_selected_NP.required' => 'Please generate NP',
            'kwitansi_date.required' => 'Kwitansi Date must be filled',
            'payment_to_id.required' => 'Please select a valid Payment To',
            'payment_to_id.numeric' => 'Please select a valid Payment To',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
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
            $identityName = 'tx_kwitansis-draft';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_kwitansis';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-'.$zero.strval($newInc);
            }

            $all_selected_NP = $request->all_selected_NP;
            if(substr($all_selected_NP,0,1)==','){
                $all_selected_NP = substr($all_selected_NP,1,strlen($all_selected_NP));
            }
            $FKarr = explode(",",$request->all_selected_NP);
            //cari expired date terjauh
            $qDOexpdate = Tx_delivery_order_non_tax::whereIn('delivery_order_no',$FKarr)
            ->orderBy('do_expired_date','DESC')
            ->first();

            $kwitansi_date = explode('/',$request->kwitansi_date);
            $ins = Tx_kwitansi::create([
                'kwitansi_no' => $kwitansi_no,
                'customer_id' => $request->customer_id,
                'kwitansi_date' => $kwitansi_date[2].'-'.$kwitansi_date[1].'-'.$kwitansi_date[0],
                'kwitansi_expired_date' => $qDOexpdate->do_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'payment_to_id' => ($request->payment_to_id!='#'?$request->payment_to_id:null),
                'np_total' => $request->totalValafterVAT,
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $retur_total_price = 0;
                        $nota_retur = Tx_nota_retur_non_tax::select(
                            'total_price'
                        )
                        ->whereRaw('approved_by IS NOT null')
                        ->where([
                            'delivery_order_id'=>$qDO->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($nota_retur){
                            $retur_total_price = $nota_retur->total_price;
                        }

                        $ins_dtl = Tx_kwitansi_detail::create([
                            'kwitansi_id' => $maxId,
                            'np_id' => $qDO->id,
                            'nota_penjualan_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'sj_no' => $qDO->sales_order_no_all,
                            'total' => ($qDO->total_price-$retur_total_price),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
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

        $queryCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_kwitansi::where('id','=',$id)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$query->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->get();

            $all_selected_NP_from_db = '';
            $all_selected_NP_count_from_db = 0;
            $invdtls = Tx_kwitansi_detail::where([
                'kwitansi_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('nota_penjualan_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_NP_from_db .= ','.$invdtl->nota_penjualan_no;
                }
                $all_selected_NP_count_from_db = $invdtls->count();
                if(substr($all_selected_NP_from_db,0,1)==','){
                    $all_selected_NP_from_db = substr($all_selected_NP_from_db,1,strlen($all_selected_NP_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCust' => $queryCustomer,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'qKwi' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_NP_from_db' => $all_selected_NP_from_db,
                'all_selected_NP_count_from_db' => $all_selected_NP_count_from_db,
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $is_director = '';
        $branch_id = '';
        $finance_admin_id = 0;
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
            $finance_admin_id = $userLogin->section_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_kwitansi::where('id','=',$id)
        ->first();
        if($query){
            $delivery_order = Tx_delivery_order_non_tax::where('customer_id', '=', (old('customer_id')?old('customer_id'):$query->customer_id))
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function($query) use ($id) {
                $query->select('tx_kwitansi_details.np_id')
                ->from('tx_kwitansi_details')
                ->leftJoin('tx_kwitansis','tx_kwitansi_details.kwitansi_id','=','tx_kwitansis.id')
                ->when($id!='', function($q) use ($id) {
                    $q->where('tx_kwitansi_details.kwitansi_id','<>', $id);
                })
                ->where('tx_kwitansi_details.active','=','Y')
                ->where('tx_kwitansis.active','=','Y');
            })
            ->where('active','=','Y')
            ->orderBy('delivery_order_date','DESC')
            ->orderBy('created_at','DESC')
            ->get();

            $all_selected_NP_from_db = '';
            $all_selected_NP_count_from_db = 0;
            $invdtls = Tx_kwitansi_detail::where([
                'kwitansi_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('nota_penjualan_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_NP_from_db .= ','.$invdtl->nota_penjualan_no;
                }
                $all_selected_NP_count_from_db = $invdtls->count();
                if(substr($all_selected_NP_from_db,0,1)==','){
                    $all_selected_NP_from_db = substr($all_selected_NP_from_db,1,strlen($all_selected_NP_from_db));
                }
            }

            $qPaymentTo = Mst_coa::select(
                'id',
                'coa_code',
                'coa_code_complete',
                'coa_name',
            )
            ->where('coa_code_complete','LIKE','112%%')
            ->where([
                'coa_level' => 5,
                'local' => 'N',
                'active' => 'Y',
                ])
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCust' => $queryCustomer,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'qKwi' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_NP_from_db' => $all_selected_NP_from_db,
                'all_selected_NP_count_from_db' => $all_selected_NP_count_from_db,
                'branches' => $branches,
                'qPaymentTo' => $qPaymentTo,
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'customer_id' => 'required|numeric',
            'all_selected_NP' => 'required',
            'kwitansi_date' => 'required',
            'payment_to_id' => 'required|numeric',
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid supplier',
            'customer_id.numeric' => 'Please select a valid supplier',
            'all_selected_NP.required' => 'Please generate NP',
            'kwitansi_date.required' => 'Kwitansi Date must be filled',
            'payment_to_id.required' => 'Please select a valid Payment To',
            'payment_to_id.numeric' => 'Please select a valid Payment To',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
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

            $nota_penjualan_no = '';
            $orders_old = Tx_kwitansi::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_kwitansi::where('id', '=', $id)
                ->where('kwitansi_no','LIKE','%Draft%')
                ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $kwitansi_no = $orders->kwitansi_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_kwitansis';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_kwitansi::where('id', '=', $id)
                ->update([
                    'kwitansi_no' => $kwitansi_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_kwitansi::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $allSO = '';
            $all_selected_NP = $request->all_selected_NP;
            if(substr($all_selected_NP,0,1)==','){
                $all_selected_NP = substr($all_selected_NP,1,strlen($all_selected_NP));
            }
            $FKarr = explode(",",$request->all_selected_NP);
            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $allSO .= $qDO->sales_order_no_all;
                    }
                }
            }

            //cari expired date terjauh
            $qDOexpdate = Tx_delivery_order_non_tax::whereIn('delivery_order_no',$FKarr)
            ->orderBy('do_expired_date','DESC')
            ->first();

            $kwitansi_date = explode('/',$request->kwitansi_date);
            $upd = Tx_kwitansi::where('id','=',$id)
            ->update([
                'customer_id' => $request->customer_id,
                'kwitansi_date' => $kwitansi_date[2].'-'.$kwitansi_date[1].'-'.$kwitansi_date[0],
                'kwitansi_expired_date' => $qDOexpdate->do_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'payment_to_id' => ($request->payment_to_id!='#'?$request->payment_to_id:null),
                'np_total' => $request->totalValafterVAT,
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // hapus data detail sebelumnya, ganti dengan baru
            $deleted = Tx_kwitansi_detail::where([
                'kwitansi_id' => $id,
            ])
            ->delete();

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $retur_total_price = 0;
                        $nota_retur = Tx_nota_retur_non_tax::select(
                            'total_price'
                        )
                        ->whereRaw('approved_by IS NOT null')
                        ->where([
                            'delivery_order_id'=>$qDO->id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($nota_retur){
                            $retur_total_price = $nota_retur->total_price;
                        }

                        $ins_dtl = Tx_kwitansi_detail::create([
                            'kwitansi_id' => $id,
                            'np_id' => $qDO->id,
                            'nota_penjualan_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'sj_no' => $qDO->sales_order_no_all,
                            'total' => ($qDO->total_price-$retur_total_price),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
