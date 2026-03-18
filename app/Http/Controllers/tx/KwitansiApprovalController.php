<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Tx_kwitansi;
use App\Models\Tx_kwitansi_detail;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order_non_tax;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_delivery_order_non_tax_part;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KwitansiApprovalController extends Controller
{
    protected $title = 'Proses Tagihan - Approval';
    protected $folder = 'kwitansi-approval';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        $query = Tx_kwitansi::leftJoin('userdetails AS usr','tx_kwitansis.created_by','=','usr.user_id')
        ->select('tx_kwitansis.*')
        ->where('tx_kwitansis.kwitansi_no','NOT LIKE','%Draft%')
        ->when($userLogin->is_director=='Y', function($q) use ($userLogin) {
            $q->where('usr.branch_id','=', $userLogin->branch_id);
        })
        ->where('tx_kwitansis.active','=','Y')
        ->orderBy('tx_kwitansis.created_at', 'DESC')
        ->get();

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'query' => $query,
            'qCurrency' => $qCurrency
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
     * @param  \App\Models\Tx_kwitansi  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($kwitansi_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_kwitansi::where('kwitansi_no','=',$kwitansi_no)
        ->first();
        if($query){
            $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
            ->first();

            // $delivery_order = Tx_delivery_order_non_tax::where('customer_id','=',$query->customer_id)
            // ->where('delivery_order_no','NOT LIKE','%Draft%')
            // ->where('active','=','Y')
            // ->get();

            $delivery_order_per_id = Tx_delivery_order_non_tax::where('id','=',$query->delivery_order_id)
            ->where('active','=','Y')
            ->first();

            $delivery_order_part = Tx_delivery_order_non_tax_part::where([
                'delivery_order_id' => $query->delivery_order_id,
                'active' => 'Y'
            ])
            ->get();

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
                'totalRow' => (old('totalRow') ? old('totalRow') : 0),
                // 'deliveryOrders' => $delivery_order,
                'delivery_order_part' => $delivery_order_part,
                'delivery_order_per_id' => $delivery_order_per_id,
                'qKwi' => $query,
                'qCurrency' => $qCurrency,
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
     * @param  \App\Models\Tx_kwitansi  $tx_purchase_order
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
     * @param  \App\Models\Tx_kwitansi  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $kwitansi_no)
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $upd = Tx_kwitansi::where('kwitansi_no','=',urldecode($kwitansi_no))
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
                $upd = Tx_kwitansi::where('kwitansi_no','=',urldecode($kwitansi_no))
                ->where('canceled_by','=',null)
                ->update([
                    'approved_by' => null,
                    'approved_at' => null,
                    'canceled_by' => Auth::user()->id,
                    'canceled_at' => now(),
                    'updated_by' => Auth::user()->id,
                ]);
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
     * @param  \App\Models\Tx_kwitansi  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
