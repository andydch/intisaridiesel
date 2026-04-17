<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\User;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_lokal_journal;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_surat_jalan_part;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_nota_retur_non_tax;
use App\Helpers\OutstandingSoSjHelper;
use App\Models\Tx_lokal_journal_detail;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_nota_retur_part_non_tax;
use App\Models\Mst_automatic_journal_detail;
use Illuminate\Validation\ValidationException;

class NotaReturNonTaxApprovalServerSideController extends Controller
{
    protected $title = 'Retur - Approval';
    protected $folder = 'retur-approval';

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
            $query = Tx_nota_retur_non_tax::leftJoin('userdetails AS usr','tx_nota_retur_non_taxes.created_by','=','usr.user_id')
            ->leftJoin('tx_delivery_order_non_taxes','tx_nota_retur_non_taxes.delivery_order_id','=','tx_delivery_order_non_taxes.id')
            ->leftJoin('mst_customers','tx_nota_retur_non_taxes.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->select(
                'tx_nota_retur_non_taxes.id as tx_id',
                'tx_nota_retur_non_taxes.nota_retur_no',
                'tx_nota_retur_non_taxes.delivery_order_id',
                'tx_nota_retur_non_taxes.total_price',
                'tx_nota_retur_non_taxes.approved_by',
                'tx_nota_retur_non_taxes.approved_at',
                'tx_nota_retur_non_taxes.canceled_by',
                'tx_nota_retur_non_taxes.canceled_at',
                'tx_nota_retur_non_taxes.active as nr_active',
                'tx_nota_retur_non_taxes.created_by as createdby',
                'tx_delivery_order_non_taxes.delivery_order_no',
                'mst_customers.name as cust_name',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'usr_sales.initial as sales_initial',
            )
            ->selectRaw('DATE_FORMAT(tx_nota_retur_non_taxes.nota_retur_date, "%d/%m/%Y") as nota_retur_date')
            ->where('tx_nota_retur_non_taxes.nota_retur_no','NOT LIKE','%Draft%')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->whereRaw('tx_nota_retur_non_taxes.approved_at IS null AND tx_nota_retur_non_taxes.canceled_at IS null')
            ->where('tx_nota_retur_non_taxes.active', '=', 'Y')
            ->orderBy('tx_nota_retur_non_taxes.nota_retur_no','DESC')
            ->orderBy('tx_nota_retur_non_taxes.created_at','DESC');

            return DataTables::of($query)
            ->addColumn('nota_retur_no_wlink', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/retur-approval/'.$query->nota_retur_no).'" style="text-decoration: underline;">View</a>';
            })
            ->addColumn('delivery_order_no', function ($query) {
                if(!is_null($query->delivery_order_no)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-local/'.$query->delivery_order_id).'" target="_new" '.
                        'style="text-decoration: underline;">'.$query->delivery_order_no.'</a>';
                }
                return '';
            })
            // ->addColumn('purchase_retur_date_string', function ($query) {
            //     return date_format(date_create($query->purchase_retur_date),"d/m/Y");
            // })
            ->addColumn('status', function ($query) {
                if (is_null($query->approved_by) && is_null($query->canceled_by)){
                    return 'Waiting for Approval';
                }
                if (!is_null($query->approved_by)){
                    $qApprovedBy = User::where('id','=',$query->approved_by)
                    ->first();
                    if ($qApprovedBy){
                        return 'Approved at '.
                            date_format(date_add(date_create($query->approved_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.$qApprovedBy->name;
                    }
                }
                if (!is_null($query->canceled_by)){
                    $qCanceledBy = User::where('id','=',$query->canceled_by)
                    ->first();
                    if ($qCanceledBy){
                        return 'Rejected at '.
                            date_format(date_add(date_create($query->canceled_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.$qCanceledBy->name;
                    }
                }
                return '';
            })
            ->rawColumns(['nota_retur_no_wlink','delivery_order_no','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency
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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($nota_retur_no)
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
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'vat' => $vat,
                'qNotaRetur' => $query,
                'qNotaReturPart' => $queryPart->get(),
                'qCurrency' => $qCurrency,
                'all_selected_SJ' => $all_selected_SJ,
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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $nota_retur_no)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 75,
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
                session()->flash('status-error', 'The document status cannot be changed because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }
        
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $q = Tx_nota_retur_non_tax::where('nota_retur_no','=',urldecode($nota_retur_no))
                ->where('approved_by','=',null)
                ->where('active','=','Y')
                ->first();
                if($q){
                    $journal_date = explode("-", $q->nota_retur_date);

                    // update status approval - approve
                    $upd = Tx_nota_retur_non_tax::where('nota_retur_no','=',urldecode($nota_retur_no))
                    ->where('approved_by','=',null)
                    ->update([
                        'approved_by' => Auth::user()->id,
                        'approved_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null,
                        'updated_by' => Auth::user()->id,
                    ]);

                    $branch_id = $q->branch_id;
                    $totalLastAvgCost = 0;

                    // tambahkan jumlah part di gudang
                    $qPart = Tx_nota_retur_part_non_tax::where('nota_retur_id','=',$q->id)
                    ->where('active','=','Y')
                    ->get();
                    foreach($qPart as $qP){
                        // ambil avg_cost sebelum proses retur dijalankan
                        $last_avg_cost = 0;
                        $qSJpartAVG = Tx_surat_jalan_part::where([
                            'id'=>$qP->surat_jalan_part_id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qSJpartAVG){
                            $last_avg_cost = $qSJpartAVG->last_avg_cost;
                        }
                        // ambil avg_cost sebelum proses retur dijalankan

                        $qPartQty = Tx_qty_part::leftJoin('mst_parts as mp','tx_qty_parts.part_id','=','mp.id')
                        ->select(
                            'tx_qty_parts.part_id',
                            'tx_qty_parts.qty',
                            'mp.avg_cost',
                            )
                        ->addSelect([
                            'qty_nasional' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('tx_qty_parts.part_id','mp.id')
                            ->limit(1)
                        ])
                        ->where('tx_qty_parts.part_id','=',$qP->part_id)
                        ->where('tx_qty_parts.branch_id','=',$q->branch_id)
                        ->first();
                        if($qPartQty){
                            // update OH sesuai branch
                            $updPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
                            ->where('branch_id','=',$q->branch_id)
                            ->update([
                                'qty' => (int)$qPartQty->qty+(int)$qP->qty_retur,
                                'updated_by' => Auth::user()->id,
                            ]);

                            // outstanding SO/Sj qty
                            $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($qP->part_id);
                            $qtyNasional = ((int)$qPartQty->qty_nasional-$qtySoSj>0?(int)$qPartQty->qty_nasional-$qtySoSj:0);

                            // hitung avg
                            if (($qtyNasional+$qP->qty_retur)>0){
                                $updAVG = Mst_part::where('id','=',$qPartQty->part_id)
                                ->update([
                                    'avg_cost' => (($qPartQty->avg_cost*$qtyNasional)+($qP->qty_retur*$last_avg_cost))/($qtyNasional+$qP->qty_retur),
                                    // 'avg_cost' => (($qPartQty->avg_cost*$qPartQty->qty_nasional)+($qP->qty_retur*$last_avg_cost))/($qPartQty->qty_nasional+$qP->qty_retur),
                                ]);
                            }
                        }

                        $qSJpart = Tx_surat_jalan_part::where([
                            'id'=>$qP->surat_jalan_part_id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qSJpart){
                            $totalLastAvgCost += ($qSJpart->last_avg_cost*$qP->qty_retur);
                        }
                    }

                    // simpan deskripsi utk jurnal - start
                    $deskripsi = '';
                    $getDesc = Tx_nota_retur_non_tax::leftJoin('mst_customers AS msc', 'tx_nota_retur_non_taxes.customer_id', '=', 'msc.id')
                    ->leftJoin('mst_globals AS msg', 'msc.entity_type_id', '=', 'msg.id')
                    ->select(
                        'tx_nota_retur_non_taxes.nota_retur_no',
                        'tx_nota_retur_non_taxes.remark',
                        'msc.name AS cust_name',
                        'msc.customer_unique_code AS cust_unique_code',
                        'msg.title_ind AS entity_type',
                    )
                    ->where('tx_nota_retur_non_taxes.id', '=', $q->id)
                    ->first();
                    if ($getDesc){
                        $deskripsi = $getDesc->nota_retur_no.', '.
                            $getDesc->cust_unique_code.' - '.($getDesc->entity_type!=null?$getDesc->entity_type.' ':'').$getDesc->cust_name.', '.
                            $getDesc->remark;
                        $deskripsi = substr($deskripsi, 0, 4096);
                    }
                    // simpan deskripsi utk jurnal - end

                    // cek apakah fitur automatic journal untuk retur sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>4,
                        'branch_id'=>$branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournal){
                        // sales retur non pajak
                        $qAutJournal_sales_retur_non_pajak = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>4,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'sales retur non pajak\'')
                        ->first();
                        // piutang
                        $qAutJournal_piutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>4,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'piutang\'')
                        ->first();
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>4,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // cogs
                        $qAutJournal_cogs = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>4,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'cogs\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_lokal_journal::where([
                            'module_no'=>$nota_retur_no,
                            'automatic_journal_id'=>4,
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
                            $yearTemp = substr($journal_date[0],2,2);
                            $monthTemp = (strlen($journal_date[1])==1?'0'.$journal_date[1]:$journal_date[1]);
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
                                    // jika bulan di server sudah berganti

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
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            // $journal_no = env('P_LOKAL_JURNAL').date('y').date('m').$zero.strval($newInc);
                            $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            $insJournal = Tx_lokal_journal::create([
                                'general_journal_no'=>$journal_no,
                                'general_journal_date'=>$q->nota_retur_date,
                                // 'general_journal_date'=>date("Y-m-d"),
                                'total_debit'=>($totalLastAvgCost+$q->total_price),
                                'total_kredit'=>($totalLastAvgCost+$q->total_price),
                                'module_no'=>$nota_retur_no,
                                'automatic_journal_id'=>4,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }

                        // sales retur non pajak
                        $ins_sales_retur_non_pajak = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_sales_retur_non_pajak->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>$q->total_price,
                            'kredit'=>0,
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
                            'debit'=>0,
                            'kredit'=>$q->total_price,
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
                            'debit'=>$totalLastAvgCost,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // cogs
                        $ins_cogs = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_cogs->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>$deskripsi,
                            'debit'=>0,
                            'kredit'=>$totalLastAvgCost,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }
                }
            }
            // if($request->order_appr == 'R'){
            //     $q = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
            //     ->first();
            //     if($q){
            //         if($q->approved_by!=null){
            //             // jika status approval sebelumnya adalah approve
            //             // kembalikan jumlah part di gudang
            //             $qPart = Tx_nota_retur_part::where('nota_retur_id','=',$q->id)
            //             ->where('active','=','Y')
            //             ->get();
            //             foreach($qPart as $qP){
            //                 $qPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
            //                 ->where('branch_id','=',$q->branch_id)
            //                 ->first();
            //                 if($qPartQty){
            //                     $updPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
            //                     ->where('branch_id','=',$q->branch_id)
            //                     ->update([
            //                         'qty' => (int)$qPartQty->qty-(int)$qP->qty_retur,
            //                         'updated_by' => Auth::user()->id,
            //                     ]);
            //                 }
            //             }
            //         }
            //     }
            //     // update status approval - reject
            //     $upd = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
            //     ->where('canceled_by','=',null)
            //     ->update([
            //         'approved_by' => null,
            //         'approved_at' => null,
            //         'canceled_by' => Auth::user()->id,
            //         'canceled_at' => now(),
            //         'updated_by' => Auth::user()->id,
            //     ]);
            // }

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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
