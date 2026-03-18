<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_surat_jalan_part;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_delivery_order_non_tax_part;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;

class DeliveryOrderNonTaxController extends Controller
{
    protected $title = 'Nota Penjualan';
    protected $folder = 'delivery-order-local';
    protected $uri = 'delivery-order-local';

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

        $query = Tx_delivery_order_non_tax::leftJoin('userdetails AS usr','tx_delivery_order_non_taxes.created_by','=','usr.user_id')
        ->select('tx_delivery_order_non_taxes.*')
        ->addSelect(['total_price' => Tx_delivery_order_non_tax_part::selectRaw('SUM(tx_delivery_order_non_tax_parts.qty_so*tx_delivery_order_non_tax_parts.final_price)')
            ->whereColumn('tx_delivery_order_non_tax_parts.delivery_order_id','tx_delivery_order_non_taxes.id')
            ->where('tx_delivery_order_non_tax_parts.active','=','Y')
        ])
        ->where(function($q){
            $q->where('tx_delivery_order_non_taxes.active', 'Y')
            ->orWhere(function($s){
                $s->where('tx_delivery_order_non_taxes.active','N')
                ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%');
            });
        })
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_delivery_order_non_taxes.delivery_order_no', 'DESC')
        ->orderBy('tx_delivery_order_non_taxes.created_at', 'DESC');

        $data = [
            'orders' => $query->get(),
            'ordersCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCurrency' => $qCurrency,
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
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        // // get active VAT
        // $vat = ENV('VAT');
        // $qVat = Mst_global::where([
        //     'data_cat' => 'vat',
        //     'active' => 'Y'
        // ])
        // ->first();
        // if ($qVat) {
        //     $vat = $qVat->numeric_val;
        // }

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $surat_jalan = [];
        $surat_jalan_date = [];
        $ship_to = [];
        if(old('customer_id')){
            $surat_jalan = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
            ->select('tx_surat_jalans.surat_jalan_no AS order_no')
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_surat_jalans.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_non_tax_parts as tx_do_part')
                    ->where('tx_do_part.active','=','Y');
            })
            ->where(function($query) {
                $query->where('approved_by','<>',null)
                    ->orWhere(function($queryA) {
                    $queryA->where('approved_by','=',null)
                        ->where('need_approval','=','N');
                });
            })
            ->where([
                'tx_surat_jalans.customer_id' => old('customer_id'),
                'tx_surat_jalans.active' => 'Y'
            ])
            ->when(old('surat_jalan_date')!='#', function($query) {
                $query->where('tx_surat_jalans.surat_jalan_date','=',old('surat_jalan_date'));
            })
            ->when(old('is_vat')=='on', function($query) {
                $query->where('tx_surat_jalans.is_vat','=','Y');
            })
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                $query->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->get();

            $surat_jalan_date = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
            ->selectRaw('DISTINCT surat_jalan_date')
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->whereNotIn('tx_surat_jalans.id', function (Builder $queryQ) {
                $queryQ->select('tx_do_part.sales_order_id')
                    ->from('tx_delivery_order_non_tax_parts as tx_do_part')
                    ->where('tx_do_part.active','=','Y');
            })
            ->where(function($query) {
                $query->where('tx_surat_jalans.approved_by','<>',null)
                    ->orWhere(function($queryA) {
                    $queryA->where('tx_surat_jalans.approved_by','=',null)
                        ->where('tx_surat_jalans.need_approval','=','N');
                });
            })
            ->where([
                'tx_surat_jalans.customer_id' => old('customer_id'),
                'tx_surat_jalans.active' => 'Y'
            ])
            ->when($userLogin->is_director=='N', function($query) use($userLogin) {
                // $query->where('usr.branch_id','=',$userLogin->branch_id);
                $query->whereRaw('((usr.branch_id='.$userLogin->branch_id.' AND tx_surat_jalans.branch_id IS null) OR tx_surat_jalans.branch_id='.$userLogin->branch_id.')');
            })
            ->get();

            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => old('customer_id'),
                'active' => 'Y'
            ])
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            // 'vat' => $vat,
            'get_surat_jalan_no' => $surat_jalan,
            'get_surat_jalan_date' => $surat_jalan_date,
            'ship_to' => $ship_to,
            'qCurrency' => $qCurrency
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
            'customer_id' => 'required|numeric',
            'surat_jalan_no_all' => 'required',
            'surat_jalan_date' => 'date',
        ];
        $errMsg = [
            'customer_id.numeric' => 'Please select a valid supplier',
            'surat_jalan_no_all.required' => 'Please select a valid surat jalan no',
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

            $draft_at = null;
            $draft_to_created_at = null;
            $identityName = 'tx_delivery_order_non_taxes-draft';
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
                $order_no = env('P_NOTA_PENJUALAN').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_delivery_order_non_taxes';
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
                $order_no = env('P_NOTA_PENJUALAN').date('y').'-'.$zero.strval($newInc);
            }

            // data user yg login saat ini
            $user = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $qCust = Mst_customer::where('id','=',$request->customer_id)
            ->first();

            $surat_jalan_date = date_create($request->surat_jalan_date);
            date_add($surat_jalan_date, date_interval_create_from_date_string($qCust->top." days"));

            $ins = Tx_delivery_order_non_tax::create([
                'delivery_order_no' => $order_no,
                'delivery_order_date' => $request->surat_jalan_date,
                'do_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
                'sales_order_no_all' => $request->surat_jalan_no_all,
                'customer_id' => $request->customer_id,
                'customer_entity_type_id' => $qCust->entity_type_id,
                'customer_name' => $qCust->name,
                'remark' => $request->remark,
                'branch_id' => $user->branch_id,
                'total_qty' => 0,
                'total_price' => 0,
                'is_draft' => $request->is_draft,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            $totalQty = 0;
            $totalPrice = 0;
            for($iPart=0;$iPart<$request->totalRow;$iPart++){
                if($request['surat_jalan_part_id'.$iPart]){
                    $partSO = Tx_surat_jalan_part::leftJoin('tx_surat_jalans as tx_sj','tx_surat_jalan_parts.surat_jalan_id','=','tx_sj.id')
                    ->select(
                        'tx_surat_jalan_parts.*',
                        'tx_sj.branch_id',
                    )
                    ->where('tx_surat_jalan_parts.id','=',$request['surat_jalan_part_id'.$iPart])
                    ->first();
                    if($partSO){
                        $insPart = Tx_delivery_order_non_tax_part::create([
                            'delivery_order_id' => $maxId,
                            'sales_order_id' => $request['surat_jalan_id'.$iPart],
                            'sales_order_part_id' => $request['surat_jalan_part_id'.$iPart],
                            'part_id' => $partSO->part_id,
                            'qty' => $partSO->qty,
                            'qty_so' => $partSO->qty,
                            'final_price' => $partSO->price,
                            'total_price'=> ($partSO->qty*$partSO->price),
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
                        ]);

                        // update stok tersedia
                        $qStock = Tx_qty_part::where('part_id','=',$partSO->part_id)
                        ->where('branch_id','=',$partSO->branch_id)
                        ->first();
                        if($qStock && $request->is_draft!='Y'){
                            // update stok diproses jika status bukan draft
                            $updStock = Tx_qty_part::where('part_id','=',$partSO->part_id)
                            ->where('branch_id','=',$partSO->branch_id)
                            ->update([
                                'qty' => $qStock->qty-$partSO->qty,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }

                        $totalQty += $partSO->qty;
                        $totalPrice += ($partSO->qty*$partSO->price);
                    }
                }
            }

            $upd = Tx_delivery_order_non_tax::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_price' => $totalPrice,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
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

        $queryCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        // // get active VAT
        // $vat = ENV('VAT');
        // $qVat = Mst_global::where([
        //     'data_cat' => 'vat',
        //     'active' => 'Y'
        // ])
        //     ->first();
        // if ($qVat) {
        //     $vat = $qVat->numeric_val;
        // }

        $query = Tx_delivery_order_non_tax::where('id','=',$id)->first();
        if($query){
            $parts = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $id,
                // 'active' => 'Y'
            ]);

            $ship_to = Mst_customer_shipment_address::where([
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts->get(),
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $parts->count()),
            // 'vat' => $vat,
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency,
        ];

        return view('tx.'.$this->folder.'.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
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

        $queryCustomer = Mst_customer::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $parts = Mst_part::where([
            'active' => 'Y'
        ])
        ->orderBy('part_name', 'ASC')
        ->get();

        $weighttype = Mst_global::where([
            'data_cat' => 'weight-type',
            'active' => 'Y'
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $couriers = Mst_courier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $ship_to = [];
        $query = [];
        $parts = [];
        $partCount = 0;

        $query = Tx_delivery_order_non_tax::where('id','=',$id)
        ->first();
        if($query){
            $parts = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $id,
                'active' => 'Y'
            ]);

            if(old('customer_id')){
                $ship_to = Mst_customer_shipment_address::where([
                    'customer_id' => old('customer_id'),
                    'active' => 'Y'
                ])
                ->get();
            }else{
                $ship_to = Mst_customer_shipment_address::where([
                    'customer_id' => $query->customer_id,
                    'active' => 'Y'
                ])
                ->get();
            }
        }else{
            $query = [];
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'uri' => $this->uri,
            'qCust' => $queryCustomer,
            'parts' => $parts->get(),
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : $parts->count()),
            'ship_to' => $ship_to,
            'queryDelivery' => $query,
            'qCurrency' => $qCurrency
        ];

        return view('tx.'.$this->folder.'.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $validateInput = [
        //     'ship_to_id' => 'required|numeric',
        //     'courier_id' => 'required|numeric',
        // ];
        // $errMsg = [
        //     'ship_to_id.numeric' => 'Please select a valid surat jalan no',
        //     'courier_id.numeric' => 'Please select a valid surat jalan no',
        // ];
        // Validator::make(
        //     $request->all(),
        //     $validateInput,
        //     $errMsg
        // )
        // ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {

            $delivery_order_no = '';
            $orders_old = Tx_delivery_order_non_tax::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_delivery_order_non_tax::where('id', '=', $id)
            ->where('delivery_order_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $delivery_order_no = $orders->delivery_order_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_delivery_order_non_taxes';
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
                $delivery_order_no = env('P_NOTA_PENJUALAN').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_delivery_order_non_tax::where('id', '=', $id)
                ->update([
                    'delivery_order_no' => $delivery_order_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);

            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_delivery_order_non_tax::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $cust = Mst_customer::where('id', '=', $orders_old->customer_id)
            ->first();

            $userLogin = Userdetail::where('user_id','=',$orders_old->created_by)
            ->first();

            $surat_jalan_date = date_create($orders_old->surat_jalan_date);
            date_add($surat_jalan_date, date_interval_create_from_date_string($cust->top." days"));

            $upd = Tx_delivery_order_non_tax::where('id','=',$id)
            ->update([
                'do_expired_date' => date_format($surat_jalan_date,"Y-m-d"),
                'remark' => $request->remark,
                'branch_id' => $userLogin->branch_id,
                'updated_by' => Auth::user()->id,
            ]);

            if($orders_old->delivery_order_no!=$delivery_order_no && $delivery_order_no!=''){
                // jika dari draft menjadi created
                $doPart = Tx_delivery_order_non_tax_part::where(
                [
                    'delivery_order_id' => $id,
                    'active' => 'Y'
                ])
                ->get();

                // data user yg create DO
                $user = Userdetail::where('user_id','=',$orders_old->created_by)
                ->first();

                foreach($doPart as $do_part){
                    $partSO = Tx_surat_jalan_part::leftJoin('tx_surat_jalans as tx_sj','tx_surat_jalan_parts.surat_jalan_id','=','tx_sj.id')
                    ->select(
                        'tx_surat_jalan_parts.*',
                        'tx_sj.branch_id',
                    )
                    ->where('tx_surat_jalan_parts.id','=',$do_part->surat_jalan_id)
                    ->first();
                    if($partSO){
                        $qtyStock = Tx_qty_part::where('part_id','=',$do_part->part_id)
                        ->where('branch_id','=',$partSO->branch_id)
                        ->first();
                        if($qtyStock){
                            $updStock = Tx_qty_part::where('part_id','=',$do_part->part_id)
                            ->where('branch_id','=',$partSO->branch_id)
                            ->update([
                                'qty' => $qtyStock->qty-$do_part->qty,
                            ]);
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->uri);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_delivery_order $tx_delivery_order)
    {
        //
    }
}
