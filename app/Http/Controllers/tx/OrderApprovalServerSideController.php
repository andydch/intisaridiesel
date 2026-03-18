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
use App\Models\Tx_purchase_quotation;
use App\Models\User;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class OrderApprovalServerSideController extends Controller
{
    protected $title = 'Purchase Order - Approval';
    protected $folder = 'order-approval';

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
            $query = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_purchase_orders.supplier_id','=','mst_suppliers.id')
            ->leftJoin('mst_globals as ent','mst_suppliers.entity_type_id','=','ent.id')
            ->leftJoin('mst_globals as curr','tx_purchase_orders.currency_id','=','curr.id')
            ->select(
                'tx_purchase_orders.id as tx_id',
                'tx_purchase_orders.purchase_date',
                'tx_purchase_orders.purchase_no',
                'tx_purchase_orders.total_before_vat',
                'tx_purchase_orders.total_after_vat',
                'tx_purchase_orders.quotation_id',
                'tx_purchase_orders.supplier_id',
                'tx_purchase_orders.approved_status',
                'tx_purchase_orders.approved_by',
                'tx_purchase_orders.approved_at',
                'tx_purchase_orders.canceled_by',
                'tx_purchase_orders.canceled_at',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_suppliers.name as supplier_name',
                'mst_suppliers.supplier_code',
                'mst_suppliers.supplier_type_id',
                'curr.string_val as curr_nm',
                'ent.title_ind as supplier_entity_type_name',
            )
            ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
            ->where('tx_purchase_orders.active','=','Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->whereRaw('tx_purchase_orders.approved_by IS null')
            ->where([
                'tx_purchase_orders.active' => 'Y'
            ])
            ->orderBy('tx_purchase_orders.purchase_date', 'DESC')
            ->orderBy('tx_purchase_orders.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('purchase_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_purchase_orders.purchase_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('purchase_date', function ($query) {
                return date_format(date_create($query->purchase_date), "d/m/Y");
            })
            ->addColumn('purchase_no_wlink', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/order-approval/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">View</a>';
            })
            // ->addColumn('total_price', function ($query) {
            //     $totPrice = Tx_purchase_order_part::selectRaw('SUM(qty*price) as tot_price')
            //     ->where('tx_purchase_order_parts.order_id','=',$query->tx_id)
            //     ->where('tx_purchase_order_parts.active','=','Y')
            //     ->first();
            //     if($totPrice){
            //         if ($query->supplier_type_id==10){
            //             return $query->curr_nm.number_format($totPrice->tot_price,2,",",".");
            //         }
            //         return number_format($totPrice->tot_price,0,",","");
            //     }
            //     return 0;
            // })
            ->filterColumn('quotation_no', function($query, $keyword) {
                $query->whereIn('quotation_id', function($q) use($keyword) {
                    $q->select('id')
                    ->from('tx_purchase_quotations')
                    ->where("quotation_no", "LIKE", "%{$keyword}%")
                    ->where([
                        'active' => 'Y',
                    ]);
                });
            })
            ->editColumn('quotation_no', function ($query) {
                if(!is_null($query->quotation_id)){
                    $pqNo = '';
                    $qPQ = Tx_purchase_quotation::where("id",'=',$query->quotation_id)
                    ->first();
                    if ($qPQ){
                        $pqNo = $qPQ->quotation_no;
                    }
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/quotation/'.$query->quotation_id).'" target="_new" style="text-decoration: underline;">'.$pqNo.'</a>';
                }else{
                    return '';
                }
            })
            ->filterColumn('supplier_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_suppliers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_suppliers.supplier_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ent.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('supplier_name', function ($query) {
                return $query->supplier_code.' - '.$query->supplier_entity_type_name.' '.$query->supplier_name;
            })
            ->addColumn('status', function ($query) {
                if (is_null($query->approved_status)){
                    return 'Waiting for approval';
                }else{
                    if ($query->approved_status=='A'){
                        $qUser = User::where('id','=',$query->approved_by)
                        ->first();
                        return 'Approved at '.
                            date_format(date_add(date_create($query->approved_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.($qUser?$qUser->name:'');
                        // return 'Approved by '.($qUser?$qUser->name:'').' at '.date_format(date_create($query->approved_at), 'd M Y H:i:s');
                    }else{
                        $qUser = User::where('id','=',$query->canceled_by)
                        ->first();
                        return 'Rejected at '.
                            date_format(date_add(date_create($query->canceled_at), 
                            date_interval_create_from_date_string(ENV("WAKTU_ID")." hours")), 'd-M-Y H:i:s').
                            ' by '.($qUser?$qUser->name:'');
                        // return 'Rejected by '.($qUser?$qUser->name:'').' at '.date_format(date_create($query->canceled_at), 'd M Y H:i:s');
                    }
                }
            })
            ->rawColumns(['purchase_date','purchase_no_wlink','quotation_no','supplier_name','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
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
            ]);
            $data = [
                'orders' => $query,
                'orderParts' => $queryOrderPart->get(),
                'title' => $this->title,
                'folder' => $this->folder,
                'suppliers' => $suppliers,
                'supplierPics' => $supplierPic,
                'branches' => $branches,
                'companies' => $companies,
                'parts' => $parts,
                'currency' => $currency,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryOrderPart->count()),
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 27,
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

        $qPv = Tx_purchase_order::where('id', '=', $id)
        ->first();
        if ($qPv){
            if (!is_null($qPv->approved_by)){
                // karena sudah disetujui maka pembayaran supplier tidak bisa diubah
                session()->flash('status-error', 'The document status cannot be changed because it has been approved.');
                return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
            }
        }

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
