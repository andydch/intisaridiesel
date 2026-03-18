<?php

namespace App\Http\Controllers\tx;

use App\Models\User;
use App\Models\V_so_sj;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use App\Models\Tx_kwitansi;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_sales_order;
use App\Models\Tx_delivery_order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\Validator;

class SalesProgressServerSideController extends Controller
{
    protected $title = 'Sales Progress';
    protected $folder = 'sales-progress';
    protected $lastStatus;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->lastStatus = 'SO/SJ';
    }

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
        if (!$userLogin){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error',ENV('ERR_MSG_01'));
        }

        if ($request->ajax()){
            $query = V_so_sj::leftJoin('userdetails AS usr_d','v_so_sj.created_by','=','usr_d.user_id')
            ->leftJoin('users','usr_d.user_id','=','users.id')
            ->leftJoin('mst_customers','v_so_sj.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('users as usr_salesman','usr_sales.user_id','=','usr_salesman.id')
            ->leftJoin('mst_branches','mst_customers.branch_id','=','mst_branches.id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'v_so_sj.tx_id',
                'v_so_sj.surat_no',
                'v_so_sj.surat_date',
                'v_so_sj.total_dpp',
                'v_so_sj.invoice_no',
                'v_so_sj.nota_retur_no',
                'v_so_sj.delivery_order_no',
                'v_so_sj.created_by as createdby',
                'usr_d.initial',
                'usr_d.is_director',
                'usr_d.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'mst_customers.branch_id as cust_branch_id',
                'usr_sales.initial as sales_initial',
                'mst_branches.initial as branch_initial',
                'ety_type.title_ind as ety_type_name',
            )
            ->selectRaw('CONCAT(mst_customers.customer_unique_code, \' - \', ety_type.title_ind, \'  \',mst_customers.name) as customer_complete_name')
            ->when($request->c_d!='', function($q) use($request){
                $q->where([
                    'mst_customers.slug'=>urldecode($request->c_d),
                ]);
            })
            ->when($request->s_d!='' && $request->e_d!='', function($q) use($request){
                $s_d = explode("/",urldecode($request->s_d));
                $e_d = explode("/",urldecode($request->e_d));
                $q->whereRaw('v_so_sj.surat_date>=\''.$s_d[2].'-'.$s_d[1].'-'.$s_d[0].'\' AND v_so_sj.surat_date<=\''.$e_d[2].'-'.$e_d[1].'-'.$e_d[0].'\'');
            })
            ->when($request->b_c!='', function($q) use($request){
                $q->where([
                    'mst_branches.slug'=>urldecode($request->b_c),
                ]);
            })
            ->when($request->s_c!='', function($q) use($request){
                $q->where([
                    'usr_salesman.slug'=>urldecode($request->s_c),
                ]);
            })
            ->orderBy('v_so_sj.surat_date', 'DESC')
            ->orderBy('v_so_sj.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('surat_no', function($query, $keyword) {
                $query->whereRaw('v_so_sj.surat_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('surat_no', function ($query) {
                $links = '';
                if (strpos('-'.$query->surat_no, env('P_SALES_ORDER'))>0){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales-order/'.$query->tx_id).'" '.
                        'style="text-decoration: underline;" target="_new">'.$query->surat_no.'</a>';
                }
                if (strpos('-'.$query->surat_no,env('P_SURAT_JALAN'))>0){
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/surat-jalan/'.$query->tx_id).'" '.
                        'style="text-decoration: underline;" target="_new">'.$query->surat_no.'</a>';
                }
                return $links;
            })
            ->filterColumn('surat_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(v_so_sj.surat_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('surat_date', function ($query) {
                return date_format(date_create($query->surat_date),"d/m/Y");
            })
            // ->filterColumn('customer_complete_name', function($query, $keyword) {
            //     $sql = "CONCAT(mst_customers.customer_unique_code,mst_customers.name) LIKE ?";
            //     $query->whereRaw($sql, ["%{$keyword}%"]);
            // })
            ->filterColumn('customer_complete_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_customers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_customers.customer_unique_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('customer_complete_name', function ($query) {
                return $query->customer_unique_code.' - '.$query->ety_type_name.' '.$query->cust_name;
            })
            ->addColumn('fk_np', function ($query) {
                if (strpos('-'.$query->delivery_order_no, env('P_FAKTUR'))>0){
                    $qFK = Tx_delivery_order::where([
                        'delivery_order_no'=>$query->delivery_order_no,
                    ])
                    ->first();
                    if ($qFK){
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/faktur/'.$qFK->id).'" '.
                            'style="text-decoration: underline;" target="_new">'.$query->delivery_order_no.'</a>';
                    }
                }
                if (strpos('-'.$query->delivery_order_no, env('P_NOTA_PENJUALAN'))>0){
                    $qNP = Tx_delivery_order_non_tax::where([
                        'delivery_order_no'=>$query->delivery_order_no,
                    ])
                    ->first();
                    if ($qNP){
                        return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$qNP->id).'" '.
                            'style="text-decoration: underline;" target="_new">'.$query->delivery_order_no.'</a>';
                    }

                }
                return '';
            })
            ->addColumn('nr_re', function ($query) {
                if (strpos('-'.$query->nota_retur_no, env('P_NOTA_RETUR'))>0){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/nota-retur/'.urlencode($query->nota_retur_no)).'" '.
                        'style="text-decoration: underline;" target="_new">'.$query->nota_retur_no.'</a>';
                }
                if (strpos('-'.$query->nota_retur_no, env('P_RETUR'))>0){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur/'.urlencode($query->nota_retur_no)).'" '.
                        'style="text-decoration: underline;" target="_new">'.$query->nota_retur_no.'</a>';
                }
                return '';
            })
            ->addColumn('in_kw', function ($query) {
                $in_kw = '';
                if (strpos('-'.$query->invoice_no, env('P_INVOICE'))>0){
                    $qInv = Tx_invoice::where([
                        'invoice_no'=>$query->invoice_no,
                    ])
                    ->first();
                    if ($qInv){
                        $in_kw = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$qInv->id).'" '.
                            'style="text-decoration: underline;" target="_new">'.$query->invoice_no.'</a>';
                    }
                }
                if (strpos('-'.$query->invoice_no, env('P_KWITANSI'))>0){
                    $qKwi = Tx_kwitansi::where([
                        'kwitansi_no'=>$query->invoice_no,
                    ])
                    ->first();
                    if ($qKwi){
                        $in_kw = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/kwitansi/'.$qKwi->id).'" '.
                            'style="text-decoration: underline;" target="_new">'.$query->invoice_no.'</a>';
                    }
                }
                return $in_kw;
            })
            ->addColumn('status', function ($query) {
                if (!is_null($query->invoice_no) || !is_null($query->kwitansi_no)){
                    return 'IN/KW';
                }
                if (!is_null($query->nota_retur_no) || !is_null($query->nota_retur_no_no_ppn)){
                    return 'NR/RE';
                }
                if (!is_null($query->delivery_order_no) || !is_null($query->delivery_order_no_no_ppn)){
                    return 'FK/NP';
                }
                return 'SO/SJ';
            })
            ->rawColumns(['surat_no','surat_date','customer_complete_name','fk_np','nr_re','in_kw','status'])
            ->toJson();
        }

        $qCustomers = Mst_customer::select(
            'customer_unique_code',
            'name',
            'slug',
        )
        ->where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();

        $qBranches = Mst_branch::where([
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();

        $qUsers = User::leftJoin('userdetails','users.id','=','userdetails.user_id')
        ->select(
            'users.slug',
            'userdetails.initial',
        )
        ->where(function($q){
            $q->where('userdetails.is_salesman','=','Y')
            ->orWhere('userdetails.is_director','=','Y');
        })
        ->where([
            'userdetails.active'=>'Y',
        ])
        ->orderBy('userdetails.initial','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'req' => $request,
            'qCurrency' => $qCurrency,
            'qCustomers' => $qCustomers,
            'qBranches' => $qBranches,
            'qUsers' => $qUsers,
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
        //
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
            'start_date'=>'required_with:end_date',
            'end_date'=>'required_with:start_date',
        ];
        $errMsg = [
            'end_date.required_with'=>'The End Date field is required when Start Date is present.',
            'start_date.required_with'=>'The Start Date field is required when End Date is present.',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        $qString = '';
        if ($request->cust_code){
            $qString = 'c_d='.$request->cust_code.'&';
        }
        if ($request->start_date && $request->end_date){
            $qString .= 's_d='.urlencode($request->start_date).'&e_d='.urlencode($request->end_date).'&';
        }
        if ($request->branch_code){
            $qString .= 'b_c='.$request->branch_code.'&';
        }
        if ($request->salesman_code){
            $qString .= 's_c='.$request->salesman_code.'&';
        }
        // if ($request->lokal_opsi){
        //     $qString .= 'l_o='.$request->lokal_opsi.'&';
        // }
        // if ($request->status_pos){
        //     $qString .= 's_p='.$request->status_pos.'&';
        // }

        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'?'.$qString);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_sales_order  $tx_sales_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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
