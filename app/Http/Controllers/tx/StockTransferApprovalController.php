<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Tx_stock_transfer;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_transfer_part;
use Illuminate\Validation\ValidationException;

class StockTransferApprovalController extends Controller
{
    protected $title = 'Stock Transfer - Approval';
    protected $folder = 'stock-transfer-approval';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $qStockTransfer = Tx_stock_transfer::leftJoin('userdetails AS usr','tx_stock_transfers.created_by','=','usr.user_id')
        ->select(
            'tx_stock_transfers.*',
            'tx_stock_transfers.id AS stock_transfer_id'
        )
        ->where('tx_stock_transfers.stock_transfer_no','NOT LIKE','%Draft%')
        ->where('tx_stock_transfers.active','=','Y')
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_stock_transfers.stock_transfer_no','DESC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qStockTransfer' => $qStockTransfer,
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
        ->where('active','=','Y');
        
        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qBranchFrom' => $qBranchFrom,
            'qBranchTo' => $qBranchTo,
            'totalRow' => (old('totalRow') ? old('totalRow') : $qStockPart->count()),
            'qPart' => $qParts,
            'qStock' => $qStock,
            'qStockPart' => $qStockPart->get(),
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
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $valPart = Tx_stock_transfer_part::leftJoin('tx_stock_transfers','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock_transfers.id')
                ->leftJoin('mst_parts','tx_stock_transfer_parts.part_id','=','mst_parts.id')
                ->select(
                    'tx_stock_transfer_parts.part_id',
                    'tx_stock_transfer_parts.qty',
                    'tx_stock_transfers.branch_from_id as branch_id',
                    'tx_stock_transfers.branch_to_id',
                    'mst_parts.part_number',
                    'mst_parts.part_name',
                )
                ->where('stock_transfer_id','=',$id)
                ->get();
                foreach($valPart as $v){
                    $qQty = Tx_qty_part::where([
                        'part_id' => $v->part_id,
                        'branch_id' => $v->branch_id
                    ])
                    ->first();
                    if($qQty){
                        if($qQty->qty<$v->qty){
                            return back()->withErrors([
                                'order_appr' => 'The quantity of parts<br/>-Part Name: '.$v->part_name.'<br/>-Part Number: '.$v->part_number.
                                    '<br/>Qty:'.$v->qty.'<br/>to be transferred is greater '.
                                    'than the quantity in the original warehouse('.$qQty->qty.').',
                            ])
                            ->onlyInput('order_appr');
                        }
                    }
                }

                foreach($valPart as $v){
                    // lakukan pengurangan part di gudang awal
                    $qQty = Tx_qty_part::where([
                        'part_id' => $v->part_id,
                        'branch_id' => $v->branch_id
                    ])
                    ->first();
                    if($qQty){
                        $updQty = Tx_qty_part::where([
                            'part_id' => $v->part_id,
                            'branch_id' => $v->branch_id
                        ])
                        ->update([
                            'qty' => $qQty->qty-$v->qty,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }

                    // cek apakah part sudah ada di qty
                    $qty = Tx_qty_part::where([
                        'part_id' => $v->part_id,
                        'branch_id' => $v->branch_to_id,
                    ])
                    ->first();
                    if(!$qty){
                        $ins = Tx_qty_part::create([
                            'part_id' => $v->part_id,
                            'qty' => 0,
                            'branch_id' => $v->branch_to_id,
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);
                    }
                }
                $upd = Tx_stock_transfer::where('id','=',$id)
                ->where('approved_by','=',null)
                ->update([
                    'approved_by' => Auth::user()->id,
                    'approved_at' => now(),
                    'canceled_by' => null,
                    'canceled_at' => null,
                    'updated_by' => Auth::user()->id,
                ]);
            }
            if($request->order_appr == 'R'){
                $val = Tx_stock_transfer::where('id','=',$id)->first();
                if($val){
                    if(!is_null($val->approved_by)){
                        $valPart = Tx_stock_transfer_part::leftJoin('tx_stock_transfers','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock_transfers.id')
                        ->leftJoin('mst_parts','tx_stock_transfer_parts.part_id','=','mst_parts.id')
                        ->select(
                            'tx_stock_transfer_parts.part_id',
                            'tx_stock_transfer_parts.qty',
                            'tx_stock_transfers.branch_from_id as branch_id',
                            'mst_parts.part_number',
                            'mst_parts.part_name',
                        )
                        ->where('stock_transfer_id','=',$id)
                        ->get();
                        foreach($valPart as $v){
                            // kembalikan jumlah yg berkurang di gudang awal
                            $qQty = Tx_qty_part::where([
                                'part_id' => $v->part_id,
                                'branch_id' => $v->branch_id
                            ])
                            ->first();
                            if($qQty){
                                $updQty = Tx_qty_part::where([
                                    'part_id' => $v->part_id,
                                    'branch_id' => $v->branch_id
                                ])
                                ->update([
                                    'qty' => $qQty->qty+$v->qty,
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }
                    }
                    $upd = Tx_stock_transfer::where('id','=',$id)
                    ->where('canceled_by','=',null)
                    ->update([
                        'approved_by' => null,
                        'approved_at' => null,
                        'canceled_by' => Auth::user()->id,
                        'canceled_at' => now(),
                        'updated_by' => Auth::user()->id,
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
