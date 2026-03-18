<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\SQnumUnique;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Models\Tx_sales_order;
use App\Models\V_log_avg_cost;
use App\Helpers\GlobalFuncHelper;
use App\Rules\ApprovalCheckingSO;
use App\Models\Tx_sales_quotation;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_sales_order_part;
use App\Rules\MaxPartQtySalesOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_part;
use Illuminate\Database\Query\Builder;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use App\Helpers\CheckTopOrCreditLimitHelper;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;

class SalesOrderServerSideController extends Controller
{
    protected $title = 'Sales Order';
    protected $folder = 'sales-order';

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
            $query = Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
            ->leftJoin('tx_sales_quotations','tx_sales_orders.sales_quotation_id','=','tx_sales_quotations.id')
            ->leftJoin('mst_customers','tx_sales_orders.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('mst_globals as ent','mst_customers.entity_type_id','=','ent.id')
            ->select(
                'tx_sales_orders.id as tx_id',
                'tx_sales_orders.sales_order_no',
                'tx_sales_orders.sales_quotation_id',
                'tx_sales_orders.sales_order_date',
                'tx_sales_orders.total_before_vat',
                'tx_sales_orders.customer_doc_no',
                'tx_sales_orders.active as so_active',
                'tx_sales_orders.need_approval',
                'tx_sales_orders.canceled_by',
                'tx_sales_orders.number_of_prints',
                'tx_sales_orders.approved_by',
                'tx_sales_orders.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'tx_sales_quotations.sales_quotation_no',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'ent.title_ind as customer_entity_type_name',
            )
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use ($userLogin) {
                $q->where('mst_customers.branch_id', '=', $userLogin->branch_id);
            })
            ->orderBy('tx_sales_orders.sales_order_date', 'DESC')
            ->orderBy('tx_sales_orders.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('sales_order_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_sales_orders.sales_order_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('sales_order_date', function ($query) {
                return date_format(date_create($query->sales_order_date),"d/m/Y");
            })
            ->filterColumn('cust_name_complete', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_customers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_customers.customer_unique_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('cust_name_complete', function ($query) {
                return $query->customer_unique_code.' - '.$query->customer_entity_type_name.' '.$query->cust_name;
            })
            ->filterColumn('sales_quotation_no', function($query, $keyword) {
                $query->whereRaw('tx_sales_quotations.sales_quotation_no LIKE ?', ["%{$keyword}%"]);
            })
            ->addColumn('sales_quotation_no', function ($query) {
                if(!is_null($query->sales_quotation_no)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-quotation/'.$query->sales_quotation_id).'" target="_new"
                        style="text-decoration: underline;">'.$query->sales_quotation_no.'</a>';
                }else{
                    return '';
                }
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $isFK = false;
                $faktur = Tx_delivery_order_part::where('sales_order_id','=',$query->tx_id)
                ->first();
                if ($faktur){
                    $isFK = true;
                }
                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y' || $userLogin->section_id==37 || Auth::user()->id==1) &&
                    $query->so_active=='Y' && ($query->need_approval=='N' || strpos($query->sales_order_no,"Draft")>0)){
                    // if ($isFK || $query->number_of_prints>0){
                    if (($isFK && $query->number_of_prints>0) || ($isFK && $query->number_of_prints==0)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="#"style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                            <a href="#"style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a> ('.$query->number_of_prints.')';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                            <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a> ('.$query->number_of_prints.')';
                    }
                }else{
                    if ($query->need_approval=='Y' && $query->canceled_by==null){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                $faktur = Tx_delivery_order_part::where('sales_order_id','=',$query->tx_id)
                ->first();
                if ($query->so_active=='N'){
                    return 'Cancel';
                }
                if ($query->so_active=='Y' && strpos($query->sales_order_no,"Draft")>0){
                    return 'Draft';
                }
                if ($query->so_active=='Y' && !strpos($query->sales_order_no,"Draft") && $query->need_approval=='N' && is_null($query->approved_by)
                    && !$faktur && $query->number_of_prints==0){
                    return 'Create';
                }
                if ($query->so_active=='Y' && $faktur){
                    return 'FK';
                }
                if ($query->so_active=='Y' && !strpos($query->sales_order_no,"Draft") && $query->need_approval=='Y'){
                    return 'Waiting for Approval';
                }
                if ($query->so_active=='Y' && !is_null($query->approved_by) && $query->number_of_prints==0){
                    return 'Approved';
                }
                if ($query->so_active=='Y' && $faktur && $query->number_of_prints>0){
                    return 'Deliver';
                }
            })
            ->rawColumns(['sales_order_date','cust_name_complete','sales_quotation_no','action','status'])
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
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

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

        $qCustomer = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where(function($q){
            $q->where('npwp_no', '<>', null)
            ->where('npwp_no', '<>', '-')
            ->where('npwp_no', '<>', '');
        })
        ->where('active', 'Y')
        ->orderBy('name','ASC')
        ->get();

        $couriers = Mst_courier::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $qCustomerInfo = [];
        $qCustomerShipmentAddressInfo = [];
        $qSQno = [];
        if (old('customer_id')) {
            $qCustomerInfo = Mst_customer::where([
                'id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->first();
            $qCustomerShipmentAddressInfo = Mst_customer_shipment_address::where([
                'customer_id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->get();
            $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) {
                $query->select('sales_quotation_id')
                    ->from('tx_sales_orders')
                    ->where('sales_quotation_id','<>',null);
            })
            ->where('sales_quotation_no','NOT LIKE','%Draft%')
            ->where([
                'customer_id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->get();
        }
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'customers' => $qCustomer,
            'custInfo' => $qCustomerInfo,
            'custShipmentAddressInfo' => $qCustomerShipmentAddressInfo,
            'qSQno' => $qSQno,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'qCurrency' => $qCurrency,
            'userLogin' => $userLogin,
            'couriers' => $couriers,
            'branches' => $branches,
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
            'menu_id' => 33,
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

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $validateInput = [
            'customer_id' => 'required|numeric',
            'cust_doc_no' => 'max:255',
            'cust_shipment_address' => 'required|numeric',
            'cust_pic' => 'required|numeric',
            'sales_quotation_no' => ['nullable', new SQnumUnique(0)],
            'courier_id' => 'required_if:courier_type,3'
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid customer',
            'customer_id.numeric' => 'Please select a valid customer',
            'cust_shipment_address.numeric' => 'Please select a valid customer shipment address',
            'cust_pic.required' => 'Please select a valid customer PIC',
            'cust_pic.numeric' => 'Please select a valid customer PIC',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            $different = '';
            $different_rule = '';
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    if($i>=1){
                        $different .= ',part_id'.$i;
                        $different_rule = '|different:'.$different;
                    }
                }
            }

            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $partNo = Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                    ->select(
                        'mst_parts.*',
                        'tx_qty.qty AS qty_oh'
                    )
                    ->where([
                        'mst_parts.id' => $request['part_id'.$i],
                        'tx_qty.branch_id' => $request->branch_id,
                    ])
                    ->first();

                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric|'.str_replace('part_id'.$i,"",$different_rule),
                        'qty'.$i => ['required','numeric','min:1',new MaxPartQtySalesOrder($request['part_id'.$i],$request->branch_id,0)],
                        'price'.$i => [new NumericCustom('Price'), 'nullable'],
                        'avg_cost_'.$i.'_db' => 'required|numeric'
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'part_id'.$i.'.different' => 'The part name must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty must be at least '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'qty'.$i.'.max' => 'The qty must not be greater than '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'price'.$i.'.numeric' => 'The price field is must be numeric',
                        'price'.$i.'.required' => 'The price field is required',
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
            $identityName = 'tx_sales_orders-draft';
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
                $order_no = ENV('P_SALES_ORDER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_sales_orders';
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
                $order_no = ENV('P_SALES_ORDER').date('y').'-'.$zero.strval($newInc);
            }

            $cust = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $sales_order_date = date_create(date("Y-m-d"));
            date_add($sales_order_date, date_interval_create_from_date_string($cust->top." days"));

            // get active VAT
            $vat = ENV('VAT');
            $vat_val = 0;
            $vatG = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vatG){
                $vat_val = $vatG->numeric_val;
                $vat = $vatG->numeric_val;
            }

            $ins = Tx_sales_order::create([
                'sales_order_no' => $order_no,
                'sales_quotation_id' => ($request->sales_quotation_no=='#'?null:$request->sales_quotation_no),
                'customer_doc_no' => $request->cust_doc_no,
                'sales_order_date' => date("Y-m-d"),
                'sales_order_expired_date' => date_format($sales_order_date,"Y-m-d"),
                'customer_id' => $request->customer_id,
                'cust_entity_type' => $cust->entity_type_id,
                'cust_name' => $cust->name,
                'cust_office_address' => $cust->office_address,
                'cust_country_id' => ($cust->province_id==9999?$cust->city->country_id:$cust->province->country_id),
                'cust_province_id' => $cust->province_id,
                'cust_city_id' => $cust->city_id,
                'cust_district_id' => $cust->district_id,
                'cust_sub_district_id' => $cust->sub_district_id,
                'cust_shipment_address' => $request->cust_shipment_address,
                'post_code' => $cust->post_code,
                'branch_id' => $request->branch_id,
                'pic_id' => $request->cust_pic,
                'pic_name' => ($request->cust_pic == 1 ? $cust->pic1_name : $cust->pic2_name),
                'cust_unit_no' => $request->cust_unit_no,
                'remark' => $request->salesRemark,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_vat' => 'Y',
                'vat_val' => $vat_val,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;

            $needApproval = 'N';
            $isThereSomePart = 0;
            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                $totalPriceAfterVAT = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if(isset($request['part_id'.$i])){
                        $isThereSomePart++; // memastikan ada part yg dibawa

                        $part = Mst_part::where('id', '=', $request['part_id'.$i])
                        ->first();
                        $insPart = Tx_sales_order_part::create([
                            'order_id' => $maxId,
                            'part_id' => $request['part_id'.$i],
                            'part_no' => $part->part_number,
                            'qty' => $request['qty'.$i],
                            'price' => $request['price'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price'.$i]),
                            'desc' => $request['desc_part'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);

                        $totalQty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) * $vat) / 100);

                        if(GlobalFuncHelper::moneyValidate($request['price'.$i])<$request['avg_cost_'.$i.'_db']){
                            $needApproval = 'Y';
                        }
                    }
                }

                if(strpos($order_no,"Draft")==0 && $needApproval=='N'){
                    $branch_id = $request->branch_id;
                    $parts = Tx_sales_order_part::where([
                        'order_id' => $maxId,
                        'active' => 'Y'
                    ])
                    ->get();
                    foreach($parts as $part){
                        $qtyPerBranch = 0;
                        $partQty = Tx_qty_part::where([
                            'part_id' => $part->part_id,
                            'branch_id' => $branch_id,
                        ])
                        ->first();
                        if($partQty){
                            // ambil total OH per cabang
                            $qtyPerBranch = $partQty->qty;
                        }else{
                            // generate data Qty part jika belum ada, dimulai dari qty = 0
                            $qtyPartIns = Tx_qty_part::create([
                                'part_id' => $part->part_id,
                                'qty' => $qtyPerBranch,
                                'branch_id' => $branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                        // update final price, total sales
                        $updFinalPrice = Mst_part::where([
                            'id' => $part->part_id
                        ])
                        ->update([
                            'final_price' => $part->price,
                            'total_sales' => $part->price*$qtyPerBranch,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }

                $upd = Tx_sales_order::where('id', '=', $maxId)
                ->update([
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
                    'need_approval' => $needApproval
                ]);

                if ($cust){
                    $msg = CheckTopOrCreditLimitHelper::checkAll($cust, '', $totalPriceBeforeVAT);
                    if ($msg != ''){
                        DB::rollback();

                        return redirect()
                        ->back()
                        ->withInput()
                        ->with('status-error', $msg);
                    }
                }
            }
            if ($isThereSomePart<1){
                DB::rollback();
                
                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            $qPart = Tx_sales_order_part::where([
                'order_id'=>$maxId,
            ])
            ->whereRaw('last_avg_cost IS null OR last_avg_cost=0')
            ->get();
            foreach($qPart as $qP){
                $avg = Mst_part::where([
                    'id'=>$qP->part_id,
                ])
                ->first();
                if ($avg){
                    $updPart = Tx_sales_order_part::where('id','=',$qP->id)
                    ->update([
                        'last_avg_cost'=>$avg->avg_cost,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
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

        $query = Tx_sales_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $qCustomer = Mst_customer::where(function($q){
                $q->where('npwp_no', '<>', null)
                ->where('npwp_no', '<>', '-')
                ->where('npwp_no', '<>', '');
            })
            ->where('active', 'Y')
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();
            $qCustomerInfo = Mst_customer::where([
                'id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->first();
            $qCustomerShipmentAddressInfo = Mst_customer_shipment_address::where([
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->get();
            $qSQno = Tx_sales_quotation::where('sales_quotation_no','NOT LIKE','%Draft%')
            ->where([
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->get();
            $queryPart = Tx_sales_order_part::where([
                'order_id' => $id,
                'active' => 'Y'
            ])
            ->orderBy('created_at', 'ASC')
            ->get();
            $queryPartCount = Tx_sales_order_part::where([
                'order_id' => $id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'customers' => $qCustomer,
                'parts' => $parts,
                'custInfo' => $qCustomerInfo,
                'custShipmentAddressInfo' => $qCustomerShipmentAddressInfo,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'orders' => $query,
                'order_parts' => $queryPart,
                'qSQno' => $qSQno,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
            ];

            return view('tx.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // ini_set('memory_limit', '128M');
        // ini_set('max_execution_time', 1800);

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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $qCustomer = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where(function($q){
            $q->where('npwp_no', '<>', null)
            ->where('npwp_no', '<>', '-')
            ->where('npwp_no', '<>', '');
        })
        ->where('active', 'Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_sales_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_number', 'ASC')
            ->get();

            $couriers = Mst_courier::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();

            $qCustomerInfo = [];
            $qCustomerShipmentAddressInfo = [];
            if (old('customer_id')) {
                $qCustomerInfo = Mst_customer::where([
                    'id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->first();
                $qCustomerShipmentAddressInfo = Mst_customer_shipment_address::where([
                    'customer_id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->get();
                $queryC = Tx_sales_order::where('customer_id','=',old('customer_id'))->first();
                if($queryC){
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($queryC,$id) {
                        $query->select('sales_quotation_id')
                            ->from('tx_sales_orders')
                            ->where('sales_quotation_id','<>',$queryC->sales_quotation_id)
                            ->where('id','<>',$id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => old('customer_id'),
                        'active' => 'Y'
                    ])
                    ->get();
                }else{
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($id) {
                        $query->select('sales_quotation_id')
                            ->from('tx_sales_orders')
                            ->where('id','<>',$id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => old('customer_id'),
                        'active' => 'Y'
                    ])
                    ->get();
                }

            } else {
                $qCustomerInfo = Mst_customer::where([
                    'id' => $query->customer_id,
                    'active' => 'Y'
                ])
                    ->orderBy('name', 'ASC')
                    ->first();
                $qCustomerShipmentAddressInfo = Mst_customer_shipment_address::where([
                    'customer_id' => $query->customer_id,
                    'active' => 'Y'
                ])
                    ->get();
                $queryC = Tx_sales_order::where('customer_id','=',$query->customer_id)->first();
                if($queryC){
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($queryC,$id) {
                        $query->select('sales_quotation_id')
                            ->from('tx_sales_orders')
                            ->where('sales_quotation_id','<>',$queryC->sales_quotation_id)
                            ->where('id','<>',$id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => $query->customer_id,
                        'active' => 'Y'
                    ])
                    ->get();
                }else{
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($id) {
                        $query->select('sales_quotation_id')
                            ->from('tx_sales_orders')
                            ->where('id','<>',$id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => $query->customer_id,
                        'active' => 'Y'
                    ])
                    ->get();
                }
            }
            $queryPart = Tx_sales_order_part::where([
                'order_id' => $id,
                'active' => 'Y'
            ])
            ->orderBy('created_at', 'ASC')
            ->get();
            $queryPartCount = Tx_sales_order_part::where([
                'order_id' => $id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'customers' => $qCustomer,
                'parts' => $parts,
                'custInfo' => $qCustomerInfo,
                'custShipmentAddressInfo' => $qCustomerShipmentAddressInfo,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'orders' => $query,
                'order_parts' => $queryPart,
                'qSQno' => $qSQno,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'couriers' => $couriers,
                'branches' => $branches,
            ];

            return view('tx.'.$this->folder.'.edit', $data);
        } else {
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
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 33,
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
            'cust_doc_no' => 'max:255',
            'customer_id' => 'required|numeric',
            'cust_shipment_address' => 'required|numeric',
            'cust_pic' => 'required|numeric',
            'sales_quotation_no' => ['nullable', new SQnumUnique($id)],
            'sales_order_no' => [new ApprovalCheckingSO],
            'courier_id' => 'required_if:courier_type,3',
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid customer',
            'customer_id.numeric' => 'Please select a valid customer',
            'cust_shipment_address.required' => 'Please select a valid customer shipment address',
            'cust_shipment_address.numeric' => 'Please select a valid customer shipment address',
            'cust_pic.required' => 'Please select a valid customer PIC',
            'cust_pic.numeric' => 'Please select a valid customer PIC',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            $different = '';
            $different_rule = '';
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    if($i>=1){
                        $different .= ',part_id'.$i;
                        $different_rule = '|different:'.$different;
                    }
                }
            }

            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $partNo = Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                    ->select(
                        'mst_parts.*',
                        'tx_qty.qty AS qty_oh'
                    )
                    ->where([
                        'mst_parts.id' => $request['part_id'.$i],
                        'tx_qty.branch_id' => $request->branch_id,
                    ])
                    ->first();

                    $initial_amount = $request['initial_amount'.$i]==null?$request['qty'.$i]:$request['initial_amount'.$i];
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric|'.str_replace('part_id'.$i,"",$different_rule),
                        'qty'.$i => ['required','numeric','min:1', 'max:'.$initial_amount, new MaxPartQtySalesOrder($request['part_id'.$i],$request->branch_id,$id)],
                        'price'.$i => [new NumericCustom('Price'), 'nullable'],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'part_id'.$i.'.different' => 'The part name must be different.',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty must be at least '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'qty'.$i.'.max' => 'The qty must not be greater than '.$initial_amount.'.',
                        'qty'.$i.'.lte' => 'The qty must be less or equal than '.$initial_amount.'.',
                        'price'.$i.'.numeric' => 'The price field is must be numeric',
                        'price'.$i.'.required' => 'The price field is required',
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
            $order_no = '';
            $draft = false;
            $orders = Tx_sales_order::where('id', '=', $id)
            ->where('sales_order_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $order_no = $orders->order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_sales_orders';
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
                $order_no = ENV('P_SALES_ORDER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_sales_order::where('id', '=', $id)
                ->update([
                    'sales_order_no' => $order_no,
                    'sales_order_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_sales_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $order = Tx_sales_order::where('id','=',$id)
            ->first();

            $cust = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $sales_order_date = date_create($order->sales_order_date);
            date_add($sales_order_date, date_interval_create_from_date_string($cust->top." days"));

            $upd = Tx_sales_order::where('id','=',$id)
            ->update([
                'sales_order_expired_date' => date_format($sales_order_date,"Y-m-d"),
                'sales_quotation_id' => ($request->sales_quotation_no=='#'?null:$request->sales_quotation_no),
                'customer_doc_no' => $request->cust_doc_no,
                'customer_id' => $request->customer_id,
                'cust_entity_type' => $cust->entity_type_id,
                'cust_name' => $cust->name,
                'cust_office_address' => $cust->office_address,
                'cust_country_id' => ($cust->province_id==9999?$cust->city->country_id:$cust->province->country_id),
                'cust_province_id' => $cust->province_id,
                'cust_city_id' => $cust->city_id,
                'cust_district_id' => $cust->district_id,
                'cust_sub_district_id' => $cust->sub_district_id,
                'cust_shipment_address' => $request->cust_shipment_address,
                'post_code' => $cust->post_code,
                'branch_id' => $request->branch_id,
                'pic_id' => $request->cust_pic,
                'pic_name' => ($request->cust_pic == 1 ? $cust->pic1_name : $cust->pic2_name),
                'cust_unit_no' => $request->cust_unit_no,
                'remark' => $request->salesRemark,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
                'is_vat' => 'Y',
                'updated_by' => Auth::user()->id
            ]);

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

            $needApproval = 'N';
            $isThereSomePart = 0;
            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                $totalPriceAfterVAT = 0;

                $updPart = Tx_sales_order_part::where('order_id','=',$id)
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id
                ]);

                for ($i = 0; $i < $request->totalRow; $i++) {
                    $qSalesPart = Tx_sales_order_part::where('id','=',$request['order_part_id'.$i])->first();
                    $part = Mst_part::where('id', '=', $request['part_id'.$i])->first();
                    if($request['part_id'.$i]){
                        $isThereSomePart++; // memastikan ada part yg dibawa

                        if($qSalesPart){
                            $updPart = Tx_sales_order_part::where('id','=',$request['order_part_id'.$i])
                            ->update([
                                'order_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'part_no' => $part->part_number,
                                'qty' => $request['qty'.$i],
                                'price' => $request['price'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'last_avg_cost' => $request['avg_cost_'.$i.'_db'],
                                'desc' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                        }else{
                            $insPart = Tx_sales_order_part::create([
                                'order_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'part_no' => $part->part_number,
                                'qty' => $request['qty'.$i],
                                'price' => $request['price'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'last_avg_cost' => $request['avg_cost_'.$i.'_db'],
                                'desc' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }

                        $totalQty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i]));
                        $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) +
                            ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) * $vat) / 100);

                        if(GlobalFuncHelper::moneyValidate($request['price'.$i])<$request['avg_cost_'.$i.'_db']){
                            $needApproval = 'Y';
                        }
                    }
                }

                if(strpos($order_no,"Draft")==0 && $needApproval=='N'){
                    $branch_id = $request->branch_id;
                    $parts = Tx_sales_order_part::where([
                        'order_id' => $id,
                        'active' => 'Y'
                    ])
                    ->get();
                    foreach($parts as $part){
                        $qtyPerBranch = 0;
                        $partQty = Tx_qty_part::where([
                            'part_id' => $part->part_id,
                            'branch_id' => $branch_id,
                        ])
                        ->first();
                        if($partQty){
                            // ambil total OH per cabang
                            $qtyPerBranch = $partQty->qty;
                        }else{
                            // generate data Qty part jika belum ada, dimulai dari qty = 0
                            $qtyPartIns = Tx_qty_part::create([
                                'part_id' => $part->part_id,
                                'qty' => $qtyPerBranch,
                                'branch_id' => $branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                        // update final price, total sales
                        $updFinalPrice = Mst_part::where([
                            'id' => $part->part_id
                        ])
                        ->update([
                            // 'final_price' => $part->price,
                            'total_sales' => $part->price*$qtyPerBranch,
                            'updated_by' => Auth::user()->id
                        ]);
                    }
                }

                $upd = Tx_sales_order::where('id', '=', $id)
                ->update([
                    'need_approval' => $needApproval,
                    'total_qty' => $totalQty,
                    'total_before_vat' => $totalPriceBeforeVAT,
                    'total_after_vat' => $totalPriceAfterVAT,
                ]);

                if ($cust){
                    $msg = CheckTopOrCreditLimitHelper::checkAll($cust, $order_no, $totalPriceBeforeVAT);
                    if ($msg != ''){
                        DB::rollback();

                        return redirect()
                        ->back()
                        ->withInput()
                        ->with('status-error', $msg);
                    }
                }
            }
            if ($isThereSomePart<1){
                DB::rollback();

                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            $qPart = Tx_sales_order_part::where([
                'order_id'=>$id,
            ])
            ->whereRaw('last_avg_cost IS null OR last_avg_cost=0')
            ->get();
            foreach($qPart as $qP){
                $avg = V_log_avg_cost::where([
                    'part_id'=>$qP->part_id,
                ])
                ->whereRaw('avg_cost>0 AND updated_at<=\''.$qP->created_at.'\'')
                ->orderBy('updated_at','DESC')
                ->first();
                if ($avg){
                    $updPart = Tx_sales_order_part::where('id','=',$qP->id)
                    ->update([
                        'last_avg_cost'=>$avg->avg_cost,
                    ]);
                }else{
                    // $avg = Mst_part::where([
                    //     'id'=>$qP->part_id,
                    // ])
                    // ->first();
                    // if ($avg){
                    //     $updPart = Tx_sales_order_part::where('id','=',$qP->id)
                    //     ->update([
                    //         'last_avg_cost'=>$avg->avg_cost,
                    //     ]);
                    // }
                }
            }

        } catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();
            throw $e;

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
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_sales_order $tx_sales_order)
    {
        //
    }
}
