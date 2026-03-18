<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_global;
use App\Models\Tx_kwitansi;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_kwitansi_detail;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class KwitansiController extends Controller
{
    protected $title = 'Kwitansi';
    protected $folder = 'kwitansi';

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

        $query = Tx_kwitansi::leftJoin('userdetails AS usr','tx_kwitansis.created_by','=','usr.user_id')
        ->select('tx_kwitansis.*')
        ->when($userLogin->is_director=='Y', function($q) use ($userLogin) {
            $q->where('usr.branch_id','=', $userLogin->branch_id);
        })
        ->orderBy('tx_kwitansis.created_at', 'DESC');

        $data = [
            'kwitansis' => $query->get(),
            'kwitansisCount' => $query->count(),
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

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $delivery_order = [];
        if(old('customer_id')){
            $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',old('customer_id'))
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            // ->where('tax_kwitansi_id','<>',null)
            ->where('active','=','Y')
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCust' => $queryCustomer,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
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
            'all_selected_NP' => 'required',
            'kwitansi_date' => 'required|date',
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
            'all_selected_NP.required' => 'Please generate NP',
            'kwitansi_date.required' => 'Kwitansi Date must be filled',
            'kwitansi_date.date' => 'kwitansi Date format must be date type',
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
            $identityName = 'tx_kwitansis-draft';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_kwitansis';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-'.$zero.strval($newInc);
            }

            $all_selected_NP = $request->all_selected_NP;
            if(substr($all_selected_NP,0,1)==','){
                $all_selected_NP = substr($all_selected_NP,1,strlen($all_selected_NP));
            }
            $FKarr = explode(",",$request->all_selected_NP);
            //cari expired date terjauh
            $qDOexpdate = Tx_delivery_order_non_tax::whereIn('delivery_order_no',$FKarr)
            ->orderBy('do_expired_date','DESC')
            ->first();

            $ins = Tx_kwitansi::create([
                'kwitansi_no' => $kwitansi_no,
                'customer_id' => $request->customer_id,
                'kwitansi_date' => $request->kwitansi_date,
                'kwitansi_expired_date' => $qDOexpdate->do_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'np_total' => $request->totalValafterVAT,
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
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $ins_dtl = Tx_kwitansi_detail::create([
                            'kwitansi_id' => $maxId,
                            'np_id' => $qDO->id,
                            'nota_penjualan_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'sj_no' => $qDO->sales_order_no_all,
                            'total' => $qDO->total_price,
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
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

        $query = Tx_kwitansi::where('id','=',$id)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $delivery_order = [];
            if(old('customer_id')){
                $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',old('customer_id'))
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }else{
                $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$query->customer_id)
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }

            $all_selected_NP_from_db = '';
            $all_selected_NP_count_from_db = 0;
            $invdtls = Tx_kwitansi_detail::where([
                'kwitansi_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('nota_penjualan_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_NP_from_db .= ','.$invdtl->nota_penjualan_no;
                }
                $all_selected_NP_count_from_db = $invdtls->count();
                if(substr($all_selected_NP_from_db,0,1)==','){
                    $all_selected_NP_from_db = substr($all_selected_NP_from_db,1,strlen($all_selected_NP_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCust' => $queryCustomer,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'qKwi' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_NP_from_db' => $all_selected_NP_from_db,
                'all_selected_NP_count_from_db' => $all_selected_NP_count_from_db,
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
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

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_kwitansi::where('id','=',$id)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',$query->created_by)
            ->first();

            $delivery_order = [];
            if(old('customer_id')){
                $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',old('customer_id'))
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }else{
                $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$query->customer_id)
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->where('active','=','Y')
                ->get();
            }

            $all_selected_NP_from_db = '';
            $all_selected_NP_count_from_db = 0;
            $invdtls = Tx_kwitansi_detail::where([
                'kwitansi_id' => $query->id,
                'active' => 'Y',
            ])
            ->orderBy('nota_penjualan_no','ASC');
            if($invdtls->get()){
                foreach($invdtls->get() as $invdtl){
                    $all_selected_NP_from_db .= ','.$invdtl->nota_penjualan_no;
                }
                $all_selected_NP_count_from_db = $invdtls->count();
                if(substr($all_selected_NP_from_db,0,1)==','){
                    $all_selected_NP_from_db = substr($all_selected_NP_from_db,1,strlen($all_selected_NP_from_db));
                }
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'qCust' => $queryCustomer,
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                'deliveryOrders' => $delivery_order,
                'qKwi' => $query,
                'qCurrency' => $qCurrency,
                'userLogin' => $userLogin,
                'all_selected_NP_from_db' => $all_selected_NP_from_db,
                'all_selected_NP_count_from_db' => $all_selected_NP_count_from_db,
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
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validateInput = [
            'customer_id' => 'required|numeric',
            'all_selected_NP' => 'required',
            'kwitansi_date' => 'required|date',
            // 'expired_kwitansi_date' => 'required|date',
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
            'all_selected_NP.required' => 'Please generate NP',
            'kwitansi_date.required' => 'Kwitansi Date must be filled',
            'kwitansi_date.date' => 'kwitansi Date format must be date type',
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

            $nota_penjualan_no = '';
            $orders_old = Tx_kwitansi::where('id', '=', $id)
            ->first();

            $draft = false;
            $orders = Tx_kwitansi::where('id', '=', $id)
                ->where('kwitansi_no','LIKE','%Draft%')
                ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $kwitansi_no = $orders->kwitansi_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_kwitansis';
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
                $kwitansi_no = env('P_KWITANSI').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_kwitansi::where('id', '=', $id)
                ->update([
                    'kwitansi_no' => $kwitansi_no,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_kwitansi::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $allSO = '';
            $all_selected_NP = $request->all_selected_NP;
            if(substr($all_selected_NP,0,1)==','){
                $all_selected_NP = substr($all_selected_NP,1,strlen($all_selected_NP));
            }
            $FKarr = explode(",",$request->all_selected_NP);
            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $allSO .= $qDO->sales_order_no_all;
                    }
                }
            }

            //cari expired date terjauh
            $qDOexpdate = Tx_delivery_order_non_tax::whereIn('delivery_order_no',$FKarr)
            ->orderBy('do_expired_date','DESC')
            ->first();

            $upd = Tx_kwitansi::where('id','=',$id)
            ->update([
                'customer_id' => $request->customer_id,
                'kwitansi_date' => $request->kwitansi_date,
                'kwitansi_expired_date' => $qDOexpdate->do_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'np_total' => $request->totalValafterVAT,
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
            ]);

            // hapus data detail sebelumnya, ganti dengan baru
            $deleted = Tx_kwitansi_detail::where([
                'kwitansi_id' => $id,
            ])
            ->delete();

            foreach($FKarr as $fk){
                if($fk!=''){
                    $qDO = Tx_delivery_order_non_tax::where('delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        $ins_dtl = Tx_kwitansi_detail::create([
                            'kwitansi_id' => $id,
                            'np_id' => $qDO->id,
                            'nota_penjualan_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'sj_no' => $qDO->sales_order_no_all,
                            'total' => $qDO->total_price,
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

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_delivery_order_non_tax  $tx_delivery_order
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
