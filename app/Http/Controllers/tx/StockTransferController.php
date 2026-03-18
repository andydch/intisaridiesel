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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockTransferController extends Controller
{
    protected $title = 'Stock Transfer';
    protected $folder = 'stock-transfer';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if($userLogin->is_director=='Y'){
            $qStockTransfer = Tx_stock_transfer::leftJoin('userdetails AS usr','tx_stock_transfers.created_by','=','usr.user_id')
            ->select(
                'tx_stock_transfers.*',
                'tx_stock_transfers.id AS stock_transfer_id',
            )
            // ->where('tx_stock_transfers.active','=','Y')
            ->where(function($q){
                $q->where('tx_stock_transfers.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_stock_transfers.active','N')
                    ->where('tx_stock_transfers.stock_transfer_no','NOT LIKE','%Draft%');
                });
            })
            ->orderBy('tx_stock_transfers.created_at','DESC');
        }else{
            $qStockTransfer = Tx_stock_transfer::leftJoin('userdetails AS usr','tx_stock_transfers.created_by','=','usr.user_id')
            ->select(
                'tx_stock_transfers.*',
                'tx_stock_transfers.id AS stock_transfer_id',
            )
            // ->where('tx_stock_transfers.active','=','Y')
            ->where(function($q){
                $q->where('tx_stock_transfers.active', 'Y')
                ->orWhere(function($s){
                    $s->where('tx_stock_transfers.active','N')
                    ->where('tx_stock_transfers.stock_transfer_no','NOT LIKE','%Draft%');
                });
            })
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_stock_transfers.created_at','DESC');
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qStockTransfer' => $qStockTransfer->get(),
            'qStockTransferCount' => $qStockTransfer->count(),
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

        $qBranchFrom = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $qBranchTo = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $qParts = Mst_part::where('active','=','Y')
            ->orderBy('part_number','ASC')
            ->get();
        $users = Userdetail::where('user_id','=',Auth::user()->id)->first();
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qBranchFrom' => $qBranchFrom,
            'qBranchTo' => $qBranchTo,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'qPart' => $qParts,
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
                        'qty'.$i => ['required','numeric',new ValidateQty($request['part_no_'.$i],$request->branch_from_id)],
                    ];
                    $errShipmentMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
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
                for ($i = 0; $i < (4 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $stock_no = env('P_STOCK_TRANSFER').date('y').'-'.$zero.strval($newInc);
            }

            $ins = Tx_stock_transfer::create([
                'stock_transfer_no' => $stock_no,
                'stock_transfer_date' => date("Y-m-d"),
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

            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_no_'.$iRowPart]){
                    $insPart = Tx_stock_transfer_part::create([
                        'stock_transfer_id' => $maxId,
                        'part_id' => $request['part_no_'.$iRowPart],
                        'qty' => $request['qty'.$iRowPart],
                        'active' => 'Y',
                        'created_by'=> Auth::user()->id,
                        'updated_by'=> Auth::user()->id,
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
        $qParts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC')
        ->get();
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
            'qPart' => $qParts,
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
                        'qty'.$i => ['required','numeric',new ValidateQty($request['part_no_'.$i],$request->branch_from_id)],
                    ];
                    $errShipmentMsg = [
                        'part_no_'.$i.'.numeric' => 'Please select a valid part',
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field is must be numeric',
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
                for ($i = 0; $i < (4 - strlen(strval($newInc))); $i++) {
                    $zero .= '0';
                }
                $stock_transfer_no = env('P_STOCK_TRANSFER') . date('y') . '-' . $zero . strval($newInc);

                $upd = Tx_stock_transfer::where('id', '=', $id)
                ->update([
                    'stock_transfer_no' => $stock_transfer_no,
                    'stock_transfer_date' => date("Y-m-d"),
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

            for($iRowPart=0;$iRowPart<$request->totalRow;$iRowPart++){
                if($request['part_no_'.$iRowPart]){
                    $checkPart = Tx_stock_transfer_part::where('id','=',$request['part_row_id'.$iRowPart])->first();
                    if($checkPart){
                        $updPart = Tx_stock_transfer_part::where('id','=',$request['part_row_id'.$iRowPart])
                        ->update([
                            'stock_transfer_id' => $id,
                            'part_id' => $request['part_no_'.$iRowPart],
                            'qty' => $request['qty'.$iRowPart],
                            'active' => 'Y',
                            'updated_by'=> Auth::user()->id,
                        ]);
                    }else{
                        $insPart = Tx_stock_transfer_part::create([
                            'stock_transfer_id' => $id,
                            'part_id' => $request['part_no_'.$iRowPart],
                            'qty' => $request['qty'.$iRowPart],
                            'active' => 'Y',
                            'created_by'=> Auth::user()->id,
                            'updated_by'=> Auth::user()->id,
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
