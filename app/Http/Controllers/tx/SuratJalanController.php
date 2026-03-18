<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Rules\SQnumSuratJalanUnique;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use App\Helpers\GlobalFuncHelper;
use App\Rules\ApprovalCheckingSJ;
use App\Models\Tx_sales_quotation;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_surat_jalan_part;
use App\Rules\MaxPartQtySalesOrder;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;

class SuratJalanController extends Controller
{
    protected $title = 'Surat Jalan';
    protected $folder = 'surat-jalan';

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
        $query = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
        ->select(
            'tx_surat_jalans.*'
        )
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_surat_jalans.surat_jalan_no', 'DESC')
        ->orderBy('tx_surat_jalans.created_at', 'DESC');

        $data = [
            'sjs' => $query->get(),
            'sjsCount' => $query->count(),
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
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name', 'ASC')
        ->get();
        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_number', 'ASC')
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
                    ->from('tx_surat_jalans')
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
            'parts' => $parts,
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
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $validateInput = [
            'customer_id' => 'required|numeric',
            'cust_doc_no' => 'max:255',
            'cust_shipment_address' => 'required|numeric',
            'cust_pic' => 'required|numeric',
            'sales_quotation_no' => ['nullable', new SQnumSuratJalanUnique(0)],
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
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                    ->select(
                        'mst_parts.*',
                        'tx_qty.qty AS qty_oh'
                    )
                    ->where([
                        'mst_parts.id' => $request['part_id'.$i],
                        'tx_qty.branch_id' => $userLogin->branch_id
                    ])
                    ->first();

                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty'.$i => ['required','numeric','min:1',new MaxPartQtySalesOrder($request['part_id'.$i],$userLogin->branch_id,0)],
                        'price'.$i => [new NumericCustom('Price'), 'nullable'],
                        // 'price'.$i => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                        'avg_cost_'.$i.'_db' => 'required|numeric'
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty must be at least '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'qty'.$i.'.max' => 'The qty must not be greater than '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'price'.$i.'.numeric' => 'The price field is must be numeric',
                        // 'price'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
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
            $identityName = 'tx_surat_jalans-draft';
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
                $order_no = env('P_SURAT_JALAN').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_surat_jalans';
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
                $order_no = env('P_SURAT_JALAN').date('y').'-'.$zero.strval($newInc);
            }

            $cust = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $surat_jalan_date = date_create(date("Y-m-d"));
            date_add($surat_jalan_date, date_interval_create_from_date_string($cust->top." days"));

            $ins = Tx_surat_jalan::create([
                'surat_jalan_no' => $order_no,
                'sales_quotation_id' => ($request->sales_quotation_no=='#'?null:$request->sales_quotation_no),
                'customer_doc_no' => $request->cust_doc_no,
                'surat_jalan_date' => date("Y-m-d"),
                'surat_jalan_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
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
                'courier_id' => ($request->courier_id=='#'?null:$request->courier_id),
                'total_qty' => 0,
                'total' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id
            ]);

            // get last ID
            $maxId = $ins->id;

            $needApproval = 'N';
            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                for ($i = 0; $i < $request->totalRow; $i++) {
                    if(isset($request['part_id'.$i])){
                        $part = Mst_part::where('id', '=', $request['part_id'.$i])
                        ->first();
                        $insPart = Tx_surat_jalan_part::create([
                            'surat_jalan_id' => $maxId,
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

                        if(GlobalFuncHelper::moneyValidate($request['price'.$i])<$request['avg_cost_'.$i.'_db']){
                            $needApproval = 'Y';
                        }
                    }
                }

                if(strpos($order_no,"Draft")==0 && $needApproval=='N'){
                    $branch_id = (!is_null($request->branch_id)?$request->branch_id:$userLogin->branch_id);
                    $parts = Tx_surat_jalan_part::where([
                        'surat_jalan_id' => $maxId,
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

                $upd = Tx_surat_jalan::where('id', '=', $maxId)
                ->update([
                    'total_qty' => $totalQty,
                    'total' => $totalPriceBeforeVAT,
                    // 'total_after_vat' => $totalPriceAfterVAT,
                    'need_approval' => $needApproval
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
     * @param  \App\Models\Tx_surat_jalan  $tx_surat_jalan
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

        $query = Tx_surat_jalan::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $qCustomer = Mst_customer::where([
                'active' => 'Y'
            ])
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
            $queryPart = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
                'active' => 'Y'
            ])
            ->orderBy('created_at', 'ASC')
            ->get();
            $queryPartCount = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
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
                'userLogin' => $userLogin
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
     * @param  \App\Models\Tx_surat_jalan  $tx_surat_jalan
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);
        
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_surat_jalan::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qCustomer = Mst_customer::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
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
                $queryC = Tx_surat_jalan::where('customer_id','=',old('customer_id'))->first();
                if(!is_null($queryC)){
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($queryC) {
                        $query->select('sales_quotation_id')
                            ->from('tx_surat_jalans')
                            ->where('sales_quotation_id','<>',$queryC->sales_quotation_id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => old('customer_id'),
                        'active' => 'Y'
                    ])
                    ->get();
                }else{
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) {
                        $query->select('sales_quotation_id')
                            ->from('tx_surat_jalans')
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
                $queryC = Tx_surat_jalan::where('customer_id','=',$query->customer_id)->first();
                if(!is_null($queryC)){
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) use ($queryC) {
                        $query->select('sales_quotation_id')
                            ->from('tx_surat_jalans')
                            ->where('sales_quotation_id','<>',$queryC->sales_quotation_id)
                            ->where('sales_quotation_id','<>',null);
                    })
                    ->where('sales_quotation_no','NOT LIKE','%Draft%')
                    ->where([
                        'customer_id' => $query->customer_id,
                        'active' => 'Y'
                    ])
                    ->get();
                }else{
                    $qSQno = Tx_sales_quotation::whereNotIn('id', function (Builder $query) {
                        $query->select('sales_quotation_id')
                            ->from('tx_surat_jalans')
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
            $queryPart = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
                'active' => 'Y'
            ])
            ->orderBy('created_at', 'ASC')
            ->get();
            $queryPartCount = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
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
     * @param  \App\Models\Tx_surat_jalan  $tx_surat_jalan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qSO = Tx_surat_jalan::where('id','=',$id)
        ->first();
        $userLogin = Userdetail::where('user_id','=',$qSO->created_by)
        ->first();

        $validateInput = [
            'cust_doc_no' => 'max:255',
            'customer_id' => 'required|numeric',
            'cust_shipment_address' => 'required|numeric',
            'cust_pic' => 'required|numeric',
            'sales_quotation_no' => ['nullable', new SQnumSuratJalanUnique($id)],
            'surat_jalan_no' => [new ApprovalCheckingSJ],
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
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $partNo = Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                    ->select(
                        'mst_parts.*',
                        'tx_qty.qty AS qty_oh'
                    )
                    ->where([
                        'mst_parts.id' => $request['part_id'.$i],
                        'tx_qty.branch_id' => $userLogin->branch_id
                    ])
                    ->first();

                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        // 'qty'.$i => 'required|numeric|min:1|max:'.(!is_null($partNo)?$partNo->qty_oh:0).'|lt:initial_amount'.$i,
                        'qty'.$i => ['required','numeric','min:1','lte:initial_amount'.$i,new MaxPartQtySalesOrder($request['part_id'.$i],$userLogin->branch_id,0)],
                        'price'.$i => [new NumericCustom('Price'), 'nullable'],
                        // 'price'.$i => 'nullable|regex:/^[0-9]{1,3}(.[0-9]{0})*\,[0-9]+$/',
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty must be at least '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'qty'.$i.'.max' => 'The qty must not be greater than '.(!is_null($partNo)?$partNo->qty_oh:0).'.',
                        'qty'.$i.'.lte' => 'The qty must be less or equal than '.$request['initial_amount'.$i].'.',
                        'price'.$i.'.numeric' => 'The price field is must be numeric',
                        // 'price'.$i.'.regex' => 'Must have exacly 2 decimal places (9,99)',
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
            $orders = Tx_surat_jalan::where('id', '=', $id)
            ->where('surat_jalan_no','LIKE','%Draft%')
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

                $identityName = 'tx_surat_jalans';
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
                $order_no = env('P_SURAT_JALAN').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_surat_jalan::where('id', '=', $id)
                ->update([
                    'surat_jalan_no' => $order_no,
                    'surat_jalan_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_surat_jalan::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $order = Tx_surat_jalan::where('id','=',$id)
            ->first();

            $cust = Mst_customer::where('id', '=', $request->customer_id)
            ->first();

            $surat_jalan_date = date_create($order->surat_jalan_date);
            date_add($surat_jalan_date, date_interval_create_from_date_string($cust->top." days"));

            $upd = Tx_surat_jalan::where('id','=',$id)
            ->update([
                'surat_jalan_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
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
                'courier_id' => ($request->courier_id=='#'?null:$request->courier_id),
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
            if ($request->totalRow > 0) {
                $totalQty = 0;
                $totalPriceBeforeVAT = 0;
                // $totalPriceAfterVAT = 0;

                $updPart = Tx_surat_jalan_part::where('surat_jalan_id','=',$id)
                ->update([
                    'active' => 'N',
                    'updated_by' => Auth::user()->id
                ]);

                for ($i = 0; $i < $request->totalRow; $i++) {
                    $qSalesPart = Tx_surat_jalan_part::where('id','=',$request['order_part_id'.$i])->first();
                    $part = Mst_part::where('id', '=', $request['part_id'.$i])->first();
                    if($request['part_id'.$i]){
                        if($qSalesPart){
                            $updPart = Tx_surat_jalan_part::where('id','=',$request['order_part_id'.$i])
                            ->update([
                                'surat_jalan_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'part_no' => $part->part_number,
                                'qty' => $request['qty'.$i],
                                'price' => $request['price'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'desc' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id
                            ]);
                        }else{
                            $insPart = Tx_surat_jalan_part::create([
                                'surat_jalan_id' => $id,
                                'part_id' => $request['part_id'.$i],
                                'part_no' => $part->part_number,
                                'qty' => $request['qty'.$i],
                                'price' => $request['price'.$i] == '' ? null : GlobalFuncHelper::moneyValidate($request['price'.$i]),
                                'desc' => $request['desc_part'.$i],
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }

                        $totalQty += $request['qty'.$i];
                        $totalPriceBeforeVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i]));
                        // $totalPriceAfterVAT += ($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) +
                        //     ((($request['qty'.$i] * GlobalFuncHelper::moneyValidate($request['price'.$i])) * $vat) / 100);

                        if(GlobalFuncHelper::moneyValidate($request['price'.$i])<$request['avg_cost_'.$i.'_db']){
                            $needApproval = 'Y';
                        }
                    }
                }

                if(strpos($order_no,"Draft")==0 && $needApproval=='N'){
                    $branch_id = (!is_null($request->branch_id)?$request->branch_id:$userLogin->branch_id);
                    $parts = Tx_surat_jalan_part::where([
                        'surat_jalan_id' => $id,
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

                $upd = Tx_surat_jalan::where('id', '=', $id)
                ->update([
                    'need_approval' => $needApproval,
                    'total_qty' => $totalQty,
                    'total' => $totalPriceBeforeVAT,
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

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_surat_jalan  $tx_surat_jalan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_surat_jalan $tx_surat_jalan)
    {
        //
    }
}
