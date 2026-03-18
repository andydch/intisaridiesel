<?php

namespace App\Exports\report;

use App\Models\Tx_qty_part;
use App\Models\V_tx_qty_part;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use Illuminate\Support\Facades\DB;
// use Maatwebsite\Excel\Concerns\WithStyles;
// use Maatwebsite\Excel\Concerns\WithHeadings;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\WithColumnWidths;
// use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SummaryStockPerMerkPerGudangExport implements FromView
{
    protected $title = 'Summary Stock Per Merk Per Gudang';
    protected $folder = 'summary-stock-per-merk-per-gudang';
    protected $date_start;
    protected $date_end;
    protected $sqlStats;
    protected $footerRow;

    public function __construct($date_start,$date_end)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);

        $this->date_start = urldecode($date_start);
        $this->date_end = urldecode($date_end);
        $this->footerRow = 0;

        $date_start = ($this->date_start!='no_date'?$this->date_start:'');
        $date_end = ($this->date_end!='no_date'?$this->date_end:'');
        $this->sqlStats = Tx_qty_part::rightJoin('mst_parts','tx_qty_parts.part_id','=','mst_parts.id')
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
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('v_tx_qty_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('v_tx_qty_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->orderBy('v_tx_qty_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['selling_price' => Tx_delivery_order_part::leftJoin('tx_delivery_orders AS tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
            ->select('tx_delivery_order_parts.final_price')
            ->whereColumn('tx_delivery_order_parts.sales_order_part_id','mst_parts.id')
            ->whereRaw('tx_delivery_order_parts.active=\'Y\'')
            ->whereRaw('tx_do.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_delivery_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_delivery_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->orderBy('tx_delivery_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['total_selling_price' => Tx_delivery_order_part::leftJoin('tx_delivery_orders AS tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
            ->select('tx_delivery_order_parts.total_price')
            ->whereColumn('tx_delivery_order_parts.sales_order_part_id','mst_parts.id')
            ->whereRaw('tx_delivery_order_parts.active=\'Y\'')
            ->whereRaw('tx_do.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_delivery_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_delivery_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->orderBy('tx_delivery_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['avg_selling_price' => Tx_receipt_order_part::leftJoin('tx_receipt_orders AS tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->select('tx_receipt_order_parts.avg_cost')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_receipt_order_parts.active=\'Y\'')
            ->whereRaw('tx_ro.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_receipt_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_receipt_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->orderBy('tx_receipt_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['total_avg_selling_price' => Tx_receipt_order_part::leftJoin('tx_receipt_orders AS tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->select(DB::raw('tx_receipt_order_parts.qty*tx_receipt_order_parts.avg_cost'))
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_receipt_order_parts.active=\'Y\'')
            ->whereRaw('tx_ro.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_receipt_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_receipt_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->orderBy('tx_receipt_order_parts.created_at','DESC')
            ->limit(1)
        ])
        ->addSelect(['purchase_memo_qty' => Tx_purchase_memo_part::selectRaw('SUM(tx_purchase_memo_parts.qty)')
            ->leftJoin('tx_purchase_memos as tx_memo','tx_purchase_memo_parts.memo_id','=','tx_memo.id')
            ->whereColumn('tx_purchase_memo_parts.part_id','mst_parts.id')
            ->whereRaw('tx_purchase_memo_parts.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_purchase_memo_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_purchase_memo_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->whereRaw('tx_memo.active=\'Y\'')
        ])
        ->addSelect(['purchase_ro_qty' => Tx_receipt_order_part::selectRaw('SUM(tx_receipt_order_parts.qty)')
            ->leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->whereColumn('tx_receipt_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_receipt_order_parts.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_receipt_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_receipt_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->where('tx_ro.approved_by','<>',null)
            ->whereRaw('tx_ro.active=\'Y\'')
        ])
        ->addSelect(['purchase_order_qty' => Tx_purchase_order_part::selectRaw('SUM(tx_purchase_order_parts.qty)')
            ->leftJoin('tx_purchase_orders as tx_order','tx_purchase_order_parts.order_id','=','tx_order.id')
            ->whereColumn('tx_purchase_order_parts.part_id','mst_parts.id')
            ->whereRaw('tx_purchase_order_parts.active=\'Y\'')
            ->when($date_start<>'', function($q0) use($date_start) {
                $q0->whereRaw('tx_purchase_order_parts.created_at>=\''.$date_start.' 23:59:59\'');
            })
            ->when($date_end<>'', function($q0) use($date_end) {
                $q0->whereRaw('tx_purchase_order_parts.created_at<=\''.$date_end.' 23:59:59\'');
            })
            ->whereRaw('tx_order.active=\'Y\'')
        ])
        ->whereRaw('mst_parts.active=\'Y\'')
        ->where(function ($q) {
            $q->whereRaw('tx_qty_parts.qty<mst_parts.safety_stock')
            ->orWhereRaw('tx_qty_parts.qty>mst_parts.max_stock');
        });
    }

    public function view(): View
    {
        $query = DB::table(DB::raw("({$this->sqlStats->toSql()}) as sub"))
        ->select(
            'brand_name',
            'branch_name',
            DB::raw('SUM(total_avg_selling_price) AS cost_amount'),
            DB::raw('SUM(total_selling_price) AS sales_amount'),
        )
        ->groupBy('brand_name','branch_name')
        ->get();
        $data = [
            'title' => 'Result',
            'folder' => $this->folder,
            'title01' => 'REPORT SUMMARY STOCK PER MERK PER GUDANG',
            'title02' => 'Date: '.(($this->date_start=='no_date')?'':date_format(date_create($this->date_start), 'd M Y')).' - '.
                (($this->date_end=='no_date')?'':date_format(date_create($this->date_end), 'd M Y')),
            'query' => $query
        ];
        return view(ENV('REPORT_FOLDER_NAME').'.'.$this->folder.'.rpt', $data);
    }
}
