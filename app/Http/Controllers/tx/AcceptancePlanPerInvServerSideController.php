<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\V_invoice;
use App\Models\Mst_global;
use App\Rules\MustEqualRule;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Rules\AcceptMonthCheck;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_acceptance_plan;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Tx_acceptance_plan_per_invoice;
use App\Models\Mst_menu_user;
use Illuminate\Validation\ValidationException;

class AcceptancePlanPerInvServerSideController extends Controller
{
    protected $title = 'Rencana Penerimaan';
    protected $folder = 'acceptance-plan';
    protected $folder_per_inv = 'acceptance-plan-inv';
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
    public function show(Request $request, $invoice_no)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $query = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
        ->select(
            'v_invoices.inv_id',
            'v_invoices.invoice_no',
            'v_invoices.customer_id',
            'v_invoices.invoice_date',
            'v_invoices.tagihan',
            'v_invoices.inv_identity',
            'v_invoices.vat_val',
            'cust.id as cust_id',
            'cust.name as cust_name',
            'cust.customer_unique_code',
            'cust.top as cust_top',
            DB::raw('CONCAT(cust.customer_unique_code, " - ", cust.name) AS customer_identity'),
            DB::raw('DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY) AS due_date_acceptance'),
        )
        ->whereRaw('DATE_FORMAT(DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY), "%c-%Y")=\''.urldecode($request->am).'\'')
        ->where([
            'v_invoices.invoice_no'=>$invoice_no,
        ])
        ->first();
        if ($query) {
            $qAcceptancePlans = Tx_acceptance_plan_per_invoice::where([
                'inv_or_kwi_id'=>$query->inv_id,
                'inv_or_kwi'=>$query->inv_identity,
                'active'=>'Y',
            ]);

            $paid_val = 0;
            $Pr = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as pr','tx_payment_receipt_invoices.payment_receipt_id','=','pr.id')
            ->select('tx_payment_receipt_invoices.total_payment')
            ->whereRaw('pr.payment_receipt_no IS NOT NULL')
            ->where([
                'tx_payment_receipt_invoices.invoice_no'=>$query->invoice_no,
                'tx_payment_receipt_invoices.active'=>'Y',
                'pr.active'=>'Y',
            ])
            ->get();
            if ($Pr){
                foreach ($Pr as $p) {
                    $paid_val += $p->total_payment;
                }
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'folder_per_inv'=>$this->folder_per_inv,
                'qCurrency'=>$qCurrency,
                'qInv'=>$query,
                'qPaymentPlans'=>$qAcceptancePlans->get(),
                'qPaymentPlansRows'=>$qAcceptancePlans->count(),
                'ap'=>$request->ap,
                'paid_val'=>$paid_val,
                'monthList'=>$this->monthList,
            ];
            return view('tx.'.$this->folder.'.show-per-inv', $data);
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
    public function edit(Request $request, $invoice_no)
    {
        $qCurrency = Mst_global::where([
            'id'=>3,
            'data_cat'=>'currency',
            'active'=>'Y'
        ])
        ->first();

        $query = V_invoice::leftJoin('mst_customers as cust','v_invoices.customer_id','=','cust.id')
        ->select(
            'v_invoices.inv_id',
            'v_invoices.invoice_no',
            'v_invoices.customer_id',
            'v_invoices.invoice_date',
            'v_invoices.tagihan',
            'v_invoices.inv_identity',
            'v_invoices.vat_val',
            'v_invoices.payment_to_id',
            'cust.id as cust_id',
            'cust.name as cust_name',
            'cust.customer_unique_code',
            'cust.top as cust_top',
            DB::raw('CONCAT(cust.customer_unique_code, " - ", cust.name) AS customer_identity'),
            DB::raw('DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY) AS due_date_acceptance'),
        )
        ->whereRaw('DATE_FORMAT(DATE_ADD(v_invoices.invoice_date, INTERVAL cust.top DAY), "%c-%Y")=\''.urldecode($request->am).'\'')
        ->where([
            'v_invoices.invoice_no'=>$invoice_no,
        ])
        ->first();
        if ($query) {
            $qAcceptancePlans = Tx_acceptance_plan_per_invoice::where([
                'inv_or_kwi_id'=>$query->inv_id,
                'inv_or_kwi'=>$query->inv_identity,
                'active'=>'Y',
            ]);

            $paid_val = 0;
            $Pr = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as pr','tx_payment_receipt_invoices.payment_receipt_id','=','pr.id')
            ->select('tx_payment_receipt_invoices.total_payment')
            ->whereRaw('pr.payment_receipt_no IS NOT NULL')
            ->where([
                'tx_payment_receipt_invoices.invoice_no'=>$query->invoice_no,
                'tx_payment_receipt_invoices.active'=>'Y',
                'pr.active'=>'Y',
            ])
            ->get();
            if ($Pr){
                foreach ($Pr as $p) {
                    $paid_val += $p->total_payment;
                }
            }

            $data = [
                'title'=>$this->title,
                'folder'=>$this->folder,
                'folder_per_inv'=>$this->folder_per_inv,
                'qCurrency'=>$qCurrency,
                'qInv'=>$query,
                'qPaymentPlans'=>$qAcceptancePlans->get(),
                'qPaymentPlansRows'=>$qAcceptancePlans->count(),
                'ap'=>$request->ap,
                'paid_val'=>$paid_val,
                'monthList'=>$this->monthList,
            ];
            return view('tx.'.$this->folder.'.edit-per-inv', $data);
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
            'menu_id' => 117,
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
                        'plan_date_'.$i=>['required', new AcceptMonthCheck()],
                        'plan_accept_'.$i=>['required', new NumericCustom('Plan Bayar'), new MustEqualRule($request->rencana_total_terima, $request->total_tagihan)],
                    ];
                    $errPerRoMsg = [
                        'plan_date_'.$i.'.required'=>'The Plan Date field is required.',
                        'plan_accept_'.$i.'.required'=>'The Plan Bayar field is required.',
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
            $idNew = $id;
            if ($idNew!=''){                
                $ins = Tx_acceptance_plan_per_invoice::where([
                    'acceptance_plan_id'=>$idNew,
                ])
                ->update([
                    'active'=>'N',
                    'updated_by'=>Auth::user()->id,
                ]);
            }

            $qInv = V_invoice::where([
                'invoice_no'=>$request->invoice_no,
            ])
            ->first();
            if ($request->plan_rows_total>0 && $qInv){
                for($i=0; $i<$request->plan_rows_total; $i++){
                    if (isset($request['plan_id_'.$i])) {
                        $ym = explode("/", $request['plan_date_'.$i]);
                        $qPlan = Tx_acceptance_plan::whereRaw('DATE_FORMAT(acceptance_month, "%Y-%c")=\''.$ym[2].'-'.$ym[1].'\'')
                        ->where([
                            'bank_id'=>$request['b_i'],
                            'active' => 'Y',
                        ])
                        ->first();
                        if($qPlan){
                            $idNew = $qPlan->id;
                        }

                        if ($request['plan_id_'.$i]==0){
                            $ins = Tx_acceptance_plan_per_invoice::create([
                                'acceptance_plan_id'=>$idNew,
                                'plan_date'=>$ym[2].'-'.$ym[1].'-'.$ym[0],
                                'plan_accept'=>GlobalFuncHelper::moneyValidate($request['plan_accept_'.$i]),
                                'inv_or_kwi_id'=>$qInv->inv_id,
                                'inv_or_kwi'=>$qInv->inv_identity,
                                'customer_id'=>$request->c_id,
                                'invoice_no'=>$request->i_no,
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            echo $request['plan_id_'.$i].'---'.$idNew.'<br>';
                            $upd = Tx_acceptance_plan_per_invoice::where([
                                'id'=>$request['plan_id_'.$i],
                            ])
                            ->update([
                                'acceptance_plan_id'=>$idNew,
                                'plan_date'=>$ym[2].'-'.$ym[1].'-'.$ym[0],
                                'plan_accept'=>GlobalFuncHelper::moneyValidate($request['plan_accept_'.$i]),
                                'inv_or_kwi_id'=>$qInv->inv_id,
                                'inv_or_kwi'=>$qInv->inv_identity,
                                'customer_id'=>$request->c_id,
                                'invoice_no'=>$request->i_no,
                                'active' => 'Y',
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder.'/'.$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_lokal_journal  $Tx_lokal_journal
     * @return \Illuminate\Http\Response
     */
    public function destroy($invoice_no)
    {
        //
    }
}
