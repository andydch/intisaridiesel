<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Userdetail;
use App\Rules\ValidateQty;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use App\Models\Tx_stock_transfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_transfer_part;
use App\Models\User;
use App\Models\Mst_menu_user;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class StockTransferServerSideController extends Controller
{
    protected $title = 'Stock Transfer';
    protected $folder = 'stock-transfer';

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
            ->where(function($q){
                $q->where('tx_stock_transfers.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_stock_transfers.active','N')
                    ->where('tx_stock_transfers.stock_transfer_no','NOT LIKE','%Draft%');
                });
            })
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->orderBy('tx_stock_transfers.is_draft','DESC')
            ->orderBy('tx_stock_transfers.stock_transfer_no','DESC');

            return DataTables::of($query)
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
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->st_active=='Y'){
                    if (is_null($query->approved_by)){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-print/'.urlencode($query->stock_transfer_no)).'" target="_new" style="text-decoration: underline;">Print</a> |
                            <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-print/'.urlencode($query->stock_transfer_no)).'" style="text-decoration: underline;">Download</a>';
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
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
            ->rawColumns(['stock_transfer_date','branch_from_name','branch_to_name','received_by_name','action','status'])
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
        // ini_set('memory_limit', '128M');
        // ini_set('max_execution_time', 1800);

        $qBranchFrom = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $qBranchTo = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $users = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qBranchFrom' => $qBranchFrom,
            'qBranchTo' => $qBranchTo,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            // 'qPart' => $qParts,
            'users' => $users
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
            'menu_id' => 42,
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
            'branch_from_id' => 'required|numeric|different:branch_to_id',
            'branch_to_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_from_id.numeric' => 'Please select a valid from branch',
            'branch_to_id.numeric' => 'Please select a valid to branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validateShipmentInput = [
                        'part_no_'.$i => 'required|numeric',
                        'qty'.$i => ['required','numeric', 'min:1', new ValidateQty($request['part_no_'.$i],$request->branch_from_id)],
                    ];
                    $errShipmentMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty field is must be at least 1',
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
            $identityName = 'tx_stock_transfers-draft';
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
                $stock_no = env('P_STOCK_TRANSFER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_stock_transfers';
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
                $stock_no = env('P_STOCK_TRANSFER').date('y').'-'.$zero.strval($newInc);
            }

            $ins = Tx_stock_transfer::create([
                'stock_transfer_no' => $stock_no,
                // 'stock_transfer_date' => date("Y-m-d"),
                'branch_from_id' => $request->branch_from_id,
                'branch_to_id' => $request->branch_to_id,
                'remark' => $request->remark_txt,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
                'created_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $isThereSomePart = 0;
            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_no_'.$iRowPart]){
                    $isThereSomePart++;

                    $last_avg_cost = 0;
                    $qPart = Mst_part::where('id', '=', $request['part_no_'.$iRowPart])
                    ->first();
                    if ($qPart){
                        $last_avg_cost = $qPart->avg_cost;
                    }

                    $insPart = Tx_stock_transfer_part::create([
                        'stock_transfer_id' => $maxId,
                        'part_id' => $request['part_no_'.$iRowPart],
                        'qty' => $request['qty'.$iRowPart],
                        'last_avg_cost' => $last_avg_cost,
                        'active' => 'Y',
                        'created_by'=> Auth::user()->id,
                        'updated_by'=> Auth::user()->id,
                    ]);
                }
            }

            if ($isThereSomePart<1){
                DB::rollback();

                return redirect()
                ->back()
                ->withInput()
                ->with('status-error', ENV('ERR_MSG_04')?ENV('ERR_MSG_04'):'No spare part selected!');
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
        $qBranchFrom = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $qBranchTo = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $qParts = Mst_part::where('active','=','Y')
            ->orderBy('part_name','ASC')
            ->get();
        $users = Userdetail::where('user_id','=',Auth::user()->id)->first();
        $qStock = Tx_stock_transfer::where('id','=',$id)->first();
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
            'users' => $users,
            'qStock' => $qStock,
            'qStockPart' => $qStockPart
        ];

        return view('tx.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        $qBranchFrom = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $qBranchTo = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        // $qParts = Mst_part::where('active','=','Y')
        // ->orderBy('part_name','ASC')
        // ->get();

        $users = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        $qStock = Tx_stock_transfer::where('id','=',$id)->first();
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
            // 'qPart' => $qParts,
            'users' => $users,
            'qStock' => $qStock,
            'qStockPart' => $qStockPart
        ];

        return view('tx.'.$this->folder.'.edit', $data);
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 42,
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

        $qApproveVal = Tx_stock_transfer::where('id', '=', $id)
        ->where('approved_by','IS NOT',null)
        ->first();
        if($qApproveVal){
            return redirect()
            ->back()
            ->withInput()
            ->with('status-error','You cannot change data if the Stock Transfer No has been approved.');
        }
        
        $validateInput = [
            'branch_from_id' => 'required|numeric|different:branch_to_id',
            'branch_to_id' => 'required|numeric',
        ];
        $errMsg = [
            'branch_from_id.numeric' => 'Please select a valid from branch',
            'branch_to_id.numeric' => 'Please select a valid to branch',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_no_'.$i]) {
                    $validateShipmentInput = [
                        'part_no_'.$i => 'required|numeric',
                        'qty'.$i => ['required','numeric', 'min:1', new ValidateQty($request['part_no_'.$i],$request->branch_from_id)],
                    ];
                    $errShipmentMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
                        'qty'.$i.'.min' => 'The qty field is must be at least 1',
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
            $stocks = Tx_stock_transfer::where('id', '=', $id)
            ->where('stock_transfer_no','LIKE','%Draft%')
            ->first();
            if($stocks){
                // looking for draft order no
                $draft = true;
                $stock_transfer_no = $stocks->stock_transfer_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created
                $identityName = 'tx_stock_transfers';
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
                $stock_transfer_no = env('P_STOCK_TRANSFER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_stock_transfer::where('id', '=', $id)
                ->update([
                    'stock_transfer_no' => $stock_transfer_no,
                    // 'stock_transfer_date' => date("Y-m-d"),
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_stock_transfer::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $upd = Tx_stock_transfer::where('id','=',$id)
                ->update([
                    'branch_from_id' => $request->branch_from_id,
                    'branch_to_id' => $request->branch_to_id,
                    'remark' => $request->remark_txt,
                    'active' => 'Y',
                    'updated_by' => Auth::user()->id,
                ]);

            $updPart = Tx_stock_transfer_part::where('stock_transfer_id','=',$id)
            ->update([
                'active' => 'N'
            ]);

            $isThereSomePart = 0;
            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_no_'.$iRowPart]){
                    $isThereSomePart++;

                    $last_avg_cost = 0;
                    $qPart = Mst_part::where('id', '=', $request['part_no_'.$iRowPart])
                    ->first();
                    if ($qPart){
                        $last_avg_cost = $qPart->avg_cost;
                    }

                    $checkPart = Tx_stock_transfer_part::where('id','=',$request['part_row_id'.$iRowPart])->first();
                    if($checkPart){
                        $updPart = Tx_stock_transfer_part::where('id','=',$request['part_row_id'.$iRowPart])
                        ->update([
                            'stock_transfer_id' => $id,
                            'part_id' => $request['part_no_'.$iRowPart],
                            'qty' => $request['qty'.$iRowPart],
                            'last_avg_cost' => $last_avg_cost,
                            'active' => 'Y',
                            'updated_by'=> Auth::user()->id,
                        ]);
                    }else{
                        $insPart = Tx_stock_transfer_part::create([
                            'stock_transfer_id' => $id,
                            'part_id' => $request['part_no_'.$iRowPart],
                            'qty' => $request['qty'.$iRowPart],
                            'last_avg_cost' => $last_avg_cost,
                            'active' => 'Y',
                            'created_by'=> Auth::user()->id,
                            'updated_by'=> Auth::user()->id,
                        ]);
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
