<?php

namespace App\Http\Controllers\tx;

use Exception;
use App\Models\Mst_global;
use App\Models\Userdetail;
use App\Models\Tx_qty_part;
use App\Models\Mst_customer;
use Illuminate\Http\Request;
use App\Models\Tx_nota_retur;
use App\Models\Tx_sales_order;
use App\Models\Tx_delivery_order;
use App\Models\Tx_nota_retur_part;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Query\Builder;
use Illuminate\Validation\ValidationException;

class NotaReturApprovalController extends Controller
{
    protected $title = 'Nota Retur - Approval';
    protected $folder = 'nota-retur-approval';

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

        $query = Tx_nota_retur::leftJoin('userdetails AS usr','tx_nota_returs.created_by','=','usr.user_id')
        ->select('tx_nota_returs.*')
        ->addSelect(['total_retur' => Tx_nota_retur_part::selectRaw('SUM(qty_retur*final_price)')
            ->whereColumn('nota_retur_id','tx_nota_returs.id')
            ->where('active','=','Y')
        ])
        ->where('tx_nota_returs.nota_retur_no','NOT LIKE','%Draft%')
        // ->where('tx_nota_returs.active','=','Y')
        ->when($userLogin->is_director!='Y', function($q) use($userLogin) {
            $q->where('usr.branch_id','=',$userLogin->branch_id);
        })
        ->orderBy('tx_nota_returs.nota_retur_no','DESC')
        ->orderBy('tx_nota_returs.created_at','DESC')
        ->get();

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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($nota_retur_no)
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

        $query = Tx_nota_retur::where('nota_retur_no', '=', urldecode($nota_retur_no))
        ->first();
        if ($query) {
            $delivery_order_no = [];
            $so = [];
            $delivery_order_no = Tx_delivery_order::select(
                'id',
                'delivery_order_no'
            )
            ->where('delivery_order_no','NOT LIKE','%Draft%')
            ->where('customer_id','=',$query->customer_id)
            ->where('active','=','Y')
            ->whereRaw('created_at >= DATE_ADD(created_at, INTERVAL -12 MONTH)')
            ->orderBy('delivery_order_no','ASC')
            ->get();

            $so = Tx_sales_order::select(
                'id',
                'sales_order_no'
            )
            ->where('sales_order_no','NOT LIKE','%Draft%')
            ->where('customer_id','=',$query->customer_id)
            ->where('active','=','Y')
            ->orderBy('sales_order_no','ASC')
            ->get();

            $queryPart = Tx_nota_retur_part::where([
                'nota_retur_id' => $query->id,
                'active' => 'Y',
            ]);

            // $qSOselected = Tx_sales_order::select(
            //     'id',
            //     'sales_order_no',
            //     'customer_doc_no',
            // )
            // ->where('sales_order_no','NOT LIKE','%Draft%')
            // ->whereNotIn('id', function (Builder $query) {
            //     $query->select('tsop.order_id')
            //     ->from('tx_nota_retur_parts')
            //     ->leftJoin('tx_sales_order_parts AS tsop','tx_nota_retur_parts.sales_order_part_id','=','tsop.id')
            //     ->where('tx_nota_retur_parts.active','=','Y');
            // })
            // ->where('customer_id','=',$query->customer_id)
            // ->where('active','=','Y');

            $qSOselected = Tx_sales_order::leftJoin('tx_sales_order_parts AS tso_part','tx_sales_orders.id','=','tso_part.order_id')
            ->leftJoin('tx_nota_retur_parts AS trn_part','tso_part.id','=','trn_part.sales_order_part_id')
            ->select(
                'tx_sales_orders.sales_order_no',
            )
            ->where('tx_sales_orders.sales_order_no','NOT LIKE','%Draft%')
            ->where('tx_sales_orders.active','=','Y')
            ->where('trn_part.nota_retur_id','=',$query->id)
            ->groupBy('tx_sales_orders.sales_order_no');
            $all_selected_SO = '';
            foreach($qSOselected->get() as $qSO){
                $all_selected_SO .= ','.$qSO->sales_order_no;
            }

            $data = [
                'title' => $this->title,
                'folder' => $this->folder,
                'queryCustomer' => $queryCustomer,
                'qDeliveryOrder' => $delivery_order_no,
                'totalRow' => (old('totalRow') ? old('totalRow') : $queryPart->count()),
                'vat' => $vat,
                'qNotaRetur' => $query,
                'qNotaReturPart' => $queryPart->get(),
                'qCurrency' => $qCurrency,
                'all_selected_SO' => $all_selected_SO,
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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $nota_retur_no)
    {
        // Start transaction!
        DB::beginTransaction();

        try {

            if($request->order_appr == 'A'){
                $q = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
                ->where('approved_by','=',null)
                ->where('active','=','Y')
                ->first();
                if($q){
                    // update status approval - approve
                    $upd = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
                    ->where('approved_by','=',null)
                    ->update([
                        'approved_by' => Auth::user()->id,
                        'approved_at' => now(),
                        'canceled_by' => null,
                        'canceled_at' => null,
                        'updated_by' => Auth::user()->id,
                    ]);

                    // tambahkan jumlah part di gudang
                    $qPart = Tx_nota_retur_part::where('nota_retur_id','=',$q->id)
                    ->where('active','=','Y')
                    ->get();
                    foreach($qPart as $qP){
                        $qPartQty = Tx_qty_part::leftJoin('mst_parts as mp','tx_qty_parts.part_id','=','mp.id')
                        ->select(
                            'tx_qty_parts.part_id',
                            'tx_qty_parts.qty',
                            'mp.avg_cost',
                            )
                        ->addSelect([
                            'qty_nasional' => Tx_qty_part::selectRaw('SUM(qty)')
                            ->whereColumn('tx_qty_parts.part_id','mp.id')
                            ->limit(1)
                        ])
                        ->where('tx_qty_parts.part_id','=',$qP->part_id)
                        ->where('tx_qty_parts.branch_id','=',$q->branch_id)
                        ->first();
                        if($qPartQty){
                            // update OH sesuai branch
                            $updPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
                            ->where('branch_id','=',$q->branch_id)
                            ->update([
                                'qty' => $qPartQty->qty+$qP->qty_retur,
                                'updated_by' => Auth::user()->id,
                            ]);

                            // hitung avg
                            $updAVG = Mst_part::where('id','=',$qPartQty->part_id)
                            ->update([
                                'avg_cost' => (($qPartQty->avg_cost*$qPartQty->qty_nasional)+($qP->qty_retur*$qP->final_price))/($qPartQty->qty_nasional+$qP->qty_retur),
                            ]);
                        }
                    }
                }
            }
            // if($request->order_appr == 'R'){
            //     $q = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
            //     ->first();
            //     if($q){
            //         if($q->approved_by!=null){
            //             // jika status approval sebelumnya adalah approve
            //             // kembalikan jumlah part di gudang
            //             $qPart = Tx_nota_retur_part::where('nota_retur_id','=',$q->id)
            //             ->where('active','=','Y')
            //             ->get();
            //             foreach($qPart as $qP){
            //                 $qPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
            //                 ->where('branch_id','=',$q->branch_id)
            //                 ->first();
            //                 if($qPartQty){
            //                     $updPartQty = Tx_qty_part::where('part_id','=',$qP->part_id)
            //                     ->where('branch_id','=',$q->branch_id)
            //                     ->update([
            //                         'qty' => (int)$qPartQty->qty-(int)$qP->qty_retur,
            //                         'updated_by' => Auth::user()->id,
            //                     ]);
            //                 }
            //             }
            //         }
            //     }
            //     // update status approval - reject
            //     $upd = Tx_nota_retur::where('nota_retur_no','=',urldecode($nota_retur_no))
            //     ->where('canceled_by','=',null)
            //     ->update([
            //         'approved_by' => null,
            //         'approved_at' => null,
            //         'canceled_by' => Auth::user()->id,
            //         'canceled_at' => now(),
            //         'updated_by' => Auth::user()->id,
            //     ]);
            // }

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
     * @param  \App\Models\Tx_invoice  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy( $id)
    {
        //
    }
}
