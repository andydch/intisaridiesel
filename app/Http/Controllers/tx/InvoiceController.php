<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use App\Models\Mst_customer;
// use App\Rules\UniqueInvoice;
use Illuminate\Http\Request;
use App\Models\Tx_invoice_detail;
use App\Models\Tx_delivery_order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use Illuminate\Support\Facades\Auth;
// use App\Models\Tx_delivery_order_part;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    protected $title = 'Invoice';
    protected $folder = 'invoice';

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

        $query = Tx_invoice::leftJoin('userdetails AS usr','tx_invoices.created_by','=','usr.user_id')
        ->select('tx_invoices.*')
        // ->where('tx_invoices.active','=','Y')
        ->when($userLogin->is_director=='Y', function($q) use ($userLogin) {
            $q->where('usr.branch_id','=', $userLogin->branch_id);
        })
        ->orderBy('tx_invoices.created_at', 'DESC');

        $data = [
            'invoices' => $query->get(),
            'invoicesCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
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

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $delivery_order = [];
        if(old('customer_id')){
            $delivery_order = Tx_delivery_order::where('customer_id','=',old('customer_id'))
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('tax_invoice_id','<>',null)
            ->where('active','=','Y')
            ->get();
        }

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCust' => $queryCustomer,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'vat' => $vat,
            'deliveryOrders' => $delivery_order,
            'qCurrency' => $qCurrency,
            'userLogin' => $userLogin,
            'branches' => $branches,
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
            'all_selected_FK' => 'required',
            'invoice_date' => 'required|date',
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid supplier',
            'customer_id.numeric' => 'Please select a valid supplier',
            'invoice_date.required' => 'Invoice Date must be filled',
            'invoice_date.date' => 'Invoice Date format must be date type',
            'all_selected_FK.required' => 'Please generate FK',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
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
            $identityName = 'tx_invoices-draft';
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
                $invoice_no = ENV('P_INVOICE').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_invoices';
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
                $invoice_no = ENV('P_INVOICE').date('y').'-'.$zero.strval($newInc);
            }

            $allSO = '';
            $all_selected_FK = $request->all_selected_FK;
            if(substr($all_selected_FK,0,1)==','){
                $all_selected_FK = substr($all_selected_FK,1,strlen($all_selected_FK));
            }
            $FKarr = explode(",",$request->all_selected_FK);
            //cari expired date terjauh
            $qDOexpdate = Tx_delivery_order::whereIn('delivery_order_no',$FKarr)
            ->orderBy('faktur_expired_date','DESC')
            ->first();

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $allSO .= $qDO->sales_order_no_all;
                    }
                }
            }

            $ins = Tx_invoice::create([
                'invoice_no' => $invoice_no,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->invoice_date,
                'invoice_expired_date' => $qDOexpdate->faktur_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'do_total' => $request->totalValbeforeVAT,
                'do_vat' => ($request->totalValafterVAT-$request->totalValbeforeVAT),
                'do_grandtotal_vat' => $request->totalValafterVAT,
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'draft_at' => $draft_at,
                'draft_to_created_at' => $draft_to_created_at,
                'is_draft' => $request->is_draft,
                'active' => 'Y',
                'created_by' => Auth::user()->id,
                'updated_by' => Auth::user()->id,
            ]);

            // get last ID
            $maxId = $ins->id;

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                    ->select('tx_delivery_orders.*','tx_tax_invoices.fp_no')
                    ->where('tx_delivery_orders.delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $ins_dtl = Tx_invoice_detail::create([
                            'invoice_id' => $maxId,
                            'fk_id' => $qDO->id,
                            'delivery_order_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'tax_invoice_id' => $qDO->tax_invoice_id,
                            'fp_no' => $qDO->fp_no,
                            'total' => $qDO->total_before_vat,
                            'vat' => ($qDO->total_after_vat-$qDO->total_before_vat),
                            'grand_total' => $qDO->total_after_vat,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
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

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $query = Tx_invoice::where('id','=',$id)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            $delivery_order = [];
            if(old('customer_id')){
                $delivery_order = Tx_delivery_order::where('customer_id','=',old('customer_id'))
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }else{
                $delivery_order = Tx_delivery_order::where('customer_id','=',$query->customer_id)
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }

            $all_selected_FK_from_db = '';
            $all_selected_FK_count_from_db = 0;
            $invdtls = Tx_invoice_detail::where([
                'invoice_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('delivery_order_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_FK_from_db .= ','.$invdtl->delivery_order_no;
                }
                $all_selected_FK_count_from_db = $invdtls->count();
                if(substr($all_selected_FK_from_db,0,1)==','){
                    $all_selected_FK_from_db = substr($all_selected_FK_from_db,1,strlen($all_selected_FK_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => $vat,
                'deliveryOrders' => $delivery_order,
                'qInv' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_FK_from_db' => $all_selected_FK_from_db,
                'all_selected_FK_count_from_db' => $all_selected_FK_count_from_db,
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

        // get active VAT
        $vat = ENV('VAT');
        $qVat = Mst_global::where([
            'data_cat' => 'vat',
            'active' => 'Y'
        ])
        ->first();
        if ($qVat) {
            $vat = $qVat->numeric_val;
        }

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_invoice::where('id','=',$id)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $delivery_order = [];
            if(old('customer_id')){
                $delivery_order = Tx_delivery_order::where('customer_id','=',old('customer_id'))
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }else{
                $delivery_order = Tx_delivery_order::where('customer_id','=',$query->customer_id)
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }

            $all_selected_FK_from_db = '';
            $all_selected_FK_count_from_db = 0;
            $invdtls = Tx_invoice_detail::where([
                'invoice_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('delivery_order_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_FK_from_db .= ','.$invdtl->delivery_order_no;
                }
                $all_selected_FK_count_from_db = $invdtls->count();
                if(substr($all_selected_FK_from_db,0,1)==','){
                    $all_selected_FK_from_db = substr($all_selected_FK_from_db,1,strlen($all_selected_FK_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCust' => $queryCustomer,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'vat' => $vat,
                'deliveryOrders' => $delivery_order,
                'qInv' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_FK_from_db' => $all_selected_FK_from_db,
                'all_selected_FK_count_from_db' => $all_selected_FK_count_from_db,
                'branches' => $branches,
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
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'customer_id' => 'required|numeric',
            'all_selected_FK' => 'required',
            'invoice_date' => 'required|date',
        ];
        if($request->is_director=='Y'){
            $validateInputBranch = [
                'branch_id' => 'required|numeric',
            ];
            $validateInput = array_merge($validateInput, $validateInputBranch);
        }
        $errMsg = [
            'customer_id.required' => 'Please select a valid supplier',
            'customer_id.numeric' => 'Please select a valid supplier',
            'invoice_date.required' => 'Invoice Date must be filled',
            'invoice_date.date' => 'Invoice Date format must be date type',
            'all_selected_FK.required' => 'Please generate FK',
            'branch_id.required' => 'Please select a valid branch',
            'branch_id.numeric' => 'Please select a valid branch',
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

            $delivery_order_no = '';
            $orders_old = Tx_invoice::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_invoice::where('id', '=', $id)
                ->where('invoice_no','LIKE','%Draft%')
                ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $invoice_no = $orders->invoice_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_invoices';
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
                $invoice_no = ENV('P_INVOICE').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_invoice::where('id', '=', $id)
                ->update([
                    'invoice_no' => $invoice_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_invoice::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $allSO = '';
            $all_selected_FK = $request->all_selected_FK;
            if(substr($all_selected_FK,0,1)==','){
                $all_selected_FK = substr($all_selected_FK,1,strlen($all_selected_FK));
            }
            $FKarr = explode(",",$request->all_selected_FK);
            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $allSO .= $qDO->sales_order_no_all;
                    }
                }
            }

            $upd = Tx_invoice::where('id','=',$id)
            ->update([
                'do_total' => $request->totalValbeforeVAT,
                'do_vat' => ($request->totalValafterVAT-$request->totalValbeforeVAT),
                'do_grandtotal_vat' => $request->totalValafterVAT,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // set not active dulu
            $notActive = Tx_invoice_detail::where([
                'invoice_id' => $id,
            ])
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id,
            ]);

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                    ->select('tx_delivery_orders.*','tx_tax_invoices.fp_no')
                    ->where('tx_delivery_orders.delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $find = Tx_invoice_detail::where([
                            'invoice_id' => $id,
                            'fk_id' => $qDO->id,
                            'delivery_order_no' => $qDO->delivery_order_no,
                            'tax_invoice_id' => $qDO->tax_invoice_id,
                            'fp_no' => $qDO->fp_no,
                        ])
                        ->first();
                        if($find){
                            $upd_dtl = Tx_invoice_detail::where([
                                'invoice_id' => $id,
                                'fk_id' => $qDO->id,
                                'delivery_order_no' => $qDO->delivery_order_no,
                                'tax_invoice_id' => $qDO->tax_invoice_id,
                                'fp_no' => $qDO->fp_no,
                            ])
                            ->update([
                                'delivery_order_date' => $qDO->delivery_order_date,
                                'total' => $qDO->total_before_vat,
                                'vat' => ($qDO->total_after_vat-$qDO->total_before_vat),
                                'grand_total' => $qDO->total_after_vat,
                                'active' => 'Y',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $ins_dtl = Tx_invoice_detail::create([
                                'invoice_id' => $id,
                                'fk_id' => $qDO->id,
                                'delivery_order_no' => $qDO->delivery_order_no,
                                'delivery_order_date' => $qDO->delivery_order_date,
                                'tax_invoice_id' => $qDO->tax_invoice_id,
                                'fp_no' => $qDO->fp_no,
                                'total' => $qDO->total_before_vat,
                                'vat' => ($qDO->total_after_vat-$qDO->total_before_vat),
                                'grand_total' => $qDO->total_after_vat,
                                'active' => 'Y',
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
