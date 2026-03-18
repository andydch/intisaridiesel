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
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_stock_transfer_part;
use App\Models\Mst_menu_user;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_surat_jalan_part;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class StockTransferApprovalServerSideController extends Controller
{
    protected $title = 'Stock Transfer - Approval';
    protected $folder = 'stock-transfer-approval';

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
            ->leftJoin('mst_branches as msb_from','tx_stock_transfers.branch_from_id','=','msb_from.id')
            ->leftJoin('mst_branches as msb_to','tx_stock_transfers.branch_to_id','=','msb_to.id')
            ->select(
                'tx_stock_transfers.id AS tx_id',
                'tx_stock_transfers.stock_transfer_no',
                'tx_stock_transfers.approved_by',
                'tx_stock_transfers.approved_at',
                'tx_stock_transfers.canceled_by',
                'tx_stock_transfers.canceled_at',
                'tx_stock_transfers.active as st_active',
                'msb_from.name as branch_from_name',
                'msb_to.name as branch_to_name',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
            )
            ->selectRaw('DATE_FORMAT(tx_stock_transfers.stock_transfer_date, "%d/%m/%Y") as stock_transfer_date')
            ->where('tx_stock_transfers.stock_transfer_no','NOT LIKE','%Draft%')
            ->where('tx_stock_transfers.active','=','Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->whereRaw('tx_stock_transfers.approved_at IS null AND tx_stock_transfers.canceled_at IS null')
            ->orderBy('tx_stock_transfers.stock_transfer_date','DESC');

            return DataTables::of($query)
            ->addColumn('stock_transfer_no_wlink', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-approval/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
            })
            ->addColumn('status', function ($query) {
                if (!is_null($query->approved_by) && $query->st_active=='Y'){
                    $qApproved = User::where('id','=',$query->approved_by)
                    ->first();
                    if($qApproved){
                        return 'Approved at '.
                            date_format(date_add(date_create($query->approved_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.$qApproved->name;
                    }
                }
                if (!is_null($query->canceled_by) && $query->st_active=='Y'){
                    $qRejected = User::where('id','=',$query->canceled_by)
                    ->first();
                    if($qRejected){
                        return 'Rejected at '.
                            date_format(date_add(date_create($query->canceled_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.$qRejected->name;
                    }
                }
                if (is_null($query->approved_by) && is_null($query->canceled_by) && $query->st_active=='Y'){
                    return 'Waiting for Approval';
                }
                return '';
            })
            ->rawColumns(['stock_transfer_no_wlink','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 43,
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
                $valPart = Tx_stock_transfer_part::leftJoin('tx_stock_transfers','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock_transfers.id')
                ->leftJoin('mst_parts','tx_stock_transfer_parts.part_id','=','mst_parts.id')
                ->select(
                    'tx_stock_transfer_parts.id as tx_stp_id',
                    'tx_stock_transfer_parts.part_id',
                    'tx_stock_transfer_parts.qty',
                    'tx_stock_transfers.branch_from_id as branch_id',
                    'tx_stock_transfers.branch_to_id',
                    'mst_parts.part_number',
                    'mst_parts.part_name',
                )
                ->where('tx_stock_transfer_parts.stock_transfer_id','=',$id)
                ->where('tx_stock_transfer_parts.active', '=', 'Y')
                ->get();
                foreach($valPart as $v){
                    $qtySJ = Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txsj','tx_surat_jalan_parts.surat_jalan_id', '=', 'txsj.id')
                    ->whereNotIn('txsj.id',function ($query) {
                        $query->select('tx_do_parts.sales_order_id')
                        ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                        ->leftJoin('tx_delivery_order_non_taxes as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where([
                            'tx_do_parts.active' => 'Y',
                            'tx_do.active' => 'Y',
                        ]);
                    })
                    ->whereRaw('txsj.surat_jalan_no NOT LIKE \'%Draft%\'')
                    ->where([
                        'tx_surat_jalan_parts.part_id' => $v->part_id,
                        'tx_surat_jalan_parts.active' => 'Y',
                        // 'txsj.need_approval'=>'N',
                        'txsj.branch_id' => $v->branch_id,
                        'txsj.active' => 'Y',
                    ])
                    ->sum('tx_surat_jalan_parts.qty');

                    $qtySO = Tx_sales_order_part::leftJoin('tx_sales_orders AS txso', 'tx_sales_order_parts.order_id', '=', 'txso.id')
                    ->whereNotIn('txso.id',function ($query) {
                        $query->select('tx_do_parts.sales_order_id')
                        ->from('tx_delivery_order_parts as tx_do_parts')
                        ->leftJoin('tx_delivery_orders as tx_do', 'tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where([
                            'tx_do_parts.active' => 'Y',
                            'tx_do.active' => 'Y',
                        ]);
                    })
                    ->whereRaw('txso.sales_order_no NOT LIKE \'%Draft%\'')
                    ->where([
                        'tx_sales_order_parts.part_id' => $v->part_id,
                        'tx_sales_order_parts.active' => 'Y',
                        // 'txso.need_approval'=>'N',
                        'txso.branch_id' => $v->branch_id,
                        'txso.active' => 'Y',
                    ])
                    ->sum('tx_sales_order_parts.qty');

                    $qQty = Tx_qty_part::where([
                        'part_id' => $v->part_id,
                        'branch_id' => $v->branch_id,
                    ])
                    ->first();
                    if($qQty){
                        if(($qQty->qty-($qtySJ+$qtySO))<$v->qty){
                            return back()->withErrors([
                                'order_appr' => 'The quantity of parts<br/>-Part Name: '.$v->part_name.'<br/>-Part Number: '.$v->part_number.
                                    '<br/>Qty:'.$v->qty.'<br/>to be transferred is greater '.
                                    'than the quantity in the original warehouse('.($qQty->qty-($qtySJ+$qtySO)).').',
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

                    // simpan avg terbaru
                    $last_avg_cost = 0;
                    $qPart = Mst_part::where('id', '=', $v->part_id)
                    ->first();
                    if ($qPart){
                        $last_avg_cost = $qPart->avg_cost;

                        $updAvg = Tx_stock_transfer_part::where('id','=',$v->tx_stp_id)
                        ->update([
                            'last_avg_cost' => $last_avg_cost,
                            'updated_by'=> Auth::user()->id,
                        ]);
                    }
                }
                $upd = Tx_stock_transfer::where('id','=',$id)
                ->where('approved_by','=',null)
                ->update([
                    'stock_transfer_date' => date_format(date_add(date_create(date("Y-m-d")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
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
                            'tx_stock_transfer_parts.id as tx_stp_id',
                            'tx_stock_transfer_parts.part_id',
                            'tx_stock_transfer_parts.qty',
                            'tx_stock_transfers.branch_from_id as branch_id',
                            'mst_parts.part_number',
                            'mst_parts.part_name',
                        )
                        ->where('tx_stock_transfer_parts.stock_transfer_id','=',$id)
                        ->where('tx_stock_transfer_parts.active', '=', 'Y')
                        ->get();
                        foreach($valPart as $v){
                            // kembalikan jumlah yg berkurang di gudang awal
                            // $qQty = Tx_qty_part::where([
                            //     'part_id' => $v->part_id,
                            //     'branch_id' => $v->branch_id
                            // ])
                            // ->first();
                            // if($qQty){
                            //     // kembalikan OH
                            //     $updQty = Tx_qty_part::where([
                            //         'part_id' => $v->part_id,
                            //         'branch_id' => $v->branch_id
                            //     ])
                            //     ->update([
                            //         'qty' => $qQty->qty+$v->qty,
                            //         'updated_by' => Auth::user()->id,
                            //     ]);
                            // }

                            // simpan avg terbaru
                            $last_avg_cost = 0;
                            $qPart = Mst_part::where('id', '=', $v->part_id)
                            ->first();
                            if ($qPart){
                                $last_avg_cost = $qPart->avg_cost;

                                $updAvg = Tx_stock_transfer_part::where('id','=',$v->tx_stp_id)
                                ->update([
                                    'last_avg_cost' => $last_avg_cost,
                                    'updated_by'=> Auth::user()->id,
                                ]);
                            }
                        }
                    }
                    $upd = Tx_stock_transfer::where('id','=',$id)
                    ->where('canceled_by','=',null)
                    ->update([
                        'stock_transfer_date' => date_format(date_add(date_create(date("Y-m-d")), date_interval_create_from_date_string((ENV("WAKTU_ID")??7)." hours")), "Y-m-d"),
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
