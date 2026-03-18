<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Auto_inc;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Mst_coa;
use App\Models\Tx_invoice;
use App\Models\Userdetail;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_delivery_order;
use App\Models\Tx_invoice_detail;
use App\Rules\CheckApprovedRetur;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_payment_receipt_invoice;
use App\Models\Mst_menu_user;
use App\Models\Tx_nota_retur;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InvoiceServerSideController extends Controller
{
    protected $title = 'Billing Process';
    protected $folder = 'invoice';

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
            $query = Tx_invoice::leftJoin('userdetails AS usr','tx_invoices.created_by','=','usr.user_id')
            ->leftJoin('mst_customers','tx_invoices.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
            ->select(
                'tx_invoices.id as tx_id',
                'tx_invoices.invoice_no',
                'tx_invoices.tax_invoice_no',
                'tx_invoices.invoice_date',
                'tx_invoices.do_total',
                'tx_invoices.do_grandtotal_vat',
                'tx_invoices.approved_by',
                'tx_invoices.canceled_by',
                'tx_invoices.active as inv_active',
                'tx_invoices.created_by as createdby',
                'tx_invoices.created_at as createdat',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_customers.name as cust_name',
                'mst_customers.customer_unique_code',
                'usr_sales.initial as sales_initial',
                'ety_type.title_ind as ety_type_name',
            )
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use ($userLogin) {
                $q->where('usr.branch_id','=', $userLogin->branch_id);
            })
            ->orderBy('tx_invoices.is_draft', 'DESC')
            ->orderBy('tx_invoices.invoice_no', 'DESC');

            return DataTables::of($query)
            ->filterColumn('invoice_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_invoices.invoice_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('invoice_date', function ($query) {
                return date_format(date_create($query->invoice_date),"d/m/Y");
            })
            ->addColumn('createdat', function ($query) {
                return date_format(date_add(date_create($query->createdat), date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours")),"d/m/Y");
            })
            ->addColumn('total_with_retur_if_any', function ($query) {
                $totRetur = Tx_nota_retur::whereRaw('approved_by IS NOT NULL')
                ->where([
                    'active' => 'Y',
                ])
                ->whereIn('id', function($q) use($query){
                    $q->select('nota_retur_id')
                    ->from('tx_nota_retur_parts')
                    ->whereIn('sales_order_part_id', function($q1) use($query){
                        $q1->select('sales_order_part_id')
                        ->from('tx_delivery_order_parts')
                        ->whereIn('delivery_order_id', function($q2) use($query){
                            $q2->select('fk_id')
                            ->from('tx_invoice_details')
                            ->where([
                                'invoice_id' => $query->tx_id,
                            ]);
                        });
                    });
                })
                ->sum('total_after_vat');

                return number_format($query->do_grandtotal_vat,0,".",",").($totRetur>0?
                    '<br/><span style="color:red;">-'.number_format($totRetur,0,".",",").'</span>':'');
            })
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->where(function($q) use($keyword){
                    $q->where('mst_customers.name', 'LIKE', "%{$keyword}%")
                    ->orWhere('mst_customers.customer_unique_code', 'LIKE', "%{$keyword}%")
                    ->orWhere('ety_type.title_ind', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('customer_name', function ($query) {
                return $query->customer_unique_code.' - '.$query->ety_type_name.' '.$query->cust_name;
            })
            ->addColumn('action', function ($query) {
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();

                $links = '';
                if (($query->createdby==Auth::user()->id || 
                    Auth::user()->id==1 || 
                    $userLogin->is_director=='Y' || 
                    $userLogin->is_branch_head=='Y') && $query->inv_active=='Y'){
                    if (strpos($query->invoice_no,"Draft")>0){
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id.'/edit?hf=2').'" style="text-decoration: underline;">Edit</a> |
                            <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                            <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                            <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                    }else{
                        // cek status di penerimaan customer
                        $qPyReceipt = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr','tx_payment_receipt_invoices.payment_receipt_id','=','tx_pr.id')
                        ->select(
                            'tx_payment_receipt_invoices.is_full_payment',
                        )
                        ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                        ->where([
                            'tx_payment_receipt_invoices.invoice_no'=>$query->invoice_no,
                            'tx_payment_receipt_invoices.active'=>'Y',
                            'tx_pr.active'=>'Y',
                        ])
                        ->orderBy('tx_pr.updated_at','DESC')
                        ->first();                        
                        if ($userLogin->is_director=='Y'){
                            if (!$qPyReceipt){
                                $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id.'/edit?hf=2').'" style="text-decoration: underline;">Edit</a> | ';
                            }
                            $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                                <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                                <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                        }else{                            
                            if ($qPyReceipt){
                                $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id.'/edit?hf=1').'" style="text-decoration: underline;">Edit</a> | ';
                            }
                            $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                                <a href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',1);">Print</a> |
                                <a download="" href="#" style="text-decoration: underline;" onclick="printDoc('.$query->tx_id.',2);">Download</a>';
                        }
                    }
                }else{
                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/invoice/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                $links = '<input type="hidden" name="delOrder'.$query->tx_id.'" id="delOrder'.$query->tx_id.'">';
                if ($query->inv_active=='Y' && strpos($query->invoice_no,'Draft')==0 && is_null($query->approved_by) && is_null($query->canceled_by)){
                    $links = 'Created';

                    // cek status di penerimaan customer
                    $qPyReceipt = Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr','tx_payment_receipt_invoices.payment_receipt_id','=','tx_pr.id')
                    ->select(
                        'tx_payment_receipt_invoices.is_full_payment',
                    )
                    ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                    ->where([
                        'tx_payment_receipt_invoices.invoice_no'=>$query->invoice_no,
                        'tx_payment_receipt_invoices.active'=>'Y',
                        'tx_pr.active'=>'Y',
                    ])
                    ->orderBy('tx_pr.updated_at','DESC')
                    ->first();
                    if ($qPyReceipt){
                        if ($qPyReceipt->is_full_payment=='Y'){
                            $links = 'Paid';
                        }else{
                            if ($qPyReceipt->is_full_payment=='N'){
                                $links = 'Partial';
                            }
                        }
                        // if ($qPyReceipt->is_full_payment=='N'){
                        //     $links = 'Partial';
                        // }
                    }
                }
                if ($query->inv_active=='Y' && strpos($query->invoice_no,'Draft')>0){
                    $links = 'Draft';
                }
                if ($query->inv_active=='N'){
                    $links = 'Canceled';
                }
                return $links;
            })
            ->rawColumns(['invoice_date','createdat','total_with_retur_if_any','customer_name','action','status'])
            ->toJson();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'is_director_now' => $userLogin->is_director,
            'is_branch_head_now' => $userLogin->is_branch_head,
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
        $is_director = '';
        $branch_id = '';
        $finance_admin_id = 0;
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
            $finance_admin_id = $userLogin->section_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
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

        $delivery_order = [];
        if(old('customer_id')){
            $delivery_order = Tx_delivery_order::where('customer_id','=',old('customer_id'))
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('tax_invoice_id','<>',null)
            ->where('active','=','Y')
            ->orderBy('delivery_order_date','DESC')
            ->orderBy('created_at','DESC')
            ->get();
        }

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        // $qPaymentTo = Mst_coa::select(
        //     'id',
        //     'coa_code',
        //     'coa_code_complete',
        //     'coa_name',
        // )
        // ->where(function($q1) {
        //     $q1->where('coa_code_complete','LIKE','111%')
        //     ->orWhere('coa_code_complete','LIKE','112%');
        //     // ->orWhere('coa_code_complete','LIKE','215%');
        // })
        // ->where(function($q2) {
        //     $q2->where('local','=','A')
        //     ->orWhere('local','=','P');
        // })
        // ->when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
        //     $c1->where([
        //         'branch_id'=>$branch_id,
        //     ]);
        // })
        // ->where([
        //     'coa_level' => 5,
        //     'active' => 'Y',
        // ])
        // ->orderBy('coa_name', 'ASC')
        // ->get();

        $qPaymentTo = Mst_coa::select(
            'id',
            'coa_code',
            'coa_code_complete',
            'coa_name',
        )
        ->where(function($qX){
            $qX->where(function($qA){
                $qA->whereIn('id', function($q1){
                    $q1->select('coa_code_id')
                    ->from('mst_automatic_journal_details')
                    ->whereRaw('(LOWER(`desc`)=\'bank\' OR LOWER(`desc`)=\'cash\' OR LOWER(`desc`)=\'customer deposit\')')
                    ->where([
                        'auto_journal_id' => 7,
                        'active' => 'Y',
                    ]);
                });
            })
            ->orWhere(function($qB){
                $qB->whereIn('id', function($q1){
                    $q1->select('coa_code_id')
                    ->from('mst_automatic_journal_detail_exts')
                    ->whereRaw('(LOWER(`desc`)=\'bank\' OR LOWER(`desc`)=\'cash\' OR LOWER(`desc`)=\'customer deposit\')')
                    ->where([
                        'auto_journal_id' => 7,
                        'active' => 'Y',
                    ]);
                });
            });
        })
        ->when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
            'active' => 'Y',
        ])
        ->orderBy('coa_name', 'ASC')
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
            'qPaymentTo' => $qPaymentTo,
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 52,
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

        $validateInput = [
            'customer_id' => 'required|numeric',
            'all_selected_FK' => ['required', new CheckApprovedRetur()],
            'invoice_date' => 'required',
            'payment_to_id' => 'required|numeric',
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
            'invoice_date.required' => 'Plan Date must be filled',
            'payment_to_id.required' => 'Please select a valid Payment To',
            'payment_to_id.numeric' => 'Please select a valid Payment To',
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

            $vat_val = 0;
            $vat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vat){
                $vat_val = $vat->numeric_val;
            }

            $invoice_date = explode('/',$request->invoice_date);
            $ins = Tx_invoice::create([
                'invoice_no' => $invoice_no,
                'customer_id' => $request->customer_id,
                'invoice_date' => $invoice_date[2].'-'.$invoice_date[1].'-'.$invoice_date[0],
                'invoice_expired_date' => $qDOexpdate->faktur_expired_date,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'payment_to_id' => ($request->payment_to_id!='#'?$request->payment_to_id:null),
                'do_total' => $request->totalValbeforeVAT,
                'do_vat' => ($request->totalValafterVAT-$request->totalValbeforeVAT),
                'do_grandtotal_vat' => $request->totalValafterVAT,
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'vat_val' => $vat_val,
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
                    ->select(
                        'tx_delivery_orders.*',
                        'tx_delivery_orders.id as faktur_id',
                        'tx_tax_invoices.fp_no'
                    )
                    ->where('tx_delivery_orders.delivery_order_no','=',$fk)
                    ->first();
                    if($qDO){
                        // nota retur - begin
                        $retur_total_before_vat = 0;
                        $nota_retur = Tx_nota_retur::select(
                            'total_before_vat'
                        )
                        ->whereRaw('approved_by IS NOT null')
                        ->where([
                            'delivery_order_id'=>$qDO->faktur_id,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($nota_retur){
                            $retur_total_before_vat = $nota_retur->total_before_vat;
                        }
                        // nota retur - end

                        $ins_dtl = Tx_invoice_detail::create([
                            'invoice_id' => $maxId,
                            'fk_id' => $qDO->id,
                            'delivery_order_no' => $qDO->delivery_order_no,
                            'delivery_order_date' => $qDO->delivery_order_date,
                            'tax_invoice_id' => $qDO->tax_invoice_id,
                            'fp_no' => $qDO->fp_no,
                            'total' => ($qDO->total_before_vat-$retur_total_before_vat),
                            'vat' => ($qDO->total_after_vat-$qDO->total_before_vat),
                            'grand_total' => ($qDO->total_after_vat-$retur_total_before_vat),
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
            
            $delivery_order = Tx_delivery_order::where('customer_id','=',$query->customer_id)
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('active','=','Y')
            ->orderBy('delivery_order_date','DESC')
            ->orderBy('created_at','DESC')
            ->get();

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
    public function edit($id, Request $request)
    {
        $is_director = '';
        $branch_id = '';
        $finance_admin_id = 0;
        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();
        if ($userLogin){
            $is_director = $userLogin->is_director;
            $branch_id = $userLogin->branch_id;
            $finance_admin_id = $userLogin->section_id;
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $queryCustomer = Mst_customer::when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            $c1->where([
                'branch_id'=>$branch_id,
            ]);
        })
        ->where([
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

            $delivery_order = [];
            if(old('customer_id')){
                $delivery_order = Tx_delivery_order::whereNotIn('id', function($q){
                    $q->select('fk_id')
                    ->from('tx_invoice_details')
                    ->where('active','=','Y');
                })
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->whereRaw('tax_invoice_id IS NOT NULL')
                ->where([
                    'customer_id'=>old('customer_id'),
                    'active'=>'Y',
                ])
                ->orderBy('delivery_order_date','DESC')
                ->orderBy('created_at','DESC')
                ->get();
            }else{
                $delivery_order = Tx_delivery_order::whereNotIn('id', function($q){
                    $q->select('fk_id')
                    ->from('tx_invoice_details')
                    ->where('active','=','Y');
                })
                ->where('delivery_order_no','NOT LIKE','%Draft%')
                ->whereRaw('tax_invoice_id IS NOT NULL')
                ->where([
                    'customer_id'=>$query->customer_id,
                    'active'=>'Y',
                ])
                ->orderBy('delivery_order_date','DESC')
                ->orderBy('created_at','DESC')
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

            // $qPaymentTo = Mst_coa::select(
            //     'id',
            //     'coa_code',
            //     'coa_code_complete',
            //     'coa_name',
            // )
            // ->where(function($q1) {
            //     $q1->where('coa_code_complete','LIKE','111%')
            //     ->orWhere('coa_code_complete','LIKE','112%');
            // })
            // ->where(function($q2) {
            //     $q2->where('local','=','A')
            //     ->orWhere('local','=','P');
            // })
            // ->when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
            //     $c1->where([
            //         'branch_id'=>$branch_id,
            //     ]);
            // })
            // ->where([
            //     'coa_level' => 5,
            //     // 'local' => 'P',
            //     'active' => 'Y',
            // ])
            // ->orderBy('coa_name', 'ASC')
            // ->get();

            $qPaymentTo = Mst_coa::select(
                'id',
                'coa_code',
                'coa_code_complete',
                'coa_name',
            )
            ->where(function($qX){
                $qX->where(function($qA){
                    $qA->whereIn('id', function($q1){
                        $q1->select('coa_code_id')
                        ->from('mst_automatic_journal_details')
                        ->whereRaw('(LOWER(`desc`)=\'bank\' OR LOWER(`desc`)=\'cash\' OR LOWER(`desc`)=\'customer deposit\')')
                        ->where([
                            'auto_journal_id' => 7,
                            'active' => 'Y',
                        ]);
                    });
                })
                ->orWhere(function($qB){
                    $qB->whereIn('id', function($q1){
                        $q1->select('coa_code_id')
                        ->from('mst_automatic_journal_detail_exts')
                        ->whereRaw('(LOWER(`desc`)=\'bank\' OR LOWER(`desc`)=\'cash\' OR LOWER(`desc`)=\'customer deposit\')')
                        ->where([
                            'auto_journal_id' => 7,
                            'active' => 'Y',
                        ]);
                    });
                });
            })
            ->when($is_director!='Y' && $finance_admin_id!=37, function($c1) use($branch_id) {
                $c1->where([
                    'branch_id'=>$branch_id,
                ]);
            })
            ->where([
                'active' => 'Y',
            ])
            ->orderBy('coa_name', 'ASC')
            ->get();

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
                'qPaymentTo' => $qPaymentTo,
            ];
            if ($request->hf==1){
                return view('tx.'.$this->folder.'.edit_hefo', $data);
            }else{
                return view('tx.'.$this->folder.'.edit', $data);
            }
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
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 52,
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
        
        $validateInput = [
            'customer_id' => 'required|numeric',
            'all_selected_FK' => ['required', new CheckApprovedRetur()],
            'invoice_date' => 'required',
            'payment_to_id' => 'required|numeric',
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
            'invoice_date.required' => 'Plan Date must be filled',
            'payment_to_id.required' => 'Please select a valid Payment To',
            'payment_to_id.numeric' => 'Please select a valid Payment To',
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

            $invoice_date = explode('/',$request->invoice_date);
            $upd = Tx_invoice::where('id','=',$id)
            ->update([
                'invoice_date' => $invoice_date[2].'-'.$invoice_date[1].'-'.$invoice_date[0],
                'do_total' => $request->totalValbeforeVAT,
                'do_vat' => ($request->totalValafterVAT-$request->totalValbeforeVAT),
                'do_grandtotal_vat' => $request->totalValafterVAT,
                'branch_id' => ($request->branch_id!='#'?$request->branch_id:null),
                'payment_to_id' => ($request->payment_to_id!='#'?$request->payment_to_id:null),
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
                    ->select(
                        'tx_delivery_orders.*',
                        'tx_delivery_orders.id as faktur_id',
                        'tx_tax_invoices.fp_no'
                    )
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
                            // nota retur - begin
                            $retur_total_before_vat = 0;
                            $nota_retur = Tx_nota_retur::select(
                                'total_before_vat'
                            )
                            ->whereRaw('approved_by IS NOT null')
                            ->where([
                                'delivery_order_id'=>$qDO->faktur_id,
                                'active'=>'Y',
                            ])
                            ->first();
                            if ($nota_retur){
                                $retur_total_before_vat = $nota_retur->total_before_vat;
                            }
                            // nota retur - end

                            $ins_dtl = Tx_invoice_detail::create([
                                'invoice_id' => $id,
                                'fk_id' => $qDO->id,
                                'delivery_order_no' => $qDO->delivery_order_no,
                                'delivery_order_date' => $qDO->delivery_order_date,
                                'tax_invoice_id' => $qDO->tax_invoice_id,
                                'fp_no' => $qDO->fp_no,
                                'total' => ($qDO->total_before_vat-$retur_total_before_vat),
                                'vat' => ($qDO->total_after_vat-$qDO->total_before_vat),
                                'grand_total' => ($qDO->total_after_vat-$retur_total_before_vat),
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

    public function update_hefo(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 52,
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

        // Start transaction!
        DB::beginTransaction();

        try {

            $upd = Tx_invoice::where('id','=',$id)
            ->update([
                'header' => $request->header,
                'footer' => $request->footer,
                'remark' => $request->remark,
                'active' => 'Y',
                'updated_by' => Auth::user()->id,
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

    public function rptInvoice(Request $request)
    {
        $validateInput = [
            'start_date' => 'required',
            'end_date' => 'required',
        ];
        $errMsg = [
            'start_date.required' => 'Start Date must be filled',
            'end_date.required' => 'End Date must be filled',
        ];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        return redirect('tx/invoice-xlsx/'.
                $request->start_date.'/'.
                $request->end_date);
    }
}
