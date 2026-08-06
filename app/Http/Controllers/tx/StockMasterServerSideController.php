<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_brand_type;
use App\Models\Mst_global;
use App\Models\Tx_qty_part;
use App\Models\Tx_receipt_order_part;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class StockMasterServerSideController extends Controller
{
    protected $title = 'Master Part';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-stock-card';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$param=null)
    {
        $queryBranch = Mst_branch::where('active', '=','Y')
        ->orderBy('name','ASC')
        ->get();
        $queryBrand = Mst_global::where('data_cat', 'brand')
        ->where('active', 'Y')
        ->orderBy('string_val','ASC')
        ->get();
        $queryBrandType = Mst_brand_type::where('active', 'Y')
        ->orderBy('brand_type','ASC')
        ->get();
        $queryPartType = Mst_global::where('data_cat', 'part-type')
        ->where('active', 'Y')
        ->orderBy('string_val','ASC')
        ->get();

        $paramTemp = str_replace("\\", "/", urldecode($param));
        $parameter = explode('::', $paramTemp);
        if(count($parameter)<6){
            return redirect(route('stockmaster.index').'/'.urlencode('::::::::::::::'));
        }
        if ($request->ajax()) {
            $sqlMstParts = DB::table('mst_parts')
            ->select(
                'id AS part_idx',
                'slug',
                'part_number',
                'part_name',
                'part_type_id',
                'quantity_type_id',
                'brand_id',
                'final_price',
                'price_list',
                'avg_cost',
                'final_cost',
                'active AS part_active',
            )
            ->where('active', 'Y')
            ->orderBy('part_number', 'ASC');
            $sqlMstGlobals01 = DB::table('mst_globals')
            ->select(
                'id',
                'title_ind'
            )
            ->where('active', 'Y')
            ->where('data_cat', 'part-type');
            $sqlMstGlobals02 = DB::table('mst_globals')
            ->select(
                'id',
                'title_ind'
            )
            ->where('active', 'Y')
            ->where('data_cat', 'quantity-type');
            $sqlMstGlobals03 = DB::table('mst_globals')
            ->select(
                'id',
                'title_ind'
            )
            ->where('active', 'Y')
            ->where('data_cat', 'brand');

            $sql = DB::table('tx_qty_parts')
            ->joinSub($sqlMstParts, 'mst_parts', function($join) {
                $join->on('tx_qty_parts.part_id', '=', 'mst_parts.part_idx');
            })
            ->leftJoinSub($sqlMstGlobals01, 'mg_01', function($join) {
                $join->on('mst_parts.part_type_id', '=', 'mg_01.id');
            })
            ->leftJoinSub($sqlMstGlobals02, 'mg_02', function($join) {
                $join->on('mst_parts.quantity_type_id', '=', 'mg_02.id');
            })
            ->leftJoinSub($sqlMstGlobals03, 'mg_03', function($join) {
                $join->on('mst_parts.brand_id', '=', 'mg_03.id');
            })
            ->select(
                'mst_parts.part_idx',
                'mst_parts.slug',
                'mst_parts.part_number',
                'mst_parts.part_name',
                'mst_parts.final_price',
                'mst_parts.price_list',
                'mst_parts.avg_cost',
                'mst_parts.final_cost',
                'mst_parts.part_active',
                'tx_qty_parts.qty',
                'tx_qty_parts.branch_id AS branch_id_tmp',
                'tx_qty_parts.id as rank',
                'mg_01.title_ind as part_type_name',
                'mg_02.title_ind as unit_name',
                'mg_03.title_ind as brand_name',
            )
            ->addSelect([
                'has_tx' => DB::raw('
                    (SELECT 
                        (CASE 
                            WHEN EXISTS(
                                SELECT 1 
                                    FROM tx_delivery_order_parts AS tx_do_parts
                                    WHERE tx_do_parts.part_id = mst_parts.part_idx
                                    AND tx_do_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_nota_retur_parts AS tx_nr_parts
                                    WHERE tx_nr_parts.part_id = mst_parts.part_idx
                                    AND tx_nr_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_purchase_memo_parts AS tx_pm_parts
                                    WHERE tx_pm_parts.part_id = mst_parts.part_idx
                                    AND tx_pm_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_purchase_order_parts AS tx_po_parts
                                    WHERE tx_po_parts.part_id = mst_parts.part_idx
                                    AND tx_po_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_purchase_quotation_parts AS tx_pq_parts
                                    WHERE tx_pq_parts.part_id = mst_parts.part_idx
                                    AND tx_pq_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_purchase_retur_parts AS tx_pr_parts
                                    WHERE tx_pr_parts.part_id = mst_parts.part_idx
                                    AND tx_pr_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_receipt_order_parts AS tx_ro_parts
                                    WHERE tx_ro_parts.part_id = mst_parts.part_idx
                                    AND tx_ro_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_sales_order_parts AS tx_so_parts
                                    WHERE tx_so_parts.part_id = mst_parts.part_idx
                                    AND tx_so_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_sales_quotation_parts AS tx_sq_parts
                                    WHERE tx_sq_parts.part_id = mst_parts.part_idx
                                    AND tx_sq_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_stock_assembly_parts AS tx_sa_parts
                                    WHERE tx_sa_parts.part_id = mst_parts.part_idx
                                    AND tx_sa_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_stock_disassembly_parts AS tx_sd_parts
                                    WHERE tx_sd_parts.part_id = mst_parts.part_idx
                                    AND tx_sd_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_stock_transfer_parts AS tx_st_parts
                                    WHERE tx_st_parts.part_id = mst_parts.part_idx
                                    AND tx_st_parts.active = "Y"
                                UNION ALL
                                SELECT 1 
                                    FROM tx_surat_jalan_parts AS tx_sj_parts
                                    WHERE tx_sj_parts.part_id = mst_parts.part_idx
                                    AND tx_sj_parts.active = "Y") THEN "Y" 
                        ELSE "N" 
                        END)) AS has_tx')
            ])
            ->selectRaw('mst_parts.part_name as part_name_wd')
            ->when($parameter[0]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_number', 'LIKE', '%'.$parameter[0].'%');
            })
            ->when($parameter[1]<>'', function($q) use($parameter) {
                $q->where('mst_parts.part_name', 'LIKE', '%'.$parameter[1].'%');
            })
            ->when($parameter[2]<>'', function($q) use($parameter) {
                $q->where('mst_parts.brand_id', '=', $parameter[2]);
            })
            ->when($parameter[3]<>'', function($q) use($parameter) {
                $q->where('mst_parts.brand_id', '=', $parameter[3]);
            })
            ->when($parameter[4]<>'', function($q) use($parameter) {
                $q->where('mg_01.id', '=', $parameter[4]);
            })
            ->when($parameter[5]<>'', function($q) use($parameter) {
                $q->where('tx_qty_parts.branch_id', '=', $parameter[5]);
            })
            ->when($parameter[7]=='Y', function($q) use($parameter) {
                $q->whereRaw('tx_qty_parts.qty>0');
            })
            ->orderBy('mst_parts.part_number', 'ASC');

            // Cache jumlah total data selama 1 jam (3600 detik)
            // $totalRecords = Cache::remember('data_count', 3600, function () use ($sql) {
            //     return $sql->count();
            // });

            return DataTables::of($sql)
            // ->setTotalRecords($totalRecords)
            // opsional: untuk menghindari penghitungan ulang recordsFiltered jika tidak ada pencarian
            // ->skipTotalRecords()
            ->addColumn('part_number_with_delimiter', function ($sql) {
                $partNumber = $sql->part_number;
                if(strlen($partNumber)<11){
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                }else{
                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                }
                return $partNumber;
            })
            ->addColumn('parts_name', function ($sql) {
                return $sql->part_name_wd;
            })
            ->editColumn('branch_name_temp', function ($sql) {
                $q = Mst_branch::where('id', $sql->branch_id_tmp)->first();
                return $q->name ?? '';
            })
            ->filterColumn('branch_name_temp', function($query, $keyword) {
                $query->whereRaw('(SELECT name FROM mst_branches WHERE id = tx_qty_parts.branch_id) LIKE ?', ["%{$keyword}%"]);
            })
            ->addColumn('SOqty', function ($sql) {
                // sales order
                $qtySO = DB::table('tx_sales_order_parts AS txsop')
                ->leftJoin('tx_sales_orders AS txso', function($join) {
                    $join->on('txsop.order_id', '=', 'txso.id')
                    ->where('txso.active', 'Y')
                    ->where('txso.is_draft', 'N')
                    ->where('txso.need_approval', 'N');
                })
                ->where('txsop.part_id', $sql->part_idx)
                ->where('txsop.active', 'Y')
                ->where('txso.branch_id', $sql->branch_id_tmp)
                ->whereNotExists(function (Builder $q1) {
                    $q1->selectRaw(1)
                    ->from('tx_delivery_order_parts as tx_do_parts')
                    ->leftJoin('tx_delivery_orders as tx_do', function($join) {
                        $join->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where('tx_do.is_draft', 'N')
                        ->where('tx_do.active', 'Y');
                    })
                    ->whereColumn('tx_do_parts.sales_order_id', 'txso.id')
                    ->where('tx_do_parts.active', 'Y');
                })
                ->sum('txsop.qty') ?? 0;

                // surat jalan
                $qtySJ = DB::table('tx_surat_jalan_parts AS txsjp')
                ->leftJoin('tx_surat_jalans AS txsj', function($join) {
                    $join->on('txsjp.surat_jalan_id', '=', 'txsj.id')
                    ->where('txsj.active', 'Y')
                    ->where('txsj.need_approval', 'N')
                    ->where('txsj.is_draft', 'N');
                })
                ->where('txsjp.part_id', $sql->part_idx)
                ->where('txsjp.active', 'Y')
                ->where('txsj.branch_id', $sql->branch_id_tmp)
                ->whereNotExists(function (Builder $q1) {
                    $q1->selectRaw(1)
                    ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                    ->leftJoin('tx_delivery_order_non_taxes as tx_do', function($join) {
                        $join->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                        ->where('tx_do.is_draft', 'N')
                        ->where('tx_do.active', 'Y');
                    })
                    ->whereColumn('tx_do_parts.sales_order_id', 'txsj.id')
                    ->where('tx_do_parts.active', 'Y');
                })
                ->sum('txsjp.qty') ?? 0;

                if(($qtySJ+$qtySO)>0){
                    return '<a href="#" onclick="dispSalesOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.($qtySO+$qtySJ).'</a>';
                }else{
                    return ($qtySJ+$qtySO);
                }
            })
            ->addColumn('OOqty', function ($sql) {
                // on order
                $purchase_memo_qty = DB::table('tx_purchase_memo_parts AS tx_mop')
                ->leftJoin('tx_purchase_memos as tx_mo', function($join) {
                    $join->on('tx_mop.memo_id', '=', 'tx_mo.id')
                    ->where('tx_mo.is_draft', 'N')
                    ->where('tx_mo.active', 'Y');
                })
                ->where('tx_mop.part_id', $sql->part_idx)
                ->where('tx_mop.active', 'Y')
                ->where('tx_mo.branch_id', $sql->branch_id_tmp)
                ->sum('tx_mop.qty') ?? 0;

                $purchase_order_qty = DB::table('tx_purchase_order_parts AS tx_pop')
                ->leftJoin('tx_purchase_orders as tx_po', function($join) {
                    $join->on('tx_pop.order_id', '=', 'tx_po.id')
                    ->where('tx_po.is_draft', 'N')
                    ->where('tx_po.active', 'Y')
                    ->whereNotNull('tx_po.approved_by');
                })
                ->where('tx_pop.part_id', $sql->part_idx)
                ->where('tx_pop.active', 'Y')
                ->where('tx_po.branch_id', $sql->branch_id_tmp)
                ->sum('tx_pop.qty') ?? 0;

                $purchase_ro_qty = DB::table('tx_receipt_order_parts AS tx_ro_parts')
                ->leftJoin('tx_receipt_orders as tx_ro', function($join) {
                    $join->on('tx_ro_parts.receipt_order_id', '=', 'tx_ro.id')
                    ->where('tx_ro.is_draft', 'N')
                    ->where('tx_ro.active', 'Y');
                })
                ->where('tx_ro_parts.part_id', $sql->part_idx)
                ->where('tx_ro.branch_id', $sql->branch_id_tmp)
                ->where('tx_ro_parts.active', 'Y')
                ->sum('tx_ro_parts.qty') ?? 0;

                $oo = $purchase_memo_qty + $purchase_order_qty - $purchase_ro_qty;
                if($oo>0){
                    return '<a href="#" onclick="dispOnOrderInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$oo.'</a>';
                }else{
                    return $oo;
                }
            })
            ->addColumn('ITqty', function ($sql) {
                $in_transit_qty = DB::table('tx_stock_transfer_parts AS tx_stockp')
                ->leftJoin('tx_stock_transfers as tx_stock', function($join) {
                    $join->on('tx_stockp.stock_transfer_id', '=', 'tx_stock.id')
                    ->where('tx_stock.approved_by', '!=', null)
                    ->where('tx_stock.received_by', null)
                    ->where('tx_stock.active', 'Y');
                })
                ->where('tx_stockp.active', 'Y')
                ->where('tx_stockp.part_id', $sql->part_idx)
                ->where('tx_stock.branch_to_id', $sql->branch_id_tmp)
                ->sum('tx_stockp.qty') ?? 0;

                if($in_transit_qty>0){
                    return '<a href="#" onclick="dispInTransitInfo('.$sql->part_idx.','.$sql->branch_id_tmp.');">'.$in_transit_qty.'</a>';
                }else{
                    return $in_transit_qty;
                }
            })
            ->addColumn('final_cost_val', function ($sql) {
                $qRO_final_cost = Tx_receipt_order_part::select('tx_receipt_order_parts.final_cost')
                ->leftJoin('tx_receipt_orders as tx_ro', function($join) {
                    $join->on('tx_receipt_order_parts.receipt_order_id', '=', 'tx_ro.id')
                    ->where('tx_ro.is_draft', 'N')
                    ->where('tx_ro.active', 'Y');
                })
                ->where('tx_receipt_order_parts.part_id', $sql->part_idx)
                ->where('tx_receipt_order_parts.final_cost', '>', 0)
                ->where('tx_receipt_order_parts.active', '=', 'Y')
                ->where('tx_ro.branch_id', $sql->branch_id_tmp)
                ->orderBy('tx_ro.created_at','DESC')
                ->take(1);

                return ($qRO_final_cost->first()->final_cost ?? 0);
            })
            ->addColumn('last_final_price_val', function ($sql) {
                // 1. Query untuk mengambil data dari Sales Order (Sudah difilter per ID & Cabang)
                $salesOrderQuery = DB::table('tx_sales_order_parts AS tx_sop')
                ->leftJoin('tx_sales_orders AS tx_so', function($join) {
                    $join->on('tx_sop.order_id', '=', 'tx_so.id')
                    ->where('tx_so.active', 'Y')
                    ->where('tx_so.is_draft', 'N')
                    ->where('tx_so.need_approval', 'N');
                })
                ->select('tx_sop.price AS price', 'tx_sop.created_at AS created_at')
                ->where('tx_sop.part_id', $sql->part_idx)
                ->where('tx_sop.active', 'Y')
                ->where('tx_so.branch_id', $sql->branch_id_tmp);

                // 2. Query dari Surat Jalan, lalu UNION ALL dengan Sales Order, lalu urutkan yang terbaru
                $latestPrice = DB::table('tx_surat_jalan_parts AS tx_sjp')
                ->leftJoin('tx_surat_jalans AS tx_sj', function($join) {
                    $join->on('tx_sjp.surat_jalan_id', '=', 'tx_sj.id')
                    ->where('tx_sj.active', 'Y')
                    ->where('tx_sj.need_approval', 'N')
                    ->where('tx_sj.is_draft', 'N');
                })
                ->select('tx_sjp.price AS price', 'tx_sjp.created_at AS created_at')
                ->where('tx_sjp.part_id', $sql->part_idx)
                ->where('tx_sjp.active', 'Y')
                ->where('tx_sj.branch_id', $sql->branch_id_tmp)
                ->unionAll($salesOrderQuery) // Gabungkan di sini
                ->orderBy('created_at', 'DESC') // Sort hasil gabungan yang sudah sedikit
                ->first(); // Mengambil 1 data teratas (LIMIT 1)

                return $latestPrice ? ($latestPrice->price ?? 0) : 0;
            })
            ->addColumn('action', function ($sql) {
                $txt = '<a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($sql->slug)).'?br_id='.$sql->branch_id_tmp.'">View</a> |
                    <a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-part/'.urlencode($sql->slug).'/edit').'">Edit</a> |
                    <a style="text-decoration: underline;" href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master-stock-card/'.$sql->part_idx).'">Stock Card</a>'.
                    '<input type="hidden" name="title_caption'.$sql->rank.'" id="title_caption'.$sql->rank.'" value="'.$sql->part_name.'">'.
                    '<input type="hidden" name="part_id'.$sql->rank.'" id="part_id'.$sql->rank.'" value="'.$sql->part_idx.'">';
                return $txt;
            })
            ->addColumn('del_checkbox', function ($sql) {
                if ($sql->has_tx=='N'){
                    return '<input type="checkbox" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }else{
                    return '<input type="hidden" name="delRow'.$sql->rank.'" id="delRow'.$sql->rank.'">';
                }
            })
            ->rawColumns(['part_number_with_delimiter', 'parts_name', 'branch_name_temp', 'SOqty', 'OOqty', 'ITqty', 'last_final_price_val', 'price_list_val', 'action', 'del_checkbox'])
            ->toJson();
        }

        $data = [
            'stocks' => [],
            'rowCount' => Tx_qty_part::count(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryBranch' => $queryBranch,
            'queryBrand' => $queryBrand,
            'queryPartType' => $queryPartType,
            'param' => $param,
            'parameter' => $parameter,
        ];

        return view('tx.'.$this->folder.'.index-stock-master-serverside', $data);
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
        $part_no = '';
        if($request->part_no!=''){
            $part_no = $request->part_no;
        }

        $part_name = '';
        if($request->part_name!=''){
            $part_name = $request->part_name;
        }

        $brand_id = '';
        if($request->brand_id!=''){
            $brand_id = $request->brand_id;
        }

        $brandtype_id = '';
        if($request->brandtype_id!=''){
            $brandtype_id = $request->brandtype_id;
        }

        $partType_id = '';
        if($request->partType_id!=''){
            $partType_id = $request->partType_id;
        }

        $branch_id = '';
        if($request->branch_id!=''){
            $branch_id = $request->branch_id;
        }

        $qRstring = $part_no.'::'
            .$part_name.'::'
            .$brand_id.'::'
            .$brandtype_id.'::'
            .$partType_id.'::'
            .$branch_id.'::'
            .($request->showCost=='on'?'Y':'N').'::'
            .($request->showOhGreaterThanZero=='on'?'Y':'N');
        $qRstring = str_replace("/","\\",$qRstring);
        return redirect(route('stockmaster.index').'/'.urlencode($qRstring));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
