<?php

namespace App\Http\Controllers\tx;

use App\Http\Controllers\Controller;
use App\Models\Mst_branch;
use App\Models\Mst_part;
use App\Models\Tx_purchase_order;
// use App\Models\V_stock_card;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockMasterStockCardController extends Controller
{
    protected $title = 'Stock Master - Stock Card';
    protected $folder = 'stock-master';
    protected $uri_folder = 'stock-master-stock-card';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->to(url(ENV('TRANSACTION_FOLDER_NAME').'/stock-master'));
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
        $validateInput = [
            'from_date' => 'required',
            'to_date' => 'required',
        ];
        $errMsg = [];
        Validator::make(
            $request->all(),
            $validateInput,
            $errMsg
        )->validate();

        $queryPart = Mst_part::where('id', '=', $request->part_idx)
        ->first();

        $queryBranch = Mst_branch::where('active', '=', 'Y')
        ->orderBy('name', 'ASC')
        ->get();

        $queryBranchBeginningBalance = Mst_branch::where('active', '=', 'Y')
        ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
            $q->where('id', '=', $request->branch_id);
        })
        ->get();

        // // old format : YYYY-MM-DD
        // // new format : DD/MM/YYYY
        // $from_date = explode("/",$request->from_date);
        // $to_date = explode("/",$request->to_date);
        // $part_id = $request->part_idx;

        // $queryStockCard = V_stock_card::where([
        //     'part_id' => $request->part_idx
        // ])
        // ->when(request()->has('branch_id') && request()->branch_id<>'',
        //     function($q) use($request) {
        //     $q->where('branch_id','=', $request->branch_id);
        // })
        // ->where('doc_no','NOT LIKE','%Draft%')
        // ->where('tx_date','>=',$from_date[2].'-'.$from_date[1].'-'.$from_date[0])
        // ->where('tx_date','<=',$to_date[2].'-'.$to_date[1].'-'.$to_date[0])
        // ->orderBy('tx_date','ASC')
        // ->orderBy('updated_at','ASC')
        // ->orderBy('doc_no','ASC')
        // ->orderBy('status','DESC');

        $startDate = explode("/", $request->from_date);
        $endDate = explode("/", $request->to_date);
        $partId = $request->part_idx;

        // -------------------------------------------------------------
        // BLOCK 1: RECEIPT ORDERS
        // -------------------------------------------------------------
        $receiptOrders = DB::table('tx_receipt_order_parts AS rop')
            ->leftJoin('tx_receipt_orders AS ro', 'rop.receipt_order_id', '=', 'ro.id')
            ->leftJoin('v_po_mo_no', 'rop.po_mo_no', '=', 'v_po_mo_no.po_mo_no')
            ->leftJoin('mst_branches AS b', 'v_po_mo_no.branch_id', '=', 'b.id')
            ->leftJoin('mst_suppliers AS s', 'ro.supplier_id', '=', 's.id')
            ->where('rop.active', 'Y')
            ->where('ro.active', 'Y')
            ->where('s.active', 'Y')
            ->where('b.active', 'Y')
            ->where('rop.part_id', $partId)
            ->where('ro.receipt_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('ro.receipt_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN ro.branch_id IS NOT NULL THEN ro.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'ro.receipt_no AS doc_no', 'ro.receipt_date AS tx_date', 'rop.qty AS qty', 
                'rop.final_cost AS price', 'rop.avg_cost AS avg_cost', 'b.name AS branch_name', 
                DB::raw("'IN' AS status"), 'rop.created_at AS created_at', 'rop.updated_at AS updated_at', 
                'rop.part_id AS part_id', 's.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 2: DELIVERY ORDERS
        // -------------------------------------------------------------
        $deliveryOrders = DB::table('tx_delivery_order_parts AS dop')
            ->leftJoin('tx_delivery_orders AS do', 'dop.delivery_order_id', '=', 'do.id')
            ->leftJoin('tx_sales_orders AS so', 'dop.sales_order_id', '=', 'so.id')
            ->leftJoin('mst_branches AS b', 'so.branch_id', '=', 'b.id')
            ->leftJoin('mst_customers AS c', 'do.customer_id', '=', 'c.id')
            ->where('dop.active', 'Y')
            ->where('do.is_draft', 'N')
            ->where('do.active', 'Y')
            ->where('c.active', 'Y')
            ->where('b.active', 'Y')
            ->where('so.active', 'Y')
            ->where('dop.part_id', $partId)
            ->where('do.delivery_order_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('do.delivery_order_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN do.branch_id IS NOT NULL THEN do.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'do.delivery_order_no AS doc_no', 'do.delivery_order_date AS tx_date', 'dop.qty AS qty', 
                DB::raw('(SELECT sop.last_avg_cost FROM tx_sales_order_parts sop WHERE sop.id = dop.sales_order_part_id) AS price'), 
                'dop.final_price AS avg_cost', 'b.name AS branch_name', DB::raw("'OUT' AS status"), 
                'so.updated_at AS created_at', 'so.created_at AS updated_at', 'dop.part_id AS part_id', 
                'c.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 3: PURCHASE RETURS
        // -------------------------------------------------------------
        $purchaseReturs = DB::table('tx_purchase_retur_parts AS prp')
            ->leftJoin('tx_purchase_returs AS pr', 'prp.purchase_retur_id', '=', 'pr.id')
            ->leftJoin('mst_branches AS b', 'pr.branch_id', '=', 'b.id')
            ->leftJoin('mst_suppliers AS s', 'pr.supplier_id', '=', 's.id')
            ->where('prp.active', 'Y')
            ->whereNotNull('pr.approved_by')
            ->where('pr.active', 'Y')
            ->where('prp.part_id', $partId)
            ->where('pr.purchase_retur_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('pr.purchase_retur_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN pr.branch_id IS NOT NULL THEN pr.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'pr.purchase_retur_no AS doc_no', 'pr.purchase_retur_date AS tx_date', 'prp.qty_retur AS qty', 
                'prp.final_cost AS price', 
                DB::raw('(SELECT vrop.avg_cost FROM tx_receipt_order_parts vrop WHERE vrop.receipt_order_id = pr.receipt_order_id AND vrop.part_id = prp.part_id LIMIT 1) AS avg_cost'), 
                'b.name AS branch_name', DB::raw("'OUT' AS status"), 'prp.created_at AS created_at', 'prp.updated_at AS updated_at', 
                'prp.part_id AS part_id', 's.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 4: NOTA RETUR
        // -------------------------------------------------------------
        $notaRetur = DB::table('tx_nota_retur_parts AS nrp')
            ->leftJoin('tx_nota_returs AS nr', 'nrp.nota_retur_id', '=', 'nr.id')
            ->leftJoin('mst_branches AS b', 'nr.branch_id', '=', 'b.id')
            ->leftJoin('mst_customers AS cust', 'nr.customer_id', '=', 'cust.id')
            ->where('nrp.active', 'Y')
            ->whereNotNull('nr.approved_by')
            ->where('nr.active', 'Y')
            ->where('b.active', 'Y')
            ->where('cust.active', 'Y')
            ->where('nrp.part_id', $partId)
            ->where('nr.nota_retur_no', 'NOT LIKE', '%Draft%')
            ->whereBetween(DB::raw("CAST(DATE_FORMAT(nr.approved_at, '%Y-%m-%d') AS DATE)"), [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN nr.branch_id IS NOT NULL THEN nr.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'nr.nota_retur_no AS doc_no', DB::raw("CAST(DATE_FORMAT(nr.approved_at, '%Y-%m-%d') AS DATE) AS tx_date"), 'nrp.qty_retur AS qty', 
                'nrp.final_price AS price', 
                DB::raw('(SELECT lq.avg_cost FROM log_tx_qty_parts lq WHERE lq.part_id = nrp.part_id AND lq.branch_id = b.id AND lq.updated_at >= nr.updated_at ORDER BY lq.updated_at LIMIT 1) AS avg_cost'), 
                'b.name AS branch_name', DB::raw("'IN' AS status"), 'nrp.created_at AS created_at', 'nr.updated_at AS updated_at', 
                'nrp.part_id AS part_id', 'cust.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 5: NOTA RETUR NON TAXES
        // -------------------------------------------------------------
        $notaReturNonTaxes = DB::table('tx_nota_retur_part_non_taxes AS nrp')
            ->leftJoin('tx_nota_retur_non_taxes AS nr', 'nrp.nota_retur_id', '=', 'nr.id')
            ->leftJoin('mst_branches AS b', 'nr.branch_id', '=', 'b.id')
            ->leftJoin('mst_customers AS cust', 'nr.customer_id', '=', 'cust.id')
            ->where('nrp.active', 'Y')
            ->whereNotNull('nr.approved_by')
            ->where('nr.active', 'Y')
            ->where('b.active', 'Y')
            ->where('cust.active', 'Y')
            ->where('nrp.part_id', $partId)
            ->where('nr.nota_retur_no', 'NOT LIKE', '%Draft%')
            ->whereBetween(DB::raw("CAST(DATE_FORMAT(nr.approved_at, '%Y-%m-%d') AS DATE)"), [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN nr.branch_id IS NOT NULL THEN nr.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'nr.nota_retur_no AS doc_no', DB::raw("CAST(DATE_FORMAT(nr.approved_at, '%Y-%m-%d') AS DATE) AS tx_date"), 'nrp.qty_retur AS qty', 
                'nrp.final_price AS price', 
                DB::raw('(SELECT lq.avg_cost FROM log_tx_qty_parts lq WHERE lq.part_id = nrp.part_id AND lq.branch_id = b.id AND lq.updated_at >= nr.updated_at ORDER BY lq.updated_at LIMIT 1) AS avg_cost'), 
                'b.name AS branch_name', DB::raw("'IN' AS status"), 'nrp.created_at AS created_at', 'nr.updated_at AS updated_at', 
                'nrp.part_id AS part_id', 'cust.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 6: DELIVERY ORDER NON TAXES
        // -------------------------------------------------------------
        $doNonTaxes = DB::table('tx_delivery_order_non_tax_parts AS np_part')
            ->leftJoin('tx_delivery_order_non_taxes AS np', 'np_part.delivery_order_id', '=', 'np.id')
            ->leftJoin('mst_branches AS b', 'np.branch_id', '=', 'b.id')
            ->leftJoin('mst_customers AS cust', 'np.customer_id', '=', 'cust.id')
            ->leftJoin('tx_surat_jalans AS tx_sj', 'np_part.sales_order_id', '=', 'tx_sj.id')
            ->where('np_part.active', 'Y')
            ->where('np.is_draft', 'N')
            ->where('np.active', 'Y')
            ->where('b.active', 'Y')
            ->where('cust.active', 'Y')
            ->where('np_part.part_id', $partId)
            ->where('np.delivery_order_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('np.delivery_order_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN np.branch_id IS NOT NULL THEN np.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'np.delivery_order_no AS doc_no', 'np.delivery_order_date AS tx_date', 'np_part.qty AS qty', 
                DB::raw('(SELECT sjp.last_avg_cost FROM tx_surat_jalan_parts sjp WHERE sjp.id = np_part.sales_order_part_id AND sjp.part_id = np_part.part_id) AS price'), 
                'np_part.final_price AS avg_cost', 'b.name AS branch_name', DB::raw("'OUT' AS status"), 
                'tx_sj.updated_at AS created_at', 'tx_sj.created_at AS updated_at', 'np_part.part_id AS part_id', 
                'cust.name AS customer_or_supplier', 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 7: STOCK ADJUSTMENTS
        // -------------------------------------------------------------
        $stockAdjustments = DB::table('tx_stock_adjustment_parts AS adj_part')
            ->leftJoin('tx_stock_adjustments AS adj', 'adj_part.stock_adj_id', '=', 'adj.id')
            ->leftJoin('mst_branches AS b', 'adj.branch_id', '=', 'b.id')
            ->where('adj_part.active', 'Y')
            ->where('adj.active', 'Y')
            ->where('adj_part.part_id', $partId)
            ->where('adj.stock_adj_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('adj.stock_adj_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN adj.branch_id IS NOT NULL THEN adj.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'adj.stock_adj_no AS doc_no', 'adj.stock_adj_date AS tx_date', 
                DB::raw('(CASE WHEN adj_part.adjustment >= 0 THEN adj_part.adjustment ELSE (adj_part.adjustment * -1) END) AS qty'), 
                'adj_part.avg_cost AS price', 'adj_part.avg_cost AS avg_cost', 'b.name AS branch_name', 
                DB::raw("(CASE WHEN adj_part.adjustment >= 0 THEN 'IN' ELSE 'OUT' END) AS status"), 
                'adj_part.created_at AS created_at', 'adj_part.updated_at AS updated_at', 'adj_part.part_id AS part_id', 
                DB::raw("'ADJUSMENT' AS customer_or_supplier"), 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 8: STOCK TRANSFERS OUT
        // -------------------------------------------------------------
        $stockTransfersOut = DB::table('tx_stock_transfer_parts AS stock_part')
            ->leftJoin('tx_stock_transfers AS stock', 'stock_part.stock_transfer_id', '=', 'stock.id')
            ->leftJoin('mst_branches AS b', 'stock.branch_from_id', '=', 'b.id')
            ->where('stock_part.active', 'Y')
            ->whereNotNull('stock.approved_by')
            ->where('stock.active', 'Y')
            ->where('stock_part.part_id', $partId)
            ->where('stock.stock_transfer_no', 'NOT LIKE', '%Draft%')
            ->whereBetween(DB::raw("DATE_FORMAT(stock.approved_at, '%Y-%m-%d')"), [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN stock.branch_from_id IS NOT NULL THEN stock.branch_from_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'stock.stock_transfer_no AS doc_no', DB::raw("DATE_FORMAT(stock.approved_at, '%Y-%m-%d') AS tx_date"), 'stock_part.qty AS qty', 
                DB::raw('(SELECT mp.avg_cost FROM mst_parts mp WHERE mp.id = stock_part.part_id) AS price'), 
                DB::raw('(SELECT vlog.avg_cost FROM v_log_avg_cost vlog WHERE vlog.part_id = stock_part.part_id AND vlog.updated_at < stock_part.updated_at AND vlog.avg_cost > 0 ORDER BY vlog.updated_at DESC LIMIT 1) AS avg_cost'), 
                'b.name AS branch_name', DB::raw("'OUT' AS status"), 'stock_part.created_at AS created_at', 'stock_part.created_at AS updated_at', 
                'stock_part.part_id AS part_id', DB::raw("'TRANSFER - OUT' AS customer_or_supplier"), 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 9: STOCK TRANSFERS IN
        // -------------------------------------------------------------
        $stockTransfersIn = DB::table('tx_stock_transfer_parts AS stock_part')
            ->leftJoin('tx_stock_transfers AS stock', 'stock_part.stock_transfer_id', '=', 'stock.id')
            ->leftJoin('mst_branches AS b', 'stock.branch_to_id', '=', 'b.id')
            ->where('stock_part.active', 'Y')
            ->whereNotNull('stock.approved_by')
            ->whereNotNull('stock.received_by')
            ->where('stock.active', 'Y')
            ->where('stock_part.part_id', $partId)
            ->where('stock.stock_transfer_no', 'NOT LIKE', '%Draft%')
            ->whereBetween(DB::raw("DATE_FORMAT(stock.approved_at, '%Y-%m-%d')"), [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN stock.branch_to_id IS NOT NULL THEN stock.branch_to_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'stock.stock_transfer_no AS doc_no', DB::raw("DATE_FORMAT(stock.approved_at, '%Y-%m-%d') AS tx_date"), 'stock_part.qty AS qty', 
                DB::raw('(SELECT mp.avg_cost FROM mst_parts mp WHERE mp.id = stock_part.part_id) AS price'), 
                DB::raw('(SELECT vlog.avg_cost FROM v_log_avg_cost vlog WHERE vlog.part_id = stock_part.part_id AND vlog.updated_at < stock_part.updated_at AND vlog.avg_cost > 0 ORDER BY vlog.updated_at DESC LIMIT 1) AS avg_cost'), 
                'b.name AS branch_name', DB::raw("'IN' AS status"), 'stock_part.created_at AS created_at', 'stock_part.updated_at AS updated_at', 
                'stock_part.part_id AS part_id', DB::raw("'TRANSFER - IN' AS customer_or_supplier"), 'b.id AS branch_id'
            ]);

        // -------------------------------------------------------------
        // BLOCK 10: STOCK ASSEMBLY PARTS
        // -------------------------------------------------------------
        $stockAssemblyParts = DB::table('tx_stock_assembly_parts AS sap')
            ->leftJoin('tx_stock_assemblys AS sa', 'sap.stock_assembly_id', '=', 'sa.id')
            ->leftJoin('mst_branches AS b', 'sa.branch_id', '=', 'b.id')
            ->where('sap.active', 'Y')
            ->where('sa.active', 'Y')
            ->where('sap.part_id', $partId)
            ->where('sa.stock_assembly_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('sa.stock_assembly_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN sa.branch_id IS NOT NULL THEN sa.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'sa.stock_assembly_no AS doc_no', 'sa.stock_assembly_date AS tx_date', 'sap.qty AS qty', 
                'sap.final_cost AS price', 'sap.avg_cost AS avg_cost', 'b.name AS branch_name', 
                DB::raw("'OUT' AS status"), 'sap.created_at AS created_at', 'sap.updated_at AS updated_at', 
                'sap.part_id AS part_id', DB::raw("'ASSEMBLY' AS customer_or_supplier"), 
                DB::raw('(CASE WHEN sa.branch_id IS NOT NULL THEN sa.branch_id ELSE b.id END) AS branch_id')
            ]);

        // -------------------------------------------------------------
        // BLOCK 11: STOCK ASSEMBLY HEADER
        // -------------------------------------------------------------
        $stockAssemblyHeader = DB::table('tx_stock_assemblys AS sa')
            ->leftJoin('mst_branches AS b', 'sa.branch_id', '=', 'b.id')
            ->where('sa.active', 'Y')
            ->where('sa.part_id', $partId)
            ->where('sa.stock_assembly_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('sa.stock_assembly_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN sa.branch_id IS NOT NULL THEN sa.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'sa.stock_assembly_no AS doc_no', 'sa.stock_assembly_date AS tx_date', 'sa.qty AS qty', 
                DB::raw('(sa.final_cost / sa.qty) AS price'), DB::raw('(sa.final_cost / sa.qty) AS avg_cost'), 'b.name AS branch_name', 
                DB::raw("'IN' AS status"), 'sa.created_at AS created_at', 'sa.updated_at AS updated_at', 
                'sa.part_id AS part_id', DB::raw("'ASSEMBLY' AS customer_or_supplier"), 
                DB::raw('(CASE WHEN sa.branch_id IS NOT NULL THEN sa.branch_id ELSE b.id END) AS branch_id')
            ]);

        // -------------------------------------------------------------
        // BLOCK 12: STOCK DISASSEMBLY PARTS
        // -------------------------------------------------------------
        $stockDisassemblyParts = DB::table('tx_stock_disassembly_parts AS sdp')
            ->leftJoin('tx_stock_disassemblies AS sd', 'sdp.stock_disassembly_id', '=', 'sd.id')
            ->leftJoin('mst_branches AS b', 'sd.branch_id', '=', 'b.id')
            ->where('sdp.active', 'Y')
            ->where('sd.active', 'Y')
            ->where('sdp.part_id', $partId)
            ->where('sd.stock_disassembly_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('sd.stock_disassembly_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN sd.branch_id IS NOT NULL THEN sd.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'sd.stock_disassembly_no AS doc_no', 'sd.stock_disassembly_date AS tx_date', 'sdp.qty AS qty', 
                'sdp.final_cost AS price', 'sdp.avg_cost AS avg_cost', 'b.name AS branch_name', 
                DB::raw("'IN' AS status"), 'sdp.created_at AS created_at', 'sdp.updated_at AS updated_at', 
                'sdp.part_id AS part_id', DB::raw("'DISASSEMBLY' AS customer_or_supplier"), 
                DB::raw('(CASE WHEN sd.branch_id IS NOT NULL THEN sd.branch_id ELSE b.id END) AS branch_id')
            ]);

        // -------------------------------------------------------------
        // BLOCK 13: STOCK DISASSEMBLY HEADER
        // -------------------------------------------------------------
        $stockDisassemblyHeader = DB::table('tx_stock_disassemblies AS sd')
            ->leftJoin('mst_branches AS b', 'sd.branch_id', '=', 'b.id')
            ->where('sd.active', 'Y')
            ->where('sd.part_id', $partId)
            ->where('sd.stock_disassembly_no', 'NOT LIKE', '%Draft%')
            ->whereBetween('sd.stock_disassembly_date', [$startDate[2].'-'.$startDate[1].'-'.$startDate[0], $endDate[2].'-'.$endDate[1].'-'.$endDate[0]])
            ->when(request()->has('branch_id') && request()->branch_id<>'', function($q) use($request) {
                $q->whereRaw('(CASE WHEN sd.branch_id IS NOT NULL THEN sd.branch_id ELSE b.id END)='.$request->branch_id);
            })
            ->select([
                'sd.stock_disassembly_no AS doc_no', 'sd.stock_disassembly_date AS tx_date', 'sd.qty AS qty', 
                'sd.avg_cost AS price', DB::raw('(sd.avg_cost / sd.qty) AS avg_cost'), 'b.name AS branch_name', 
                DB::raw("'OUT' AS status"), 'sd.created_at AS created_at', 'sd.updated_at AS updated_at', 
                'sd.part_id AS part_id', DB::raw("'DISASSEMBLY' AS customer_or_supplier"), 
                DB::raw('(CASE WHEN sd.branch_id IS NOT NULL THEN sd.branch_id ELSE b.id END) AS branch_id')
            ]);


        // =============================================================
        // TAHAP AKHIR: RUN UNION ALL GLOBAL + ORDER BY + LIMIT 1
        // =============================================================
        $queryStockCard = $receiptOrders
            ->unionAll($deliveryOrders)
            ->unionAll($purchaseReturs)
            ->unionAll($notaRetur)
            ->unionAll($notaReturNonTaxes)
            ->unionAll($doNonTaxes)
            ->unionAll($stockAdjustments)
            ->unionAll($stockTransfersOut)
            ->unionAll($stockTransfersIn)
            ->unionAll($stockAssemblyParts)
            ->unionAll($stockAssemblyHeader)
            ->unionAll($stockDisassemblyParts)
            ->unionAll($stockDisassemblyHeader)
            ->orderBy('tx_date', 'asc')
            ->orderBy('updated_at', 'asc')
            ->orderBy('doc_no', 'asc')
            ->orderBy('status', 'desc');

        $data = [
            'stockcards_part' => $queryStockCard->get(),
            'stockcards_part_first' => $queryStockCard->first(),
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryPart' => $queryPart,
            'queryBranch' => $queryBranch,
            'queryBranchBeginningBalance' => $queryBranchBeginningBalance,
            'request' => $request
        ];

        return view('tx.'.$this->folder.'.index-stock-card', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Tx_purchase_order  $tx_purchase_order
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $queryPart = Mst_part::where('id','=',$id)->first();
        $queryBranch = Mst_branch::where('active','=','Y')
            ->orderBy('name','ASC')
            ->get();
        $data = [
            'stockcards_qty' => [],
            'stockcards_part' => [],
            'title' => $this->title,
            'folder' => $this->folder,
            'uri_folder' => $this->uri_folder,
            'queryPart' => $queryPart,
            'queryBranch' => $queryBranch,
            'queryBranchBeginningBalance' => [],
        ];

        return view('tx.'.$this->folder.'.index-stock-card', $data);
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
