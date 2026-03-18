<?php

namespace App\Http\Controllers\tx;

use Exception;
use Carbon\Carbon;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_company;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_surat_jalan;
use Illuminate\Support\Facades\DB;
use App\Models\Tx_surat_jalan_part;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Mst_customer_shipment_address;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Mst_menu_user;

class SuratJalanApprovalServerSideController extends Controller
{
    protected $title = 'Surat Jalan - Approval';
    protected $folder = 'surat-jalan-approval';

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
            $query = Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
            ->leftJoin('tx_sales_quotations as tx_sq','tx_surat_jalans.sales_quotation_id','=','tx_sq.id')
            ->leftJoin('mst_customers','tx_surat_jalans.customer_id','=','mst_customers.id')
            ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
            ->select(
                'tx_surat_jalans.id as tx_id',
                'tx_surat_jalans.surat_jalan_no',
                'tx_surat_jalans.customer_doc_no',
                'tx_surat_jalans.total',
                'tx_surat_jalans.sales_quotation_id',
                'tx_surat_jalans.need_approval',
                'tx_sq.sales_quotation_no',
                'mst_customers.name as cust_name',
                'usr.initial',
                'usr.is_director',
                'usr.is_branch_head',
                'usr_sales.initial as sales_initial',
            )
            ->selectRaw('DATE_FORMAT(tx_surat_jalans.surat_jalan_date, "%d/%m/%Y") as surat_jalan_date')
            ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
            ->where([
                'tx_surat_jalans.need_approval' => 'Y',
                'tx_surat_jalans.active' => 'Y'
            ])
            ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
                $q->where(function ($query) use($userLogin) {
                    $query->where('tx_surat_jalans.branch_id','=',$userLogin->branch_id)
                    ->orWhere('usr.branch_id','=',$userLogin->branch_id);
                });
            })
            ->orderBy('tx_surat_jalans.created_at', 'DESC');

            return DataTables::of($query)
            ->addColumn('surat_jalan_no_wlink', function ($query) {
                return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/surat-jalan-approval/'.$query->tx_id).'/edit" style="text-decoration: underline;">View</a>';
            })
            ->addColumn('sales_quotation_no', function ($query) {
                if(!is_null($query->sales_quotation_no)){
                    return '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/sales_quotation/'.$query->sales_quotation_id).'" target="_new" '.
                        'style="text-decoration: underline;">'.$query->sales_quotation_no.'</a>';
                }
                return '';
            })
            // ->addColumn('purchase_retur_date_string', function ($query) {
            //     return date_format(date_create($query->purchase_retur_date),"d/m/Y");
            // })
            ->addColumn('status', function ($query) {
                if ($query->need_approval=='Y'){
                    return 'Need Approval';
                }
                return '';
            })
            ->rawColumns(['surat_jalan_no_wlink','sales_quotation_no','status'])
            ->toJson();
        }

        $data = [
            // 'suratJalans' => $query,
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show(Tx_receipt_order $tx_receipt_order)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
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

        $query = Tx_surat_jalan::where('id', '=', $id)
        ->first();
        if ($query) {
            $qCustomer = Mst_customer::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $qCompany = Mst_company::where([
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->get();
            $parts = Mst_part::where([
                'active' => 'Y'
            ])
            ->orderBy('part_name', 'ASC')
            ->get();
            $qCustomerInfo = Mst_customer::where([
                'id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->orderBy('name', 'ASC')
            ->first();
            $qCustomerShipmentAddressInfo = Mst_customer_shipment_address::where([
                'id' => $query->cust_shipment_address,
                'customer_id' => $query->customer_id,
                'active' => 'Y'
            ])
            ->first();
            $queryPart = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
                'active' => 'Y'
            ])
            ->orderBy('created_at', 'ASC')
            ->get();
            $queryPartCount = Tx_surat_jalan_part::where([
                'surat_jalan_id' => $id,
                'active' => 'Y'
            ])
            ->count();
            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'customers' => $qCustomer,
                'companies' => $qCompany,
                'parts' => $parts,
                'custInfo' => $qCustomerInfo,
                'b' => $qCustomerShipmentAddressInfo,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'orders' => $query,
                'order_parts' => $queryPart,
                'qCurrency' => $qCurrency,
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $qCheckPriv = Mst_menu_user::where([
            'menu_id' => 71,
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

        $qPv = Tx_surat_jalan::where('id', '=', $id)
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
            'reason.required_if' => 'The reason field is required when Approval Status is Reject.'
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
            $qUser = Tx_surat_jalan::leftJoin('userdetails','tx_surat_jalans.created_by','=','userdetails.user_id')
            ->select(
                'userdetails.branch_id AS user_branch_id',
                'tx_surat_jalans.branch_id AS sj_branch_id')
            ->where('tx_surat_jalans.id','=',$id)
            ->first();

            if($request->order_appr=='A'){
                $upd = Tx_surat_jalan::where([
                    'id' => $id,
                    'approved_by' => null,
                    'canceled_by' => null
                ])
                ->update([
                    'need_approval' => 'N',
                    'approved_by' => Auth::user()->id,
                    'approved_at' => Carbon::now(),
                    'updated_by' => Auth::user()->id
                ]);

                $queryOrderPart = Tx_surat_jalan_part::where([
                    'surat_jalan_id' => $id,
                    'active' => 'Y'
                ])
                ->get();
                foreach ($queryOrderPart as $q) {
                    $totQty = 0;
                    $qtyPart = Tx_qty_part::where([
                        'part_id' => $q->part_id,
                        'branch_id' => (!is_null($qUser->sj_branch_id)?$qUser->sj_branch_id:$qUser->user_branch_id),
                    ])
                    ->first();
                    if ($qtyPart) {
                        // update
                        $totQty = $qtyPart->qty;
                    } else {
                        // insert
                        $totQty = $q->qty;
                        $qtyPartIns = Tx_qty_part::create([
                            'part_id' => $q->part_id,
                            'qty' => 0,
                            'branch_id' => (!is_null($qUser->sj_branch_id)?$qUser->sj_branch_id:$qUser->user_branch_id),
                            'created_by' => Auth::user()->id,
                            'updated_by' => Auth::user()->id
                        ]);
                    }

                    // update final price, total sales
                    $updFinalPrice = Mst_part::where([
                        'id' => $q->part_id
                    ])
                    ->update([
                        // 'final_price' => $q->price,
                        'total_sales' => $totQty*$q->price,
                        'updated_by' => Auth::user()->id
                    ]);
                }
            }

            if($request->order_appr=='R'){
                $upd = Tx_surat_jalan::where([
                    'id' => $id,
                    'approved_by' => null,
                    'canceled_by' => null
                ])
                ->update([
                    'need_approval' => 'N',
                    'reason' => $request->reason,
                    'active' => 'N',
                    'canceled_by' => Auth::user()->id,
                    'canceled_at' => Carbon::now(),
                    'updated_by' => Auth::user()->id
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_receipt_order $tx_receipt_order)
    {
        //
    }
}
