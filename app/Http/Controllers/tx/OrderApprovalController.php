<?php

namespace App\Http\Controllers\tx;

use Exception;
use Carbon\Carbon;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_company;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_order_part;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_supplier_bank_information;
use App\Models\Tx_purchase_order_oo_oh_part;
use Illuminate\Validation\ValidationException;

class OrderApprovalController extends Controller
{
    protected $title = 'Purchase Order - Approval';
    protected $folder = 'order-approval';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $query = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
        ->select('tx_purchase_orders.*')
        ->addSelect(['total_price' => Tx_purchase_order_part::selectRaw('SUM(qty*price)')
            ->whereColumn('tx_purchase_order_parts.order_id','tx_purchase_orders.id')
            ->where('tx_purchase_order_parts.active','=','Y')
        ])
        ->where([
            'tx_purchase_orders.active' => 'Y'
        ])
        ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
        // ->where('tx_purchase_orders.approved_by','=',null)
        ->where('tx_purchase_orders.active','=','Y')
        // ->where('usr.branch_id','=',$userLogin->branch_id)
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_purchase_orders.purchase_date', 'DESC')
        ->orderBy('tx_purchase_orders.created_at', 'DESC')
        ->get();

        $data = [
            'orders' => $query,
            'title' => $this->title,
            'folder' => $this->folder
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show(Tx_purchase_order $tx_purchase_order)
    {
        //
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
        $query = Tx_purchase_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();
            $suppliers = Mst_supplier::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $branches = Mst_branch::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $companies = Mst_company::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();
            if (old('supplier_id')) {
                $supplierPic = Mst_supplier::where([
                    'id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->get();
            } else {
                $supplierPic = Mst_supplier::where([
                    'id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('name', 'ASC')
                ->get();
                $currency = Mst_supplier_bank_information::where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->get();
            }
            $queryOrderPart = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->get();
            $queryOrderPartCount = Tx_purchase_order_part::where([
                'order_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'orders' => $query,
                'orderParts' => $queryOrderPart,
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'branches' => $branches,
                'companies' => $companies,
                'parts' => $parts,
                'currency' => $currency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryOrderPartCount),
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
            ];

            return view('tx.'.$this->folder.'.edit', $data);
        } else {
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
        $validateInput = [
            'reason' => 'required_if:order_appr,R|max:2048',
        ];
        $errMsg = [
            'reason.required_if' => 'The reason field is required when selected approval is Reject',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            // ambil info user
            $qUser = Tx_purchase_order::leftJoin('userdetails','tx_purchase_orders.created_by','=','userdetails.user_id')
            ->select('userdetails.branch_id AS user_branch_id')
            ->where('tx_purchase_orders.id','=',$id)
            ->first();

            $upd = Tx_purchase_order::where([
                'id' => $id,
            ])
            ->update([
                'approved_status' => $request->order_appr,
                'rejected_reason' => $request->reason,
                'canceled_by' => ($request->order_appr == 'R' ? Auth::user()->id : null),
                'canceled_at' => ($request->order_appr == 'R' ? Carbon::now() : null),
                'approved_by' => ($request->order_appr == 'A' ? Auth::user()->id : null),
                'approved_at' => ($request->order_appr == 'A' ? Carbon::now() : null),
                'updated_by' => Auth::user()->id
            ]);

            // update qty hanya dari receipt order, bukan purchase order
            if ($upd && $request->order_appr == 'A') {
                // if approved
                $queryOrderPart = Tx_purchase_order_part::where([
                    'order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();
                foreach ($queryOrderPart as $q) {
                    $qtyPart = Tx_qty_part::where([
                        'part_id' => $q->part_id,
                        'branch_id' => $qUser->branch_id
                        // 'branch_id' => $qUser->user_branch_id
                    ])
                    ->first();
                    if ($qtyPart) {
                        // update
                        // update total OH diproses di menu RO dg status approved
                    } else {
                        // insert jika part belum ada di gudang di masing2 cabang
                        if(!is_null($qUser->branch_id)){
                            $qtyPartIns = Tx_qty_part::create([
                                'part_id' => $q->part_id,
                                'qty' => 0,
                                'branch_id' => $qUser->branch_id,
                                // 'branch_id' => $qUser->user_branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }

                    $qO = Tx_purchase_order::where('id','=',$id)
                    ->first();
                    if($qO){
                        if($qO->currency_id!=3){
                            // update fob currency, final fob
                            $updFOB = Mst_part::where([
                                'id' => $q->part_id
                            ])
                            ->update([
                                'fob_currency' => $qO->currency_id,
                                'final_fob' => $q->price
                            ]);
                        }
                    }
                }

                if ($request->totalRow > 0) {
                    for ($i = 0; $i < $request->totalRow; $i++) {
                        if ($request['order_part_id_'.$i]) {
                            $qOhOo = Tx_purchase_order_oo_oh_part::where([
                                'purchase_order_part_id' => $request['order_part_id_'.$i],
                            ])
                            ->first();
                            if($qOhOo){
                                $updOhOo = Tx_purchase_order_oo_oh_part::where([
                                    'purchase_order_part_id' => $request['order_part_id_'.$i],
                                ])
                                ->update([
                                    'last_OO_PO_approval' => $request['oo_'.$i.'_tmp'],
                                    'last_OH_PO_approval' => $request['oh_'.$i.'_tmp'],
                                    'last_OO_OH_PO_approval' => now(),
                                    'updated_by' => Auth::user()->id,
                                ]);
                            }
                        }
                    }
                }
            }

            // if ($upd && $request->order_appr == 'R') {
            //     // if rejected
            //     $queryOrderPart = Tx_purchase_order_part::where([
            //         'order_id' => $id,
            //         'active' => 'Y'
            //     ])
            //         ->get();
            //     foreach ($queryOrderPart as $q) {
            //         $qtyPart = Tx_qty_part::where('part_id', '=', $q->part_id)
            //             ->first();
            //         if($qtyPart){
            //             $qtyPartUpd = Tx_qty_part::where('part_id', '=', $q->part_id)
            //                 ->update([
            //                     'qty' => $qtyPart->qty-$q->qty,
            //                     'updated_by' => Auth::user()->id
            //                 ]);
            //         }
            //     }
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
