<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_part;
use App\Models\User;
use App\Models\Mst_branch;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Tx_stock_transfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Auto_inc;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Tx_general_journal;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_transfer_part;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class StockTransferReceivedServerSideController extends Controller
{
    protected $title = 'Stock Transfer - Received';
    protected $folder = 'stock-transfer-received';

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
            $query = Tx_stock_transfer::leftJoin('userdetails AS usr','tx_stock_transfers.created_by','=','usr.user_id')
            ->select(
                'tx_stock_transfers.id AS tx_id',
                'tx_stock_transfers.stock_transfer_no',
                'tx_stock_transfers.stock_transfer_date',
                'tx_stock_transfers.branch_from_id',
                'tx_stock_transfers.branch_to_id',
                'tx_stock_transfers.approved_by',
                'tx_stock_transfers.received_by',
                'tx_stock_transfers.active as st_active',
                'tx_stock_transfers.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->where('tx_stock_transfers.approved_by','<>',null)
            ->where('tx_stock_transfers.active','=','Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('tx_stock_transfers.branch_to_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_stock_transfers.stock_transfer_no','DESC');

            return DataTables::of($query)
            ->filterColumn('stock_transfer_link', function($q, $keyword) {
                $q->whereRaw('tx_stock_transfers.stock_transfer_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('stock_transfer_link', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-received/'.$query->tx_id).'" style="text-decoration: underline;">'.$query->stock_transfer_no.'</a>';
            })
            ->filterColumn('stock_transfer_date', function($q, $keyword) {
                $q->whereRaw('DATE_FORMAT(tx_stock_transfers.stock_transfer_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('stock_transfer_date', function ($query) {
                return date_format(date_create($query->stock_transfer_date),"d/m/Y");
            })
            ->addColumn('branch_from_name', function ($query) {
                $qBranch = Mst_branch::where('id','=',$query->branch_from_id)
                ->first();
                if($qBranch){
                    return $qBranch->name;
                }
                return '';
            })
            ->addColumn('branch_to_name', function ($query) {
                $qBranch = Mst_branch::where('id','=',$query->branch_to_id)
                ->first();
                if($qBranch){
                    return $qBranch->name;
                }
                return '';
            })
            ->addColumn('received_by_name', function ($query) {
                $qRc = User::where('id','=',$query->received_by)
                ->first();
                if($qRc){
                    return $qRc->name;
                }
                return '';
            })
            ->addColumn('status', function ($query) {
                if (!is_null($query->approved_by) && is_null($query->received_by) && $query->st_active=='Y'){
                    return 'Approved';
                }
                if (!is_null($query->canceled_by) && is_null($query->received_by) && $query->st_active=='Y'){
                    return 'Rejected';
                }
                if (!is_null($query->approved_by) && !is_null($query->received_by) && $query->st_active=='Y'){
                    return 'Received';
                }
                if (is_null($query->approved_by) && is_null($query->canceled_by) && $query->st_active=='Y' && strpos($query->stock_transfer_no,'Draft')==0){
                    return 'Waiting for Approval';
                }
                if (is_null($query->approved_by) && $query->st_active=='Y' && strpos($query->stock_transfer_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->st_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['stock_transfer_link','stock_transfer_date','branch_from_name','branch_to_name','received_by_name','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            // 'qStockTransfer' => $qStockTransfer,
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_stock_transfer  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qBranchFrom = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $qBranchTo = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $qParts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC')
        ->get();
        $qStock = Tx_stock_transfer::where('id','=',$id)
        ->first();
        $qStockPart = Tx_stock_transfer_part::where('stock_transfer_id','=',$id)
        ->where('active','=','Y')
        ->get();
        $qStockPartCount = Tx_stock_transfer_part::where('stock_transfer_id','=',$id)
        ->where('active','=','Y')
        ->count();
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qBranchFrom' => $qBranchFrom,
            'qBranchTo' => $qBranchTo,
            'totalRow' => (old('totalRow') ? old('totalRow') : $qStockPartCount),
            'qPart' => $qParts,
            'qStock' => $qStock,
            'qStockPart' => $qStockPart
        ];

        return view('tx.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_stock_transfer  $tx_purchase_order
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
     * @param  \App\Models\Tx_stock_transfer  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 44,
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

        $qPv = Tx_stock_transfer::where('id', '=', $id)
        ->first();
        if ($qPv){
            if (!is_null($qPv->received_by)){
                // karena proses transfer sudah dilakukan maka batalkan proses received nya
                session()->flash('status-error', 'The spare parts transfer process has been carried out.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }
        
        // Start transaction!
        DB::beginTransaction();

        try {

            $branch_from_id = '';
            $branch_to_id = '';
            $stock_transfer_no = '';
            $totalPrice = 0;

            $valPart = Tx_stock_transfer_part::leftJoin('tx_stock_transfers','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock_transfers.id')
            ->leftJoin('mst_parts','tx_stock_transfer_parts.part_id','=','mst_parts.id')
            ->select(
                'tx_stock_transfer_parts.id as tx_stp_id',
                'tx_stock_transfer_parts.part_id',
                'tx_stock_transfer_parts.qty',
                'tx_stock_transfers.branch_from_id',
                'tx_stock_transfers.branch_to_id',
                'tx_stock_transfers.stock_transfer_no',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.avg_cost',
            )
            ->where('tx_stock_transfer_parts.stock_transfer_id','=',$id)
            ->where('tx_stock_transfer_parts.active', '=', 'Y')
            ->get();
            foreach($valPart as $v){
                $branch_from_id = $v->branch_from_id;
                $branch_to_id = $v->branch_to_id;
                $stock_transfer_no = $v->stock_transfer_no;
                $totalPrice += ($v->avg_cost*$v->qty);

                // update OH di cabang tujuan
                $qQty = Tx_qty_part::where([
                    'part_id' => $v->part_id,
                    'branch_id' => $v->branch_to_id
                ])
                ->first();
                if($qQty){
                    // nambah OH
                    $updQty = Tx_qty_part::where([
                        'part_id' => $v->part_id,
                        'branch_id' => $v->branch_to_id
                    ])
                    ->update([
                        'qty' => $qQty->qty+$v->qty,
                        'updated_by' => Auth::user()->id,
                    ]);
                }else{
                    $updQty = Tx_qty_part::create([
                        'part_id' => $v->part_id,
                        'qty' => $v->qty,
                        'branch_id' => $v->branch_to_id,
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id,
                    ]);
                }

                // simpan avg terbaru
                $updAvg = Tx_stock_transfer_part::where('id','=',$v->tx_stp_id)
                ->update([
                    'last_avg_cost' => $v->avg_cost,
                    'updated_by'=> Auth::user()->id,
                ]);
            }

            $updStockMaster = Tx_stock_transfer::where('id','=',$id)
            ->update([
                'received_by' => Auth::user()->id,
                'received_at' => now(),
                'updated_by' => Auth::user()->id,
            ]);

            $journal_date = [];
            $qStockMaster = Tx_stock_transfer::where('id', '=', $id)
            ->first();
            if ($qStockMaster){
                $journal_date = explode("-", $qStockMaster->stock_transfer_date);


                // cek apakah fitur automatic journal untuk stock adjustment plus sudah tersedia
                $qAutJournal = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>12,
                    'branch_id'=>$branch_from_id,
                    'branch_in_id'=>$branch_to_id,
                    'active'=>'Y',
                ])
                ->first();
                if ($qAutJournal){
                    // transfer in
                    $qAutJournal_transfer_in = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>12,
                        'branch_id'=>$branch_from_id,
                        'branch_in_id'=>$branch_to_id,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'transfer in\'')
                    ->first();
                    // transfer out
                    $qAutJournal_transfer_out = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>12,
                        'branch_id'=>$branch_from_id,
                        'branch_in_id'=>$branch_to_id,
                        'active'=>'Y',
                    ])
                    ->whereRaw('LOWER(`desc`)=\'transfer out\'')
                    ->first();
    
                    // cek apakah module sudah pernah dibuat
                    $insJournal = [];
                    $qJournals = Tx_general_journal::where([
                        'module_no'=>$stock_transfer_no,
                        'automatic_journal_id'=>12,
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
                            // 'general_journal_date'=>date("Y-m-d"),
                            'general_journal_date'=>$journal_date[0].'-'.$journal_date[1].'-'.$journal_date[2],
                            'total_debit'=>$totalPrice,
                            'total_kredit'=>$totalPrice,
                            'module_no'=>$stock_transfer_no,
                            'automatic_journal_id'=>12,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }
    
                    // transfer in
                    $ins_transfer_in = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_transfer_in->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$totalPrice,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
    
                    // transfer out
                    $ins_transfer_out = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_transfer_out->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$totalPrice,
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

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME') . '/' . $this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_stock_transfer  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
