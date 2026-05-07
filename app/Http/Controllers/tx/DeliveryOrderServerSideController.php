<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Models\Tx_sales_order;
use App\Models\Tx_delivery_order;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_sales_order_part;
use App\Rules\IsSOnotConnectedToFK;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_part;
use Illuminate\Database\Query\Builder;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;

class DeliveryOrderServerSideController extends Controller
{
    protected $title = 'Faktur';
    protected $folder = 'delivery-order';
    protected $uri = 'faktur';
    protected $uriFp = 'faktur-fp';

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
            $query = Tx_delivery_order::leftJoin('userdetails AS usr','tx_delivery_orders.created_by','=','usr.user_id')
            ->leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
            ->leftJoin('mst_customers','tx_delivery_orders.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_delivery_orders.id as tx_id',
                'tx_delivery_orders.delivery_order_no',
                'tx_delivery_orders.delivery_order_date',
                'tx_delivery_orders.sales_order_no_all',
                'tx_delivery_orders.tax_invoice_id',
                'tx_delivery_orders.active as fk_active',
                'tx_delivery_orders.created_by as createdby',
                'tx_delivery_orders.created_at as createdat',
                'tx_delivery_orders.updated_at as updatedat',
                'tx_delivery_orders.draft_to_created_at',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'tx_tax_invoices.fp_no',
                'tx_tax_invoices.prefiks_code',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'ety_type.title_ind as ety_type_name',
            )
            ->selectRaw('IF(ISNULL(tx_tax_invoices.prefiks_code), tx_tax_invoices.fp_no, CONCAT(tx_tax_invoices.prefiks_code, " ", tx_tax_invoices.fp_no)) AS fp_no_w_prefiks')
            ->addSelect(['total_price' => Tx_delivery_order_part::selectRaw('SUM(tx_delivery_order_parts.qty_so*tx_delivery_order_parts.final_price)')
                ->whereColumn('tx_delivery_order_parts.delivery_order_id','tx_delivery_orders.id')
            ])
            ->where(function($q){
                $q->where('tx_delivery_orders.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_delivery_orders.active','N')
                    ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director!='Y' && $userLogin->section_id!=37 && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_delivery_orders.delivery_order_no', 'DESC')
            ->orderBy('tx_delivery_orders.created_at', 'DESC');

            return DataTables::of($query)
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
            ->filterColumn('delivery_order_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_delivery_orders.delivery_order_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('delivery_order_date', function ($query) {
                return date_format(date_create($query->delivery_order_date),"d/m/Y");
            })
            ->addColumn('created_at', function ($query) {
                $date = date_create($query->draft_to_created_at!=null?$query->draft_to_created_at:$query->createdat);
                date_add($date, date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours"));
                return date_format($date,"d/m/Y H:i:s");
            })
            ->addColumn('updated_at', function ($query) {
                $date = date_create($query->updatedat);
                date_add($date, date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours"));
                return date_format($date,"d/m/Y H:i:s");
            })
            ->filterColumn('sales_order_no_all', function($query, $keyword) {
                $query->whereIn('tx_delivery_orders.id', function($q) use($keyword){
                    $q->select('tx_dop.delivery_order_id')
                    ->from('tx_delivery_order_parts as tx_dop')
                    ->leftJoin('tx_sales_orders as tx_so', 'tx_dop.sales_order_id', '=', 'tx_so.id')
                    ->where('tx_so.sales_order_no', 'LIKE', "%{$keyword}%")
                    ->where([
                        'tx_dop.active' => 'Y',
                        'tx_so.active' => 'Y',
                    ]);
                });
            })
            ->editColumn('sales_order_no_all', function ($query) {
                $links = '';
                $so_arr = explode(",",$query->sales_order_no_all);
                foreach($so_arr as $so){
                    if($so!=''){
                        $qSO = Tx_sales_order::where('sales_order_no','=',$so)
                        ->first();
                        if($qSO){
                            $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$qSO->id).'" target="_new" style="text-decoration: underline;">'.$so.'</a><br/>';
                        }
                    }
                }
                return $links;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y' || $userLogin->section_id==37 || Auth::user()->id==1) &&
                    $query->fk_active=='Y'){
                    if ($query->fk_active=='Y' && strpos($query->delivery_order_no,'Draft')>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-faktur?fk='.$query->tx_id).'&p=1" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-faktur?fk='.$query->tx_id).'&p=2" target="_new" style="text-decoration: underline;">Download</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur-fp/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">'.
                            (!is_null($query->tax_invoice_id)?'Edit FP':'Add FP').'</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                        <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-faktur?fk='.$query->tx_id).'&p=1" target="_new" style="text-decoration: underline;">Print</a> |
                        <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-faktur?fk='.$query->tx_id).'&p=2" target="_new" style="text-decoration: underline;">Download</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if ($query->fk_active=='Y' && strpos($query->delivery_order_no,'Draft')==0 && is_null($query->tax_invoice_id)){
                    return 'FK';
                }
                if ($query->fk_active=='Y' && strpos($query->delivery_order_no,'Draft')==0 && !is_null($query->tax_invoice_id)){
                    return 'FP';
                }
                if ($query->fk_active=='Y' && strpos($query->delivery_order_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->fk_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['delivery_order_date','customer_name','created_at','updated_at','sales_order_no_all','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'uriFp' => $this->uriFp,
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
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
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

        $sales_order = [];
        $sales_order_date = [];
        $ship_to = [];
        if(old('customer_id')){
            $sales_order = Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
            ->select('tx_sales_orders.sales_order_no AS order_no')
            ->where('tx_sales_orders.sales_order_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_sales_orders.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_parts as tx_do_part')
                    ->where('tx_do_part.active','=','Y');
            })
            ->where(function($query) {
                $query->where('approved_by','<>',null)
                ->orWhere(function($queryA) {
                    $queryA->where('approved_by','=',null)
                    ->where('need_approval','=','N');
                });
            })
            ->where([
                'tx_sales_orders.customer_id' => old('customer_id'),
                'tx_sales_orders.active' => 'Y'
            ])
            ->when(old('sales_order_date')!='#', function($query) {
                $query->where('tx_sales_orders.sales_order_date','=',old('sales_order_date'));
            })
            ->when(old('is_vat')=='on', function($query) {
                $query->where('tx_sales_orders.is_vat','=','Y');
            })
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                $query->whereRaw('((usr.branch_id='.$userLogin->branch_id.' AND tx_sales_orders.branch_id IS null) OR tx_sales_orders.branch_id='.$userLogin->branch_id.')');
            })
            ->get();

            $sales_order_date = Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
            ->selectRaw('DISTINCT DATE_FORMAT(tx_sales_orders.sales_order_date, "%d/%m/%Y") as sales_order_date')
            ->where('tx_sales_orders.sales_order_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_sales_orders.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_parts as tx_do_part')
                    ->where('tx_do_part.active','=','Y');
            })
            ->where(function($query) {
                $query->where('tx_sales_orders.approved_by','<>',null)
                    ->orWhere(function($queryA) {
                    $queryA->where('tx_sales_orders.approved_by','=',null)
                        ->where('tx_sales_orders.need_approval','=','N');
                });
            })
            ->where([
                'tx_sales_orders.customer_id' => old('customer_id'),
                'tx_sales_orders.active' => 'Y'
            ])
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                $query->whereRaw('((usr.branch_id='.$userLogin->branch_id.' AND tx_sales_orders.branch_id IS null) OR tx_sales_orders.branch_id='.$userLogin->branch_id.')');
            })
            // ->orderBy('tx_sales_orders.sales_order_date','DESC')
            ->get();

            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'vat' => $vat,
            'get_sales_order_no' => $sales_order,
            'get_sales_order_date' => $sales_order_date,
            'ship_to' => $ship_to,
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
            'menu_id' => 39,
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
            'customer_id' => 'required|numeric',
            'sales_order_no_all' => ['required', new IsSOnotConnectedToFK(0)],
        ];
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'sales_order_no_all.required' => 'Please select a valid sales order no',
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
            $identityName = 'tx_delivery_orders-draft';
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
                $order_no = ENV('P_FAKTUR').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_delivery_orders';
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
                $order_no = ENV('P_FAKTUR').date('y').'-'.$zero.strval($newInc);
            }

            $is_vat = 'N';
            if ($request->vatOption == 'on') {
                $is_vat = 'Y';
            }

            // data user yg login saat ini
            $user = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qCust = Mst_customer::where('id','=',$request->customer_id)
            ->first();

            $so_date_arr = explode("/",$request->sales_order_date);
            $sales_order_date = date_create($so_date_arr[2].'-'.$so_date_arr[1].'-'.$so_date_arr[0]);
            date_add($sales_order_date, date_interval_create_from_date_string($qCust->top." days"));

            // get active VAT
            $vat_val = 0;
            $vat = ENV('VAT');
            $vatG = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vatG){
                $vat_val = $vatG->numeric_val;
                $vat = $vatG->numeric_val;
            }

            $ins = Tx_delivery_order::create([
                'delivery_order_no' => $order_no,
                'delivery_order_date' => $so_date_arr[2].'-'.$so_date_arr[1].'-'.$so_date_arr[0],
                'faktur_expired_date' => date_format($sales_order_date,"Y-m-d"),
                'sales_order_no_all' => $request->sales_order_no_all,
                'customer_id' => $request->customer_id,
                'customer_entity_type_id' => $qCust->entity_type_id,
                'customer_name' => $qCust->name,
                'remark' => $request->remark,
                'branch_id' => $user->branch_id,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_vat' => 'Y',
                'vat_val' => $vat_val,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $totalQty = 0;
            $totalPrice = 0;
            $lastBranchID = 0;
            $totalPerLastAVGcost = 0;
            $isThereSomePart = 0;
            for($iPart=0;$iPart<$request->totalRow;$iPart++){
                if($request['sales_order_part_id'.$iPart]){
                    $isThereSomePart++; // memastikan ada part yg dibawa

                    $partSO = Tx_sales_order_part::leftJoin('tx_sales_orders as tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                    ->select(
                        'tx_sales_order_parts.*',
                        'tx_so.branch_id',
                    )
                    ->where('tx_sales_order_parts.id','=',$request['sales_order_part_id'.$iPart])
                    ->first();
                    if($partSO){
                        $lastBranchID = $partSO->branch_id;
                        $totalPerLastAVGcost += ($partSO->last_avg_cost*$partSO->qty);

                        $insPart = Tx_delivery_order_part::create([
                            'delivery_order_id' => $maxId,
                            'sales_order_id' => $request['sales_order_id'.$iPart],
                            'sales_order_part_id' => $request['sales_order_part_id'.$iPart],
                            'part_id' => $partSO->part_id,
                            'qty' => $partSO->qty,
                            'qty_so' => $partSO->qty,
                            'final_price' => $partSO->price,
                            'total_price'=> ($partSO->qty*$partSO->price),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);

                        if($request->is_draft!='Y'){
                            // update final price
                            $updFinalPrice = Mst_part::where([
                                'id' => $partSO->part_id,
                            ])
                            ->update([
                                'final_price' => $partSO->price,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        // update stok tersedia
                        $qStock = Tx_qty_part::where('part_id','=',$partSO->part_id)
                        ->where('branch_id','=',$partSO->branch_id)
                        ->first();
                        if($qStock && $request->is_draft!='Y'){
                            if ($qStock->qty<$partSO->qty){
                                DB::rollback();
                                
                                return redirect()
                                ->back()
                                ->withInput()
                                ->with('status-error', 'The number of spare parts to be sold exceeds the available stock.');
                            }else{
                                // update stok diproses jika status bukan draft
                                $updStock = Tx_qty_part::where('part_id','=',$partSO->part_id)
                                ->where('branch_id','=',$partSO->branch_id)
                                ->update([
                                    'qty' => $qStock->qty-$partSO->qty,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }

                        $totalQty += $partSO->qty;
                        $totalPrice += ($partSO->qty*$partSO->price);
                    }
                }
            }
            if ($isThereSomePart<1){
                DB::rollback();
                
                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_03')?ENV('ERR_MSG_03'):'Make sure the selected SO has at least 1 part!');
            }

            $upd = Tx_delivery_order::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_after_vat' => $totalPrice+($totalPrice*$vat/100),
            ]);

            // buat automatic general journal untuk faktur
            if($request->is_draft!='Y'){
                // simpan deskripsi utk jurnal - start
                $deskripsi = '';
                $getDesc = Tx_delivery_order::leftJoin('mst_customers AS msc', 'tx_delivery_orders.customer_id', '=', 'msc.id')
                ->leftJoin('mst_globals AS msg', 'msc.entity_type_id', '=', 'msg.id')
                ->select(
                    'tx_delivery_orders.delivery_order_no',
                    'tx_delivery_orders.remark',
                    'msc.name AS cust_name',
                    'msc.customer_unique_code AS cust_unique_code',
                    'msg.title_ind AS entity_type',
                )
                ->where('tx_delivery_orders.id', '=', $maxId)
                ->first();
                if ($getDesc){
                    $deskripsi = $getDesc->delivery_order_no.', '.
                        $getDesc->cust_unique_code.' - '.($getDesc->entity_type!=null?$getDesc->entity_type.' ':'').$getDesc->cust_name.', '.
                        $getDesc->remark;
                    $deskripsi = substr($deskripsi, 0, 4096);
                }
                // simpan deskripsi utk jurnal - end

                // cek apakah fitur automatic journal faktur sudah tersedia
                $qAutJournal = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>1,
                    'branch_id'=>$lastBranchID,
                    'active'=>'Y',
                ])
                ->first();
                if ($qAutJournal){
                    // cogs
                    $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'cogs\'')
                    ->first();
                    // inventory
                    $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'inventory\'')
                    ->first();
                    // piutang
                    $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'piutang\'')
                    ->first();
                    // sales pajak
                    $qAutJournal_sales_pajak = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'sales pajak\'')
                    ->first();
                    // ppn keluaran
                    $qAutJournal_ppn_keluaran = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                    ->first();

                    // cek apakah module sudah pernah dibuat
                    $insJournal = [];
                    $qJournals = Tx_general_journal::where([
                        'module_no'=>$order_no,
                        'automatic_journal_id'=>1,
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
                        // create journal
                        $yearTemp = substr($so_date_arr[2], 2, 2);
                        $monthTemp = $so_date_arr[1];
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
                            $date = date_format(date_create($autoInc->updated_at), "n");
                            $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                            $dateNow = date("ym");
                            if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                // jika bulan di server berbeda dengan bulan jurnal yg dipilih

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
                        $zero = '';
                        for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                            $zero .= '0';
                        }
                        $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                        // buat jurnal
                        $insJournal = Tx_general_journal::create([
                            'general_journal_no'=>$journal_no,
                            'general_journal_date'=>$so_date_arr[2].'-'.$so_date_arr[1].'-'.$so_date_arr[0],
                            'total_debit'=>$totalPerLastAVGcost+$totalPrice+($totalPrice*$vat/100),
                            'total_kredit'=>$totalPerLastAVGcost+$totalPrice+($totalPrice*$vat/100),
                            'module_no'=>$order_no,
                            'automatic_journal_id'=>1,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }

                    // cogs
                    $ins_cogs = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>$deskripsi,
                        'debit'=>$totalPerLastAVGcost,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // inventory
                    $ins_inventory = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>$deskripsi,
                        'debit'=>0,
                        'kredit'=>$totalPerLastAVGcost,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // piutang
                    $ins_piutang = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_piutang->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>$deskripsi,
                        'debit'=>$totalPrice+($totalPrice*$vat/100),
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // sales pajak
                    $ins_sales_pajak = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_sales_pajak->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>$deskripsi,
                        'debit'=>0,
                        'kredit'=>$totalPrice,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // ppn keluaran
                    $ins_ppn_keluaran = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_ppn_keluaran->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>$deskripsi,
                        'debit'=>0,
                        'kredit'=>($totalPrice*$vat/100),
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
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

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
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

        $query = Tx_delivery_order::where('id','=',$id)->first();
        if($query){
            $parts = Tx_delivery_order_part::where([
                'delivery_order_id' => $id,
                // 'active' => 'Y'
            ]);

            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts->get(),
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $parts->count()),
            'vat' => $vat,
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency,
        ];

        return view('tx.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $is_director = '';
        $branch_id = '';
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
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

        $ship_to = [];
        $query = [];
        $parts = [];
        $partCount = 0;

        $query = Tx_delivery_order::where('id','=',$id)
        ->first();
        if($query){
            $parts = Tx_delivery_order_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $partCount = Tx_delivery_order_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ])
            ->count();

            if(old('customer_id')){
                $ship_to = Mst_customer_shipment_address::where([
                    'customer_id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->get();
            }else{
                $ship_to = Mst_customer_shipment_address::where([
                    'customer_id' => $query->customer_id,
                    'active' => 'Y'
                ])
                ->get();
            }
        }else{
            $query = [];
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $partCount),
            'vat' => $vat,
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency
        ];

        return view('tx.'.$this->folder.'.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 39,
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
            'sales_order_no_all' => ['required', new IsSOnotConnectedToFK($id)],
        ];
        $errMsg = [
            'sales_order_no_all.required' => 'Please select a valid sales order no',
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
            // get active VAT
            $vat_val = 0;
            $vat = ENV('VAT');
            $vatG = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vatG){
                $vat_val = $vatG->numeric_val;
                $vat = $vatG->numeric_val;
            }

            $delivery_order_no = '';
            $orders_old = Tx_delivery_order::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_delivery_order::where('id', '=', $id)
            ->where('delivery_order_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $delivery_order_no = $orders->delivery_order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_delivery_orders';
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
                $delivery_order_no = ENV('P_FAKTUR').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_delivery_order::where('id', '=', $id)
                ->update([
                    'delivery_order_no' => $delivery_order_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_delivery_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $cust = Mst_customer::where('id', '=', $orders_old->customer_id)
            ->first();

            $userLogin = Userdetail::where('user_id','=',$orders_old->created_by)
            ->first();

            $sales_order_date = date_create($orders_old->sales_order_date);
            date_add($sales_order_date, date_interval_create_from_date_string($cust->top." days"));

            $upd = Tx_delivery_order::where('id','=',$id)
            ->update([
                'faktur_expired_date' => date_format($sales_order_date,"Y-m-d"),
                'remark' => $request->remark,
                'branch_id' => $userLogin->branch_id,
                'updated_by' => Auth::user()->id,
            ]);

            if($orders_old->delivery_order_no!=$delivery_order_no && $delivery_order_no!=''){
                // jika dari draft menjadi created
                $doPart = Tx_delivery_order_part::where([
                    'delivery_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();

                // data user yg create DO
                $user = Userdetail::where('user_id','=',$orders_old->created_by)
                ->first();

                $totalQty = 0;
                $totalPrice = 0;
                $lastBranchID = 0;
                $totalPerLastAVGcost = 0;
                foreach($doPart as $do_part){
                    $partSO = Tx_sales_order_part::leftJoin('tx_sales_orders as tx_so','tx_sales_order_parts.order_id','=','tx_so.id')
                    ->select(
                        'tx_sales_order_parts.*',
                        'tx_so.branch_id',
                    )
                    ->where('tx_sales_order_parts.id','=',$do_part->sales_order_part_id)
                    ->first();
                    if($partSO){
                        $lastBranchID = $partSO->branch_id;
                        $totalPerLastAVGcost += ($partSO->last_avg_cost*$partSO->qty);

                        $qtyStock = Tx_qty_part::where('part_id','=',$do_part->part_id)
                        ->where('branch_id','=',$partSO->branch_id)
                        ->first();
                        if($qtyStock){
                            if ($qtyStock->qty<$do_part->qty){
                                DB::rollback();

                                return redirect()
                                ->back()
                                ->withInput()
                                ->with('status-error', 'The number of spare parts to be sold exceeds the available stock.');
                            }else{
                                $updStock = Tx_qty_part::where('part_id','=',$do_part->part_id)
                                ->where('branch_id','=',$partSO->branch_id)
                                ->update([
                                    'qty' => $qtyStock->qty-$do_part->qty,
                                ]);
                            }
                        }

                        // update final price
                        $updFinalPrice = Mst_part::where([
                            'id' => $partSO->part_id,
                        ])
                        ->update([
                            'final_price' => $partSO->price,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $totalQty += $partSO->qty;
                        $totalPrice += ($partSO->qty*$partSO->price);
                    }
                }
                // journal
                if($request->is_draft=='N'){
                    // $lastBranchID = $orders_old->branch_id;
                    $so_date_arr = explode("-", $orders_old->delivery_order_date);

                    // simpan deskripsi utk jurnal - start
                    $deskripsi = '';
                    $getDesc = Tx_delivery_order::leftJoin('mst_customers AS msc', 'tx_delivery_orders.customer_id', '=', 'msc.id')
                    ->leftJoin('mst_globals AS msg', 'msc.entity_type_id', '=', 'msg.id')
                    ->select(
                        'tx_delivery_orders.delivery_order_no',
                        'tx_delivery_orders.remark',
                        'msc.name AS cust_name',
                        'msc.customer_unique_code AS cust_unique_code',
                        'msg.title_ind AS entity_type',
                    )
                    ->where('tx_delivery_orders.id', '=', $id)
                    ->first();
                    if ($getDesc){
                        $deskripsi = $getDesc->delivery_order_no.', '.
                            $getDesc->cust_unique_code.' - '.($getDesc->entity_type!=null?$getDesc->entity_type.' ':'').$getDesc->cust_name.', '.
                            $getDesc->remark;
                        $deskripsi = substr($deskripsi, 0, 4096);
                    }
                    // simpan deskripsi utk jurnal - end
    
                    // cek apakah fitur automatic journal faktur sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>1,
                        'branch_id'=>$lastBranchID,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournal){
                        // cogs
                        $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>1,
                            'branch_id'=>$lastBranchID,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'cogs\'')
                        ->first();
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>1,
                            'branch_id'=>$lastBranchID,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // piutang
                        $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>1,
                            'branch_id'=>$lastBranchID,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'piutang\'')
                        ->first();
                        // sales pajak
                        $qAutJournal_sales_pajak = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>1,
                            'branch_id'=>$lastBranchID,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'sales pajak\'')
                        ->first();
                        // ppn keluaran
                        $qAutJournal_ppn_keluaran = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>1,
                            'branch_id'=>$lastBranchID,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'ppn keluaran\'')
                        ->first();
    
                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_general_journal::where([
                            'module_no'=>$delivery_order_no,
                            'automatic_journal_id'=>1,
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
                            // create journal
                            $yearTemp = substr($so_date_arr[0], 2, 2);
                            $monthTemp = $so_date_arr[1];
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
                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");
                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server berbeda dengan bulan jurnal yg dipilih
    
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
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);
                            
                            // buat jurnal
                            $insJournal = Tx_general_journal::create([
                                'general_journal_no'=>$journal_no,
                                'general_journal_date'=>$orders_old->delivery_order_date,
                                'total_debit'=>$totalPerLastAVGcost+$totalPrice+($totalPrice*$vat/100),
                                'total_kredit'=>$totalPerLastAVGcost+$totalPrice+($totalPrice*$vat/100),
                                'module_no'=>$delivery_order_no,
                                'automatic_journal_id'=>1,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
    
                        // cogs
                        $ins_cogs = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_cogs->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>$totalPerLastAVGcost,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
    
                        // inventory
                        $ins_inventory = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_inventory->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>$totalPerLastAVGcost,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
    
                        // piutang
                        $ins_piutang = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_piutang->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>$totalPrice+($totalPrice*$vat/100),
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
    
                        // sales pajak
                        $ins_sales_pajak = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_sales_pajak->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>$totalPrice,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
    
                        // ppn keluaran
                        $ins_ppn_keluaran = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_ppn_keluaran->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>($totalPrice*$vat/100),
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_delivery_order $tx_delivery_order)
    {
        //
    }
}
