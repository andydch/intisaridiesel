<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\User;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_receipt_order;
use App\Models\Tx_purchase_order;
use App\Models\Tx_purchase_retur;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\OutstandingSoSjHelper;
use App\Models\Tx_purchase_retur_part;
use App\Models\Tx_lokal_journal_detail;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_general_journal_detail;
use App\Models\Mst_automatic_journal_detail;
use Illuminate\Validation\ValidationException;

class PurchaseReturApprovalServerSideController extends Controller
{
    protected $title = 'Purchase Retur Approval';
    protected $folder = 'purchase-retur-approval';

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
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_purchase_returs.supplier_id','=','mst_suppliers.id')
            ->leftJoin('tx_receipt_orders','tx_purchase_returs.receipt_order_id','=','tx_receipt_orders.id')
            ->select(
                'tx_purchase_returs.id as tx_id',
                'tx_purchase_returs.purchase_retur_no',
                'tx_purchase_returs.receipt_order_id',
                'tx_purchase_returs.supplier_id',
                'tx_purchase_returs.total_before_vat',
                'tx_purchase_returs.total_after_vat',
                'tx_purchase_returs.active as pr_active',
                'tx_purchase_returs.created_by as createdBy',
                'tx_purchase_returs.approved_by',
                'tx_purchase_returs.canceled_by',
                'tx_purchase_returs.approved_at',
                'tx_purchase_returs.canceled_at',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_suppliers.name as supplier_name',
                'tx_receipt_orders.receipt_no',
                'tx_receipt_orders.invoice_no',
            )
            ->selectRaw('DATE_FORMAT(tx_purchase_returs.purchase_retur_date, "%d/%m/%Y") as purchase_retur_date')
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
            ->whereRaw('tx_purchase_returs.approved_at IS null AND tx_purchase_returs.canceled_by IS null')
            ->where('tx_purchase_returs.active','=','Y')
            ->orderBy('tx_purchase_returs.created_at','DESC')
            ->orderBy('tx_purchase_returs.purchase_retur_date','DESC');

            return DataTables::of($query)
            ->addColumn('purchase_retur_no_wlink', function ($query) {
                if(is_null($query->approved_by) && is_null($query->canceled_by)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/purchase-retur-approval/'.$query->tx_id).'" '.
                        'style="text-decoration: underline;">View</a>';
                }
                return '';
            })
            ->addColumn('receipt_no', function ($query) {
                if(!is_null($query->receipt_no)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->receipt_order_id).'" target="_new" '.
                        'style="text-decoration: underline;">'.$query->receipt_no.'</a>';
                }
                return '';
            })
            ->addColumn('status', function ($query) {
                if (is_null($query->approved_by) && is_null($query->canceled_by)){
                    return 'Waiting for approval';
                }else{
                    if (!is_null($query->approved_by)){
                        $qUser = User::where('id','=',$query->approved_by)
                        ->first();
                        if ($qUser){
                            return 'Approved at '.
                                date_format(date_add(date_create($query->approved_at), 
                                date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                                ' by '.($qUser?$qUser->name:'');
                            // return 'Approved by '.($qUser?$qUser->name:'').' at '.date_format(date_create($query->approved_at), 'd M Y H:i:s');
                        }
                    }
                    if (!is_null($query->canceled_by)){
                        $qUser = User::where('id','=',$query->canceled_by)
                        ->first();
                        if ($qUser){
                            return 'Rejected at '.
                                date_format(date_add(date_create($query->canceled_at), 
                                date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                                ' by '.($qUser?$qUser->name:'');
                            // return 'Rejected by '.($qUser?$qUser->name:'').' at '.date_format(date_create($query->canceled_at), 'd M Y H:i:s');
                        }
                    }
                }
            })
            ->rawColumns(['purchase_retur_no_wlink','receipt_no','status'])
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $querySupplier = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
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

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_purchase_retur::where('id', '=', $id)->first();
        if ($query) {
            $invoice_no = [];
            if(old('supplier_id')){
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }else{
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }

            $queryPart = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'invoice_no' => $invoice_no,
                'vat' => $vat,
                'qRo' => $query,
                'qRoPart' => $queryPart,
                'qCurrency' => $qCurrency
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 38,
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

        $qPv = Tx_purchase_retur::where('id', '=', $id)
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'The document status cannot be changed because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }
        
        // ambil info user
        $qUser = Tx_purchase_retur::leftJoin('userdetails','tx_purchase_returs.created_by','=','userdetails.user_id')
        ->leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
        ->select(
            'userdetails.branch_id AS user_branch_id',
            'tx_ro.branch_id AS ro_branch_id',
        )
        ->where('tx_purchase_returs.id','=',$id)
        ->first();

        $qPart = Tx_purchase_retur_part::where([
            'purchase_retur_id' => $id,
            'active' => 'Y'
        ])
        ->get();

        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $qCheck = Tx_purchase_retur::where('id','=',$id)
                ->where('approved_by','=',null)
                ->where('canceled_by','=',null)
                ->first();
                if($qCheck){
                    $journal_date = explode("-", $qCheck->purchase_retur_date);
                    foreach($qPart as $qP){
                        $last_avg_cost = 0;
                        $parts = Mst_part::where('id','=',$qP->part_id)
                        ->first();
                        if ($parts){
                            $last_avg_cost = $parts->avg_cost;
                        }

                        // outstanding SO/Sj qty
                        $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($qP->part_id);

                        // OH
                        $sumOH = Tx_qty_part::where('part_id', '=', $qP->part_id)
                        ->whereIn('branch_id', function($q) {
                            $q->select('id')
                            ->from('mst_branches')
                            ->where('active', '=', 'Y');
                        })
                        ->sum('qty');
                        $freeOH = ($sumOH-$qtySoSj>0?$sumOH-$qtySoSj:0);

                        // avg cost RO
                        $avg_cost_ro = 0;
                        $qAvgCostRO = DB::table('tx_receipt_order_parts AS tx_rop')
                        ->leftJoin('tx_receipt_orders AS tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                        ->leftJoin('tx_purchase_returs AS tx_pr','tx_ro.id','=','tx_pr.receipt_order_id')
                        ->leftJoin('tx_purchase_retur_parts AS tx_prp','tx_pr.id','=','tx_prp.purchase_retur_id')
                        ->where('tx_rop.active', '=', 'Y')
                        ->where('tx_ro.active', '=', 'Y')
                        ->where('tx_prp.part_id', '=', $qP->part_id)
                        ->where('tx_pr.id', '=', $id)
                        ->where('tx_prp.active', '=', 'Y')
                        ->where('tx_pr.active', '=', 'Y')
                        ->first();
                        if ($qAvgCostRO){
                            $avg_cost_ro = $qAvgCostRO->avg_cost;
                        }

                        $new_avg_cost = (($last_avg_cost*$freeOH)+($avg_cost_ro*$qP->qty_retur))/($freeOH+$qP->qty_retur);
                        // update AVG
                        $updAvgCost = Mst_part::where('id','=',$qP->part_id)
                        ->update([
                            'avg_cost' => $new_avg_cost,
                            'updated_by' => Auth::user()->id,
                        ]);

                        $qQty = Tx_qty_part::where([
                            'part_id' => $qP->part_id,
                            'branch_id' => $qUser->ro_branch_id,
                        ])
                        ->first();
                        if($qQty){
                            $upd = Tx_qty_part::where([
                                'part_id' => $qP->part_id,
                                'branch_id' => $qUser->ro_branch_id,
                            ])
                            ->update([
                                'qty' => $qQty->qty-$qP->qty_retur,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $ins = Tx_qty_part::create([
                                'part_id' => $qP->part_id,
                                'qty' => -$qP->qty_retur,
                                'branch_id' => $qUser->ro_branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }

                    $upd = Tx_purchase_retur::where('id','=',$id)
                    ->where('approved_by','=',null)
                    ->update([
                        'approved_by' => Auth::user()->id,
                        'approved_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null
                    ]);

                    // cari RO terkait
                    $is_vat = '';
                    $branch_id = '';
                    $journal_type_id = '';
                    $qRo = Tx_receipt_order::where([
                        'id'=>$qCheck->receipt_order_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qRo){
                        $pOmOs = explode(",",$qRo->po_or_pm_no);
                        $journal_type_id = $qRo->journal_type_id;
                        foreach($pOmOs as $pOmO){
                            $no = $pOmO;
                            if (strpos("q-".$no,env('P_PURCHASE_MEMO'))>0){
                                $qMo = Tx_purchase_memo::where([
                                    'memo_no'=>$no,
                                    'active'=>'Y',
                                ])
                                ->first();
                                if ($qMo){
                                    $is_vat = $qMo->is_vat;
                                    $branch_id = $qMo->branch_id;
                                    break;
                                }
                            }
                            if (strpos("q-".$no,env('P_PURCHASE_ORDER'))>0){
                                $qPo = Tx_purchase_order::where([
                                    'purchase_no'=>$no,
                                    'active'=>'Y',
                                ])
                                ->first();
                                if ($qPo){
                                    $is_vat = $qPo->is_vat;
                                    $branch_id = $qPo->branch_id;
                                    break;
                                }
                            }
                        }
                    }
                    if ($is_vat=='Y' || $journal_type_id=='P'){
                        // cek apakah fitur automatic journal untuk purchase retur sudah tersedia
                        $qAutJournal = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>15,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qAutJournal){
                            // hutang
                            $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>15,
                                'branch_id'=>$branch_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                            ->first();
                            // inventory
                            $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>15,
                                'branch_id'=>$branch_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                            ->first();
                            // ppn masukan
                            $qAutJournal_ppn_masukan = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>15,
                                'branch_id'=>$branch_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                            ->first();

                            // cek apakah module sudah pernah dibuat
                            $insJournal = [];
                            $qJournals = Tx_general_journal::where([
                                'module_no'=>$qCheck->purchase_retur_no,
                                'automatic_journal_id'=>15,
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
                                $yearTemp = substr($journal_date[0],2,2);
                                $monthTemp = (strlen($journal_date[1])==1?'0'.$journal_date[1]:$journal_date[1]);
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
                                        // jika bulan di server sudah berganti
                                        
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
                                $zero = '';
                                for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                    $zero .= '0';
                                }
                                $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                                // buat jurnal
                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$qCheck->purchase_retur_date,
                                    // 'general_journal_date'=>date("Y-m-d"),
                                    'total_debit'=>(($is_vat=='N' && $journal_type_id=='P')?$qCheck->total_before_vat:$qCheck->total_after_vat),
                                    'total_kredit'=>(($is_vat=='N' && $journal_type_id=='P')?$qCheck->total_before_vat:$qCheck->total_after_vat),
                                    'module_no'=>$qCheck->purchase_retur_no,
                                    'automatic_journal_id'=>15,
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
                                'description'=>null,
                                'debit'=>(($is_vat=='N' && $journal_type_id=='P')?$qCheck->total_before_vat:$qCheck->total_after_vat),
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
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$qCheck->total_before_vat,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                            // ppn_masukan
                            $ins_ppn_masukan = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>(($is_vat=='N' && $journal_type_id=='P')?0:($qCheck->total_after_vat-$qCheck->total_before_vat)),
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }
                    if ($is_vat=='N' && ($journal_type_id=='N' || $journal_type_id=='' || $journal_type_id==null)){
                        // cek apakah fitur automatic journal untuk purchase retur sudah tersedia
                        $qAutJournal = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>16,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qAutJournal){
                            // hutang
                            $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>16,
                                'branch_id'=>$branch_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'hutang\'')
                            ->first();
                            // inventory
                            $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                                'auto_journal_id'=>16,
                                'branch_id'=>$branch_id,
                                'active'=>'Y',
                            ])
                            ->whereRaw('LOWER(`desc`)=\'inventory\'')
                            ->first();

                            // cek apakah module sudah pernah dibuat
                            $insJournal = [];
                            $qJournals = Tx_lokal_journal::where([
                                'module_no'=>$qCheck->purchase_retur_no,
                                'automatic_journal_id'=>16,
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
                                $identityName = 'tx_lokal_journal';
                                $newInc = 1;
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
                                $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                                // buat jurnal
                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$qCheck->purchase_retur_date,
                                    // 'general_journal_date'=>date("Y-m-d"),
                                    'total_debit'=>$qCheck->total_before_vat,
                                    'total_kredit'=>$qCheck->total_before_vat,
                                    'module_no'=>$qCheck->purchase_retur_no,
                                    'automatic_journal_id'=>16,
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
                                'description'=>null,
                                'debit'=>$qCheck->total_before_vat,
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
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$qCheck->total_before_vat,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }
                }
            }
            if($request->order_appr == 'R'){
                $qCheck = Tx_purchase_retur::where('id','=',$id)
                ->where('approved_by','=',null)
                ->where('canceled_by','=',null)
                ->first();
                if($qCheck){
                    $upd = Tx_purchase_retur::where('id','=',$id)
                    ->where('canceled_by','=',null)
                    ->update([
                        'approved_by' => null,
                        'approved_at' => null,
                        'canceled_by' => Auth::user()->id,
                        'canceled_at' => now()
                    ]);

                    // cari RO terkait
                    $is_vat = '';
                    $branch_id = '';
                    $qRo = Tx_receipt_order::where([
                        'id'=>$qCheck->receipt_order_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qRo){
                        $pOmOs = explode(",",$qRo->po_or_pm_no);
                        foreach($pOmOs as $pOmO){
                            $no = $pOmO;
                            if (strpos("q-".$no,env('P_PURCHASE_MEMO'))>0){
                                $qMo = Tx_purchase_memo::where([
                                    'memo_no'=>$no,
                                    'active'=>'Y',
                                ])
                                ->first();
                                if ($qMo){
                                    $is_vat = $qMo->is_vat;
                                    $branch_id = $qMo->branch_id;
                                    break;
                                }
                            }
                            if (strpos("q-".$no,env('P_PURCHASE_ORDER'))>0){
                                $qPo = Tx_purchase_order::where([
                                    'purchase_no'=>$no,
                                    'active'=>'Y',
                                ])
                                ->first();
                                if ($qPo){
                                    $is_vat = $qPo->is_vat;
                                    $branch_id = $qPo->branch_id;
                                    break;
                                }
                            }
                        }
                        if ($is_vat=='Y'){
                            $qJournals = Tx_general_journal::where([
                                'module_no'=>$qCheck->purchase_retur_no,
                                'automatic_journal_id'=>15,
                                'active'=>'Y',
                            ])
                            ->first();
                            if ($qJournals){
                                $upd1 = Tx_general_journal::where([
                                    'id'=>$qJournals->id,
                                ])
                                ->update([
                                    'active'=>'N',
                                ]);
                                $upd1 = Tx_general_journal_detail::where([
                                    'general_journal_id'=>$qJournals->id,
                                ])
                                ->update([
                                    'active'=>'N',
                                ]);
                            }
                        }
                        if ($is_vat=='N'){
                            $qJournals = Tx_lokal_journal::where([
                                'module_no'=>$qCheck->purchase_retur_no,
                                'automatic_journal_id'=>16,
                                'active'=>'Y',
                            ])
                            ->first();
                            if ($qJournals){
                                $upd1 = Tx_lokal_journal::where([
                                    'id'=>$qJournals->id,
                                ])
                                ->update([
                                    'active'=>'N',
                                ]);
                                $upd1 = Tx_lokal_journal_detail::where([
                                    'lokal_journal_id'=>$qJournals->id,
                                ])
                                ->update([
                                    'active'=>'N',
                                ]);
                            }
                        }
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
