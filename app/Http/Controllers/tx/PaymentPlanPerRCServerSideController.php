<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Tx_payment_plan;
use App\Models\Tx_receipt_order;
use App\Rules\PaymentMonthCheck;
use App\Helpers\GlobalFuncHelper;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_payment_plan_per_rc_order;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;

class PaymentPlanPerRCServerSideController extends Controller
{
    protected $title = 'Rencana Pembayaran';
    protected $folder = 'payment-plan';
    protected $folder_per_ro = 'payment-plan-ro';
    protected $monthList = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $query = Tx_receipt_order::leftJoin('mst_suppliers as sp','tx_receipt_orders.supplier_id','=','sp.id')
        ->select(
            'tx_receipt_orders.id as ro_id',
            'tx_receipt_orders.receipt_no',
            'tx_receipt_orders.receipt_date',
            'tx_receipt_orders.invoice_no',
            'tx_receipt_orders.total_after_vat',
            'tx_receipt_orders.exchange_rate',
            'tx_receipt_orders.supplier_type_id',
            'sp.name as supplier_name',
            'sp.supplier_code',
            'sp.top as supplier_top',
            DB::raw('CONCAT(sp.supplier_code, " - ", sp.name) AS supplier_identity'),
            DB::raw('DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY) AS due_date_payment'),
        )
        ->whereRaw('DATE_FORMAT(DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY), "%m-%Y")=\''.urldecode($request->pm).'\'')
        ->where([
            'tx_receipt_orders.id'=>$id,
            'tx_receipt_orders.active'=>'Y',
            'sp.payment_from_id'=>$request->b_i,
        ])
        ->orderBy('tx_receipt_orders.receipt_date','DESC')
        ->first();
        if ($query) {
            $qPaymentPlans = Tx_payment_plan_per_rc_order::where([
                'receipt_order_id'=>$query->ro_id,
                'active'=>'Y',
            ]);

            $paid_val = 0;
            $Pv = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as pv','tx_payment_voucher_invoices.payment_voucher_id','=','pv.id')
            // ->selectRaw('tx_payment_voucher_invoices.total_payment+(tx_payment_voucher_invoices.total_payment*pv.vat_num/100) as bayar_tagihan')
            ->selectRaw('
                CASE
                    WHEN pv.vat_num>0 THEN (tx_payment_voucher_invoices.total_payment+(tx_payment_voucher_invoices.total_payment*pv.vat_num/100))
                    ELSE tx_payment_voucher_invoices.total_payment
                END as bayar_tagihan
            ')
            ->whereRaw('pv.payment_voucher_no IS NOT NULL')
            ->where([
                'tx_payment_voucher_invoices.receipt_order_id'=>$query->ro_id,
                'tx_payment_voucher_invoices.active'=>'Y',
                'pv.active'=>'Y',
            ])
            ->get();
            if ($Pv){
                foreach ($Pv as $p) {
                    $paid_val += $p->bayar_tagihan;
                }
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'folder_per_ro'=>$this->folder_per_ro,
                'qCurrency'=>$qCurrency,
                'qRO'=>$query,
                'qPaymentPlans'=>$qPaymentPlans->get(),
                'qPaymentPlansRows'=>$qPaymentPlans->count(),
                'pp'=>$request->pp,
                'paid_val'=>$paid_val,
                'monthList'=>$this->monthList,
            ];
            return view('tx.'.$this->folder.'.show-per-ro', $data);
        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $query = Tx_receipt_order::leftJoin('mst_suppliers as sp','tx_receipt_orders.supplier_id','=','sp.id')
        ->select(
            'tx_receipt_orders.id as ro_id',
            'tx_receipt_orders.receipt_no',
            'tx_receipt_orders.receipt_date',
            'tx_receipt_orders.invoice_no',
            'tx_receipt_orders.total_after_vat',
            'tx_receipt_orders.exchange_rate',
            'tx_receipt_orders.supplier_type_id',
            'sp.name as supplier_name',
            'sp.supplier_code',
            'sp.top as supplier_top',
            DB::raw('CONCAT(sp.supplier_code, " - ", sp.name) AS supplier_identity'),
            DB::raw('DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY) AS due_date_payment'),
        )
        ->whereRaw('DATE_FORMAT(DATE_ADD(tx_receipt_orders.receipt_date, INTERVAL sp.top DAY), "%m-%Y")=\''.urldecode($request->pm).'\'')
        ->where([
            'tx_receipt_orders.id'=>$id,
            'tx_receipt_orders.active'=>'Y',
            'sp.payment_from_id'=>$request->b_i,
        ])
        ->orderBy('tx_receipt_orders.receipt_date','DESC')
        ->first();
        if ($query) {
            $qPaymentPlans = Tx_payment_plan_per_rc_order::where([
                'receipt_order_id'=>$query->ro_id,
                'active'=>'Y',
            ]);

            $paid_val = 0;
            $Pv = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as pv','tx_payment_voucher_invoices.payment_voucher_id','=','pv.id')
            ->selectRaw('
                CASE
                    WHEN pv.vat_num>0 THEN (tx_payment_voucher_invoices.total_payment+(tx_payment_voucher_invoices.total_payment*pv.vat_num/100))
                    ELSE tx_payment_voucher_invoices.total_payment
                END as bayar_tagihan
            ')
            ->whereRaw('pv.payment_voucher_no IS NOT NULL')
            ->where([
                'tx_payment_voucher_invoices.receipt_order_id'=>$query->ro_id,
                'tx_payment_voucher_invoices.active'=>'Y',
                'pv.active'=>'Y',
            ])
            ->get();
            if ($Pv){
                foreach ($Pv as $p) {
                    $paid_val += $p->bayar_tagihan;
                }
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'folder_per_ro'=>$this->folder_per_ro,
                'qCurrency'=>$qCurrency,
                'qRO'=>$query,
                'qPaymentPlans'=>$qPaymentPlans->get(),
                'qPaymentPlansRows'=>$qPaymentPlans->count(),
                'pp'=>$request->pp,
                'b_i'=>$request->b_i,
                'paid_val'=>$paid_val,
                'monthList'=>$this->monthList,
            ];
            return view('tx.'.$this->folder.'.edit-per-ro', $data);
        } else {
            $data = [
                'errNotif'=>'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 116,
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
        
        $validateInput = [];
        $errMsg = [];
        if ($request->plan_rows_total>0){
            for($i=0; $i<$request->plan_rows_total; $i++){
                if (isset($request['plan_id_'.$i])) {
                    $validatePerRoInput = [
                        'plan_date_'.$i=>['required', new PaymentMonthCheck()],
                        'plan_payment_'.$i=>['required',new NumericCustom('Plan Bayar')],
                    ];
                    $errPerRoMsg = [
                        'plan_date_'.$i.'.required'=>'The Plan Date field is required.',
                        'plan_payment_'.$i.'.required'=>'The Plan Bayar field is required.',
                    ];
                    $validateInput = array_merge($validateInput, $validatePerRoInput);
                    $errMsg = array_merge($errMsg, $errPerRoMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg,
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {
            $qRo = Tx_receipt_order::where([
                'receipt_no'=>$request->receipt_no,
                'active'=>'Y',
            ])
            ->first();
            if ($request->plan_rows_total>0 && $qRo){
                for($i=0; $i<$request->plan_rows_total; $i++){
                    if (isset($request['plan_id_'.$i])) {
                        $ym = explode("/", $request['plan_date_'.$i]);
                        $idNew = $id;
                        $qPlan = Tx_payment_plan::whereRaw('DATE_FORMAT(payment_month, "%Y-%m")=\''.$ym[2].'-'.$ym[1].'\'')
                        ->where([
                            'bank_id' => $request->b_i,
                            'active' => 'Y',
                        ])
                        ->first();
                        if($qPlan){
                            $idNew = $qPlan->id;
                        }

                        if ($request['plan_id_'.$i]==0){
                            $ins = Tx_payment_plan_per_rc_order::create([
                                'payment_plan_id'=>$idNew,
                                'plan_date'=>$ym[2].'-'.$ym[1].'-'.$ym[0],
                                'plan_pay'=>GlobalFuncHelper::moneyValidate($request['plan_payment_'.$i]),
                                'receipt_order_id'=>$qRo->id,
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            $ins = Tx_payment_plan_per_rc_order::where([
                                'id'=>$request['plan_id_'.$i],
                            ])
                            ->update([
                                'payment_plan_id'=>$idNew,
                                'plan_date'=>$ym[2].'-'.$ym[1].'-'.$ym[0],
                                'plan_pay'=>GlobalFuncHelper::moneyValidate($request['plan_payment_'.$i]),
                                'receipt_order_id'=>$qRo->id,
                                'updated_by'=>Auth::user()->id,
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

        session()->flash('status', 'Existing data has been updated successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
