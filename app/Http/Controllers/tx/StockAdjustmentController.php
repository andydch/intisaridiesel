<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Mst_branch;
use App\Models\Userdetail;
// use App\Rules\ValidateQty;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use App\Models\Tx_stock_adjustment;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_qty_part;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_adjustment_part;
use App\Rules\ValidateQtyToOH;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    protected $title = 'Stock Adjustment';
    protected $folder = 'stock-adjustment';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $qStockAdj = Tx_stock_adjustment::leftJoin('userdetails AS usr','tx_stock_adjustments.created_by','=','usr.user_id')
        ->select(
            'tx_stock_adjustments.*',
            'tx_stock_adjustments.id AS stock_transfer_id',
        )
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_stock_adjustments.created_at','DESC');

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qStockAdj' => $qStockAdj->get(),
            'qStockAdjCount' => $qStockAdj->count(),
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
            'qCurrency' => $qCurrency,
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
        ->orderBy('part_number','ASC');

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'branch' => $branch,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'parts' => $parts->get(),
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
        $validateInput = [
            'branch_id' => 'required|numeric',
            // 'remark' => 'required',
        ];
        $errMsg = [
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

            $ins = Tx_stock_adjustment::create([
                'stock_adj_no' => $stock_adj_no,
                'stock_adj_date' => date("Y-m-d"),
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

                    // update OH jika status CREATED
                    if(strpos($stock_adj_no,'Draft')==0){
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

        $parts = Mst_part::where('active','=','Y')
        ->orderBy('part_number','ASC');

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

        $validateInput = [
            'branch_id' => 'required|numeric',
            // 'remark' => 'required',
        ];
        $errMsg = [
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
                    'stock_adj_date' => date("Y-m-d"),
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

                    // update OH jika status CREATED
                    if(strpos($stock_adj_no,'Draft')==0){
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
