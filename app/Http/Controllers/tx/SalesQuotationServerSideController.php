<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_sales_quotation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_sales_quotation_part;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class SalesQuotationServerSideController extends Controller
{
    protected $title = 'Sales Quotation';
    protected $folder = 'sales-quotation';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        if ($request->ajax()){
            $query = Tx_sales_quotation::leftJoin('userdetails AS usr','tx_sales_quotations.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_sales_quotations.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('tx_sales_orders','tx_sales_quotations.id','=','tx_sales_orders.sales_quotation_id')
            ->leftJoin('mst_globals as ent','mst_customers.entity_type_id','=','ent.id')
            ->select(
                'tx_sales_quotations.id AS tx_id',
                'tx_sales_quotations.sales_quotation_no',
                'tx_sales_quotations.sales_quotation_date',
                'tx_sales_quotations.active as sq_active',
                'tx_sales_quotations.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'tx_sales_orders.sales_order_no',
                DB::raw('CONCAT(mst_customers.customer_unique_code, " - ", mst_customers.name) AS cust_name_complete'),
                'ent.title_ind as customer_entity_type_name',
            )
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_sales_quotations.sales_quotation_date', 'DESC')
            ->orderBy('tx_sales_quotations.created_at', 'DESC');

            return DataTables::of($query)
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
            ->filterColumn('sales_quotation_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_sales_quotations.sales_quotation_date, "%e/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('sales_quotation_date', function ($query) {
                return date_format(date_create($query->sales_quotation_date),"d/m/Y");
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director_now=='Y' || $userLogin->is_branch_head_now=='Y') &&
                    $query->sq_active=='Y' && is_null($query->sales_order_no)){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-quotation/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                        <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-quotation/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                        <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                        <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$query->tx_id).'" style="text-decoration: underline;">Download</a>';
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-quotation/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                        <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                        <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$query->tx_id).'" style="text-decoration: underline;">Download</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if ($query->sq_active=='N'){
                    return 'Canceled';
                }
                if(($query->sq_active=='Y' && strpos($query->sales_quotation_no,"Draft")>0)){
                    return 'Draft';
                }
                if(($query->sq_active=='Y' && !strpos($query->sales_quotation_no,"Draft"))){
                    return 'Created';
                }
            })
            ->rawColumns(['cust_name_complete','sales_quotation_date','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
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
        ini_set('memory_limit', '64M');
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

        $customers = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
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

        $customerPic = [];
        if (old('customer_id')) {
            $customerPic = Mst_customer::where([
                'id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
        }
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'customers' => $customers,
            // 'parts' => $parts,
            'customerPics' => $customerPic,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'userLogin' => $userLogin,
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
            'menu_id' => 36,
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
            'customer_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'salesHeader' => 'required|max:1000',
            'salesFooter' => 'required|max:1000',
            'salesRemark' => 'max:1000|nullable',
        ];
        if(isset($request->is_director)){
            if($request->is_director=='Y'){
                $validateInputBranch = [
                    'branch_id' => 'required|numeric',
                ];
                $validateInput = array_merge($validateInput, $validateInputBranch);
            }
        }
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'customer_id.required' => 'Please select a valid customer',
            'customer_pic.required' => 'Please select a valid customer pic',
            'customer_pic.numeric' => 'Please select a valid customer pic',
            'salesHeader.required' => 'The header field is required',
            'salesHeader.max' => 'Max 1000 characters',
            'salesFooter.required' => 'The footer field is required',
            'salesFooter.max' => 'Max 1000 characters',
            'salesRemark.max' => 'Max 1000 characters',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                        'price'.$i => ['required', new NumericCustom('Price')],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'price'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
                        'price'.$i.'.required' => 'The qty field is required',
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
            $identityName = 'tx_sales_quotations-draft';
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
                $sales_quotation_no = ENV('P_SALES_QUOTATION').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_sales_quotations';
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
                $sales_quotation_no = ENV('P_SALES_QUOTATION').date('y').'-'.$zero.strval($newInc);
            }

            $qCustomer = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $vat_val = 0;
            $vat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vat){
                $vat_val = $vat->numeric_val;
            }

            $ins = Tx_sales_quotation::create([
                'sales_quotation_no' => $sales_quotation_no,
                'sales_quotation_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                'customer_id' => $request->customer_id,
                'customer_type_id' => null,
                'customer_entity_type_id' => $qCustomer->entity_type_id,
                'customer_name' => $qCustomer->name,
                'customer_office_address' => $qCustomer->office_address,
                'customer_country_id' => ($qCustomer->province_id==9999?$qCustomer->city->country_id:$qCustomer->province->country_id),
                'customer_province_id' => $qCustomer->province_id,
                'customer_city_id' => $qCustomer->city_id,
                'customer_district_id' => $qCustomer->district_id,
                'customer_sub_district_id' => $qCustomer->sub_district_id,
                'customer_post_code' => $qCustomer->post_code,
                'branch_id' => (is_numeric($request->branch_id)?$request->branch_id:null),
                'header' => $request->salesHeader,
                'footer' => $request->salesFooter,
                'remark' => $request->salesRemark,
                'pic_idx' => $request->customer_pic,
                'vat_val' => $vat_val,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;

            $isThereSomePart = 0;
            if ($request->totalRow > 0) {
                $qty = 0;

                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $isThereSomePart++;

                        $insPart = Tx_sales_quotation_part::create([
                            'sales_quotation_id' => $maxId,
                            'part_id' => $request['part_id'.$i],
                            'qty' => $request['qty'.$i],
                            'price_part' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                            'description' => $request['desc_part'.$i],
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);

                        $qty += $request['qty'.$i];
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

            // update total qty
            $upd = Tx_sales_quotation::where('id', '=', $maxId)
            ->update([
                'total_qty' => $qty,
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
    public function show($id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $customers = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();
        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_number', 'ASC')
        ->get();

        $query = Tx_sales_quotation::where('id', '=', $id)->first();
        if($query){
            $customerPic = [];
            if (old('customer_id')) {
                $customerPic = Mst_customer::where([
                    'id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }else{
                $customerPic = Mst_customer::where([
                    'id' => $query->customer_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }

            $querySalesQuoPart = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $querySalesQuoPartCount = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'salesQuo' => $query,
                'customers' => $customers,
                'parts' => $parts,
                'customerPics' => $customerPic,
                'querySalesQuoPart' => $querySalesQuoPart,
                'totalRow' => (old('totalRow') ? old('totalRow') : $querySalesQuoPartCount),
                'qCurrency' => $qCurrency,
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
    public function edit($id)
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

        $customers = Mst_customer::when($is_director!='Y', function($c1) use($branch_id) {
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

        $query = Tx_sales_quotation::where('id', '=', $id)
        ->first();
        if($query){
            $customerPic = [];
            if (old('customer_id')) {
                $customerPic = Mst_customer::where([
                    'id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }else{
                $customerPic = Mst_customer::where([
                    'id' => $query->customer_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
            }

            $querySalesQuoPart = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $querySalesQuoPartCount = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'salesQuo' => $query,
                'customers' => $customers,
                'customerPics' => $customerPic,
                'querySalesQuoPart' => $querySalesQuoPart,
                'totalRow' => (old('totalRow') ? old('totalRow') : $querySalesQuoPartCount),
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'branches' => $branches,
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
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 36,
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
            'customer_pic' => 'required|numeric',
            'is_draft' => 'in:Y,N',
            'salesHeader' => 'required|max:1000',
            'salesFooter' => 'required|max:1000',
            'salesRemark' => 'max:1000|nullable',
        ];
        if(isset($request->is_director)){
            if($request->is_director=='Y'){
                $validateInputBranch = [
                    'branch_id' => 'required|numeric',
                ];
                $validateInput = array_merge($validateInput, $validateInputBranch);
            }
        }
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'customer_id.required' => 'Please select a valid customer',
            'customer_pic.required' => 'Please select a valid customer pic',
            'customer_pic.numeric' => 'Please select a valid customer pic',
            'salesHeader.required' => 'The header field is required',
            'salesHeader.max' => 'Max 1000 characters',
            'salesFooter.required' => 'The footer field is required',
            'salesFooter.max' => 'Max 1000 characters',
            'salesRemark.max' => 'Max 1000 characters',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];

        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => 'required|numeric',
                        'price'.$i => ['required', new NumericCustom('Price')],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'price'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
                        'price'.$i.'.required' => 'The qty field is required',
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
            $sales_quotations = Tx_sales_quotation::where('id', '=', $id)
            ->where('sales_quotation_no','LIKE','%Draft%')
            ->first();
            if($sales_quotations){
                // looking for draft sales_quotation no
                $draft = true;
                $sales_quotation_no = $sales_quotations->sales_quotation_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_sales_quotations';
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
                $sales_quotation_no = ENV('P_SALES_QUOTATION').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_sales_quotation::where('id', '=', $id)
                ->update([
                    'sales_quotation_no' => $sales_quotation_no,
                    'sales_quotation_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_sales_quotation::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qCustomer = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $upd = Tx_sales_quotation::where('id','=',$id)
            ->update([
                'customer_id' => $request->customer_id,
                'customer_type_id' => null,
                'customer_entity_type_id' => $qCustomer->entity_type_id,
                'customer_name' => $qCustomer->name,
                'customer_office_address' => $qCustomer->office_address,
                'customer_country_id' => ($qCustomer->province_id==9999?$qCustomer->city->country_id:$qCustomer->province->country_id),
                'customer_province_id' => $qCustomer->province_id,
                'customer_city_id' => $qCustomer->city_id,
                'customer_district_id' => $qCustomer->district_id,
                'customer_sub_district_id' => $qCustomer->sub_district_id,
                'customer_post_code' => $qCustomer->post_code,
                'branch_id' => (is_numeric($request->branch_id)?$request->branch_id:null),
                'header' => $request->salesHeader,
                'footer' => $request->salesFooter,
                'remark' => $request->salesRemark,
                'pic_idx' => $request->customer_pic,
                'updated_by' => Auth::user()->id
            ]);

            // set not active
            $updPart = Tx_sales_quotation_part::where([
                'sales_quotation_id' => $id
            ])->update([
                'active' => 'N'
            ]);

            $isThereSomePart = 0;
            if ($request->totalRow > 0) {
                $qty = 0;

                for ($i = 0; $i < $request->totalRow; $i++) {
                    if ($request['part_id'.$i]) {
                        $isThereSomePart++;

                        if ($request['salesQuo_part_id_'.$i]==0) {
                            $insPart = Tx_sales_quotation_part::create([
                                'sales_quotation_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price_part' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }else{
                            $updPart = Tx_sales_quotation_part::where('id','=',$request['salesQuo_part_id_'.$i])
                            ->update([
                                'sales_quotation_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'qty' => $request['qty'.$i],
                                'price_part' => GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'description' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                        $qty += $request['qty'.$i];
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

            // update total qty
            $upd = Tx_sales_quotation::where('id', '=', $id)
            ->update([
                'total_qty' => $qty,
            ]);

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
