<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Userdetail;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Models\Tx_payment_voucher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_payment_voucher_invoice;
use Illuminate\Validation\ValidationException;

class PaymentVoucherApprovalController extends Controller
{
    protected $title = 'Payment Voucher - Approval';
    protected $folder = 'payment-voucher-approval';

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
            $query = Tx_payment_voucher::leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id','=','mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by','=','users.id')
            ->leftJoin('userdetails AS usr','tx_payment_vouchers.created_by','=','usr.user_id')
            ->select(
                'tx_payment_vouchers.id AS tx_payment_vouchers_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.canceled_by',
                'tx_payment_vouchers.created_by',
                'tx_payment_vouchers.active',
                'mst_suppliers.name AS supplier_name',
                'users.name AS created_by_name'
            )
            ->where('tx_payment_vouchers.payment_voucher_no','NOT LIKE','%Draft%')
            ->where('tx_payment_vouchers.active','=','Y')
            ->orderBy('tx_payment_vouchers.created_at','DESC')
            ->get();
        }else{
            $query = Tx_payment_voucher::leftJoin('mst_suppliers','tx_payment_vouchers.supplier_id','=','mst_suppliers.id')
            ->leftJoin('users','tx_payment_vouchers.created_by','=','users.id')
            ->leftJoin('userdetails AS usr','tx_payment_vouchers.created_by','=','usr.user_id')
            ->select(
                'tx_payment_vouchers.id AS tx_payment_vouchers_id',
                'tx_payment_vouchers.payment_voucher_no',
                'tx_payment_vouchers.payment_date',
                'tx_payment_vouchers.payment_total',
                'tx_payment_vouchers.approved_by',
                'tx_payment_vouchers.canceled_by',
                'tx_payment_vouchers.created_by',
                'tx_payment_vouchers.active',
                'mst_suppliers.name AS supplier_name',
                'users.name AS created_by_name'
            )
            ->where('tx_payment_vouchers.payment_voucher_no','NOT LIKE','%Draft%')
            ->where('tx_payment_vouchers.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_payment_vouchers.created_at','DESC')
            ->get();
        }

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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($voucher_no)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($voucher_no))
        ->first();
        if($query){
            $queryInv = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->get();
            $queryInvCount = Tx_payment_voucher_invoice::where('payment_voucher_id','=',$query->id)
            ->where('active','=','Y')
            ->count();

            $paymentInvId = $query->id;
            $receiptOrders = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereNotIn('id', function ($q01) use ($paymentInvId) {
                $q01->select('receipt_order_id')
                ->from('tx_payment_voucher_invoices')
                ->where('payment_voucher_id','<>',$paymentInvId)
                ->where('is_full_payment','=','Y');
            })
            ->where('approved_by','<>',null)
            ->where('active','=','Y')
            ->orderBy('invoice_no','ASC')
            ->get();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'totalRow' => (old('totalRow')?old('totalRow'):$queryInvCount),
                // 'suppliers' => $suppliers,
                // 'coaCashInBank' => $coaCashInBank,
                // 'receiptOrders' => $receiptOrders,
                // 'paymentRef' => $paymentRef,
                'qPaymentInv' => $query,
                'queryInv' => $queryInv,
                'qCurrency' => $qCurrency
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $payment_voucher_no)
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($payment_voucher_no))
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
                $upd = Tx_payment_voucher::where('payment_voucher_no','=',urldecode($payment_voucher_no))
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
     * @param  \App\Models\Tx_payment_voucher  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
