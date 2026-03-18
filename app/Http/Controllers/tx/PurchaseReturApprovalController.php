<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_part;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Mst_courier;
use App\Models\Tx_qty_part;
use App\Models\Mst_supplier;
use Illuminate\Http\Request;
use App\Models\Tx_receipt_order;
use App\Models\Tx_purchase_retur;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Tx_purchase_retur_part;
use Illuminate\Validation\ValidationException;

class PurchaseReturApprovalController extends Controller
{
    protected $title = 'Purchase Retur Approval';
    protected $folder = 'purchase-retur-approval';

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
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->select('tx_purchase_returs.*')
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
            ->where('tx_purchase_returs.active','=','Y')
            ->orderBy('tx_purchase_returs.created_at','DESC');
        }else{
            $query = Tx_purchase_retur::leftJoin('userdetails AS usr','tx_purchase_returs.created_by','=','usr.user_id')
            ->select('tx_purchase_returs.*')
            ->addSelect(['total_retur' => Tx_purchase_retur_part::selectRaw('SUM(qty_retur*final_cost)')
                ->whereColumn('purchase_retur_id','tx_purchase_returs.id')
                ->where('active','=','Y')
            ])
            ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
            ->where('tx_purchase_returs.active','=','Y')
            ->where('usr.branch_id','=',$userLogin->branch_id)
            ->orderBy('tx_purchase_returs.created_at','DESC');
        }

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $data = [
            'returs' => $query->get(),
            'retursCount' => $query->count(),
            'title' => $this->title,
            'folder' => $this->folder,
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
     * @param  \App\Models\Tx_receipt_order  $tx_receipt_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
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

        $qCurrency = Mst_global::where([
            'id' => 3,
            'data_cat' => 'currency',
            'active' => 'Y'
        ])
        ->first();

        $query = Tx_purchase_retur::where('id', '=', $id)->first();
        if ($query) {
            $invoice_no = [];
            if(old('supplier_id')){
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => old('supplier_id'),
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }else{
                $invoice_no = Tx_receipt_order::where('approved_by','IS NOT',null)
                ->where([
                    'supplier_id' => $query->supplier_id,
                    'active' => 'Y'
                ])
                ->orderBy('receipt_no','ASC')
                ->get();
            }

            $queryPart = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->get();
            $queryPartCount = Tx_purchase_retur_part::where([
                'purchase_retur_id' => $id,
                'active' => 'Y'
            ])
            ->count();

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'querySupplier' => $querySupplier,
                'parts' => $parts,
                'couriers' => $couriers,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPartCount),
                'invoice_no' => $invoice_no,
                'vat' => $vat,
                'qRo' => $query,
                'qRoPart' => $queryPart,
                'qCurrency' => $qCurrency
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
    public function edit($id)
    {
        //
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
        // ambil info user
        $qUser = Tx_purchase_retur::leftJoin('userdetails','tx_purchase_returs.created_by','=','userdetails.user_id')
        ->select('userdetails.branch_id AS user_branch_id')
        ->where('tx_purchase_returs.id','=',$id)
        ->first();

        $qPart = Tx_purchase_retur_part::where([
            'purchase_retur_id' => $id,
            'active' => 'Y'
        ])
        ->get();

        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $qCheck = Tx_purchase_retur::where('id','=',$id)
                ->where('approved_by','=',null)
                ->where('canceled_by','=',null)
                ->first();
                if($qCheck){
                    foreach($qPart as $qP){
                        $parts = Mst_part::where('id','=',$qP->part_id)
                        ->first();

                        $qQty = Tx_qty_part::where([
                            'part_id' => $qP->part_id,
                            'branch_id' => $qUser->branch_id,
                        ])
                        ->first();
                        if($qQty){
                            $upd = Tx_qty_part::where([
                                'part_id' => $qP->part_id,
                                'branch_id' => $qUser->branch_id,
                            ])
                            ->update([
                                'qty' => $qQty->qty-$qP->qty_retur,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }else{
                            $ins = Tx_qty_part::create([
                                'part_id' => $qP->part_id,
                                'qty' => -$qP->qty_retur,
                                'branch_id' => $qUser->branch_id,
                                'created_by' => Auth::user()->id,
                                'updated_by' => Auth::user()->id,
                            ]);
                        }
                    }

                    $upd = Tx_purchase_retur::where('id','=',$id)
                    ->where('approved_by','=',null)
                    ->update([
                        'approved_by' => Auth::user()->id,
                        'approved_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null
                    ]);
                }
            }
            if($request->order_appr == 'R'){
                $qCheck = Tx_purchase_retur::where('id','=',$id)
                ->where('approved_by','=',null)
                ->where('canceled_by','=',null)
                ->first();
                if($qCheck){
                    $upd = Tx_purchase_retur::where('id','=',$id)
                    ->where('canceled_by','=',null)
                    ->update([
                        'approved_by' => null,
                        'approved_at' => null,
                        'canceled_by' => Auth::user()->id,
                        'canceled_at' => now()
                    ]);
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
    public function destroy($id)
    {
        //
    }
}
