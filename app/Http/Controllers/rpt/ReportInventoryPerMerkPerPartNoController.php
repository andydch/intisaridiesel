<?php

namespace App\Http\Controllers\rpt;

use App\Models\Mst_part;
use App\Models\Mst_branch;
use App\Models\Mst_global;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\V_tx_qty_part;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_purchase_order_part;
// use Illuminate\Support\Facades\Validator;

class ReportInventoryPerMerkPerPartNoController extends Controller
{
    protected $title = 'Inventory Per Merk Per Part No';
    protected $folder = 'inventory-per-merk-per-part-no';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $parts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC')
        ->get();
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y',
        ])
        ->orderBy('title_ind','ASC')
        ->get();
        $data = [
            'parts' => $parts,
            'branches' => $branches,
            'brands' => $brands,
            'title' => $this->title,
            'folder' => $this->folder,
            'query' => []
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
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
        $parts = Mst_part::where('active','=','Y')
        ->orderBy('part_name','ASC')
        ->get();
        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        $brands = Mst_global::where([
            'data_cat' => 'brand',
            'active' => 'Y',
        ])
        ->orderBy('title_ind','ASC')
        ->get();

        $journal_date = '';
        // $journal_date = date('Y-m-d');
        if($request->journal_date!=''){
            $journal_date = $request->journal_date;
        }

        $querySub = Tx_qty_part::rightJoin('mst_parts','tx_qty_parts.part_id','=','mst_parts.id')
        ->leftJoin('mst_globals AS mg_brand','mst_parts.brand_id','=','mg_brand.id')
        ->leftJoin('mst_branches AS branches','tx_qty_parts.branch_id','=','branches.id')
        ->leftJoin('mst_globals AS mg_unit','mst_parts.quantity_type_id','=','mg_unit.id')
        ->leftJoin('mst_globals AS mg_part_type','mst_parts.part_type_id','=','mg_part_type.id')
        ->leftJoin('mst_globals AS mg_weight','mst_parts.weight_id','=','mg_weight.id')
        ->select(
            'mst_parts.id AS part_id',
            'mst_parts.part_number',
            'mst_parts.part_name',
            'mst_parts.final_price AS part_final_price',
            'mst_parts.avg_cost AS part_avg_cost',
            'mst_parts.active AS part_active',
            'mst_parts.safety_stock',
            'mst_parts.max_stock',
            'mst_parts.weight',
            'mst_parts.part_brand',
            'tx_qty_parts.qty AS qty_on_hand',
            'mg_brand.id AS brand_id',
            'mg_brand.title_ind AS brand_name',
            'branches.name AS branch_name',
            'mg_unit.title_ind AS unit_name',
            'mg_part_type.title_ind AS part_type_name',
            'mg_weight.title_ind AS weight_name',
        )
        ->addSelect(['last_qty_on_hand_per_date' => V_tx_qty_part::select('v_tx_qty_parts.qty')
            ->whereColumn('v_tx_qty_parts.part_id','mst_parts.id')
            ->whereColumn('v_tx_qty_parts.branch_id','branches.id')
            // ->whereRaw('v_tx_qty_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('v_tx_qty_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->orderBy('v_tx_qty_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['selling_price' => Tx_delivery_order_part::leftJoin('tx_delivery_orders AS tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
            ->select('tx_delivery_order_parts.final_price')
            ->whereColumn('tx_delivery_order_parts.sales_order_part_id','mst_parts.id')
            ->whereRaw('tx_delivery_order_parts.active=\'Y\'')
            ->whereRaw('tx_do.active=\'Y\'')
            // ->whereRaw('tx_delivery_order_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('tx_delivery_order_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->orderBy('tx_delivery_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['avg_selling_price' => Tx_receipt_order_part::leftJoin('tx_receipt_orders AS tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->select('tx_receipt_order_parts.avg_cost')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_receipt_order_parts.active=\'Y\'')
            ->whereRaw('tx_ro.active=\'Y\'')
            // ->whereRaw('tx_receipt_order_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('tx_receipt_order_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->orderBy('tx_receipt_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('SUM(tx_purchase_memo_parts.qty)')
            ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
            ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
            ->whereRaw('tx_purchase_memo_parts.active=\'Y\'')
            // ->whereRaw('tx_purchase_memo_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('tx_purchase_memo_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->whereRaw('tx_memo.active=\'Y\'')
        ])
        ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_receipt_order_parts.active=\'Y\'')
            // ->whereRaw('tx_receipt_order_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('tx_receipt_order_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->where('tx_ro.approved_by','<>',null)
            ->whereRaw('tx_ro.active=\'Y\'')
        ])
        ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('SUM(tx_purchase_order_parts.qty)')
            ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
            ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_purchase_order_parts.active=\'Y\'')
            // ->whereRaw('tx_purchase_order_parts.created_at<=\''.$journal_date.' 23:59:59\'')
            ->when($journal_date<>'', function($q0) use($journal_date) {
                $q0->whereRaw('tx_purchase_order_parts.created_at=\''.$journal_date.' 23:59:59\'');
            })
            ->whereRaw('tx_order.active=\'Y\'')
        ])
        ->whereRaw('mst_parts.active=\'Y\'')
        ->when(request()->has('part_no') && request()->part_no<>0, function($q) use($request) {
            $q->whereRaw('mst_parts.id='.$request->part_no);
        })
        ->when(request()->has('part_name') && request()->part_name<>0, function($q) use($request) {
            $q->whereRaw('mst_parts.id='.$request->part_name);
        })
        ->when(request()->has('brand_id') && request()->brand_id<>0, function($q) use($request) {
            $q->whereRaw('mg_brand.id='.$request->brand_id);
        })
        ->when(request()->has('branch_id') && request()->branch_id<>0, function($q) use($request) {
            $q->whereRaw('branches.id='.$request->branch_id);
        });
        // ->toSql();dd($query);

        $query = DB::table(DB::raw("({$querySub->toSql()}) as sub"))
        ->select(
            'part_id',
            'part_number',
            'part_name',
            'brand_id',
            'brand_name',
            'part_brand',
            'selling_price',
            // DB::raw('IFNULL(selling_price,part_final_price) AS selling_price'),
            'avg_selling_price',
            // DB::raw('IFNULL(avg_selling_price,part_avg_cost) AS avg_selling_price'),
            'qty_on_hand',
            'branch_name',
            DB::raw('(purchase_memo_qty+purchase_order_qty-purchase_ro_qty) AS on_o'),
            'safety_stock',
            'max_stock',
            'unit_name',
            'part_type_name',
            'weight',
            'weight_name',
            'last_qty_on_hand_per_date'
        )
        ->get();

        if($request->view_mode=='V'){
            $data = [
                'parts' => $parts,
                'branches' => $branches,
                'brands' => $brands,
                'title' => $this->title,
                'folder' => $this->folder,
                'query' => $query,
                'reqs' => $request,
            ];
            return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.index', $data);
        }
        if($request->view_mode=='P'){
            return redirect(ENV('REPORT_FOLDER_NAME').'/inventory-per-merk-per-part-no-xlsx/'.
                urlencode(($journal_date!=''?$journal_date:'no_date')).'/'.
                $request->part_no.'/'.
                $request->part_name.'/'.
                $request->brand_id.'/'.
                $request->branch_id);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mst_part  $mst_part
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
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mst_part  $mst_part
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mst_part $mst_part)
    {
        //
    }
}
