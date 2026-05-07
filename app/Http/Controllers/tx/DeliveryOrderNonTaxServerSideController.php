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
use App\Models\Tx_surat_jalan;
use App\Models\Tx_tax_invoice;
use App\Models\Tx_lokal_journal;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_surat_jalan_part;
use App\Rules\IsSJnotConnectedToNP;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Tx_lokal_journal_detail;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_customer_shipment_address;
use App\Models\Tx_delivery_order_non_tax_part;
use Illuminate\Validation\ValidationException;

class DeliveryOrderNonTaxServerSideController extends Controller
{
    protected $title = 'Nota Penjualan';
    protected $folder = 'delivery-order-local';
    protected $uri = 'delivery-order-local';

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
            $query = Tx_delivery_order_non_tax::leftJoin('userdetails AS usr','tx_delivery_order_non_taxes.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_delivery_order_non_taxes.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_delivery_order_non_taxes.id as tx_id',
                'tx_delivery_order_non_taxes.delivery_order_no',
                'tx_delivery_order_non_taxes.delivery_order_date',
                'tx_delivery_order_non_taxes.sales_order_no_all',
                'tx_delivery_order_non_taxes.tax_invoice_id',
                'tx_delivery_order_non_taxes.total_price',
                'tx_delivery_order_non_taxes.active as np_active',
                'tx_delivery_order_non_taxes.created_by as createdby',
                'tx_delivery_order_non_taxes.created_at as createdat',
                'tx_delivery_order_non_taxes.updated_at as updatedat',
                'tx_delivery_order_non_taxes.draft_to_created_at',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'ety_type.title_ind as ety_type_name',
            )
            ->where(function($q){
                $q->where('tx_delivery_order_non_taxes.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_delivery_order_non_taxes.active','N')
                    ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director!='Y' && $userLogin->section_id!=37, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_delivery_order_non_taxes.delivery_order_no', 'DESC')
            ->orderBy('tx_delivery_order_non_taxes.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('delivery_order_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_delivery_order_non_taxes.delivery_order_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
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
                $query->whereRaw('tx_delivery_order_non_taxes.sales_order_no_all LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('sales_order_no_all', function ($query) {
                $links = '';
                $sj_arr = explode(",", $query->sales_order_no_all);
                foreach($sj_arr as $sj){
                    $qSJ = Tx_surat_jalan::where('surat_jalan_no','=',$sj)
                    ->first();
                    if($qSJ){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/surat-jalan/'.$qSJ->id).'" target="_new" style="text-decoration: underline;">'.$sj.'</a>';
                    }
                }
                return $links;
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
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y' || $userLogin->section_id==37) &&
                    $query->np_active=='Y'){
                    $qTaxInv = Tx_tax_invoice::where('id','=',$query->tax_invoice_id)
                    ->first();
                    if($query->np_active=='Y' && strpos($query->delivery_order_no,'Draft')==0 && !$qTaxInv){
                        //
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |';
                    }
                }
                $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                    <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-do?fk='.$query->tx_id.'&p=1').'" target="_new" style="text-decoration: underline;">Print</a> |
                    <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-do?fk='.$query->tx_id.'&p=2').'" target="_new" style="text-decoration: underline;">Download</a>';                
                return $links;
            })
            ->addColumn('status', function ($query) {
                $qTaxInv = Tx_tax_invoice::where('id','=',$query->tax_invoice_id)
                ->first();
                if(($query->np_active=='Y' && strpos($query->delivery_order_no,'Draft')==0 && is_null($qTaxInv))){
                    return 'Created';
                }
                if($query->np_active=='Y' && strpos($query->delivery_order_no,'Draft')==0 && !is_null($qTaxInv)){
                    return 'FP';
                }
                if($query->np_active=='Y' && strpos($query->delivery_order_no,'Draft')>0){
                    return 'Draft';
                }
                if($query->np_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['delivery_order_date','created_at','updated_at','sales_order_no_all','customer_name','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
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

        $surat_jalan = [];
        $surat_jalan_date = [];
        $ship_to = [];
        if(old('customer_id')){
            $surat_jalan = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
            ->select('tx_surat_jalans.surat_jalan_no AS order_no')
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_surat_jalans.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_non_tax_parts as tx_do_part')
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
                'tx_surat_jalans.customer_id' => old('customer_id'),
                'tx_surat_jalans.active' => 'Y'
            ])
            ->when(old('surat_jalan_date')!='#', function($query) {
                $query->where('tx_surat_jalans.surat_jalan_date','=',old('surat_jalan_date'));
            })
            ->when(old('is_vat')=='on', function($query) {
                $query->where('tx_surat_jalans.is_vat','=','Y');
            })
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                $query->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->get();

            $surat_jalan_date = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
            ->selectRaw('DISTINCT tx_surat_jalans.surat_jalan_date')
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_surat_jalans.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_non_tax_parts as tx_do_part')
                    ->where('tx_do_part.active','=','Y');
            })
            ->where(function($query) {
                $query->where('tx_surat_jalans.approved_by','<>',null)
                    ->orWhere(function($queryA) {
                    $queryA->where('tx_surat_jalans.approved_by','=',null)
                        ->where('tx_surat_jalans.need_approval','=','N');
                });
            })
            ->where([
                'tx_surat_jalans.customer_id' => old('customer_id'),
                'tx_surat_jalans.active' => 'Y'
            ])
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                $query->whereRaw('((usr.branch_id='.$userLogin->branch_id.' AND tx_surat_jalans.branch_id IS null) OR tx_surat_jalans.branch_id='.$userLogin->branch_id.')');
            })
            ->orderBy('tx_surat_jalans.surat_jalan_date','DESC')
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
            'get_surat_jalan_no' => $surat_jalan,
            'get_surat_jalan_date' => $surat_jalan_date,
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
            'menu_id' => 69,
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
            'surat_jalan_no_all' => ['required', new IsSJnotConnectedToNP(0)],
            'surat_jalan_date' => 'date',
        ];
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid customer',
            'surat_jalan_no_all.required' => 'Please select a valid surat jalan no',
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
            $identityName = 'tx_delivery_order_non_taxes-draft';
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
                $order_no = env('P_NOTA_PENJUALAN').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_delivery_order_non_taxes';
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
                $order_no = env('P_NOTA_PENJUALAN').date('y').'-'.$zero.strval($newInc);
            }

            // data user yg login saat ini
            $user = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qCust = Mst_customer::where('id','=',$request->customer_id)
            ->first();

            $surat_jalan_date = date_create($request->surat_jalan_date);
            date_add($surat_jalan_date, date_interval_create_from_date_string($qCust->top." days"));

            $ins = Tx_delivery_order_non_tax::create([
                'delivery_order_no' => $order_no,
                'delivery_order_date' => $request->surat_jalan_date,
                'do_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
                'sales_order_no_all' => $request->surat_jalan_no_all,
                'customer_id' => $request->customer_id,
                'customer_entity_type_id' => ($qCust?$qCust->entity_type_id:999),
                'customer_name' => ($qCust?$qCust->name:'-'),
                'remark' => $request->remark,
                'branch_id' => ($user?$user->branch_id:999),
                'total_qty' => 0,
                'total_price' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $totalQty = 0;
            $totalPrice = 0;
            $branch_id = '';
            $totalLastAvgCost = 0;
            $isThereSomePart = 0;
            for($iPart=0;$iPart<$request->totalRow;$iPart++){
                if($request['surat_jalan_part_id'.$iPart]){
                    $isThereSomePart++; // memastikan ada part yg dibawa

                    $partSO = Tx_surat_jalan_part::leftJoin('tx_surat_jalans as tx_sj','tx_surat_jalan_parts.surat_jalan_id','=','tx_sj.id')
                    ->select(
                        'tx_surat_jalan_parts.*',
                        'tx_sj.branch_id',
                    )
                    ->where('tx_surat_jalan_parts.id','=',$request['surat_jalan_part_id'.$iPart])
                    ->first();
                    if($partSO){
                        $branch_id = $partSO->branch_id;
                        $totalLastAvgCost += ($partSO->last_avg_cost*$partSO->qty);

                        $insPart = Tx_delivery_order_non_tax_part::create([
                            'delivery_order_id' => $maxId,
                            'sales_order_id' => $request['surat_jalan_id'.$iPart],
                            'sales_order_part_id' => $request['surat_jalan_part_id'.$iPart],
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

            $upd = Tx_delivery_order_non_tax::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
            ]);

            // simpan deskripsi utk jurnal - start
            $deskripsi = '';
            $getDesc = Tx_delivery_order_non_tax::leftJoin('mst_customers AS msc', 'tx_delivery_order_non_taxes.customer_id', '=', 'msc.id')
            ->leftJoin('mst_globals AS msg', 'msc.entity_type_id', '=', 'msg.id')
            ->select(
                'tx_delivery_order_non_taxes.delivery_order_no',
                'tx_delivery_order_non_taxes.remark',
                'msc.name AS cust_name',
                'msc.customer_unique_code AS cust_unique_code',
                'msg.title_ind AS entity_type',
            )
            ->where('tx_delivery_order_non_taxes.id', '=', $maxId)
            ->first();
            if ($getDesc){
                $deskripsi = $getDesc->delivery_order_no.', '.
                    $getDesc->cust_unique_code.' - '.($getDesc->entity_type!=null?$getDesc->entity_type.' ':'').$getDesc->cust_name.', '.
                    $getDesc->remark;
                $deskripsi = substr($deskripsi, 0, 4096);
            }
            // simpan deskripsi utk jurnal - end

            // cek apakah fitur automatic journal untuk nota penjualan sudah tersedia
            $qAutJournal = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>3,
                'branch_id'=>$branch_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournal && $request->is_draft!='Y'){
                // cogs
                $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>3,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'cogs\'')
                ->first();
                // inventory
                $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>3,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'inventory\'')
                ->first();
                // piutang
                $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>3,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'piutang\'')
                ->first();
                // sales non pajak
                $qAutJournal_sales_non_pajak = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>3,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'sales non pajak\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_lokal_journal::where([
                    'module_no'=>$order_no,
                    'automatic_journal_id'=>3,
                    'active'=>'Y',
                ])
                ->first();
                if ($qJournals){
                    // non aktifkan jurnal detail jika ada
                    $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                    ->update([
                        'active'=>'N',
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $journal_date_new = explode("-", $request->surat_jalan_date);                    
                    $yearTemp = substr($journal_date_new[0], 2, 2);
                    $monthTemp = $journal_date_new[1];
                    $ymTemp = $yearTemp.$monthTemp;
                    $zero = '';
                    $YearMonth = '';
                    $newInc = 1;

                    $identityName = 'Tx_lokal_journal';
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
                            $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                            ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
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
                        $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                        ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
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
                    $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_lokal_journal::create([
                        'general_journal_no'=>$journal_no,
                        'general_journal_date'=>$request->surat_jalan_date,
                        'total_debit'=>($totalLastAvgCost+$totalPrice),
                        'total_kredit'=>($totalLastAvgCost+$totalPrice),
                        'module_no'=>$order_no,
                        'automatic_journal_id'=>3,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                // cogs
                $ins_cogs = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_cogs->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>$deskripsi,
                    'debit'=>$totalLastAvgCost,
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // inventory
                $ins_inventory = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_inventory->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>$deskripsi,
                    'debit'=>0,
                    'kredit'=>$totalLastAvgCost,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // piutang
                $ins_piutang = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_piutang->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>$deskripsi,
                    'debit'=>$totalPrice,
                    'kredit'=>0,
                    'active'=>'Y',
                    'created_by'=>Auth::user()->id,
                    'updated_by'=>Auth::user()->id,
                ]);

                // sales non pajak
                $ins_sales_non_pajak = Tx_lokal_journal_detail::create([
                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                    'coa_id'=>$qAutJournal_sales_non_pajak->coa_code_id,
                    'coa_detail_id'=>null,
                    'description'=>$deskripsi,
                    'debit'=>0,
                    'kredit'=>$totalPrice,
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

        $query = Tx_delivery_order_non_tax::where('id','=',$id)->first();
        if($query){
            $parts = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $id,
            ]);

            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'uri' => $this->uri,
                'qCust' => $queryCustomer,
                'parts' => $parts->get(),
                'weighttype' => $weighttype,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $parts->count()),
                'ship_to' => $ship_to,
                'queryDelivery' => $query,
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
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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

        $ship_to = [];
        $query = [];
        $parts = [];
        $partCount = 0;

        $query = Tx_delivery_order_non_tax::where('id','=',$id)
        ->first();
        if($query){
            $parts = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ]);

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
            'parts' => $parts->get(),
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $parts->count()),
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
            'menu_id' => 69,
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
            'surat_jalan_no_all' => ['required', new IsSJnotConnectedToNP($id)],
        ];
        $errMsg = [
            'surat_jalan_no_all.required' => 'Please select a valid surat jalan no',
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

            $delivery_order_no = '';
            $orders_old = Tx_delivery_order_non_tax::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_delivery_order_non_tax::where('id', '=', $id)
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

                $identityName = 'tx_delivery_order_non_taxes';
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
                $delivery_order_no = env('P_NOTA_PENJUALAN').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_delivery_order_non_tax::where('id', '=', $id)
                ->update([
                    'delivery_order_no' => $delivery_order_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_delivery_order_non_tax::where('id', '=', $id)
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

            $surat_jalan_date = date_create($orders_old->surat_jalan_date);
            date_add($surat_jalan_date, date_interval_create_from_date_string($cust->top." days"));

            $upd = Tx_delivery_order_non_tax::where('id','=',$id)
            ->update([
                'do_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
                'remark' => $request->remark,
                'branch_id' => $userLogin->branch_id,
                'updated_by' => Auth::user()->id,
            ]);
            
            if($orders_old->delivery_order_no!=$delivery_order_no && $delivery_order_no!=''){
                // jika dari draft menjadi created
                $doPart = Tx_delivery_order_non_tax_part::where(
                [
                    'delivery_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();

                // data user yg create DO
                $user = Userdetail::where('user_id','=',$orders_old->created_by)
                ->first();

                $branch_id = '';
                $totalLastAvgCost = 0;
                foreach($doPart as $do_part){
                    $partSO = Tx_surat_jalan_part::leftJoin('tx_surat_jalans as tx_sj','tx_surat_jalan_parts.surat_jalan_id','=','tx_sj.id')
                    ->select(
                        'tx_surat_jalan_parts.*',
                        'tx_sj.branch_id',
                    )
                    ->where('tx_surat_jalan_parts.id','=',$do_part->sales_order_part_id)
                    ->first();
                    if($partSO){
                        $branch_id = $partSO->branch_id;
                        $totalLastAvgCost += ($partSO->last_avg_cost*$partSO->qty);

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
                    }
                }

                $qNP = Tx_delivery_order_non_tax::where('id', '=', $id)
                ->first();
                if ($qNP){

                    // simpan deskripsi utk jurnal - start
                    $deskripsi = '';
                    $getDesc = Tx_delivery_order_non_tax::leftJoin('mst_customers AS msc', 'tx_delivery_order_non_taxes.customer_id', '=', 'msc.id')
                    ->leftJoin('mst_globals AS msg', 'msc.entity_type_id', '=', 'msg.id')
                    ->select(
                        'tx_delivery_order_non_taxes.delivery_order_no',
                        'tx_delivery_order_non_taxes.remark',
                        'msc.name AS cust_name',
                        'msc.customer_unique_code AS cust_unique_code',
                        'msg.title_ind AS entity_type',
                    )
                    ->where('tx_delivery_order_non_taxes.id', '=', $id)
                    ->first();
                    if ($getDesc){
                        $deskripsi = $getDesc->delivery_order_no.', '.
                            $getDesc->cust_unique_code.' - '.($getDesc->entity_type!=null?$getDesc->entity_type.' ':'').$getDesc->cust_name.', '.
                            $getDesc->remark;
                        $deskripsi = substr($deskripsi, 0, 4096);
                    }
                    // simpan deskripsi utk jurnal - end

                    // cek apakah fitur automatic journal untuk nota penjualan sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>3,
                        'branch_id'=>$branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournal && $request->is_draft!='Y'){
                        // cogs
                        $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>3,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'cogs\'')
                        ->first();
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>3,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // piutang
                        $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>3,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'piutang\'')
                        ->first();
                        // sales non pajak
                        $qAutJournal_sales_non_pajak = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>3,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'sales non pajak\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_lokal_journal::where([
                            'module_no'=>$delivery_order_no,
                            'automatic_journal_id'=>3,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $journal_date_new = explode("-", $orders_old->delivery_order_date);                    
                            $yearTemp = substr($journal_date_new[0], 2, 2);
                            $monthTemp = $journal_date_new[1];
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;

                            $identityName = 'Tx_lokal_journal';
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
                                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
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
                                $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
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
                            $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            $insJournal = Tx_lokal_journal::create([
                                'general_journal_no'=>$journal_no,
                                'general_journal_date'=>$orders_old->delivery_order_date,
                                'total_debit'=>($totalLastAvgCost+$qNP->total_price),
                                'total_kredit'=>($totalLastAvgCost+$qNP->total_price),
                                'module_no'=>$delivery_order_no,
                                'automatic_journal_id'=>3,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }

                        // cogs
                        $ins_cogs = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_cogs->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>$totalLastAvgCost,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // inventory
                        $ins_inventory = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_inventory->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>$totalLastAvgCost,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // piutang
                        $ins_piutang = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_piutang->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>$qNP->total_price,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // sales non pajak
                        $ins_sales_non_pajak = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_sales_non_pajak->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>$qNP->total_price,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
