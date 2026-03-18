<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Rules\ROisPaid;
use App\Models\Auto_inc;
use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use App\Rules\NumericCustom;
use Illuminate\Http\Request;
use App\Models\Mst_menu_user;
use Illuminate\Validation\Rule;
use App\Models\Tx_lokal_journal;
use App\Models\Tx_purchase_memo;
use App\Models\Tx_receipt_order;
use App\Rules\CheckDupInvoiceNo;
use App\Helpers\GlobalFuncHelper;
use App\Models\Tx_purchase_order;
use App\Rules\ApprovalCheckingRO;
use App\Models\Tx_general_journal;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Helpers\OutstandingSoSjHelper;
use App\Models\Tx_purchase_order_part;
use App\Models\Tx_lokal_journal_detail;
use App\Rules\CheckAmountEqualWithTotal;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Tx_general_journal_detail;
use Illuminate\Support\Facades\Validator;
use App\Models\Tx_payment_voucher_invoice;
use App\Models\Tx_tagihan_supplier_detail;
use App\Models\Mst_automatic_journal_detail;
use App\Models\Mst_supplier_bank_information;
// use App\Rules\ValidateBeaMasukForSupplierType;
use Illuminate\Validation\ValidationException;
use App\Rules\ValidateExchangeRateForSupplierType;

class ReceiptOrderServerSideController extends Controller
{
    protected $title = 'Receipt Order';
    protected $folder = 'receipt-order';
    protected $journal_type = ['P','N'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $param=null)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $parameter = explode('::',$param);
        if(count($parameter)<4){
            return redirect(route('ro.index').'/'.urlencode('::::::::'));
        }
        if ($request->ajax()) {
            $query = Tx_receipt_order::leftJoin('userdetails AS usr','tx_receipt_orders.created_by','=','usr.user_id')
            ->leftJoin('mst_suppliers','tx_receipt_orders.supplier_id','=','mst_suppliers.id')
            ->leftJoin('mst_globals as curr','tx_receipt_orders.currency_id','=','curr.id')
            ->leftJoin('mst_globals as ent','mst_suppliers.entity_type_id','=','ent.id')
            ->leftJoin('tx_purchase_returs as tx_pr','tx_receipt_orders.id','=','tx_pr.receipt_order_id')
            ->select(
                'tx_receipt_orders.id as tx_id',
                'tx_receipt_orders.receipt_no',
                'tx_receipt_orders.po_or_pm_no',
                'tx_receipt_orders.invoice_no',
                'tx_receipt_orders.total_before_vat',
                'tx_receipt_orders.total_before_vat_rp',
                'tx_receipt_orders.total_after_vat',
                'tx_receipt_orders.total_after_vat_rp',
                'tx_receipt_orders.exchange_rate',
                'tx_receipt_orders.receipt_date',
                'tx_receipt_orders.active as ro_active',
                'tx_receipt_orders.created_by as createdBy',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'mst_suppliers.name as supplier_name',
                'mst_suppliers.supplier_code',
                'mst_suppliers.supplier_type_id',
                'curr.string_val as curr_nm',
                'ent.string_val as supplier_entity_type_name',
                'tx_pr.purchase_retur_no',
                'tx_pr.total_before_vat as total_before_vat_pr',
                'tx_pr.approved_by as approved_by_pr',
            )
            ->where('tx_receipt_orders.active','=','Y')
            ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->when($parameter[0]<>'', function($q) use($parameter){
                $q->where('mst_suppliers.id', '=', $parameter[0]);
            })
            ->when($parameter[1]<>'' && $parameter[2]<>'', function($q) use($parameter){
                $d1 = explode('-', $parameter[1]);
                $d2 = explode('-', $parameter[2]);
                $q->whereRaw('tx_receipt_orders.receipt_date>=\''.$d1[2].'-'.$d1[1].'-'.$d1[0].'\' 
                    AND tx_receipt_orders.receipt_date<=\''.$d2[2].'-'.$d2[1].'-'.$d2[0].'\'');
            })
            ->when($parameter[3]<>'', function($q) use($parameter){
                $q->whereRaw('(CASE 
                    WHEN tx_receipt_orders.is_draft=\'Y\' THEN \'draft\'
                    WHEN (tx_receipt_orders.is_draft=\'N\' 
                        AND (SELECT COUNT(*) FROM tx_payment_voucher_invoices AS tx_pvi 
                        WHERE tx_pvi.receipt_order_id=tx_receipt_orders.id 
                        AND tx_pvi.active=\'Y\')=0 
                        AND (SELECT COUNT(*) FROM tx_tagihan_supplier_details AS tx_tsd 
                        WHERE tx_tsd.receipt_order_id=tx_receipt_orders.id 
                        AND tx_tsd.active=\'Y\')=0) THEN \'created\'
                    WHEN (tx_receipt_orders.is_draft=\'N\' 
                        AND (SELECT COUNT(*) 
                        FROM tx_payment_voucher_invoices AS tx_pvi 
                        LEFT JOIN tx_payment_vouchers AS tx_pv ON tx_pvi.payment_voucher_id=tx_pv.id
                        WHERE tx_pvi.receipt_order_id=tx_receipt_orders.id 
                        AND tx_pvi.is_full_payment=\'Y\'
                        AND tx_pvi.active=\'Y\'
                        AND tx_pv.approved_by IS NOT null
                        AND tx_pv.active=\'Y\')>0) THEN \'paid\'
                    WHEN (tx_receipt_orders.is_draft=\'N\' 
                        AND (SELECT COUNT(*) 
                        FROM tx_payment_voucher_invoices AS tx_pvi 
                        LEFT JOIN tx_payment_vouchers AS tx_pv ON tx_pvi.payment_voucher_id=tx_pv.id
                        WHERE tx_pvi.receipt_order_id=tx_receipt_orders.id 
                        AND tx_pvi.is_full_payment=\'N\'
                        AND tx_pvi.active=\'Y\'
                        AND tx_pv.approved_by IS NOT null
                        AND tx_pv.active=\'Y\')>0) THEN \'partial\'
                    WHEN (tx_receipt_orders.is_draft=\'N\' 
                        AND (SELECT COUNT(*) FROM tx_payment_voucher_invoices AS tx_pvi 
                        WHERE tx_pvi.receipt_order_id=tx_receipt_orders.id 
                        AND tx_pvi.active=\'Y\')=0 
                        AND (SELECT COUNT(*) FROM tx_tagihan_supplier_details AS tx_tsd 
                        WHERE tx_tsd.receipt_order_id=tx_receipt_orders.id 
                        AND tx_tsd.active=\'Y\')>0) THEN \'ts\'
                    ELSE \'\'
                    END)=\''.$parameter[3].'\'');
            })
            ->orderBy('tx_receipt_orders.receipt_no', 'DESC')
            ->orderBy('tx_receipt_orders.created_at', 'DESC');

            return DataTables::of($query)
            ->filterColumn('po_mo_no', function($query, $keyword) {
                $query->whereIn('tx_receipt_orders.id', function($q) use($keyword) {
                    $q->select('receipt_order_id')
                    ->from('tx_receipt_order_parts')
                    ->where('active', '=', 'Y')
                    ->where('po_mo_no', 'LIKE', "%{$keyword}%");
                });
            })
            ->editColumn('po_mo_no', function ($query) {
                $links = '';
                $po_mo_all = explode(",",$query->po_or_pm_no);
                foreach($po_mo_all as $po_mo){
                    if($po_mo!=''){
                        if (strpos('x'.$po_mo,'MO')>=0){
                            $qMo = Tx_purchase_memo::where('memo_no','=',$po_mo)
                            ->first();
                            if($qMo){
                                $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$qMo->id).'" target="_new" style="text-decoration: underline;">'.$po_mo.'</a><br/>';
                            }
                        }
                        if (strpos('x'.$po_mo,'PO')>=0){
                            $qPo = Tx_purchase_order::where('purchase_no','=',$po_mo)
                            ->first();
                            if($qPo){
                                $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/order/'.$qPo->id).'" target="_new" style="text-decoration: underline;">'.$po_mo.'</a><br/>';
                            }
                        }
                    }
                }
                return $links;
            })
            ->filterColumn('receipt_date', function($query, $keyword) {
                $query->whereRaw('DATE_FORMAT(tx_receipt_orders.receipt_date, "%d/%m/%Y") LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('receipt_date', function ($query) {
                return date_format(date_create($query->receipt_date),"d/m/Y");
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
            ->filterColumn('purchase_retur_info', function($query, $keyword) {
                $query->whereRaw('tx_pr.purchase_retur_no LIKE ?', ["%{$keyword}%"]);
            })
            ->editColumn('purchase_retur_info', function ($query) use($qCurrency) {
                if (!is_null($query->purchase_retur_no) && !is_null($query->approved_by_pr)){
                    return $query->purchase_retur_no.'<br/>'.($qCurrency?$qCurrency->string_val:'').number_format($query->total_before_vat_pr,0,",",".");
                }else{
                    return '';
                }
            })
            ->addColumn('total_price', function ($query) {
                if ($query->total_after_vat_rp!=null && $query->total_after_vat_rp>0){
                    return number_format($query->total_after_vat_rp,0,",",".");
                }else{
                    return number_format($query->total_after_vat,0,",",".");
                }
            })
            ->addColumn('action', function ($query) {
                $links = '';
                $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
                ->first();
                if ($userLogin){
                    if((($query->createdBy==Auth::user()->id || $userLogin->is_director=='Y' || $userLogin->is_branch_head=='Y') && $query->ro_active=='Y')){
                        if(strpos($query->receipt_no,"Draft")){
                            $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->tx_id.'/edit').'" style="text-decoration: underline;">Edit</a> |
                                <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                        }else{
                            if ($userLogin->section_id==37 || $userLogin->section_id==46 || $userLogin->section_id==48 ){
                                // tagihan supplier
                                $qTagihanSupplier = Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tx_ts','tx_tagihan_supplier_details.tagihan_supplier_id','=','tx_ts.id')
                                ->select(
                                    'tx_tagihan_supplier_details.receipt_order_id',
                                )
                                ->where([
                                    'tx_tagihan_supplier_details.receipt_order_id'=>$query->tx_id,
                                    'tx_tagihan_supplier_details.active'=>'Y',
                                    'tx_ts.active'=>'Y',
                                ])
                                ->first();

                                // payment voucher
                                $qPySupplier = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as tx_pv','tx_payment_voucher_invoices.payment_voucher_id','=','tx_pv.id')
                                ->select(
                                    'tx_payment_voucher_invoices.is_full_payment',
                                )
                                ->whereRaw('tx_pv.approved_by IS NOT NULL')
                                ->where([
                                    'tx_payment_voucher_invoices.receipt_order_id'=>$query->tx_id,
                                    'tx_payment_voucher_invoices.active'=>'Y',
                                    'tx_pv.active'=>'Y',
                                ])
                                ->orderBy('tx_pv.created_at','DESC')
                                ->first();
                                if (!$qPySupplier && !$qTagihanSupplier){
                                    // boleh edit jika belum ada PV dan TS
                                    $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->tx_id.'/edit?jt=Y').'" 
                                        style="text-decoration: underline;">Edit Journal</a> | ';
                                }
                            }
                            $links .= '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a> |
                                <a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-receipt-order/'.$query->tx_id).'" target="_new" style="text-decoration: underline;">Print</a> |
                                <a download="" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/print-receipt-order/'.$query->tx_id).'" style="text-decoration: underline;">Download</a>';
                        }
                    }else{
                        $links = '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$query->tx_id).'" style="text-decoration: underline;">View</a>';
                    }
                }
                return $links;
            })
            ->addColumn('status', function ($query) {
                $status = 'Created';
                if (strpos($query->receipt_no,"Draft")){
                    $status = 'Draft';
                }

                $qTagihanSupplier = Tx_tagihan_supplier_detail::leftJoin('tx_tagihan_suppliers as tx_ts','tx_tagihan_supplier_details.tagihan_supplier_id','=','tx_ts.id')
                ->select(
                    'tx_tagihan_supplier_details.receipt_order_id',
                )
                ->where([
                    'tx_tagihan_supplier_details.receipt_order_id'=>$query->tx_id,
                    'tx_tagihan_supplier_details.active'=>'Y',
                    'tx_ts.active'=>'Y',
                ])
                ->first();
                if ($qTagihanSupplier){
                    $status = 'TS';
                }

                $qPySupplier = Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as tx_pv','tx_payment_voucher_invoices.payment_voucher_id','=','tx_pv.id')
                ->select(
                    'tx_payment_voucher_invoices.is_full_payment',
                )
                ->whereRaw('tx_pv.approved_by IS NOT NULL')
                ->where([
                    'tx_payment_voucher_invoices.receipt_order_id'=>$query->tx_id,
                    'tx_payment_voucher_invoices.active'=>'Y',
                    'tx_pv.active'=>'Y',
                ])
                ->orderBy('tx_pv.created_at','DESC')
                ->first();
                if ($qPySupplier){
                    switch ($qPySupplier->is_full_payment) {
                        case 'Y':
                            $status = 'Paid';
                            break;
                        case 'N':
                            $status = 'Partial';
                            break;
                        default:
                            $status = '-';
                    }
                }
                return $status;
            })
            ->rawColumns(['po_mo_no','supplier_name','total_price','receipt_date','purchase_retur_info','action','status'])
            ->toJson();
        }

        $suppliers = Mst_supplier::where([
            'active' => 'Y'
        ])
        ->orderBy('name','ASC')
        ->get();

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'qCurrency' => $qCurrency,
            'suppliers' => $suppliers,
            'supplier_id' => $parameter[0],
            'start_date' => $parameter[1],
            'end_date' => $parameter[2],
            'st' => $parameter[3],
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
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $userLogin = Userdetail::where('user_id','=',Auth::user()->id)
        ->first();

        $querySupplier = Mst_supplier::where([
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

        // branch
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $order = [];
        $currency_code = '';
        if(old('supplier_id')){
            $qCurr = Mst_supplier_bank_information::leftJoin('mst_globals as curr','mst_supplier_bank_information.currency_id','=','curr.id')
            ->select(
                'curr.title_ind as curr_name',
                'curr.string_val as curr_code',
                'curr.id as curr_id'
                )
            ->where([
                'mst_supplier_bank_information.supplier_id' => old('supplier_id'),
            ])
            ->orderBy('mst_supplier_bank_information.created_at','ASC')
            ->first();
            if ($qCurr){
                $currency_code = $qCurr->curr_code;
            }

            $memo = Tx_purchase_memo::leftJoin('userdetails AS usr','tx_purchase_memos.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_memos.memo_no AS order_no',
                'tx_purchase_memos.is_vat',
            )
            ->where('tx_purchase_memos.memo_no','NOT LIKE','%Draft%')
            ->addSelect(['memo_po_qty' => Tx_purchase_memo_part::selectRaw('SUM(tx_purchase_memo_parts.qty)')
                ->whereColumn('tx_purchase_memo_parts.memo_id','tx_purchase_memos.id')
                ->where('tx_purchase_memo_parts.active','=','Y')
            ])
            ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
                ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_memos.memo_no')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where([
                'tx_purchase_memos.supplier_id' => old('supplier_id'),
                'tx_purchase_memos.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            });

            $order = Tx_purchase_order::leftJoin('userdetails AS usr','tx_purchase_orders.created_by','=','usr.user_id')
            ->select(
                'tx_purchase_orders.purchase_no AS order_no',
                'tx_purchase_orders.is_vat',
            )
            ->addSelect(['memo_po_qty' => Tx_purchase_order_part::selectRaw('SUM(tx_purchase_order_parts.qty)')
                ->whereColumn('tx_purchase_order_parts.order_id','tx_purchase_orders.id')
                ->where('tx_purchase_order_parts.active','=','Y')
            ])
            ->addSelect(['memo_po_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
                ->whereColumn('tx_receipt_order_parts.po_mo_no','tx_purchase_orders.purchase_no')
                ->where('tx_receipt_order_parts.active','=','Y')
            ])
            ->where('tx_purchase_orders.purchase_no','NOT LIKE','%Draft%')
            ->where('tx_purchase_orders.approved_by','<>',null)
            ->where([
                'tx_purchase_orders.supplier_id' => old('supplier_id'),
                'tx_purchase_orders.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where('usr.branch_id','=',$userLogin->branch_id);
            })
            ->union($memo)
            ->get();
        }

        $data = [
            'title' => $this->title,
            'folder' => $this->folder,
            'querySupplier' => $querySupplier,
            'parts' => $parts,
            'weighttype' => $weighttype,
            'couriers' => $couriers,
            'totalRow' => (old('totalRow') ? old('totalRow') : 0),
            'vat' => $vat,
            'get_po_pm_no' => $order,
            'qCurrency' => $qCurrency,
            'branches' => $branches,
            'userLogin' => $userLogin,
            'journal_type' => $this->journal_type,
            'currency_code' => $currency_code,
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
            'menu_id' => 29,
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
            'supplier_id' => 'required|numeric',
            'journal_type_id' => ['required', Rule::in(['P', 'N'])],
            'invoice_no' => ['required',new CheckDupInvoiceNo($request->supplier_id,$request->invoice_no,0)],
            'invoice_amount' => ['required', new NumericCustom('Invoice Amount'), new CheckAmountEqualWithTotal($request->lastTotalAmountTmp)],
            'exc_rate' => [new NumericCustom('Exchange Rate'), new ValidateExchangeRateForSupplierType($request->supplier_id),'nullable'],
            'vat_import' => [new NumericCustom('VAT Import'), 'nullable'],
            'bea_masuk_val' => [new NumericCustom('Bea Masuk'), 'nullable'],
            'import_shipping_cost_val' => [new NumericCustom('Import Shipping Cost'), 'nullable'],
            'bl_no' => 'required',
            'gross_weight' => [new NumericCustom('Gross Weight'),'nullable'],
            'measurement' => [new NumericCustom('Measurement'),'nullable'],
            'courier_id' => 'required_if:courier_type,3'
        ];

        $errMsg = [
            'supplier_id.required' => 'Please select a valid supplier',
            'supplier_id.numeric' => 'Please select a valid supplier',
            'journal_type_id.required' => 'Please select a journal type',
            'bl_no.required' => 'The B/L No field is required.',
            'weight_type_id01.numeric' => 'Please select a valid weight type',
            'weight_type_id02.numeric' => 'Please select a valid weight type',
            'courier_id.numeric' => 'Please select a valid ship by',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty'.$i => 'required|numeric|lte:qty_on_po'.$i.'|min:0',
                    ];
                    $errShipmentMsg = [
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'qty'.$i.'.lte' => 'The qty must be less than '.$request['qty_on_po'.$i].'.',
                        'qty'.$i.'.min' => 'The qty must be at least 0.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
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
            $identityName = 'tx_receipt_orders-draft';
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
                $order_no = ENV('P_RECEIPT_ORDER').date('y').'-Draft'.strval($newInc);
            }

            $identityName = 'tx_receipt_orders';
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
                $order_no = ENV('P_RECEIPT_ORDER').date('y').'-'.$zero.strval($newInc);
            }

            $qSupplier = Mst_supplier::where('id', '=', $request->supplier_id)
            ->first();

            $vat_val = 0;
            $vat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vat){
                $vat_val = $vat->numeric_val;
            }

            $exc_rate = (($request->exc_rate!='' && $request->exc_rate!=0)?GlobalFuncHelper::moneyValidate($request->exc_rate):null);
            $vat_import = (($request->vat_import!='' && $request->vat_import!=0)?GlobalFuncHelper::moneyValidate($request->vat_import):null);
            $bea_masuk_val = (($request->bea_masuk_val!='' && $request->bea_masuk_val!=0)?GlobalFuncHelper::moneyValidate($request->bea_masuk_val):null);
            $import_shipping_cost_val = (($request->import_shipping_cost_val!='' && $request->import_shipping_cost_val!=0)?GlobalFuncHelper::moneyValidate($request->import_shipping_cost_val):null);

            $receipt_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");
            
            $ins = Tx_receipt_order::create([
                'receipt_no' => $order_no,
                'receipt_date' => $receipt_date,
                'po_or_pm_no' => $request->po_pm_no_all,
                'journal_type_id' => $request->journal_type_id,
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'currency_id' => $request->currency_id,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'branch_id' => $request->branch_id,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
                'invoice_no' => $request->invoice_no,
                'invoice_amount' => GlobalFuncHelper::moneyValidate($request->invoice_amount),
                'exchange_rate' => $exc_rate,
                'exc_rate_for_vat' => 0,
                // 'exc_rate_for_vat' => $exch_rate_for_vat,
                'bea_masuk' => $bea_masuk_val,
                'import_shipping_cost' => $import_shipping_cost_val,
                'bl_no' => $request->bl_no,
                'vessel_no' => $request->vessel_no,
                'weight_type_id01' => is_numeric($request->weight_type_id01)?$request->weight_type_id01:null,
                'weight_type_id02' => is_numeric($request->weight_type_id02)?$request->weight_type_id02:null,
                'gross_weight' => !is_numeric($request->gross_weight)?null:GlobalFuncHelper::moneyValidate($request->gross_weight),
                'measurement' => !is_numeric($request->measurement)?null:GlobalFuncHelper::moneyValidate($request->measurement),
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

            // ambil info user termasuk cabang dari user pembuat RO
            $qUser = Tx_receipt_order::leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
            ->select('userdetails.branch_id AS user_branch_id')
            ->where('tx_receipt_orders.id','=',$maxId)
            ->first();

            $totalQty = 0;
            $totalPrice = 0;
            $is_partial_received_last = 'N';
            $is_vat_for_auto_journal = '';
            for($lastIdx=0;$lastIdx<$request->totalRow;$lastIdx++){
                if($request['qty'.$lastIdx]){
                    $price_fob = 0;
                    $price_local = 0;
                    $total_fob = 0;
                    $total_local = 0;
                    if($qSupplier->supplier_type_id==10){
                        // international - update price
                        $price_fob = $request['price_fob_val'.$lastIdx];
                        $price_local = $request['price_fob_val'.$lastIdx]*(is_numeric($exc_rate)?$exc_rate:1);
                        $total_fob = $request['qty'.$lastIdx]*$price_fob;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_fob;
                    }
                    if($qSupplier->supplier_type_id==11){
                        // lokal - update price
                        $price_fob = 0;
                        $price_local = $request['price_local_val'.$lastIdx];
                        $total_fob = 0;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_local;
                    }

                    $is_partial_received = 'N';
                    if($request['qty_on_po'.$lastIdx]>$request['qty'.$lastIdx]){
                        $is_partial_received = 'Y';
                        $is_partial_received_last = 'Y';
                    }

                    $totalQty += $request['qty'.$lastIdx];
                    $insPart = Tx_receipt_order_part::create([
                        'receipt_order_id' => $maxId,
                        'po_mo_no' => $request['po_mo_no'.$lastIdx],
                        'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                        'part_id' => $request['part_id'.$lastIdx],
                        'qty' => $request['qty'.$lastIdx],
                        'qty_on_po' => $request['qty_on_po'.$lastIdx],
                        'part_price' => $price_local,
                        'final_fob' => $price_fob,
                        'final_cost' => $price_local,
                        'total_fob_price' => $total_fob,
                        'total_price' => $total_local,
                        'is_partial_received' => $is_partial_received,
                        'active' => 'Y',
                        'created_by' => Auth::user()->id,
                        'updated_by' => Auth::user()->id
                    ]);

                    if (strpos('PO-MO-'.$request['po_mo_no'.$lastIdx],env('P_PURCHASE_MEMO'))>0){
                        $qMO = Tx_purchase_memo::where('memo_no','=',$request['po_mo_no'.$lastIdx])
                        ->first();
                        if ($qMO){
                            $is_vat_for_auto_journal = $qMO->is_vat;
                        }
                    }
                    if (strpos('PO-MO-'.$request['po_mo_no'.$lastIdx],env('P_PURCHASE_ORDER'))>0){
                        $qPO = Tx_purchase_order::where('purchase_no','=',$request['po_mo_no'.$lastIdx])
                        ->first();
                        if ($qPO){
                            $is_vat_for_auto_journal = $qPO->is_vat;
                        }
                    }

                    if(strpos($order_no,"Draft")==0){
                        // update qty and all price jika bukan DRAFT
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.avg_cost',
                            'mst_parts.price_list',
                            'tx_qty_parts.branch_id',
                        )
                        ->addSelect([
                            'qty' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->where('tx_qty_parts.branch_id','=',$request->branch_id)
                            ->limit(1)
                        ])
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_id'.$lastIdx],
                            'tx_qty_parts.branch_id' => $request->branch_id,
                        ])
                        ->first();
                        if($queryMstPart){
                            // ambil total qty dari part yang sudah masuk outstanding SO/SJ
                            $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_id'.$lastIdx]);

                            // ambil informasi part yang masuk
                            // cek keberadaan part di master part
                            $lastQty = is_null($queryMstPart->qty_total)?0:
                                ($queryMstPart->qty_total-$qtySoSj?$queryMstPart->qty_total-$qtySoSj:0);
                            $lastQtyPerBranch = is_null($queryMstPart->qty)?0:$queryMstPart->qty;
                            $lastPrice = ($queryMstPart->avg_cost==0)?$queryMstPart->price_list:$queryMstPart->avg_cost;
                            if (($lastQty+$request['qty'.$lastIdx])>0){
                                $avg_cost = (($lastQty*$lastPrice)+($request['qty'.$lastIdx]*$price_local))/($lastQty+$request['qty'.$lastIdx]);
                            }else{
                                $avg_cost = $price_local;
                            }

                            // update avg cost di part RO
                            $updTxRoPart = Tx_receipt_order_part::where([
                                'receipt_order_id' => $maxId,
                                'po_mo_no' => $request['po_mo_no'.$lastIdx],
                                'part_id' => $request['part_id'.$lastIdx],
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master qty utk masing2 part
                            $qtyPart = Tx_qty_part::where([
                                'part_id' => $queryMstPart->part_id_tmp,
                                'branch_id' => $request->branch_id,
                            ])
                            ->first();
                            if($qtyPart){
                                $updQty = Tx_qty_part::where([
                                    'part_id' => $qtyPart->part_id,
                                    'branch_id' => $qtyPart->branch_id,
                                ])
                                ->update([
                                    'qty' => $qtyPart->qty+($request['qty'.$lastIdx]?$request['qty'.$lastIdx]:0),
                                    'updated_by' => Auth::user()->id
                                ]);
                            }else{
                                // insert
                                $qtyPartIns = Tx_qty_part::create([
                                    'part_id' => $qtyPart->part_id,
                                    'branch_id' => $qtyPart->branch_id,
                                    'qty' => ($request['qty'.$lastIdx]?$request['qty'.$lastIdx]:0),
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }

                            // update master part terhadap part terkait
                            $upqMstPart = Mst_part::where([
                                'id' => $queryMstPart->part_id_tmp
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'initial_cost' => $queryMstPart->avg_cost,
                                'final_cost' => $price_local,
                                'total_cost' => ($lastQty+$request['qty'.$lastIdx])*$avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                }
            }

            if($is_partial_received_last=='Y'){
                // jika salah satu part ber status partial received (Y)
                // maka part yang lain ber status partial received (Y) juga
                $updPartialReceived = Tx_receipt_order_part::where([
                    'receipt_order_id' => $maxId,
                    'active' => 'Y'
                ])
                ->update([
                    'is_partial_received' => 'Y',
                    'updated_by' => Auth::user()->id
                ]);
            }

            $total_after_vat = ($is_vat_for_auto_journal=='Y'?$totalPrice+($totalPrice*$vat_val/100):$totalPrice);
            $total_before_vat_rp = 0;
            $total_vat_rp = 0;
            $total_after_vat_rp = 0;
            if($qSupplier->supplier_type_id==10){
                // international
                $total_after_vat = ($is_vat_for_auto_journal=='Y'?
                    (($totalPrice*$exc_rate)+($vat_import))/($exc_rate==0?1:$exc_rate):
                    ($totalPrice*$exc_rate)/($exc_rate==0?1:$exc_rate)
                );
                $total_before_vat_rp = $totalPrice*$exc_rate;
                $total_vat_rp = $vat_import;
                $total_after_vat_rp = $total_before_vat_rp+$total_vat_rp;
            }
            $updRO = Tx_receipt_order::where('id','=',$maxId)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_before_vat_rp' => $total_before_vat_rp,
                'total_vat' => ($total_after_vat-$totalPrice),
                'total_vat_rp' => $total_vat_rp,
                'total_after_vat' => $total_after_vat,
                'total_after_vat_rp' => $total_after_vat_rp,
                'vat_val' => ($is_vat_for_auto_journal=='Y'?$vat_val:0),
                'updated_by' => Auth::user()->id
            ]);

            // buat automatic general journal untuk receipt order
            if($request->is_draft!='Y'){

                if ($is_vat_for_auto_journal=='Y' || $request->journal_type_id=='P'){
                    // cek apakah fitur automatic journal RO PPN sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>5,
                        'branch_id'=>$request->branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if($qAutJournal){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // bea masuk import
                        $qAutJournal_bea_masuk_import = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'bea masuk import\'')
                        ->first();
                        // ppn masukan
                        $qAutJournal_ppn_masukan = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_general_journal::where([
                            'module_no'=>$order_no,
                            'automatic_journal_id'=>5,
                            'active'=>'Y',
                        ])
                        ->first();
                        if($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_general_journal_detail::where('general_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);

                        }else{
                            $yearTemp = substr(date("Y"),2,2);
                            $monthTemp = (strlen(date("m"))==1?'0'.$date("m"):date("m"));
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'tx_general_journal';
                            $draft_to_created_at = now();
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");
                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            if ($qSupplier->supplier_type_id==10){
                                // international
                                if ($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P'){
                                    $vat_tmp = 0;
                                }else{
                                    $vat_tmp = $vat_import;
                                }
                                $totalPrice_tmp = (is_numeric($exc_rate)?
                                    ($totalPrice*$exc_rate):
                                    ($totalPrice));

                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$vat_tmp+$totalPrice_tmp,
                                    'total_kredit'=>$vat_tmp+$totalPrice_tmp,
                                    'module_no'=>$order_no,
                                    'automatic_journal_id'=>5,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }else{
                                // lokal
                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                        ($totalPrice):
                                        (($totalPrice)+(($totalPrice+$bea_masuk_val)*$vat_val/100)),
                                    'total_kredit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                        ($totalPrice):
                                        (($totalPrice)+(($totalPrice+$bea_masuk_val)*$vat_val/100)),
                                    'module_no'=>$order_no,
                                    'automatic_journal_id'=>5,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }
                        }

                        if ($qSupplier->supplier_type_id==10){
                            // international
                            if ($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P'){
                                $vat_tmp = 0;
                            }else{
                                $vat_tmp = $vat_import;
                                // $vat_tmp = ((($totalPrice+$import_shipping_cost_val)*$exch_rate_for_vat)+$bea_masuk_val)*$vat_val/100;
                            }
                            $totalPrice_tmp = (is_numeric($exc_rate)?
                                ($totalPrice*$exc_rate):$totalPrice);

                            // inventory
                            $ins_inventory = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bea masuk import
                            // $ins_bea_masuk_import = Tx_general_journal_detail::create([
                            //     'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            //     'coa_id'=>$qAutJournal_bea_masuk_import->coa_code_id,
                            //     'coa_detail_id'=>null,
                            //     'description'=>null,
                            //     'debit'=>$bea_masuk_val,
                            //     'kredit'=>0,
                            //     'active'=>'Y',
                            //     'created_by'=>Auth::user()->id,
                            //     'updated_by'=>Auth::user()->id,
                            // ]);

                            // ppn masukan
                            $ins_ppn_masukan = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$vat_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$vat_tmp+$totalPrice_tmp,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            // inventory
                            $ins_inventory = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bea masuk import
                            // $ins_bea_masuk_import = Tx_general_journal_detail::create([
                            //     'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            //     'coa_id'=>$qAutJournal_bea_masuk_import->coa_code_id,
                            //     'coa_detail_id'=>null,
                            //     'description'=>null,
                            //     'debit'=>($bea_masuk_val==null?0:$bea_masuk_val),
                            //     'kredit'=>0,
                            //     'active'=>'Y',
                            //     'created_by'=>Auth::user()->id,
                            //     'updated_by'=>Auth::user()->id,
                            // ]);

                            // ppn masukan
                            $ins_ppn_masukan = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?0:(($totalPrice+$bea_masuk_val)*$vat_val/100),
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                    ($totalPrice):(($totalPrice)+(($totalPrice+$bea_masuk_val)*$vat_val/100)),
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }
                }

                if ($is_vat_for_auto_journal=='N' && ($request->journal_type_id=='N' || $request->journal_type_id=='' || $request->journal_type_id==null)){
                    // cek apakah fitur automatic journal RO non PPN sudah tersedia
                    $qAutJournalNonPpn = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>6,
                        'branch_id'=>$request->branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournalNonPpn){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_lokal_journal::where([
                            'module_no'=>$order_no,
                            'automatic_journal_id'=>6,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $yearTemp = substr(date("Y"),2,2);
                            $monthTemp = (strlen(date("m"))==1?'0'.$date("m"):date("m"));
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'Tx_lokal_journal';
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");

                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            if ($qSupplier->supplier_type_id==10){
                                // international
                                $totalPrice_tmp = is_numeric($exc_rate)?$exc_rate*$totalPrice:$totalPrice;

                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$totalPrice_tmp,
                                    'total_kredit'=>$totalPrice_tmp,
                                    'module_no'=>$order_no,
                                    'automatic_journal_id'=>6,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);

                            }else{
                                // lokal
                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$totalPrice,
                                    'total_kredit'=>$totalPrice,
                                    'module_no'=>$order_no,
                                    'automatic_journal_id'=>6,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }
                        }

                        if ($qSupplier->supplier_type_id==10){
                            // international
                            $totalPrice_tmp = is_numeric($exc_rate)?$exc_rate*$totalPrice:$totalPrice;

                            // inventory
                            $ins_inventory = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$totalPrice_tmp,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            // inventory
                            $ins_inventory = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$totalPrice,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
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

        session()->flash('status', 'New data has been inserted successfully.');
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
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

        $querySupplier = Mst_supplier::where([
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

        $currency_code = '';
        $query = Tx_receipt_order::where('id', '=', $id)
        ->first();
        if ($query) {
            $qCurr = Mst_supplier_bank_information::leftJoin('mst_globals as curr','mst_supplier_bank_information.currency_id','=','curr.id')
            ->select(
                'curr.title_ind as curr_name',
                'curr.string_val as curr_code',
                'curr.id as curr_id'
                )
            ->where([
                'mst_supplier_bank_information.supplier_id' => $query->supplier_id,
            ])
            ->orderBy('mst_supplier_bank_information.created_at','ASC')
            ->first();
            if ($qCurr){
                $currency_code = $qCurr->curr_code;
            }

            $query_part = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
            ->orderBy('created_at','ASC');

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'weighttype' => $weighttype,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $query_part->count()),
                'vat' => $vat,
                'ro' => $query,
                'ro_part' => $query_part->get(),
                'qCurrency' => $qCurrency,
                'currency_code' => $currency_code,
            ];

            return view('tx.'.$this->folder.'.show', $data);
        } else {
            $data = [
                'errNotif' => 'The data you are looking for is not found'
            ];
            return view('error-notif.not-found-notif', $data);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $querySupplier = Mst_supplier::where([
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

        // branch
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();

        $query = Tx_receipt_order::where('id', '=', $id)
        ->first();
        if ($query) {

            $qSupplierSelected = Mst_supplier::where('id','=',$query->supplier_id)
            ->first();

            $order = [];
            $currency_code = '';
            if(old('supplier_id')){
                $qCurr = Mst_supplier_bank_information::leftJoin('mst_globals as curr','mst_supplier_bank_information.currency_id','=','curr.id')
                ->select(
                    'curr.title_ind as curr_name',
                    'curr.string_val as curr_code',
                    'curr.id as curr_id'
                    )
                ->where([
                    'mst_supplier_bank_information.supplier_id' => old('supplier_id'),
                ])
                ->orderBy('mst_supplier_bank_information.created_at','ASC')
                ->first();
                if ($qCurr){
                    $currency_code = $qCurr->curr_code;
                }

                $memo = Tx_purchase_memo::select(
                    'memo_no AS order_no',
                    'is_vat',
                )
                ->where('memo_no','NOT LIKE','%Draft%')
                ->whereNotIn('memo_no', function($q) use($id){
                    $q->select('po_mo_no')
                    ->from('tx_receipt_order_parts')
                    ->where('receipt_order_id','<>',$id)
                    ->where([
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ]);
                $order = Tx_purchase_order::select(
                    'purchase_no AS order_no',
                    'is_vat',
                )
                ->where('purchase_no','NOT LIKE','%Draft%')
                ->whereNotIn('purchase_no', function($q) use($id){
                    $q->select('po_mo_no')
                    ->from('tx_receipt_order_parts')
                    ->where('receipt_order_id','<>',$id)
                    ->where([
                        'active'=>'Y',
                    ]);
                })
                ->where('approved_by','<>',null)
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->union($memo)
                ->get();
            }else{
                $qCurr = Mst_supplier_bank_information::leftJoin('mst_globals as curr','mst_supplier_bank_information.currency_id','=','curr.id')
                ->select(
                    'curr.title_ind as curr_name',
                    'curr.string_val as curr_code',
                    'curr.id as curr_id'
                    )
                ->where([
                    'mst_supplier_bank_information.supplier_id' => $query->supplier_id,
                ])
                ->orderBy('mst_supplier_bank_information.created_at','ASC')
                ->first();
                if ($qCurr){
                    $currency_code = $qCurr->curr_code;
                }

                $memo = Tx_purchase_memo::select(
                    'memo_no AS order_no',
                    'is_vat',
                )
                ->where('memo_no','NOT LIKE','%Draft%')
                ->whereNotIn('memo_no', function($q) use($id){
                    $q->select('po_mo_no')
                    ->from('tx_receipt_order_parts')
                    ->where('receipt_order_id','<>',$id)
                    ->where([
                        'active'=>'Y',
                    ]);
                })
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ]);
                $order = Tx_purchase_order::select(
                    'purchase_no AS order_no',
                    'is_vat',
                )
                ->where('purchase_no','NOT LIKE','%Draft%')
                ->whereNotIn('purchase_no', function($q) use($id){
                    $q->select('po_mo_no')
                    ->from('tx_receipt_order_parts')
                    ->where('receipt_order_id','<>',$id)
                    ->where([
                        'active'=>'Y',
                    ]);
                })
                ->where('approved_by','<>',null)
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->union($memo)
                ->get();
            }

            $query_part = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
            ->orderBy('created_at','ASC')
            ->get();
            $query_part_count = Tx_receipt_order_part::where([
                'receipt_order_id' => $query->id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'qSupplierSelected' => $qSupplierSelected,
                'parts' => $parts,
                'weighttype' => $weighttype,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $query_part_count),
                'vat' => $vat,
                'get_po_pm_no' => $order,
                'ro' => $query,
                'ro_part' => $query_part,
                'qCurrency' => $qCurrency,
                'branches' => $branches,
                'userLogin' => $userLogin,
                'journal_type' => $this->journal_type,
                'currency_code' => $currency_code,
            ];

            $jt = $request->jt=='Y'?'Y':'N';
            if ($jt=='Y'){
                return view('tx.'.$this->folder.'.edit-journal-type', $data);
            }else{
                return view('tx.'.$this->folder.'.edit', $data);
            }

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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 29,
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
            'supplier_id' => 'required|numeric',
            'journal_type_id' => ['required', Rule::in(['P', 'N'])],
            'invoice_no' => ['required',new CheckDupInvoiceNo($request->supplier_id,$request->invoice_no,$id)],
            'invoice_amount' => ['required', new NumericCustom('Invoice Amount'), new CheckAmountEqualWithTotal($request->lastTotalAmountTmp)],
            'exc_rate' => [new NumericCustom('Exchange Rate'), new ValidateExchangeRateForSupplierType($request->supplier_id),'nullable'],
            'vat_import' => [new NumericCustom('VAT Import'), 'nullable'],
            'bea_masuk_val' => [new NumericCustom('Bea Masuk'), 'nullable'],
            'import_shipping_cost_val' => [new NumericCustom('Import Shipping Cost'), 'nullable'],
            'bl_no' => 'required',
            'gross_weight' => [new NumericCustom('Gross Weight'),'nullable'],
            'measurement' => [new NumericCustom('Measurement'),'nullable'],
            'receipt_no' => [new ApprovalCheckingRO],
            'courier_id' => 'required_if:courier_type,3'
        ];
        $errMsg = [
            'supplier_id.required' => 'Please select a valid supplier',
            'supplier_id.numeric' => 'Please select a valid supplier',
            'journal_type_id.required' => 'Please select a journal type',
            'journal_type_id.in' => 'The selected journal type is invalid.',
            'invoice_amount.regex' => 'Must have exacly 2 decimal places (9,99)',
            'bl_no.required' => 'The B/L No field is required.',
            'weight_type_id01.numeric' => 'Please select a valid weight type',
            'weight_type_id02.numeric' => 'Please select a valid weight type',
            'courier_id.numeric' => 'Please select a valid ship by',
            'gross_weight.regex' => 'Must have exacly 2 decimal places (9,99)',
            'measurement.regex' => 'Must have exacly 2 decimal places (9,99)',
            'courier_id.required_if' => 'Please select a valid courier',
        ];
        if ($request->totalRow > 0) {
            for ($i = 0; $i < $request->totalRow; $i++) {
                if ($request['part_id'.$i]) {
                    $validateShipmentInput = [
                        'qty'.$i => 'required|numeric|lte:qty_on_po'.$i.'|min:0',
                    ];
                    $errShipmentMsg = [
                        'qty'.$i.'.required' => 'The qty field is required',
                        'qty'.$i.'.numeric' => 'The qty field must be numeric',
                        'qty'.$i.'.lte' => 'The qty must be less than '.$request['qty_on_po'.$i].'.',
                        'qty'.$i.'.min' => 'The qty must be at least 0.',
                    ];
                    $validateInput = array_merge($validateInput, $validateShipmentInput);
                    $errMsg = array_merge($errMsg, $errShipmentMsg);
                }
            }
        }
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )
        ->validate();

        // Start transaction!
        DB::beginTransaction();

        try {
            $receipt_date = date_format(date_add(date_create(date("Y-m-d H:i:s")), date_interval_create_from_date_string(ENV("WAKTU_ID")." HOURS")),"Y-m-d");

            // ambil info user termasuk cabang dari user pembuat RO jika bukan direktur
            $qUser = Tx_receipt_order::leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
            ->select(
                'userdetails.branch_id AS user_branch_id',
                'tx_receipt_orders.receipt_no AS last_receipt_no'
            )
            ->where('tx_receipt_orders.id','=',$id)
            ->first();

            $receipt_no = '';
            $draft = false;
            $orders = Tx_receipt_order::where('id', '=', $id)
            ->where('receipt_no','LIKE','%Draft%')
            ->first();
            if($orders){
                // looking for draft order no
                $draft = true;
                $receipt_no = $orders->receipt_no;
            }

            if($request->is_draft=='Y' && $draft){
                // still save as draft
                // no action to do here
            }

            if($request->is_draft!='Y' && $draft){
                // promote to created

                $identityName = 'tx_receipt_orders';
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
                $receipt_no = ENV('P_RECEIPT_ORDER').date('y').'-'.$zero.strval($newInc);

                $upd = Tx_receipt_order::where('id', '=', $id)
                ->update([
                    'receipt_no' => $receipt_no,
                    'receipt_date' => $receipt_date,
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            if($request->is_draft!='Y' && !$draft){
                $upd = Tx_receipt_order::where('id', '=', $id)
                ->update([
                    'is_draft' => $request->is_draft,
                    'draft_to_created_at' => now(),
                    'updated_by' => Auth::user()->id
                ]);
            }

            $qSupplier = Mst_supplier::where('id','=',$request->supplier_id)
            ->first();

            $exc_rate = (($request->exc_rate!='' && $request->exc_rate!=0)?GlobalFuncHelper::moneyValidate($request->exc_rate):null);
            $vat_import = (($request->vat_import!='' && $request->vat_import!=0)?GlobalFuncHelper::moneyValidate($request->vat_import):null);
            $bea_masuk_val = (($request->bea_masuk_val!='' && $request->bea_masuk_val!=0)?GlobalFuncHelper::moneyValidate($request->bea_masuk_val):null);
            $import_shipping_cost_val = (($request->import_shipping_cost_val!='' && $request->import_shipping_cost_val!=0)?GlobalFuncHelper::moneyValidate($request->import_shipping_cost_val):null);
            $updRO = Tx_receipt_order::where('id','=',$id)
            ->update([
                'po_or_pm_no' => $request->po_pm_no_all,
                'journal_type_id' => $request->journal_type_id,
                'supplier_id' => $request->supplier_id,
                'supplier_type_id' => $qSupplier->supplier_type_id,
                'supplier_entity_type_id' => $qSupplier->entity_type_id,
                'supplier_name' => $qSupplier->name,
                'currency_id' => $request->currency_id,
                'total_qty' => 0,
                'total_before_vat' => 0,
                'total_after_vat' => 0,
                'branch_id' => $request->branch_id,
                'courier_id' => ($request->courier_type==3?($request->courier_id==''?null:$request->courier_id):null),
                'courier_type' => $request->courier_type,
                'invoice_no' => $request->invoice_no,
                'invoice_amount' => GlobalFuncHelper::moneyValidate($request->invoice_amount),
                'exchange_rate' => $exc_rate,
                'exc_rate_for_vat' => 0,
                // 'exc_rate_for_vat' => $exch_rate_for_vat,
                'bea_masuk' => $bea_masuk_val,
                'import_shipping_cost' => $import_shipping_cost_val,
                'bl_no' => $request->bl_no,
                'vessel_no' => $request->vessel_no,
                'weight_type_id01' => is_numeric($request->weight_type_id01)?$request->weight_type_id01:null,
                'weight_type_id02' => is_numeric($request->weight_type_id02)?$request->weight_type_id02:null,
                'gross_weight' => !is_numeric($request->gross_weight)?null:GlobalFuncHelper::moneyValidate($request->gross_weight),
                'measurement' => !is_numeric($request->measurement)?null:GlobalFuncHelper::moneyValidate($request->measurement),
                'remark' => $request->remark,
                'updated_by' => Auth::user()->id,
            ]);

            $vat_val = 0;
            $vat = Mst_global::where([
                'data_cat'=>'vat',
                'active'=>'Y',
            ])
            ->first();
            if($vat){
                $vat_val = $vat->numeric_val;
            }

            // set not active utk memisahkan part lama dan part baru karena perubahan supplier dan PO/MO
            $updNotActivePart = Tx_receipt_order_part::where([
                'receipt_order_id' => $id,
            ])
            ->update([
                'active' => 'N',
                'updated_by' => Auth::user()->id
            ]);

            $totalQty = 0;
            $totalPrice = 0;
            $is_partial_received_last = 'N';
            $is_vat_for_auto_journal = '';
            for($lastIdx=0;$lastIdx<$request->totalRow;$lastIdx++){
                if($request['qty'.$lastIdx]){
                    $price_fob = 0;
                    $price_local = 0;
                    $total_fob = 0;
                    $total_local = 0;
                    if($qSupplier->supplier_type_id==10){
                        // international - update price
                        $price_fob = $request['price_fob_val'.$lastIdx];
                        $price_local = $request['price_fob_val'.$lastIdx]*GlobalFuncHelper::moneyValidate($request->exc_rate);
                        $total_fob = $request['qty'.$lastIdx]*$price_fob;
                        $total_local = $request['qty'.$lastIdx]*$price_local;

                        $totalPrice += $total_fob;
                    }
                    if($qSupplier->supplier_type_id==11){
                        // lokal - update price
                        $price_fob = 0;
                        $price_local = $request['price_local_val'.$lastIdx];
                        $total_fob = 0;
                        $total_local = $request['qty'.$lastIdx]*$price_local;
                        $totalPrice += $total_local;
                    }

                    $is_partial_received = 'N';
                    if($request['qty_on_po'.$lastIdx]>$request['qty'.$lastIdx]){
                        $is_partial_received = 'Y';
                        $is_partial_received_last = 'Y';
                    }

                    $totalQty += $request['qty'.$lastIdx];
                    $qPart = Tx_receipt_order_part::where([
                        'id' => $request['ro_part_id'.$lastIdx],
                        'receipt_order_id' => $id,
                    ])
                    ->first();
                    if($qPart){
                        $updPart = Tx_receipt_order_part::where([
                            'id' => $request['ro_part_id'.$lastIdx],
                            'receipt_order_id' => $id,
                        ])
                        ->update([
                            'po_mo_no' => $request['po_mo_no'.$lastIdx],
                            'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                            'part_id' => $request['part_id'.$lastIdx],
                            'qty' => $request['qty'.$lastIdx],
                            'qty_on_po' => $request['qty_on_po'.$lastIdx],
                            'part_price' => $request['price_local_val'.$lastIdx],
                            'final_fob' => $price_fob,
                            'final_cost' => $price_local,
                            'total_fob_price' => $total_fob,
                            'total_price' => $total_local,
                            'is_partial_received' => $is_partial_received,
                            'active' => 'Y',
                            'updated_by' => Auth::user()->id
                        ]);
                    }else{
                        $insPart = Tx_receipt_order_part::create([
                            'receipt_order_id' => $id,
                            'po_mo_no' => $request['po_mo_no'.$lastIdx],
                            'po_mo_id' => $request['po_mo_id_'.$lastIdx],
                            'part_id' => $request['part_id'.$lastIdx],
                            'qty' => $request['qty'.$lastIdx],
                            'qty_on_po' => $request['qty_on_po'.$lastIdx],
                            'part_price' => $request['price_local_val'.$lastIdx],
                            'final_fob' => $price_fob,
                            'final_cost' => $price_local,
                            'total_fob_price' => $total_fob,
                            'total_price' => $total_local,
                            'is_partial_received' => $is_partial_received,
                            'active' => 'Y',
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }

                    if (strpos('PO-MO-'.$request['po_mo_no'.$lastIdx],env('P_PURCHASE_MEMO'))>0){
                        $qMO = Tx_purchase_memo::where('memo_no','=',$request['po_mo_no'.$lastIdx])
                        ->first();
                        if ($qMO){
                            $is_vat_for_auto_journal = $qMO->is_vat;
                        }
                    }
                    if (strpos('PO-MO-'.$request['po_mo_no'.$lastIdx],env('P_PURCHASE_ORDER'))>0){
                        $qPO = Tx_purchase_order::where('purchase_no','=',$request['po_mo_no'.$lastIdx])
                        ->first();
                        if ($qPO){
                            $is_vat_for_auto_journal = $qPO->is_vat;
                        }
                    }

                    if(strpos($receipt_no,"Draft")==0){
                        // update qty and all price jika bukan DRAFT
                        $queryMstPart = Mst_part::leftJoin('tx_qty_parts','mst_parts.id','=','tx_qty_parts.part_id')
                        ->select(
                            'mst_parts.id as part_id_tmp',
                            'mst_parts.avg_cost',
                            'mst_parts.price_list',
                            'tx_qty_parts.branch_id',
                        )
                        ->addSelect([
                            'qty' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->where('tx_qty_parts.branch_id','=',$request->branch_id)
                            ->limit(1)
                        ])
                        ->addSelect([
                            'qty_total' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('mst_parts.id','tx_qty_parts.part_id')
                            ->limit(1)
                        ])
                        ->where([
                            'mst_parts.id' => $request['part_id'.$lastIdx],
                            'tx_qty_parts.branch_id' => $request->branch_id,
                        ])
                        ->first();

                        if($queryMstPart){
                            // ambil total qty dari part yang sudah masuk outstanding SO/SJ
                            $qtySoSj = OutstandingSoSjHelper::getOutstandingSoSj($request['part_id'.$lastIdx]);

                            // ambil informasi part yang masuk
                            // cek keberadaan part di master part
                            $lastQty = is_null($queryMstPart->qty_total)?0:
                                ($queryMstPart->qty_total-$qtySoSj>0?$queryMstPart->qty_total-$qtySoSj:0);
                            $lastQtyPerBranch = is_null($queryMstPart->qty)?0:$queryMstPart->qty;
                            $lastPrice = ($queryMstPart->avg_cost==0)?$queryMstPart->price_list:$queryMstPart->avg_cost;
                            if (($lastQty+$request['qty'.$lastIdx])>0){
                                $avg_cost = (($lastQty*$lastPrice)+($request['qty'.$lastIdx]*$price_local))/($lastQty+$request['qty'.$lastIdx]);
                            }else{
                                $avg_cost = $price_local;
                            }

                            // update avg cost di part RO
                            $updTxRoPart = Tx_receipt_order_part::where([
                                'receipt_order_id' => $id,
                                'po_mo_no' => $request['po_mo_no'.$lastIdx],
                                'part_id' => $request['part_id'.$lastIdx],
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);

                            // update master qty utk masing2 part
                            $qtyPart = Tx_qty_part::where([
                                'part_id' => $queryMstPart->part_id_tmp,
                                'branch_id' => $request->branch_id,
                            ])
                            ->first();
                            if($qtyPart){
                                $updQty = Tx_qty_part::where([
                                    'part_id' => $qtyPart->part_id,
                                    'branch_id' => $qtyPart->branch_id,
                                ])
                                ->update([
                                    'qty' => $qtyPart->qty+($request['qty'.$lastIdx]?$request['qty'.$lastIdx]:0),
                                    'updated_by' => Auth::user()->id
                                ]);
                            }else{
                                // insert
                                $qtyPartIns = Tx_qty_part::create([
                                    'part_id' => $qtyPart->part_id,
                                    'branch_id' => $qtyPart->branch_id,
                                    'qty' => ($request['qty'.$lastIdx]?$request['qty'.$lastIdx]:0),
                                    'created_by' => Auth::user()->id,
                                    'updated_by' => Auth::user()->id
                                ]);
                            }

                            // update master part terhadap part terkait
                            $upqMstPart = Mst_part::where([
                                'id' => $queryMstPart->part_id_tmp
                            ])
                            ->update([
                                'avg_cost' => $avg_cost,
                                'initial_cost' => $queryMstPart->avg_cost,
                                'final_cost' => $price_local,
                                'total_cost' => ($lastQty+$request['qty'.$lastIdx])*$avg_cost,
                                'updated_by' => Auth::user()->id
                            ]);
                        }
                    }
                }
            }

            if($is_partial_received_last=='Y'){
                // jika salah satu part ber status partial received (Y)
                // maka part yang lain ber status partial received (Y) juga
                $updPartialReceived = Tx_receipt_order_part::where([
                    'receipt_order_id' => $id,
                    'active' => 'Y'
                ])
                ->update([
                    'is_partial_received' => 'Y',
                    'updated_by' => Auth::user()->id
                ]);
            }

            $total_after_vat = ($is_vat_for_auto_journal=='Y'?$totalPrice+($totalPrice*$vat_val/100):$totalPrice);
            $total_before_vat_rp = 0;
            $total_vat_rp = 0;
            $total_after_vat_rp = 0;
            if($qSupplier->supplier_type_id==10){
                // international
                $total_after_vat = ($is_vat_for_auto_journal=='Y'?(($totalPrice*$exc_rate)+$vat_import)/($exc_rate==0?1:$exc_rate):($totalPrice*$exc_rate)/($exc_rate==0?1:$exc_rate));
                $total_before_vat_rp = $totalPrice*$exc_rate;
                $total_vat_rp = $vat_import;
                $total_after_vat_rp = $total_before_vat_rp+$total_vat_rp;
            }
            $updRO = Tx_receipt_order::where('id','=',$id)
            ->update([
                'total_qty' => $totalQty,
                'total_before_vat' => $totalPrice,
                'total_before_vat_rp' => $total_before_vat_rp,
                'total_vat' => ($total_after_vat-$totalPrice),
                'total_vat_rp' => $total_vat_rp,
                'total_after_vat' => $total_after_vat,
                'total_after_vat_rp' => $total_after_vat_rp,
                'vat_val' => ($is_vat_for_auto_journal=='Y'?$vat_val:0),
                'updated_by' => Auth::user()->id
            ]);

            // buat automatic general journal untuk receipt order
            if($request->is_draft!='Y'){

                if ($is_vat_for_auto_journal=='Y' || $request->journal_type_id=='P'){
                    // cek apakah fitur automatic journal RO PPN sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>5,
                        'branch_id'=>$request->branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if($qAutJournal){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // bea masuk import
                        $qAutJournal_bea_masuk_import = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'bea masuk import\'')
                        ->first();
                        // ppn masukan
                        $qAutJournal_ppn_masukan = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_general_journal::where([
                            'module_no'=>$receipt_no,
                            'automatic_journal_id'=>5,
                            'active'=>'Y',
                        ])
                        ->first();
                        if($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_general_journal_detail::where('general_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);

                        }else{
                            $yearTemp = substr(date("Y"),2,2);
                            $monthTemp = (strlen(date("m"))==1?'0'.$date("m"):date("m"));
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'tx_general_journal';
                            $draft_to_created_at = now();
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");
                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            if ($qSupplier->supplier_type_id==10){
                                // international
                                if ($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P'){
                                    $vat_tmp = 0;
                                }else{
                                    $vat_tmp = $vat_import;
                                    // $vat_tmp = ((($totalPrice+$import_shipping_cost_val)*$exch_rate_for_vat)+$bea_masuk_val)*$vat_val/100;
                                }
                                $totalPrice_tmp = (is_numeric($exc_rate)?
                                    (($totalPrice*$exc_rate)):
                                    ($totalPrice));

                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$vat_tmp+$totalPrice_tmp,
                                    'total_kredit'=>$vat_tmp+$totalPrice_tmp,
                                    'module_no'=>$receipt_no,
                                    'automatic_journal_id'=>5,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }else{
                                // lokal
                                $insJournal = Tx_general_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                        ($totalPrice):
                                        ($totalPrice+(($totalPrice+$bea_masuk_val)*$vat_val/100)),
                                    'total_kredit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                        ($totalPrice):
                                        ($totalPrice+(($totalPrice+$bea_masuk_val)*$vat_val/100)),
                                    'module_no'=>$receipt_no,
                                    'automatic_journal_id'=>5,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }
                        }

                        if ($qSupplier->supplier_type_id==10){
                            // international
                            if ($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P'){
                                $vat_tmp = 0;
                            }else{
                                $vat_tmp = $vat_import;
                                // $vat_tmp = ((($totalPrice+$import_shipping_cost_val)*$exch_rate_for_vat)+$bea_masuk_val)*$vat_val/100;
                            }
                            $totalPrice_tmp = (is_numeric($exc_rate)?
                                ($totalPrice*$exc_rate):$totalPrice);

                            // inventory
                            $ins_inventory = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bea masuk import
                            // $ins_bea_masuk_import = Tx_general_journal_detail::create([
                            //     'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            //     'coa_id'=>$qAutJournal_bea_masuk_import->coa_code_id,
                            //     'coa_detail_id'=>null,
                            //     'description'=>null,
                            //     'debit'=>$bea_masuk_val,
                            //     'kredit'=>0,
                            //     'active'=>'Y',
                            //     'created_by'=>Auth::user()->id,
                            //     'updated_by'=>Auth::user()->id,
                            // ]);

                            // ppn masukan
                            $ins_ppn_masukan = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$vat_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$totalPrice_tmp+$vat_tmp,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            // lokal

                            // inventory
                            $ins_inventory = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // bea masuk import
                            // $ins_bea_masuk_import = Tx_general_journal_detail::create([
                            //     'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            //     'coa_id'=>$qAutJournal_bea_masuk_import->coa_code_id,
                            //     'coa_detail_id'=>null,
                            //     'description'=>null,
                            //     'debit'=>$bea_masuk_val==null?0:$bea_masuk_val,
                            //     'kredit'=>0,
                            //     'active'=>'Y',
                            //     'created_by'=>Auth::user()->id,
                            //     'updated_by'=>Auth::user()->id,
                            // ]);

                            // ppn masukan
                            $ins_ppn_masukan = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                    0:
                                    (($totalPrice+$bea_masuk_val)*$vat_val/100),
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_general_journal_detail::create([
                                'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>($is_vat_for_auto_journal=='N' && $request->journal_type_id=='P')?
                                    ($totalPrice):
                                    $totalPrice+(($totalPrice+$bea_masuk_val)*$vat_val/100),
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }
                    }
                }

                if ($is_vat_for_auto_journal=='N' && ($request->journal_type_id=='N' || $request->journal_type_id=='' || $request->journal_type_id==null)){
                    // cek apakah fitur automatic journal RO non PPN sudah tersedia
                    $qAutJournalNonPpn = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>6,
                        'branch_id'=>$request->branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournalNonPpn){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$request->branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_lokal_journal::where([
                            'module_no'=>$receipt_no,
                            'automatic_journal_id'=>6,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);

                        }else{
                            $yearTemp = substr(date("Y"),2,2);
                            $monthTemp = (strlen(date("m"))==1?'0'.$date("m"):date("m"));
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'Tx_lokal_journal';
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");

                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            if ($qSupplier->supplier_type_id==10){
                                // international
                                $totalPrice_tmp = is_numeric($exc_rate)?$exc_rate*$totalPrice:$totalPrice;

                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$totalPrice_tmp,
                                    'total_kredit'=>$totalPrice_tmp,
                                    'module_no'=>$receipt_no,
                                    'automatic_journal_id'=>6,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }else{
                                // lokal
                                $insJournal = Tx_lokal_journal::create([
                                    'general_journal_no'=>$journal_no,
                                    'general_journal_date'=>$receipt_date,
                                    'total_debit'=>$totalPrice,
                                    'total_kredit'=>$totalPrice,
                                    'module_no'=>$receipt_no,
                                    'automatic_journal_id'=>6,
                                    'active'=>'Y',
                                    'created_by'=>Auth::user()->id,
                                    'updated_by'=>Auth::user()->id,
                                ]);
                            }
                        }

                        if ($qSupplier->supplier_type_id==10){
                            $totalPrice_tmp = is_numeric($exc_rate)?$exc_rate*$totalPrice:$totalPrice;

                            // inventory
                            $ins_inventory = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice_tmp,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$totalPrice_tmp,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }else{
                            // inventory
                            $ins_inventory = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_inventory->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>$totalPrice,
                                'kredit'=>0,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);

                            // hutang
                            $ins_hutang = Tx_lokal_journal_detail::create([
                                'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                                'coa_id'=>$qAutJournal_hutang->coa_code_id,
                                'coa_detail_id'=>null,
                                'description'=>null,
                                'debit'=>0,
                                'kredit'=>$totalPrice,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
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
        return redirect(ENV('TRANSACTION_FOLDER_NAME').'/'.$this->folder);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_receipt_order)
    {
        //
    }

    public function updJournalType(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 29,
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
            'journal_type_id' => ['required', Rule::in(['P', 'N']), 'different:journal_type_id_current', new ROisPaid($id)],
        ];
        $errMsg = [
            'journal_type_id.required' => 'Please select a journal type',
            'journal_type_id.in' => 'The selected journal type is invalid.',
            'journal_type_id.different' => 'The currently selected journal type and the previous journal type must be different..',
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

            $qRO = Tx_receipt_order::where('id', '=', $id)
            ->first();
            if ($qRO){
                // RO tersedia

                $updRO = Tx_receipt_order::where('id', '=', $qRO->id)
                ->update([
                    'journal_type_id' => $request->journal_type_id,
                    'updated_by' => Auth::user()->id,
                ]);

                // non aktifkan jurnal terkait (GJ & LJ)
                $delGJdtl = Tx_general_journal_detail::whereIn('general_journal_id', function($q) use($qRO){
                    $q->select('id')
                    ->from('tx_general_journals')
                    ->where([
                        'module_no' => $qRO->receipt_no,
                        'automatic_journal_id' => 5,
                    ]);
                })
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id,
                ]);
                $delGJ = Tx_general_journal::where([
                    'module_no' => $qRO->receipt_no,
                    'automatic_journal_id' => 5,
                ])
                ->update([
                    'active'=>'N',
                    'module_no' => $qRO->receipt_no.'-RO',
                    'updated_by' => Auth::user()->id,
                ]);

                $updLJdtl = Tx_lokal_journal_detail::whereIn('lokal_journal_id', function($q) use($qRO){
                    $q->select('id')
                    ->from('tx_lokal_journals')
                    ->where([
                        'module_no' => $qRO->receipt_no,
                        'automatic_journal_id' => 6,
                    ]);
                })
                ->update([
                    'active'=>'N',
                    'updated_by' => Auth::user()->id,
                ]);
                $delLJ = Tx_lokal_journal::where([
                    'module_no' => $qRO->receipt_no,
                    'automatic_journal_id' => 6,
                ])
                ->update([
                    'active'=>'N',
                    'module_no' => $qRO->receipt_no.'-RO',
                    'updated_by' => Auth::user()->id,
                ]);
                // non aktifkan jurnal terkait

                $receipt_no = $qRO->receipt_no;
                $journal_date = explode("-", $qRO->receipt_date);
                $total_before_vat = ($qRO->total_before_vat_rp==null || $qRO->total_before_vat_rp==0)?$qRO->total_before_vat:$qRO->total_before_vat_rp;
                $total_vat = ($qRO->total_vat_rp==null || $qRO->total_vat_rp==0)?$qRO->total_vat:$qRO->total_vat_rp;
                $total_after_vat = ($qRO->total_after_vat_rp==null || $qRO->total_after_vat_rp==0)?$qRO->total_after_vat:$qRO->total_after_vat_rp;
                $exc_rate_for_vat = ($qRO->exc_rate_for_vat==null || $qRO->exc_rate_for_vat==0)?0:$qRO->exc_rate_for_vat;
                $bea_masuk = ($qRO->bea_masuk==null || $qRO->bea_masuk==0)?0:$qRO->bea_masuk;
                $import_shipping_cost = (($qRO->import_shipping_cost==null || $qRO->import_shipping_cost==0)?0:$qRO->import_shipping_cost)*$exc_rate_for_vat;
                $vat_val = ($qRO->vat_val==null || $qRO->vat_val==0)?0:$qRO->vat_val;
                $branch_id = $qRO->branch_id;

                if ($request->journal_type_id=='P'){
                // if ($vat_val>0 || $request->journal_type_id=='P'){
                    // GJ

                    // cek apakah fitur automatic journal RO PPN sudah tersedia
                    $qAutJournal = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>5,
                        'branch_id'=>$branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if($qAutJournal){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // bea masuk import
                        $qAutJournal_bea_masuk_import = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'bea masuk import\'')
                        ->first();
                        // ppn masukan
                        $qAutJournal_ppn_masukan = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'ppn masukan\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>5,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_general_journal::where([
                            'module_no'=>$receipt_no,
                            'automatic_journal_id'=>5,
                            'active'=>'Y',
                        ])
                        ->first();
                        if($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_general_journal_detail::where('general_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);

                        }else{
                            $yearTemp = substr($journal_date[0],2,2);
                            $monthTemp = (strlen($journal_date[1])==1?'0'.$journal_date[1]:$journal_date[1]);
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'tx_general_journal';
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");
                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_general_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_GENERAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_GENERAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_GENERAL_JURNAL').$YearMonth.$zero.strval($newInc);                            

                            // buat jurnal
                            $insJournal = Tx_general_journal::create([
                                'general_journal_no'=>$journal_no,
                                'general_journal_date'=>$journal_date[0].'-'.$journal_date[1].'-'.$journal_date[2],
                                'total_debit'=>$total_after_vat,
                                'total_kredit'=>$total_after_vat,
                                'module_no'=>$receipt_no,
                                'automatic_journal_id'=>5,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }

                        // inventory
                        $ins_inventory = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_inventory->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>null,
                            'debit'=>$total_before_vat,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // bea masuk import
                        // $ins_bea_masuk_import = Tx_general_journal_detail::create([
                        //     'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                        //     'coa_id'=>$qAutJournal_bea_masuk_import->coa_code_id,
                        //     'coa_detail_id'=>null,
                        //     'description'=>null,
                        //     'debit'=>$bea_masuk_val,
                        //     'kredit'=>0,
                        //     'active'=>'Y',
                        //     'created_by'=>Auth::user()->id,
                        //     'updated_by'=>Auth::user()->id,
                        // ]);

                        // ppn masukan
                        $ins_ppn_masukan = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_ppn_masukan->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>null,
                            'debit'=>$total_vat,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // hutang
                        $ins_hutang = Tx_general_journal_detail::create([
                            'general_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_hutang->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>null,
                            'debit'=>0,
                            'kredit'=>$total_after_vat,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);
                    }
                }
                if ($request->journal_type_id=='N' || $request->journal_type_id=='' || $request->journal_type_id==null){
                // if ($vat_val==0 && ($request->journal_type_id=='N' || $request->journal_type_id=='' || $request->journal_type_id==null)){
                    // LJ

                    // cek apakah fitur automatic journal RO non PPN sudah tersedia
                    $qAutJournalNonPpn = Mst_automatic_journal_detail::where([
                        'auto_journal_id'=>6,
                        'branch_id'=>$branch_id,
                        'active'=>'Y',
                    ])
                    ->first();
                    if ($qAutJournalNonPpn){
                        // inventory
                        $qAutJournal_inventory = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'inventory\'')
                        ->first();
                        // hutang
                        $qAutJournal_hutang = Mst_automatic_journal_detail::where([
                            'auto_journal_id'=>6,
                            'branch_id'=>$branch_id,
                            'active'=>'Y',
                        ])
                        ->whereRaw('LOWER(`desc`)=\'hutang\'')
                        ->first();

                        // cek apakah module sudah pernah dibuat
                        $insJournal = [];
                        $qJournals = Tx_lokal_journal::where([
                            'module_no'=>$receipt_no,
                            'automatic_journal_id'=>6,
                            'active'=>'Y',
                        ])
                        ->first();
                        if ($qJournals){
                            // non aktifkan jurnal detail jika ada
                            $updJournalDtl = Tx_lokal_journal_detail::where('lokal_journal_id','=',$qJournals->id)
                            ->update([
                                'active'=>'N',
                                'updated_by' => Auth::user()->id,
                            ]);

                        }else{
                            $yearTemp = substr($journal_date[0],2,2);
                            $monthTemp = (strlen($journal_date[1])==1?'0'.$journal_date[1]:$journal_date[1]);
                            $ymTemp = $yearTemp.$monthTemp;
                            $zero = '';
                            $YearMonth = '';
                            $newInc = 1;
                            $identityName = 'Tx_lokal_journal';
                            $autoInc = Auto_inc::where([
                                'identity_name' => $identityName
                            ])
                            ->first();
                            if ($autoInc) {                                
                                // jika counter sudah terbentuk

                                $date = date_format(date_create($autoInc->updated_at), "n");
                                $lastUpdAt = date_format(date_create($autoInc->updated_at), "ym");
                                $dateNow = date("ym");

                                if (($lastUpdAt <> $ymTemp) || ($lastUpdAt <> $dateNow)) {
                                    // jika bulan di server sudah berganti

                                    // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                    // untuk menghindari duplikasi
                                    $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                    ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                    ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->orderBy('general_journal_no', 'DESC')
                                    ->first();
                                    if ($lastCounterIfAny){
                                        $newInc = $lastCounterIfAny->lastCounter+1;
                                    }

                                } else {
                                    // jika bulan di server sama dengan bulan jurnal yg dipilih

                                    $newInc = (int)($autoInc->id_auto_inc ? $autoInc->id_auto_inc : 0) + 1;
                                    $updInc = Auto_inc::where([
                                        'identity_name' => $identityName
                                    ])
                                    ->update([
                                        'id_auto_inc' => $newInc
                                    ]);
                                }

                                $YearMonth = $yearTemp.$monthTemp;
                            } else {
                                // jika counter belum pernah terbentuk

                                $dateNow = date("ym");
                                // cek apakah sudah ada no jurnal yang terbentuk di bulan-bulan sebelumnya
                                // untuk menghindari duplikasi
                                $lastCounterIfAny = Tx_lokal_journal::selectRaw('CAST(REPLACE(general_journal_no,\''.env('P_LOKAL_JURNAL').$ymTemp.'\',\'\') AS UNSIGNED) AS lastCounter')
                                ->whereRaw('general_journal_no LIKE \''.env('P_LOKAL_JURNAL').$ymTemp.'%\'')
                                ->whereRaw('general_journal_no NOT LIKE \'%Draft%\'')
                                ->where([
                                    'active'=>'Y',
                                ])
                                ->orderBy('general_journal_no', 'DESC')
                                ->first();
                                if ($lastCounterIfAny){
                                    // ambil no urut terakhir dan ditambahkan 1

                                    $newInc = $lastCounterIfAny->lastCounter+1;
                                }

                                $insInc = Auto_inc::create([
                                    'identity_name'=>$identityName,
                                    'id_auto_inc'=>$newInc
                                ]);

                                $YearMonth = date('y').date('m');
                            }
                            $zero = '';
                            for ($i = 0; $i < (5 - strlen(strval($newInc))); $i++) {
                                $zero .= '0';
                            }
                            $journal_no = env('P_LOKAL_JURNAL').$YearMonth.$zero.strval($newInc);

                            // buat jurnal
                            $insJournal = Tx_lokal_journal::create([
                                'general_journal_no'=>$journal_no,
                                'general_journal_date'=>$journal_date[0].'-'.$journal_date[1].'-'.$journal_date[2],
                                'total_debit'=>$total_before_vat,
                                'total_kredit'=>$total_before_vat,
                                'module_no'=>$receipt_no,
                                'automatic_journal_id'=>6,
                                'active'=>'Y',
                                'created_by'=>Auth::user()->id,
                                'updated_by'=>Auth::user()->id,
                            ]);
                        }

                        // inventory
                        $ins_inventory = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_inventory->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>null,
                            'debit'=>$total_before_vat,
                            'kredit'=>0,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
                        ]);

                        // hutang
                        $ins_hutang = Tx_lokal_journal_detail::create([
                            'lokal_journal_id'=>($qJournals?$qJournals->id:$insJournal->id),
                            'coa_id'=>$qAutJournal_hutang->coa_code_id,
                            'coa_detail_id'=>null,
                            'description'=>null,
                            'debit'=>0,
                            'kredit'=>$total_before_vat,
                            'active'=>'Y',
                            'created_by'=>Auth::user()->id,
                            'updated_by'=>Auth::user()->id,
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
}
