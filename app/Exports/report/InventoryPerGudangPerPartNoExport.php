<?php

namespace App\Exports\report;

use App\Models\Mst_branch;
use App\Models\Tx_qty_part;
use App\Models\V_tx_qty_part;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_receipt_order_part;
use App\Models\Tx_purchase_memo_part;
use App\Models\Tx_purchase_order_part;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryPerGudangPerPartNoExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $journal_date;
    protected $part_no;
    protected $part_name;
    protected $brand_id;
    protected $branch_id;

    public function __construct($journal_date,$part_no,$part_name,$brand_id,$branch_id)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', 1800);

        $this->journal_date = urldecode($journal_date);
        $this->part_no = $part_no;
        $this->part_name = $part_name;
        $this->brand_id = $brand_id;
        $this->branch_id = $branch_id;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $journal_date = ($this->journal_date!='no_date'?$this->journal_date:'');

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
        ->when($this->part_no<>0, function($q) {
            $q->whereRaw('mst_parts.id='.$this->part_no);
        })
        ->when($this->part_name<>0, function($q) {
            $q->whereRaw('mst_parts.id='.$this->part_name);
        })
        ->when($this->brand_id<>0, function($q) {
            $q->whereRaw('mg_brand.id='.$this->brand_id);
        })
        ->when($this->branch_id<>0, function($q) {
            $q->whereRaw('branches.id='.$this->branch_id);
        });
        // ->toSql();dd($query);

        return DB::table(DB::raw("({$querySub->toSql()}) as sub"))
        ->select(
            // 'part_id',
            DB::raw('CONCAT("\'",part_number)'),
            'part_name',
            // 'brand_id',
            'brand_name',
            'selling_price',
            // DB::raw('IFNULL(selling_price,part_final_price) AS selling_price'),
            'avg_selling_price',
            // DB::raw('IFNULL(avg_selling_price,part_avg_cost) AS avg_selling_price'),
            // 'qty_on_hand',
            'last_qty_on_hand_per_date',
            DB::raw('(purchase_memo_qty+purchase_order_qty-purchase_ro_qty) AS on_o'),
            'safety_stock',
            'max_stock',
            'unit_name',
            'part_brand',
            // 'part_type_name',
            // 'weight',
            // 'weight_name',
            // DB::raw('CONCAT(weight," ",weight_name)'),
            // 'branch_name',
        )
        ->get();
    }

    public function headings(): array
    {
        $branch = 'All';
        $branch = Mst_branch::where([
            'id' => $this->branch_id,
            'active' => 'Y',
        ])
        ->first();
        if($branch){
            $branch = $branch->name;
        }
        return [
            ['REPORT INVENTORY PER GUDANG per PARTS NO'],
            ['GUDANG: '.$branch],
            [
                "PARTS NO",
                "DESCRIPTION",
                "BRAND",
                "HARGA JUAL",
                "COST RATA2",
                "QTY OH",
                "QTY OO",
                "QTY MIN",
                "QTY MAX",
                "SATUAN",
                "MERK",
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // set text style
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],

            // Styling a specific cell by coordinate.
            // 'B2' => ['font' => ['italic' => true]],

            // Styling an entire column.
            // 'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function columnWidths(): array
    {
        // set column width
        return [
            'A' => 30,
            'B' => 30,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 20,
            'M' => 30,
            'N' => 30,
        ];
    }
}
