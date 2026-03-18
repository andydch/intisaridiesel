<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_nota_retur_non_tax;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_receipt_order;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_nota_retur_part_non_tax;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidateQtyNotaReturNonTax;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class ReturServerSideController extends Controller
{
    protected $title = 'Retur';
    protected $folder = 'retur';

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

        $userLogin = Userdetail::where('user_id','=', Auth::user()->id)
        ->first();

        if ($request->ajax()){
            $query = Tx_nota_retur_non_tax::leftJoin('userdetails AS usr','tx_nota_retur_non_taxes.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_nota_retur_non_taxes.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('tx_delivery_order_non_taxes','tx_nota_retur_non_taxes.delivery_order_id','=','tx_delivery_order_non_taxes.id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_nota_retur_non_taxes.id as tx_id',
                'tx_nota_retur_non_taxes.nota_retur_no',
                'tx_nota_retur_non_taxes.nota_retur_date',
                'tx_nota_retur_non_taxes.total_price',
                'tx_nota_retur_non_taxes.delivery_order_id',
                'tx_nota_retur_non_taxes.approved_at',
                'tx_nota_retur_non_taxes.approved_by',
                'tx_nota_retur_non_taxes.canceled_by',
                'tx_nota_retur_non_taxes.active as rt_active',
                'tx_nota_retur_non_taxes.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'tx_delivery_order_non_taxes.delivery_order_no',
                'ety_type.title_ind as ety_type_name',
            )
            ->addSelect(['total_retur' => Tx_nota_retur_part_non_tax::selectRaw('SUM(qty_retur*final_price)')
                ->whereColumn('nota_retur_id','tx_nota_retur_non_taxes.id')
                ->where('active','=','Y')
            ])
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->where(function($q){
                $q->where('tx_nota_retur_non_taxes.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_nota_retur_non_taxes.active','N')
                    ->where('tx_nota_retur_non_taxes.nota_retur_no','NOT LIKE','%Draft%');
                });
            })
            ->orderBy('tx_nota_retur_non_taxes.nota_retur_no','DESC')
            ->orderBy('tx_nota_retur_non_taxes.created_at','DESC');

            return DataTables::of($query)
            ->filterColumn('approved_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(DATE_ADD(tx_nota_retur_non_taxes.approved_at, INTERVAL '.(env("WAKTU_ID")??7).' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('approved_date', function ($query) {
                if ($query->approved_at!=null){
                    return date_format(date_add(date_create($query->approved_at), date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours")),"d/m/Y");
                }else{
                    return null;
                }
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
            ->filterColumn('delivery_order_no', function($query, $keyword) {
                $query->whereRaw('tx_delivery_order_non_taxes.delivery_order_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('delivery_order_no', function ($query) {
                if (!is_null($query->delivery_order_id)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$query->delivery_order_id).'" target="_new"
                        style="text-decoration: underline;">'.$query->delivery_order_no.'</a>';
                }else{
                    return '';
                }
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->rt_active=='Y'){
                    if (is_null($query->approved_by)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($query->nota_retur_no).'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($query->nota_retur_no)).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($query->nota_retur_no)).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-retur/'.urlencode($query->nota_retur_no)).'" target="print_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-retur/'.urlencode($query->nota_retur_no)).'" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($query->nota_retur_no)).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if (!is_null($query->approved_by) && $query->rt_active=='Y'){
                    return 'Approved';
                }
                if (!is_null($query->canceled_by) && $query->rt_active=='Y'){
                    return 'Rejected';
                }
                if (is_null($query->approved_by) && is_null($query->canceled_by) && $query->rt_active=='Y' && strpos($query->nota_retur_no,'Draft')==0){
                    return 'Waiting for Approval';
                }
                if (is_null($query->approved_by) && is_null($query->canceled_by) && $query->rt_active=='Y' && strpos($query->nota_retur_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->rt_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['approved_date','customer_name','delivery_order_no','action','status'])
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $delivery_order_no = [];
        $sj = [];
        if(old('customer_id')){
            $delivery_order_no = Tx_delivery_order_non_tax::select(
                'id',
                'delivery_order_no'
            )
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('customer_id','=',old('customer_id'))
            ->where('active','=','Y')
            ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
            ->orderBy('delivery_order_no','ASC')
            ->get();

            $sj = Tx_surat_jalan::select(
                'id',
                'surat_jalan_no'
            )
            ->where('surat_jalan_no','NOT LIKE','%Draft%')
            // ->where('customer_id','=',old('customer_id'))
            ->whereIn('id', function ($q1) {
                $q1->select('sales_order_id')
                ->from('tx_delivery_order_non_tax_parts')
                ->where([
                    'delivery_order_id'=>old('nota_penjualan_id'),
                    'active'=>'Y',
                ]);
            })
            ->where('active','=','Y')
            ->orderBy('surat_jalan_no','ASC')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'queryCustomer' => $queryCustomer,
            'qDeliveryOrder' => $delivery_order_no,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'sj' => $sj,
            'vat' => $vat,
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
            'menu_id' => 74,
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
            'nota_penjualan_id' => 'required|numeric',
            'all_selected_SJ' => 'required',
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
            'nota_penjualan_id.required' => 'Please select a valid NP',
            'nota_penjualan_id.numeric' => 'Please select a valid NP',
            'all_selected_SJ.required' => 'Select at least 1 SJ number',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id_'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => ['required','numeric',new ValidateQtyNotaReturNonTax($request['surat_jalan_part_id'.$i])],
                        'surat_jalan_part_id'.$i => 'required|numeric',
                        'part_id_'.$i => 'required|numeric',
                        'qty_do_'.$i => 'required|numeric',
                        'price_ori_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'qty_retur'.$i.'.required' => 'The qty retur field is required',
                        'qty_retur'.$i.'.numeric' => 'The qty retur field must be numeric',
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
            $identityName = 'tx_nota_retur_non_taxes-draft';
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
                $nota_retur_no = env('P_RETUR').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_nota_retur_non_taxes';
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
                $nota_retur_no = env('P_RETUR').date('y').'-'.$zero.strval($newInc);
            }

            $qCustomer = Mst_customer::where('id', '=', $request->customer_id)
            ->first();
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $ins = Tx_nota_retur_non_tax::create([
                'nota_retur_no' => $nota_retur_no,
                'nota_retur_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                'delivery_order_id' => $request->nota_penjualan_id,
                'customer_id' => $request->customer_id,
                'customer_entity_type_id' => $qCustomer->entity_type_id,
                'customer_name' => $qCustomer->name,
                'branch_id' => $request->branch_id,
                'remark' => $request->remark,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_vat' => 'Y',
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $isThereSomePart = 0;
            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if($request['part_id_'.$iRow]){
                    $isThereSomePart++;

                    $insPart = Tx_nota_retur_part_non_tax::create([
                        'nota_retur_id' => $maxId,
                        'surat_jalan_part_id' => $request['surat_jalan_part_id'.$iRow],
                        'part_id' => $request['part_id_'.$iRow],
                        'qty_retur' => $request['qty_retur'.$iRow],
                        'qty_do' => $request['qty_do_'.$iRow],
                        'final_price' => $request['price_ori_'.$iRow],
                        'total_price' => ($request['price_ori_'.$iRow]*$request['qty_retur'.$iRow]),
                        'description' => null,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);

                    $totalQty += $request['qty_retur'.$iRow];
                    $totalPrice += ($request['price_ori_'.$iRow]*$request['qty_retur'.$iRow]);
                }
            }

            if ($isThereSomePart<1){
                DB::rollback();

                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            // // get active VAT
            // $vat = ENV('VAT');
            // $qVat = Mst_global::where([
            //     'data_cat' => 'vat',
            //     'active' => 'Y'
            // ])
            // ->first();
            // if ($qVat) {
            //     $vat = $qVat->numeric_val;
            // }

            $updRO = Tx_nota_retur_non_tax::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
                'updated_by' => Auth::user()->id
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show($nota_retur_no)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

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

        // // get active VAT
        // $vat = ENV('VAT');
        // $qVat = Mst_global::where([
        //     'data_cat' => 'vat',
        //     'active' => 'Y'
        // ])
        // ->first();
        // if ($qVat) {
        //     $vat = $qVat->numeric_val;
        // }

        $query = Tx_nota_retur_non_tax::where('nota_retur_no', '=', urldecode($nota_retur_no))
        ->first();
        if ($query) {
            $delivery_order_no = [];
            $sj = [];
            if(old('customer_id')){
                $delivery_order_no = Tx_delivery_order_non_tax::select(
                    'id',
                    'delivery_order_no'
                )
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',old('customer_id'))
                ->where('active','=','Y')
                ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
                ->orderBy('delivery_order_no','ASC')
                ->get();

                $sj = Tx_surat_jalan::select(
                    'id',
                    'surat_jalan_no'
                )
                ->where('surat_jalan_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',old('customer_id'))
                ->where('active','=','Y')
                ->orderBy('surat_jalan_no','ASC')
                ->get();
            }else{
                $delivery_order_no = Tx_delivery_order_non_tax::select(
                    'id',
                    'delivery_order_no'
                )
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',$query->customer_id)
                ->where('active','=','Y')
                ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
                ->orderBy('delivery_order_no','ASC')
                ->get();

                $sj = Tx_surat_jalan::select(
                    'id',
                    'surat_jalan_no'
                )
                ->where('surat_jalan_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',$query->customer_id)
                ->where('active','=','Y')
                ->orderBy('surat_jalan_no','ASC')
                ->get();
            }

            $queryPart = Tx_nota_retur_part_non_tax::where([
                'nota_retur_id' => $query->id,
                'active' => 'Y',
            ]);

            $qSJselected = Tx_surat_jalan::leftJoin('tx_surat_jalan_parts AS tsj_part','tx_surat_jalans.id','=','tsj_part.surat_jalan_id')
            ->leftJoin('tx_nota_retur_part_non_taxes AS tr_non_tax_part','tsj_part.id','=','tr_non_tax_part.surat_jalan_part_id')
            ->select(
                'tx_surat_jalans.surat_jalan_no',
            )
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->where('tx_surat_jalans.active','=','Y')
            ->where('tr_non_tax_part.nota_retur_id','=',$query->id)
            ->groupBy('tx_surat_jalans.surat_jalan_no');
            $all_selected_SJ = '';
            foreach($qSJselected->get() as $qSJ){
                $all_selected_SJ .= ','.$qSJ->surat_jalan_no;
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'queryCustomer' => $queryCustomer,
                'qDeliveryOrder' => $delivery_order_no,
                'totRowSJ' => (old('totRowSJ') ? old('totRowSJ') : $qSJselected->count()),
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'vat' => $vat,
                'qNotaRetur' => $query,
                'qNotaReturPart' => $queryPart->get(),
                'sj' => $sj,
                'qCurrency' => $qCurrency,
                'qSJselected' => $qSJselected->get(),
                'all_selected_SJ' => $all_selected_SJ,
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function edit($nota_retur_no)
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_nota_retur_non_tax::where('nota_retur_no', '=', urldecode($nota_retur_no))
        ->first();
        if ($query) {
            $delivery_order_no = [];
            $sj = [];
            if(old('customer_id')){
                $delivery_order_no = Tx_delivery_order_non_tax::select(
                    'id',
                    'delivery_order_no'
                )
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',old('customer_id'))
                ->where('active','=','Y')
                ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
                ->orderBy('delivery_order_no','ASC')
                ->get();

                $sj = Tx_surat_jalan::select(
                    'id',
                    'surat_jalan_no'
                )
                ->where('surat_jalan_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',old('customer_id'))
                ->where('active','=','Y')
                ->orderBy('surat_jalan_no','ASC')
                ->get();
            }else{
                $delivery_order_no = Tx_delivery_order_non_tax::select(
                    'id',
                    'delivery_order_no'
                )
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',$query->customer_id)
                ->where('active','=','Y')
                ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
                ->orderBy('delivery_order_no','ASC')
                ->get();

                $sj = Tx_surat_jalan::select(
                    'id',
                    'surat_jalan_no'
                )
                ->where('surat_jalan_no','NOT LIKE','%Draft%')
                ->where('customer_id','=',$query->customer_id)
                ->where('active','=','Y')
                ->orderBy('surat_jalan_no','ASC')
                ->get();
            }

            $queryPart = Tx_nota_retur_part_non_tax::where([
                'nota_retur_id' => $query->id,
                'active' => 'Y',
            ]);

            $countSJ = 0;
            $qSJselected = Tx_surat_jalan::leftJoin('tx_surat_jalan_parts AS tsj_part','tx_surat_jalans.id','=','tsj_part.surat_jalan_id')
            ->leftJoin('tx_nota_retur_part_non_taxes AS tr_non_tax_part','tsj_part.id','=','tr_non_tax_part.surat_jalan_part_id')
            ->select(
                'tx_surat_jalans.surat_jalan_no',
            )
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->where('tx_surat_jalans.active','=','Y')
            ->where('tr_non_tax_part.nota_retur_id','=',$query->id)
            ->groupBy('tx_surat_jalans.surat_jalan_no');
            $all_selected_SJ = '';
            foreach($qSJselected->get() as $qSJ){
                $all_selected_SJ .= ','.$qSJ->surat_jalan_no;
                $countSJ++;
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'queryCustomer' => $queryCustomer,
                'qDeliveryOrder' => $delivery_order_no,
                'totRowSJ' => (old('totRowSJ') ? old('totRowSJ') : $countSJ),
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'vat' => $vat,
                'qNotaRetur' => $query,
                'qNotaReturPart' => $queryPart->get(),
                'sj' => $sj,
                'qCurrency' => $qCurrency,
                'qSJselected' => $qSJselected->get(),
                'all_selected_SJ' => $all_selected_SJ,
                'userLogin' => $userLogin,
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $nota_retur_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 74,
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

        $qPv = Tx_nota_retur_non_tax::where('nota_retur_no', '=', urldecode($nota_retur_no))
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'The document cannot be updated because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }
        
        $notaRetursOld = Tx_nota_retur_non_tax::where('nota_retur_no', '=', urldecode($nota_retur_no))
        ->first();

        $validateInput = [
            'customer_id' => 'required|numeric',
            'nota_penjualan_id' => 'required|numeric',
            'all_selected_SJ' => 'required',
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
            'nota_penjualan_id.required' => 'Please select a valid NP',
            'nota_penjualan_id.numeric' => 'Please select a valid NP',
            'all_selected_SJ.required' => 'Select at least 1 SJ number',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id_'.$i]) {
                    $validateShipmentInput = [
                        'qty_retur'.$i => ['required','numeric',new ValidateQtyNotaReturNonTax($request['surat_jalan_part_id'.$i])],
                        'surat_jalan_part_id'.$i => 'required|numeric',
                        'part_id_'.$i => 'required|numeric',
                        'qty_do_'.$i => 'required|numeric',
                        'price_ori_'.$i => 'required|numeric',
                    ];
                    $errShipmentMsg = [
                        'qty_retur'.$i.'.required' => 'The qty retur field is required',
                        'qty_retur'.$i.'.numeric' => 'The qty retur field must be numeric',
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
            $notaReturs = Tx_nota_retur_non_tax::where('nota_retur_no', '=', urldecode($nota_retur_no))
            ->where('nota_retur_no','LIKE','%Draft%')
            ->first();
            if($notaReturs){
                // looking for draft order no
                $draft = true;
                $nota_retur_no = $notaReturs->nota_retur_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_nota_retur_non_taxes';
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
                $nota_retur_no = env('P_RETUR').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_nota_retur_non_tax::where('id', '=', $notaRetursOld->id)
                ->update([
                    'nota_retur_no' => $nota_retur_no,
                    'nota_retur_date' => date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_nota_retur_non_tax::where('id', '=', $notaRetursOld->id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qCustomer = Mst_customer::where('id', '=', $request->customer_id)
            ->first();
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $upd = Tx_nota_retur_non_tax::where('id','=',$notaRetursOld->id)
            ->update([
                'delivery_order_id' => $request->nota_penjualan_id,
                'branch_id' => $request->branch_id,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // set non active untuk part yang tidak masuk retur
            $updPart = Tx_nota_retur_part_non_tax::where('nota_retur_id','=',$notaRetursOld->id)
            ->delete();

            $isThereSomePart = 0;
            $totalQty = 0;
            $totalPrice = 0;
            $totalRowPart = $request->totalRow;
            for($iRow=0;$iRow<$totalRowPart;$iRow++){
                if($request['part_id_'.$iRow]){
                    $isThereSomePart++;

                    $insPart = Tx_nota_retur_part_non_tax::create([
                        'nota_retur_id' => $notaRetursOld->id,
                        'surat_jalan_part_id' => $request['surat_jalan_part_id'.$iRow],
                        'part_id' => $request['part_id_'.$iRow],
                        'qty_retur' => $request['qty_retur'.$iRow],
                        'qty_do' => $request['qty_do_'.$iRow],
                        'final_price' => $request['price_ori_'.$iRow],
                        'total_price' => ($request['price_ori_'.$iRow]*$request['qty_retur'.$iRow]),
                        'description' => null,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);

                    $totalQty += $request['qty_retur'.$iRow];
                    $totalPrice += ($request['price_ori_'.$iRow]*$request['qty_retur'.$iRow]);
                }
            }

            if ($isThereSomePart<1){
                DB::rollback();
                
                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
            }

            // // get active VAT
            // $vat = ENV('VAT');
            // $qVat = Mst_global::where([
            //     'data_cat' => 'vat',
            //     'active' => 'Y'
            // ])
            // ->first();
            // if ($qVat) {
            //     $vat = $qVat->numeric_val;
            // }

            $updRO = Tx_nota_retur_non_tax::where('id','=',$notaRetursOld->id)
            ->update([
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
                'updated_by' => Auth::user()->id
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_receipt_order)
    {
        //
    }
}
