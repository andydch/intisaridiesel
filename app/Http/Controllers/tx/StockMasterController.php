<?php

namespace App\Http\Controllers\tx;

use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use Illuminate\Http\Request;
use App\Models\Tx_purchase_order;
use App\Http\Controllers\Controller;
use App\Models\Mst_brand_type;
use App\Models\Tx_purchase_order_part;
use Illuminate\Database\Query\Builder;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_sales_order_part;
use App\Models\Tx_stock_transfer_part;

class StockMasterController extends Controller
{
    protected $title = 'Master Part';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-stock-card';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1800);

        $queryBranch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $queryBrand = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();
        $queryBrandType = Mst_brand_type::where([
            'active' => 'Y'
        ])
        ->orderBy('brand_type','ASC')
        ->get();
        $queryPartType = Mst_global::where([
            'data_cat' => 'part-type',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();
        $data = [
            'stocks' => [],
            'rowCount' => 0,
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryBranch' => $queryBranch,
            'queryBrand' => $queryBrand,
            'queryBrandType' => $queryBrandType,
            'queryPartType' => $queryPartType
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
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 1800);

        $queryBranch = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $queryBrand = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();
        $queryBrandType = Mst_brand_type::where([
            'active' => 'Y'
        ])
        ->orderBy('brand_type','ASC')
        ->get();
        $queryPartType = Mst_global::where([
            'data_cat' => 'part-type',
            'active' => 'Y'
        ])
        ->orderBy('string_val','ASC')
        ->get();
        $query = Mst_part::leftJoin('tx_qty_parts as tx_qty','tx_qty.part_id','=','mst_parts.id')
            ->leftJoin('mst_globals as mg_01','mst_parts.part_type_id','=','mg_01.id')
            ->leftJoin('mst_globals as mg_02','mst_parts.quantity_type_id','=','mg_02.id')
            ->leftJoin('mst_globals as mg_03','mst_parts.brand_id','=','mg_03.id')
            ->leftJoin('mst_branches as mb','tx_qty.branch_id','=','mb.id')
            ->select(
                'mst_parts.id AS part_idx',
                'mst_parts.part_number',
                'mst_parts.slug',
                'mst_parts.part_name',
                'mst_parts.final_price',
                'mst_parts.price_list',
                'mst_parts.avg_cost',
                'mst_parts.final_cost',
                'mst_parts.active AS part_active',
                'mg_01.title_ind as part_type_name',
                'tx_qty.qty',
                'tx_qty.branch_id AS branch_id_tmp',
                'mb.name as branch_name',
                'mg_02.string_val as unit_name',
                'mg_03.string_val as brand_name',
            )
            // purchase memo
            ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('IFNULL(SUM(tx_purchase_memo_parts.qty),0)')    // total qty dari memo yg aktif
                ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
                ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
                // ->whereColumn('tx_memo.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty.branch_id)')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->where('tx_purchase_memo_parts.active','=','Y')
                ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                ->where('tx_memo.active','=','Y')
                // ->where('tx_memo.is_received','=','N')
            ])
            // purchase order
            ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('IFNULL(SUM(tx_purchase_order_parts.qty),0)')  // total qty dari po yg aktif
                ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
                ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
                // ->whereColumn('tx_order.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty.branch_id)')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->where('tx_purchase_order_parts.active','=','Y')
                ->where('tx_order.approved_by','<>',null)
                ->where('tx_order.active','=','Y')
            ])
            // ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg approved
            //     ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            //     ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
            //     ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            //     // ->whereColumn('usr.branch_id','tx_qty.branch_id')
            //     ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
            //     ->where('tx_receipt_order_parts.is_partial_received','=','Y')
            //     ->where('tx_receipt_order_parts.active','=','Y')
            //     ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            //     ->where('tx_ro.active','=','Y')
            // ])
            ->addSelect(['purchase_ro_qty_mo' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg konek ke MO
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                // ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                // ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_memo.memo_no')
                    ->from('tx_purchase_memos as tx_memo')
                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty.branch_id)')
                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                    ->where('tx_memo.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_qty_po' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO yg konek ke PO
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                // ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                // ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_order.purchase_no')
                    ->from('tx_purchase_orders as tx_order')
                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty.branch_id)')
                    ->where('tx_order.approved_by','<>',null)
                    ->where('tx_order.active','=','Y');
                })
            ])
            // ->addSelect(['purchase_ro_qty_no_partial' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
            //     ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            //     ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
            //     ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            //     // ->whereColumn('usr.branch_id','tx_qty.branch_id')
            //     ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
            //     ->where('tx_receipt_order_parts.is_partial_received','=','N')
            //     ->where('tx_receipt_order_parts.active','=','Y')
            //     ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            //     ->where('tx_ro.active','=','Y')
            // ])
            ->addSelect(['purchase_ro_qty_no_partial_mo' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_memo.memo_no')
                    ->from('tx_purchase_memos as tx_memo')
                    ->leftJoin('userdetails as usr','tx_memo.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_memo.branch_id IS null) OR tx_memo.branch_id=tx_qty.branch_id)')
                    ->where('tx_memo.memo_no','NOT LIKE','%Draft%')
                    ->where('tx_memo.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_qty_no_partial_po' => Tx_receipt_order_part::selectRaw('IFNULL(SUM(tx_receipt_order_parts.qty),0)')  // total qty dari RO dg is_partial_received=N
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->whereIn('tx_receipt_order_parts.po_mo_no', function($query){
                    $query->select('tx_order.purchase_no')
                    ->from('tx_purchase_orders as tx_order')
                    ->leftJoin('userdetails as usr','tx_order.created_by','=','usr.user_id')
                    ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_order.branch_id IS null) OR tx_order.branch_id=tx_qty.branch_id)')
                    ->where('tx_order.approved_by','<>',null)
                    ->where('tx_order.active','=','Y');
                })
            ])
            ->addSelect(['purchase_ro_branch_id' => Tx_receipt_order_part::select('usr.branch_id')  // branch
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->limit(1)
            ])
            ->addSelect(['purchase_ro_final_cost' => Tx_receipt_order_part::select('tx_receipt_order_parts.final_cost')  // final cost
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.final_cost','>',0)
                ->where('tx_receipt_order_parts.is_partial_received','=','Y')
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->orderBy('tx_ro.created_at','DESC')
                ->orderBy('tx_receipt_order_parts.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['purchase_ro_qty_no_partial_final_cost' => Tx_receipt_order_part::select('tx_receipt_order_parts.final_cost')  // final cost
                ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
                ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND tx_ro.branch_id IS null) OR tx_ro.branch_id=tx_qty.branch_id)')
                ->where('tx_receipt_order_parts.is_partial_received','=','N')
                ->where('tx_receipt_order_parts.final_cost','>',0)
                ->where('tx_receipt_order_parts.active','=','Y')
                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                ->where('tx_ro.active','=','Y')
                ->orderBy('tx_ro.created_at','DESC')
                ->orderBy('tx_receipt_order_parts.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['in_transit_qty' => Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                ->whereColumn('tx_stock_transfer_parts.part_id','mst_parts.id')
                ->whereColumn('tx_stock.branch_to_id','tx_qty.branch_id')
                ->where('tx_stock_transfer_parts.active','=','Y')
                ->where('tx_stock.approved_by','<>',null)
                ->where('tx_stock.received_by','=',null)
                ->where('tx_stock.active','=','Y')
            ])
            ->addSelect(['last_final_price' => Tx_sales_order_part::selectRaw('IFNULL(tx_sales_order_parts.price,0)')
                ->leftJoin('tx_sales_orders as txso','tx_sales_order_parts.order_id','=','txso.id')
                ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                ->whereColumn('tx_sales_order_parts.part_id','mst_parts.id')
                // ->whereColumn('usr.branch_id','tx_qty.branch_id')
                // ---
                // gunakan kode cabang user ketika cabang SO kosong
                // jika cabang SO ada maka gunakan kode cabang SO
                ->whereRaw('((usr.branch_id=tx_qty.branch_id AND txso.branch_id IS null) OR txso.branch_id=tx_qty.branch_id)')
                // ---
                ->where('tx_sales_order_parts.active','=','Y')
                // ->where('txso.need_approval','=','N')
                ->where('txso.active','=','Y')
                ->orderBy('txso.created_at','DESC')
                ->limit(1)
            ])
            ->addSelect(['brand_type_name' => Mst_brand_type::select('brand_type')
                ->where('mst_brand_types.brand_id','=','mg_03.id')
            ])
            ->addSelect(['brand_type_id' => Mst_brand_type::select('id')
                ->where('mst_brand_types.brand_id','=','mg_03.id')
            ])
            ->where([
                'mst_parts.active' => 'Y',
            ])
            ->when(request()->has('part_no') && request()->part_no<>'', function($q) use($request) {
                $q->where('mst_parts.part_number','LIKE', '%'.$request->part_no.'%');
            })
            ->when(request()->has('part_name') && request()->part_name<>'', function($q) use($request) {
                $q->where('mst_parts.part_name','LIKE', '%'.$request->part_name.'%');
            })
            ->when(request()->has('branch_id') && request()->branch_id<>null, function($q) use($request) {
                $q->where('mb.id','=', $request->branch_id);
            })
            ->when(request()->has('brand_id') && request()->brand_id<>null, function($q) use($request) {
                $q->where('mst_parts.brand_id','=', $request->brand_id);
            })
            ->when(request()->has('brandtype_id') && request()->brandtype_id<>null, function($q) use($request) {
                $q->where(function (Builder $query) {
                    $query->select('id')
                    ->from('mst_brand_types')
                    ->whereColumn('mst_brand_types.brand_id', 'mg_03.id')
                    ->limit(1);
                }, $request->brandtype_id);
            })
            ->when(request()->has('partType_id') && request()->partType_id<>null, function($q) use($request) {
                $q->where('mg_01.id','=', $request->partType_id);
            });
            // ->toSql();dd($query);
            $queryCount = $query->count();

        $data = [
            'stocks' => $query->orderBy('mst_parts.part_number','ASC')->get(),
            // 'stocks' => $query->orderBy('mst_parts.part_number','ASC')->paginate(15),
            'rowCount' => $queryCount,
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'request' => $request,
            'queryBranch' => $queryBranch,
            'queryBrand' => $queryBrand,
            'queryBrandType' => $queryBrandType,
            'queryPartType' => $queryPartType
        ];

        return view('tx.'.$this->folder.'.index-without-dt', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
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
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tx_purchase_order $tx_purchase_order)
    {
        //
    }
}
