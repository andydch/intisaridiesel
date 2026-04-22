<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
// use App\Models\User;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Models\Tx_payment_voucher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Auto_inc;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_automatic_journal_detail_ext;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_lokal_journal_detail;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class PaymentVoucherApprovalServerSideController extends Controller
{
    protected $title = 'Pembayaran Supplier - Approval';
    protected $folder = 'payment-voucher-approval';
    protected $payment_mode_string = 'Cash,Bank';
    protected $payment_mode_id = '1,2';

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->payment_mode_string = env('METHOD_BAYAR_SUPPLIER_NAME');
        $this->payment_mode_id = env('METHOD_BAYAR_SUPPLIER_ID');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        if ($request->ajax()){
            $query = Tx_payment_voucher::leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id','=','mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by','=','users.id')
            ->leftJoin('userdetails AS usr','tx_payment_vouchers.created_by','=','usr.user_id')
            ->leftJoin('mst_globals AS ety_type','mst_suppliers.entity_type_id', '=', 'ety_type.id')
            ->leftJoin('users AS usr_appr','tx_payment_vouchers.approved_by','=','usr_appr.id')
            ->leftJoin('users AS usr_rejt','tx_payment_vouchers.canceled_by','=','usr_rejt.id')
            ->leftJoin('tx_tagihan_suppliers AS cts','tx_payment_vouchers.tagihan_supplier_id', '=', 'cts.id')
            ->select(
                'tx_payment_vouchers.id AS tx_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_voucher_plan_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_type_id',
                'tx_payment_vouchers.journal_type_id',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.payment_total_after_vat',
                'tx_payment_vouchers.admin_bank',
                'tx_payment_vouchers.biaya_asuransi',
                'tx_payment_vouchers.biaya_kirim',
                'tx_payment_vouchers.biaya_lainnya',
                'tx_payment_vouchers.diskon_pembelian',
                'tx_payment_vouchers.vat_num',
                'tx_payment_vouchers.pv_created_at',
                'tx_payment_vouchers.ps_created_at',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.created_by as createdby',
                'tx_payment_vouchers.created_at as createdat',
                'tx_payment_vouchers.active as pv_active',
                'mst_suppliers.name AS supplier_name',
                'mst_suppliers.supplier_code',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'ety_type.title_ind as ety_type_name',
                'cts.tagihan_supplier_no',
            )
            ->selectRaw('(CASE 
                WHEN tx_payment_vouchers.approved_by IS NOT null THEN CONCAT(\'Approved at \', \' \', DATE_FORMAT(tx_payment_vouchers.approved_at, \'%b %e, %Y %l:%i %p\'), \' by \', usr_appr.name)
                WHEN tx_payment_vouchers.canceled_by IS NOT null THEN CONCAT(\'Rejected at \', \' \', DATE_FORMAT(tx_payment_vouchers.canceled_at, \'%b %e, %Y %l:%i %p\'), \' by \', usr_rejt.name)
                WHEN tx_payment_vouchers.approved_by IS null AND tx_payment_vouchers.canceled_by IS null THEN \'Waiting for Approval\'
                ELSE \'\'
                END) AS approval_status')
            ->where('tx_payment_vouchers.payment_voucher_no','NOT LIKE','%Draft%')
            ->whereRaw('tx_payment_vouchers.approved_by IS null')
            ->where('tx_payment_vouchers.active','=','Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            // ->orderBy('tx_payment_vouchers.payment_voucher_plan_no','DESC')
            ->orderBy('tx_payment_vouchers.payment_date','ASC');

            return DataTables::of($query)
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_suppliers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_suppliers.supplier_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($query) {
                return $query->supplier_code.' - '.$query->ety_type_name.' '.$query->supplier_name;
            })
            ->addColumn('payment_voucher_no_wlink', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/payment-voucher-approval/'.
                    urlencode(!is_null($query->payment_voucher_no)?$query->payment_voucher_no:$query->payment_voucher_plan_no)).'" style="text-decoration: underline;">View</a>';
            })
            ->filterColumn('doc_created_at', function($query, $keyword) {
                $query->where(function($q) use($keyword) {
                    $q->whereRaw('DATE_FORMAT(DATE_ADD(tx_payment_vouchers.pv_created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(DATE_ADD(tx_payment_vouchers.ps_created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"])
                    ->orwhereRaw('DATE_FORMAT(DATE_ADD(tx_payment_vouchers.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR), "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
                });
            })
            ->editColumn('doc_created_at', function ($query) {
                $doc_created_at = null;
                if (!is_null($query->payment_voucher_no)){
                    $doc_created_at=date_create($query->pv_created_at);
                    return date_format(date_add($doc_created_at, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")),"d/m/Y");
                }
                if (!is_null($query->payment_voucher_plan_no)){
                    $doc_created_at=date_create($query->ps_created_at);
                    return date_format(date_add($doc_created_at, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")),"d/m/Y");
                }
                $doc_created_at=date_create($query->createdat);
                return date_format(date_add($doc_created_at, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")),"d/m/Y");
            })
            ->filterColumn('journal_date_at', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_payment_vouchers.payment_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('journal_date_at', function ($query) {
                $journal_date_at=date_create($query->payment_date);
                return date_format($journal_date_at,"d/m/Y");
            })
            ->addColumn('payment_total', function ($query) {
                $tot = $query->payment_total_after_vat+
                    $query->admin_bank+
                    $query->biaya_kirim+
                    $query->biaya_lainnya+
                    $query->biaya_asuransi-
                    $query->diskon_pembelian;
                return $tot;
            })
            ->filterColumn('journal_no', function ($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->whereIn('tx_payment_vouchers.payment_voucher_no', function($q1) use($keyword){
                        $q1->select('module_no')
                        ->from('tx_general_journals')
                        ->where('general_journal_no', 'LIKE', "%{$keyword}%");
                    })
                    ->orWhereIn('tx_payment_vouchers.payment_voucher_no', function($q1) use($keyword){
                        $q1->select('module_no')
                        ->from('tx_lokal_journals')
                        ->where('general_journal_no', 'LIKE', "%{$keyword}%");
                    });
                });
            })
            ->editColumn('journal_no', function ($query) {
                if(!is_null($query->payment_voucher_no)){
                    $qGJ = Tx_general_journal::select('general_journal_no')
                    ->where([
                        'module_no'=>$query->payment_voucher_no,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qGJ){
                        return $qGJ->general_journal_no;
                    }
                    $qLJ = Tx_lokal_journal::select('general_journal_no')
                    ->where([
                        'module_no'=>$query->payment_voucher_no,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qLJ){
                        return $qLJ->general_journal_no;
                    }
                }
                return '';
            })
            ->filterColumn('approval_status', function ($query, $keyword) {
                $query->whereRaw('(CASE 
                    WHEN tx_payment_vouchers.approved_by IS NOT null THEN CONCAT(\'Approved at \', \' \', DATE_FORMAT(tx_payment_vouchers.approved_at, \'%b %e, %Y %l:%i %p\'), \' by \', usr_appr.name)
                    WHEN tx_payment_vouchers.canceled_by IS NOT null THEN CONCAT(\'Rejected at \', \' \', DATE_FORMAT(tx_payment_vouchers.canceled_at, \'%b %e, %Y %l:%i %p\'), \' by \', usr_rejt.name)
                    WHEN tx_payment_vouchers.approved_by IS null AND tx_payment_vouchers.canceled_by IS null THEN \'Waiting for Approval\'
                    ELSE \'\'
                    END) LIKE \'%'.$keyword.'%\'');
            })
            ->editColumn('approval_status', function ($query) {
                return $query->approval_status;
            })
            ->rawColumns(['supplier_name','doc_created_at','journal_date_at','journal_no','payment_total','payment_voucher_no_wlink','approval_status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($voucher_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $qVat = Mst_global::where([
            'data_cat'=>'vat',
            'active'=>'Y',
        ])
        ->first();

        $query = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->get();
            $queryInvCount = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->count();

            $paymentInvId = $query->id;
            $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('payment_voucher_id','<>',$paymentInvId)
                ->where('is_full_payment','=','Y');
            })
            ->where('approved_by','<>',null)
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC')
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInvCount),
                'qPaymentInv' => $query,
                'queryInv' => $queryInv,
                'qCurrency' => $qCurrency,
                'payment_mode_string'=>explode("|",$this->payment_mode_string),
                'payment_mode_id'=>explode("|",$this->payment_mode_id),
                'qVat' => $qVat,
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $payment_voucher_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 51,
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

        $qPv = Tx_payment_voucher::where('payment_voucher_no', '=', urldecode($payment_voucher_no))
        ->orWhere('payment_voucher_plan_no', '=', urldecode($payment_voucher_no))
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'The document status cannot be changed because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }
        
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($payment_voucher_no))
                ->where('approved_by','=',null)
                ->update([
                    'approved_by' => Auth::user()->id,
                    'approved_at' => now(),
                    'canceled_by' => null,
                    'canceled_at' => null,
                    'updated_by' => Auth::user()->id,
                ]);

                $q = Tx_payment_voucher::where([
                    'payment_voucher_no'=>urldecode($payment_voucher_no),
                ])
                ->first();
                if ($q){
                    $branch_id = '';
                    $methodNm = '';

                    $description = '';
                    $qSuppliers = DB::table('mst_suppliers AS mst_sp')
                    ->leftJoin('mst_globals AS ety_type','mst_sp.entity_type_id', '=', 'ety_type.id')
                    ->select(
                        'mst_sp.supplier_code',
                        'ety_type.title_ind as ety_type_name',
                        'mst_sp.name as supplier_name',
                    )
                    ->where('mst_sp.id','=',$q->supplier_id)
                    ->first();
                    if ($qSuppliers){
                        $description = substr($q->payment_voucher_no.', '.
                            ($qSuppliers->ety_type_name!=null?$qSuppliers->ety_type_name.' ':'').$qSuppliers->supplier_name.', '.
                            $q->remark, 0, 4096);
                    }

                    $payment_mode = $q->payment_mode;
                    switch ($payment_mode) {
                        case 2:
                            $methodNm = 'Bank';
                            break;
                        case 3:
                            $methodNm = 'Advance Payment';
                            break;
                        default:
                            $methodNm = 'Cash';
                    }
                    $qInv = Tx_payment_voucher_invoice::leftJoin('tx_receipt_orders as tx_ro','tx_payment_voucher_invoices.receipt_order_id','=','tx_ro.id')
                    ->select(
                        'tx_ro.branch_id',
                        'tx_ro.po_or_pm_no',
                        'tx_payment_voucher_invoices.id as inv_id',
                    )
                    ->where([
                        'payment_voucher_id'=>$q->id,
                    ])
                    ->get();
                    foreach($qInv as $qI){
                        $branch_id = $qI->branch_id;
                        if ($branch_id!=''){break;}
                    }

                    $is_vat = ($q->payment_type_id=='P'?'Y':'N');
                    $journal_type = $q->journal_type_id;

                    if ($is_vat=='Y' || $journal_type=='P'){
                        // pembentukan general journal terjadi jika No Pembayaran Supplier menggunakan PPN(P) atau
                        // No Pembayaran Supplier menggunakan Non PPN(N) dan Journal Type adalah PPN(P)

                        // cek apakah fitur automatic journal untuk payment voucher sudah tersedia - PPN
                        $qAutJournal = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>8,
                            'branch_id'=>$branch_id,
                            'method_id'=>$payment_mode,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qAutJournal){
                            // hutang
                            $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                            ->first();
                            // bank admin
                            $qAutJournal_bank_admin = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                            ->first();
                            // biaya asuransi
                            $qAutJournal_biaya_asuransi = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                            ->first();
                            // biaya kirim
                            $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                            ->first();
                            // biaya lainnya
                            $qAutJournal_biaya_lainnya = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                            ->first();
                            // discount
                            $qAutJournal_discount = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                            ->first();
                            // cash/bank/advance payment
                            $qAutJournal_cash_ext = Mst_automatic_journal_detail_ext::select('coa_code_id')
                            ->where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'coa_code_id'=>$q->coa_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'');
                            $qAutJournal_cash = Mst_automatic_journal_detail::select('coa_code_id')
                            ->where([
                                'auto_journal_id'=>8,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'coa_code_id'=>$q->coa_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                            ->union($qAutJournal_cash_ext)
                            ->first();

                            // PPN
                            $total_cash = $q->payment_total_after_vat+
                                $q->admin_bank+
                                $q->biaya_asuransi+
                                $q->biaya_kirim+
                                $q->biaya_lainnya-
                                $q->diskon_pembelian;

                            $qVat = Mst_global::where([
                                'data_cat'=>'vat',
                                'active'=>'Y',
                            ])
                            ->first();

                            // cek apakah module sudah pernah dibuat
                            $insJournal = [];
                            $qJournals = Tx_general_journal::where([
                                'module_no'=>urldecode($payment_voucher_no),
                                'automatic_journal_id'=>8,
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
                                $yearTemp = date_format(date_create($q->payment_date), "y");
                                $monthTemp = date_format(date_create($q->payment_date), "m");
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
                                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                    $zero .= '0';
                                }
                                $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                                $total_cash = $q->payment_total_after_vat+
                                    $q->admin_bank+
                                    $q->biaya_asuransi+
                                    $q->biaya_kirim+
                                    $q->biaya_lainnya-
                                    $q->diskon_pembelian;

                                // buat jurnal
                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$q->payment_date,
                                    'total_debit'=>$total_cash,
                                    'total_kredit'=>$total_cash,
                                    'module_no'=>urldecode($payment_voucher_no),
                                    'automatic_journal_id'=>8,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // hutang
                            $ins_hutang = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>$description,
                                'debit'=>$q->payment_total_after_vat,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bank admin
                            if ($qAutJournal_bank_admin->coa_code_id>0){
                                $ins_hutang = Tx_general_journal_detail::create([
                                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_bank_admin->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->admin_bank,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya asuransi
                            if ($qAutJournal_biaya_asuransi->coa_code_id>0){
                                $ins_hutang = Tx_general_journal_detail::create([
                                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_asuransi->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_asuransi,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya kirim
                            if ($qAutJournal_biaya_kirim->coa_code_id>0){
                                $ins_hutang = Tx_general_journal_detail::create([
                                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_kirim,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya lainnya
                            if ($qAutJournal_biaya_lainnya->coa_code_id>0){
                                $ins_hutang = Tx_general_journal_detail::create([
                                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_lainnya->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_lainnya,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // diskon pembelian
                            if ($qAutJournal_discount->coa_code_id>0){
                                $ins_hutang = Tx_general_journal_detail::create([
                                    'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>0,
                                    'kredit'=>$q->diskon_pembelian,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // cash/bank/advance payment
                            $ins_cash = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_cash->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>$description,
                                'debit'=>0,
                                'kredit'=>$total_cash,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }

                    if ($is_vat=='N' && ($journal_type=='N' || $journal_type=='' || $journal_type==null)){
                        // pembentukan lokal journal terjadi jika No Pembayaran Supplier menggunakan non PPN(P) dan Journal Type adalah Non PPN(N)

                        // Non PPN
                        $total_cash = 
                            $q->payment_total_after_vat+
                            $q->admin_bank+
                            $q->biaya_asuransi+
                            $q->biaya_kirim+
                            $q->biaya_lainnya-
                            $q->diskon_pembelian;

                        // cek apakah fitur automatic journal untuk payment voucher sudah tersedia - NON PPN
                        $qAutJournal = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>13,
                            'branch_id'=>$branch_id,
                            'method_id'=>$payment_mode,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qAutJournal){
                            // hutang
                            $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                            ->first();
                            // bank admin
                            $qAutJournal_bank_admin = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'bank admin\'')
                            ->first();
                            // biaya asuransi
                            $qAutJournal_biaya_asuransi = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya asuransi\'')
                            ->first();
                            // biaya kirim
                            $qAutJournal_biaya_kirim = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya kirim\'')
                            ->first();
                            // biaya lainnya
                            $qAutJournal_biaya_lainnya = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'biaya lainnya\'')
                            ->first();
                            // discount
                            $qAutJournal_discount = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'discount\'')
                            ->first();
                            // cash/bank/advance payment
                            $qAutJournal_cash_ext = Mst_automatic_journal_detail_ext::select('coa_code_id')
                            ->where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'coa_code_id'=>$q->coa_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'');
                            $qAutJournal_cash = Mst_automatic_journal_detail::select('coa_code_id')
                            ->where([
                                'auto_journal_id'=>13,
                                'branch_id'=>$branch_id,
                                'method_id'=>$payment_mode,
                                'coa_code_id'=>$q->coa_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\''.strtolower($methodNm).'\'')
                            ->union($qAutJournal_cash_ext)
                            ->first();

                            // ---
                            // cek apakah module sudah pernah dibuat
                            $insJournal = [];
                            $qJournals = Tx_lokal_journal::where([
                                'module_no'=>urldecode($payment_voucher_no),
                                'automatic_journal_id'=>13,
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
                                $yearTemp = date_format(date_create($q->payment_date), "y");
                                $monthTemp = date_format(date_create($q->payment_date), "m");
                                $ymTemp = $yearTemp.$monthTemp;
                                $zero = '';
                                $YearMonth = '';
                                $newInc = 1;
                                $identityName = 'tx_lokal_journal';
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
                                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                    $zero .= '0';
                                }
                                $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                                $total_cash = $q->payment_total_after_vat+
                                    $q->admin_bank+
                                    $q->biaya_asuransi+
                                    $q->biaya_kirim+
                                    $q->biaya_lainnya-
                                    $q->diskon_pembelian;

                                // buat jurnal
                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$q->payment_date,
                                    'total_debit'=>$total_cash,
                                    'total_kredit'=>$total_cash,
                                    'module_no'=>urldecode($payment_voucher_no),
                                    'automatic_journal_id'=>13,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // hutang
                            $ins_hutang = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>$description,
                                'debit'=>$q->payment_total_after_vat,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bank admin
                            if ($qAutJournal_bank_admin->coa_code_id>0){
                                $ins_hutang = Tx_lokal_journal_detail::create([
                                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_bank_admin->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->admin_bank,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya asuransi
                            if ($qAutJournal_biaya_asuransi->coa_code_id>0) {
                                $ins_hutang = Tx_lokal_journal_detail::create([
                                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_asuransi->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_asuransi,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya kirim
                            if ($qAutJournal_biaya_kirim->coa_code_id>0){
                                $ins_hutang = Tx_lokal_journal_detail::create([
                                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_kirim->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_kirim,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // biaya lainnya
                            if ($qAutJournal_biaya_lainnya->coa_code_id>0){
                                $ins_hutang = Tx_lokal_journal_detail::create([
                                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_biaya_lainnya->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>$q->biaya_lainnya,
                                    'kredit'=>0,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // diskon pembelian
                            if ($qAutJournal_discount->coa_code_id>0){
                                $ins_hutang = Tx_lokal_journal_detail::create([
                                    'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                    'coa_id'=>$qAutJournal_discount->coa_code_id,
                                    'coa_detail_id'=>null,
                                    'description'=>$description,
                                    'debit'=>0,
                                    'kredit'=>$q->diskon_pembelian,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }

                            // cash/bank/advance payment
                            $ins_cash = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_cash->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>$description,
                                'debit'=>0,
                                'kredit'=>$total_cash,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }
                }
            }
            if($request->order_appr == 'R'){
                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($payment_voucher_no))
                ->where('canceled_by','=',null)
                ->update([
                    'approved_by' => null,
                    'approved_at' => null,
                    'canceled_by' => Auth::user()->id,
                    'canceled_at' => now(),
                    'updated_by' => Auth::user()->id,
                ]);

                // non aktifkan jurnal jika ada - PPN
                $updJournal = Tx_general_journal::where([
                    'module_no'=>urldecode($payment_voucher_no),
                ])
                ->update([
                    'active'=>'N',
                    'updated_by'=>Auth::user()->id,
                ]);

                // non aktifkan jurnal jika ada - Non PPN
                $updJournal = Tx_lokal_journal::where([
                    'module_no'=>urldecode($payment_voucher_no),
                ])
                ->update([
                    'active'=>'N',
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
