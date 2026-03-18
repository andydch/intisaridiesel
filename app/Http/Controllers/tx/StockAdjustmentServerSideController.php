<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use App\Rules\ValidateQtyToOH;
use App\Models\Tx_purchase_order;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_stock_adjustment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\OutstandingSoSjHelper;
use App\Models\Tx_stock_adjustment_part;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_automatic_journal_detail;
use Illuminate\Validation\ValidationException;

class StockAdjustmentServerSideController extends Controller
{
    protected $title = 'Stock Adjustment';
    protected $folder = 'stock-adjustment';

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
            $query = Tx_stock_adjustment::leftJoin('userdetails AS usr','tx_stock_adjustments.created_by','=','usr.user_id')
            ->select(
                'tx_stock_adjustments.id AS tx_id',
                'tx_stock_adjustments.stock_adj_no',
                'tx_stock_adjustments.stock_adj_date',
                'tx_stock_adjustments.total',
                'tx_stock_adjustments.active as sa_active',
                'tx_stock_adjustments.created_by as createdby',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_stock_adjustments.created_at','DESC');

            return DataTables::of($query)
            ->filterColumn('stock_adj_date', function($q, $keyword) {
                $q->whereRaw('DATE_FORMAT(tx_stock_adjustments.stock_adj_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('stock_adj_date', function ($query) {
                return date_format(date_create($query->stock_adj_date),"d/m/Y");
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->sa_active=='Y'){
                    if (strpos($query->stock_adj_no,'Draft')>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment-print/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                if ($query->sa_active=='Y' && strpos($query->stock_adj_no,'Draft')==0){
                    return 'Created';
                }
                if ($query->sa_active=='Y' && strpos($query->stock_adj_no,'Draft')>0){
                    return 'Draft';
                }
                if ($query->sa_active=='N'){
                    return 'Canceled';
                }
            })
            ->rawColumns(['stock_adj_date','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
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
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $branch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $users = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'branch' => $branch,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            // 'parts' => $parts->get(),
            'users' => $users,
            'qCurrency' => $qCurrency,
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
            'menu_id' => 76,
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
            'branch_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty_adj_'.$i => ['required','numeric',new ValidateQtyToOH($request['oh_ori_'.$i])],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty_adj_'.$i.'.required' => 'The qty field is required',
                        'qty_adj_'.$i.'.numeric' => 'The qty field is must be numeric',
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
            $identityName = 'tx_stock_adjustments-draft';
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
                $stock_adj_no = env('P_STOCK_ADJUSTMENT').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_stock_adjustments';
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
                $stock_adj_no = env('P_STOCK_ADJUSTMENT').date('y').'-'.$zero.strval($newInc);
            }

            $stock_adj_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $ins = Tx_stock_adjustment::create([
                'stock_adj_no' => $stock_adj_no,
                'stock_adj_date' => $stock_adj_date,
                'branch_id' => $request->branch_id,
                'remark' => $request->remark,
                'total' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
                'created_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $total_all = 0;
            $total_adjustment = 0;
            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_id'.$iRowPart]){
                    $insPart = Tx_stock_adjustment_part::create([
                        'stock_adj_id' => $maxId,
                        'part_id' => $request['part_id'.$iRowPart],
                        'adjustment' => $request['qty_adj_'.$iRowPart],
                        'qty_oh' => $request['oh_ori_'.$iRowPart],
                        'qty_oh_adjustment' => ($request['qty_adj_'.$iRowPart]+$request['oh_ori_'.$iRowPart]),
                        'qty_so' => $request['so_ori_'.$iRowPart],
                        'avg_cost' => $request['avg_cost_ori_'.$iRowPart],
                        'total' => ($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]),
                        'notes' => $request['notes_'.$iRowPart],
                        'active' => 'Y',
                        'created_by'=> Auth::user()->id,
                        'updated_by'=> Auth::user()->id,
                    ]);

                    $total_all += ($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]);
                    $total_adjustment += $request['qty_adj_'.$iRowPart];

                    // update OH jika status CREATED
                    if(strpos($stock_adj_no,'Draft')==0){
                        // outstanding SO/Sj qty
                        $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_id'.$iRowPart]);

                        // OH
                        $sumOH = Tx_qty_part::where('part_id', '=', $request['part_id'.$iRowPart])
                        ->whereIn('branch_id', function($q) {
                            $q->select('id')
                            ->from('mst_branches')
                            ->where('active', '=', 'Y');
                        })
                        ->sum('qty');
                        $freeOH = ($sumOH-$qtySoSj>0?$sumOH-$qtySoSj:0);

                        $newAvgCost = 0;
                        $qPart = Mst_part::where('id','=',$request['part_id'.$iRowPart])
                        ->first();
                        if ($qPart){
                            $newAvgCost = (($freeOH*$qPart->avg_cost)+($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]))/($freeOH+$request['qty_adj_'.$iRowPart]);
                            $updPart = Mst_part::where('id','=',$request['part_id'.$iRowPart])
                            ->update([
                                'avg_cost' => $newAvgCost,
                                'final_cost' => ($freeOH+$request['qty_adj_'.$iRowPart])*$newAvgCost,
                                'updated_by'=> Auth::user()->id,
                            ]);
                        }

                        $qQtyOH = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$iRowPart],
                            'branch_id' => $request->branch_id,
                        ])
                        ->first();
                        if($qQtyOH){
                            $updQtyOH = Tx_qty_part::where([
                                'part_id' => $request['part_id'.$iRowPart],
                                'branch_id' => $request->branch_id,
                            ])
                            ->update([
                                'qty' => $qQtyOH->qty + $request['qty_adj_'.$iRowPart],
                                'updated_by'=> Auth::user()->id,
                            ]);
                        }
                    }
                }
            }

            $updStockAdj = Tx_stock_adjustment::where('id','=',$maxId)
            ->update([
                'total' => $total_all,
                'updated_by'=> Auth::user()->id,
            ]);

            $branch_id = $request->branch_id;
            // cek apakah fitur automatic journal untuk stock adjustment plus sudah tersedia
            $qAutJournalPlus = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>11,
                'branch_id'=>$branch_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournalPlus && $request->is_draft!='Y'){
                // inventory-plus
                $qAutJournal_inventory_plus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'inventory-plus\'')
                ->first();
                // cogs-plus
                $qAutJournal_cogs_plus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'cogs-plus\'')
                ->first();
                // cogs-minus
                $qAutJournal_cogs_minus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'cogs-minus\'')
                ->first();
                // inventory-minus
                $qAutJournal_inventory_minus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'inventory-minus\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_general_journal::where([
                    'module_no'=>$stock_adj_no,
                    'automatic_journal_id'=>11,
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
                    $identityName = 'tx_general_journal';
                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    $newInc = 1;
                    if ($autoInc) {
                        $date = date_format(date_create($autoInc->updated_at), "n");
                        if ((int)date("n") > (int)$date) {
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
                    $journal_no = env('P_GENERAL_JURNAL').date('y').date('m').$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_general_journal::create([
                        'general_journal_no'=>$journal_no,
                        // 'general_journal_date'=>date("Y-m-d"),
                        'general_journal_date'=>$stock_adj_date,
                        'total_debit'=>$total_all+$total_all,
                        'total_kredit'=>$total_all+$total_all,
                        'module_no'=>$stock_adj_no,
                        'automatic_journal_id'=>4,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                if ($total_adjustment>=0){
                    // inventory-plus
                    $ins_inventory_plus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory_plus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$total_all,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // cogs-plus
                    $ins_cogs_plus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs_plus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$total_all,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }else{
                    // cogs-minus
                    $ins_cogs_minus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs_minus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$total_all,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // inventory-minus
                    $ins_inventory_minus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory_minus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$total_all,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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

        $branch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $users = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $parts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC');

        $query = Tx_stock_adjustment::where('id','=',$id)
        ->first();
        if($query){
            $queryPart = Tx_stock_adjustment_part::where([
                'stock_adj_id' => $query->id,
                'active' => 'Y'
            ]);
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'branch' => $branch,
                'qCurrency' => $qCurrency,
                'queryStock' => $query,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'queryStockPart' => $queryPart->get(),
                'users' => $users,
                'parts' => $parts->get(),
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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

        $branch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $users = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_stock_adjustment::where('id','=',$id)
        ->first();
        if($query){
            $queryPart = Tx_stock_adjustment_part::where([
                'stock_adj_id' => $query->id,
                'active' => 'Y'
            ]);
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'branch' => $branch,
                'qCurrency' => $qCurrency,
                'queryStock' => $query,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'queryStockPart' => $queryPart->get(),
                'users' => $users,
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 76,
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
            'branch_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'part_id'.$i => 'required|numeric',
                        'qty_adj_'.$i => ['required','numeric',new ValidateQtyToOH($request['oh_ori_'.$i])],
                    ];
                    $errShipmentMsg = [
                        'part_id'.$i.'.numeric' => 'Please select a valid part',
                        'qty_adj_'.$i.'.required' => 'The qty field is required',
                        'qty_adj_'.$i.'.numeric' => 'The qty field must be numeric',
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
            $stock_adj_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            $draft = false;
            $stocks = Tx_stock_adjustment::where('id', '=', $id)
            ->where('stock_adj_no','LIKE','%Draft%')
            ->first();
            if($stocks){
                // looking for draft order no
                $draft = true;
                $stock_adj_no = $stocks->stock_adj_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created
                $identityName = 'tx_stock_adjustments';
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
                $stock_adj_no = env('P_STOCK_ADJUSTMENT').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_stock_adjustment::where('id', '=', $id)
                ->update([
                    'stock_adj_no' => $stock_adj_no,
                    'stock_adj_date' => $stock_adj_date,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id,
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_stock_adjustment::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id,
                ]);
            }

            $upd = Tx_stock_adjustment::where('id','=',$id)
            ->update([
                'branch_id' => $request->branch_id,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            $updPart = Tx_stock_adjustment_part::where('stock_adj_id','=',$id)
            ->update([
                'active' => 'N',
                'updated_by'=> Auth::user()->id,
            ]);

            $total_all = 0;
            $total_adjustment = 0;
            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_id'.$iRowPart]){
                    $checkPart = Tx_stock_adjustment_part::where('id','=',$request['adj_part_id_'.$iRowPart])
                    ->first();
                    if($checkPart){
                        $updPart = Tx_stock_adjustment_part::where('id','=',$request['adj_part_id_'.$iRowPart])
                        ->update([
                            'part_id' => $request['part_id'.$iRowPart],
                            'adjustment' => $request['qty_adj_'.$iRowPart],
                            'qty_oh' => $request['oh_ori_'.$iRowPart],
                            'qty_oh_adjustment' => ($request['qty_adj_'.$iRowPart]+$request['oh_ori_'.$iRowPart]),
                            'qty_so' => $request['so_ori_'.$iRowPart],
                            'avg_cost' => $request['avg_cost_ori_'.$iRowPart],
                            'total' => ($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]),
                            'notes' => $request['notes_'.$iRowPart],
                            'active' => 'Y',
                            'updated_by'=> Auth::user()->id,
                        ]);
                    }else{
                        $insPart = Tx_stock_adjustment_part::create([
                            'stock_adj_id' => $id,
                            'part_id' => $request['part_id'.$iRowPart],
                            'adjustment' => $request['qty_adj_'.$iRowPart],
                            'qty_oh' => $request['oh_ori_'.$iRowPart],
                            'qty_oh_adjustment' => ($request['qty_adj_'.$iRowPart]+$request['oh_ori_'.$iRowPart]),
                            'qty_so' => $request['so_ori_'.$iRowPart],
                            'avg_cost' => $request['avg_cost_ori_'.$iRowPart],
                            'total' => ($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]),
                            'notes' => $request['notes_'.$iRowPart],
                            'active' => 'Y',
                            'created_by'=> Auth::user()->id,
                            'updated_by'=> Auth::user()->id,
                        ]);
                    }

                    $total_all += ($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]);
                    $total_adjustment += $request['qty_adj_'.$iRowPart];

                    // update OH jika status CREATED
                    if(strpos($stock_adj_no,'Draft')==0){
                        // outstanding SO/Sj qty
                        $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_id'.$iRowPart]);

                        // OH
                        $sumOH = Tx_qty_part::where('part_id', '=', $request['part_id'.$iRowPart])
                        ->whereIn('branch_id', function($q) {
                            $q->select('id')
                            ->from('mst_branches')
                            ->where('active', '=', 'Y');
                        })
                        ->sum('qty');
                        $freeOH = ($sumOH-$qtySoSj>0?$sumOH-$qtySoSj:0);

                        $newAvgCost = 0;
                        $qPart = Mst_part::where('id','=',$request['part_id'.$iRowPart])
                        ->first();
                        if ($qPart){
                            $newAvgCost = (($freeOH*$qPart->avg_cost)+($request['qty_adj_'.$iRowPart]*$request['avg_cost_ori_'.$iRowPart]))/($freeOH+$request['qty_adj_'.$iRowPart]);
                            $updPart = Mst_part::where('id','=',$request['part_id'.$iRowPart])
                            ->update([
                                'avg_cost' => $newAvgCost,
                                'final_cost' => ($freeOH+$request['qty_adj_'.$iRowPart])*$newAvgCost,
                                'updated_by'=> Auth::user()->id,
                            ]);
                        }
                        
                        $qQtyOH = Tx_qty_part::where([
                            'part_id' => $request['part_id'.$iRowPart],
                            'branch_id' => $request->branch_id,
                        ])
                        ->first();
                        if($qQtyOH){
                            $updQtyOH = Tx_qty_part::where([
                                'part_id' => $request['part_id'.$iRowPart],
                                'branch_id' => $request->branch_id,
                            ])
                            ->update([
                                'qty' => $qQtyOH->qty + $request['qty_adj_'.$iRowPart],
                                'updated_by'=> Auth::user()->id,
                            ]);
                        }
                    }
                }
            }

            $updStockAdj = Tx_stock_adjustment::where('id','=',$id)
            ->update([
                'total' => $total_all,
                'updated_by'=> Auth::user()->id,
            ]);

            $branch_id = $request->branch_id;
            // cek apakah fitur automatic journal untuk stock adjustment plus sudah tersedia
            $qAutJournalPlus = Mst_automatic_journal_detail::where([
                'auto_journal_id'=>11,
                'branch_id'=>$branch_id,
                'active'=>'Y',
            ])
            ->first();
            if ($qAutJournalPlus && $request->is_draft!='Y'){
                // inventory-plus
                $qAutJournal_inventory_plus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'inventory-plus\'')
                ->first();
                // cogs-plus
                $qAutJournal_cogs_plus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'cogs-plus\'')
                ->first();
                // cogs-minus
                $qAutJournal_cogs_minus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'cogs-minus\'')
                ->first();
                // inventory-minus
                $qAutJournal_inventory_minus = Mst_automatic_journal_detail::where([
                    'auto_journal_id'=>11,
                    'branch_id'=>$branch_id,
                    'active'=>'Y',
                ])
                ->whereRaw('LOWER(`desc`)=\'inventory-minus\'')
                ->first();

                // cek apakah module sudah pernah dibuat
                $insJournal = [];
                $qJournals = Tx_general_journal::where([
                    'module_no'=>$stock_adj_no,
                    'automatic_journal_id'=>11,
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

                    // ubah jurnal
                    $insJournal = Tx_general_journal::where('general_journal_id','=',$qJournals->id)
                    ->update([
                        'total_debit'=>$total_all+$total_all,
                        'total_kredit'=>$total_all+$total_all,
                        'module_no'=>$stock_adj_no,
                        'automatic_journal_id'=>11,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }else{
                    $identityName = 'tx_general_journal';
                    $autoInc = Auto_inc::where([
                        'identity_name' => $identityName
                    ])
                    ->first();
                    $newInc = 1;
                    if ($autoInc) {
                        $date = date_format(date_create($autoInc->updated_at), "n");
                        if ((int)date("n") > (int)$date) {
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
                    $journal_no = env('P_GENERAL_JURNAL').date('y').date('m').$zero.strval($newInc);

                    // buat jurnal
                    $insJournal = Tx_general_journal::create([
                        'general_journal_no'=>$journal_no,
                        // 'general_journal_date'=>date("Y-m-d"),
                        'general_journal_date'=>$stock_adj_date,
                        'total_debit'=>$total_all+$total_all,
                        'total_kredit'=>$total_all+$total_all,
                        'module_no'=>$stock_adj_no,
                        'automatic_journal_id'=>11,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }

                if ($total_adjustment>=0){
                    // inventory-plus
                    $ins_inventory_plus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory_plus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$total_all,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // cogs-plus
                    $ins_cogs_plus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs_plus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$total_all,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);
                }else{
                    // cogs-minus
                    $ins_cogs_minus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_cogs_minus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>$total_all,
                        'kredit'=>0,
                        'active'=>'Y',
                        'created_by'=>Auth::user()->id,
                        'updated_by'=>Auth::user()->id,
                    ]);

                    // inventory-minus
                    $ins_inventory_minus = Tx_general_journal_detail::create([
                        'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        'coa_id'=>$qAutJournal_inventory_minus->coa_code_id,
                        'coa_detail_id'=>null,
                        'description'=>null,
                        'debit'=>0,
                        'kredit'=>$total_all,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
