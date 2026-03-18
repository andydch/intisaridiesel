<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mst_branch;
use App\Models\Rpt_stock_inventory_acc_per_branch;
use App\Models\Tx_qty_part;
use App\Models\Tx_stock_transfer_part;
use App\Models\Tx_receipt_order;
use App\Models\Tx_purchase_retur;
use App\Models\Tx_delivery_order;
use App\Models\Tx_nota_retur;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_nota_retur_non_tax;

class ReportUID extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenReport:StockInventoryAccurationPerBranch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate data untuk report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // generate data report stock inventory accuration per branch
        // dijalankan setiap bulan di tanggal terakhir
        $br = PHP_EOL;

        $date = now();
        date_add($date, date_interval_create_from_date_string("-1 months"));
        $month = date_format($date, "m");
        $year = date_format($date, "Y");

        $mountRO = 0;
        $mountPR = 0;
        $mountFK = 0;
        $mountNP = 0;
        $mountNR = 0;
        $mountRE = 0;

        $branches = Mst_branch::where('active','=','Y')
        ->orderBy('name','ASC')
        ->get();
        foreach($branches as $branch){
            echo $br.$branch->name.$br;
            $totalActualStockPerBranch = 0;

            // total actual stock - begin
            $q = Tx_qty_part::leftJoin('mst_branches as br','tx_qty_parts.branch_id','=','br.id')
            ->leftJoin('mst_parts as pr','tx_qty_parts.part_id','=','pr.id')
            ->select(
                'br.name as branch_name',
                'tx_qty_parts.qty as qty_per_branch',
                'pr.avg_cost',
            )
            // in transit
            ->addSelect(['in_transit_qty' => Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
                ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
                ->where('tx_stock_transfer_parts.active','=','Y')
                ->where('tx_stock.approved_by','<>',null)
                ->where('tx_stock.received_by','=',null)
                ->where('tx_stock.active','=','Y')
            ])
            ->where('br.id','=',$branch->id)
            ->where('pr.active','=','Y')
            ->get();
            foreach($q as $s){
                // $totalActualStockPerBranch += (round($s->avg_cost)*$s->qty_per_branch);
                $totalActualStockPerBranch += ((round($s->avg_cost)*$s->qty_per_branch)+(round($s->avg_cost)*$s->in_transit_qty));
            }
            echo 'actual stock '.$totalActualStockPerBranch.$br;
            // total actual stock - end

            // purchase in - Receipt Order - begin
            $sumRO = Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(receipt_date)='.$month)
            ->whereRaw('YEAR(receipt_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_before_vat');
            $mountRO = $sumRO;
            echo 'RO '.$sumRO.$br;
            // purchase in - Receipt Order - end

            // purchase in - Purchase Retur - begin
            $sumPR = Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(purchase_retur_date)='.$month)
            ->whereRaw('YEAR(purchase_retur_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_before_vat');
            $mountPR = $sumPR;
            echo 'PR '.$sumPR.$br;
            // purchase in - Purchase Retur - end

            // faktur - begin
            $sumFK = Tx_delivery_order::leftJoin('mst_customers as mst_c','tx_delivery_orders.customer_id','=','mst_c.id')
            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
            ->whereRaw('tx_delivery_orders.delivery_order_no NOT LIKE \'%Draft%\'')
            ->whereRaw('MONTH(tx_delivery_orders.delivery_order_date)='.$month)
            ->whereRaw('YEAR(tx_delivery_orders.delivery_order_date)='.$year)
            ->where([
                'tx_delivery_orders.active'=>'Y',
                'usr_s.branch_id'=>$branch->id,
            ])
            ->sum('tx_delivery_orders.total_before_vat');
            $mountFK = $sumFK;
            echo 'FK '.$sumFK.$br;
            // faktur - end

            // nota penjualan - begin
            $sumNP = Tx_delivery_order_non_tax::leftJoin('mst_customers as mst_c','tx_delivery_order_non_taxes.customer_id','=','mst_c.id')
            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_no NOT LIKE \'%Draft%\'')
            ->whereRaw('MONTH(tx_delivery_order_non_taxes.delivery_order_date)='.$month)
            ->whereRaw('YEAR(tx_delivery_order_non_taxes.delivery_order_date)='.$year)
            ->where([
                'tx_delivery_order_non_taxes.active'=>'Y',
                'usr_s.branch_id'=>$branch->id,
            ])
            ->sum('tx_delivery_order_non_taxes.total_price');
            $mountNP = $sumNP;
            echo 'NP '.$sumNP.$br;
            // nota penjualan - end

            // nota retur - begin
            $sumNR = Tx_nota_retur::leftJoin('mst_customers as mst_c','tx_nota_returs.customer_id','=','mst_c.id')
            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
            ->whereRaw('tx_nota_returs.nota_retur_no NOT LIKE \'%Draft%\'')
            ->whereRaw('MONTH(tx_nota_returs.nota_retur_date)='.$month)
            ->whereRaw('YEAR(tx_nota_returs.nota_retur_date)='.$year)
            ->where([
                'tx_nota_returs.active'=>'Y',
                'usr_s.branch_id'=>$branch->id,
            ])
            ->sum('tx_nota_returs.total_before_vat');
            $mountNR = $sumNR;
            echo 'NR '.$sumNR.$br;
            // nota retur - end

            // nota retur non tax - begin
            $sumRE = Tx_nota_retur_non_tax::leftJoin('mst_customers as mst_c','tx_nota_retur_non_taxes.customer_id','=','mst_c.id')
            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
            ->whereRaw('tx_nota_retur_non_taxes.nota_retur_no NOT LIKE \'%Draft%\'')
            ->whereRaw('MONTH(tx_nota_retur_non_taxes.nota_retur_date)='.$month)
            ->whereRaw('YEAR(tx_nota_retur_non_taxes.nota_retur_date)='.$year)
            ->where([
                'tx_nota_retur_non_taxes.active'=>'Y',
                'usr_s.branch_id'=>$branch->id,
            ])
            ->sum('tx_nota_retur_non_taxes.total_price');
            $mountRE = $sumRE;
            echo 'RE '.$sumRE.$br;
            // nota retur non tax - end

            $q = Rpt_stock_inventory_acc_per_branch::where([
                'branch_id' => $branch->id,
                'rpt_month' => $month,
                'rpt_year' => $year,
                'active' => 'Y',
            ])
            ->first();
            if(!$q){
                // simpan data
                $stockInfo = Rpt_stock_inventory_acc_per_branch::create([
                    'branch_id' => $branch->id,
                    'rpt_month' => $month,
                    'rpt_year' => $year,
                    'purchase_in' => $mountRO-$mountPR,
                    'sales_out' => ($mountFK+$mountNP)-($mountNR+$mountRE),
                    'end_stock' => ($totalActualStockPerBranch-($mountRO-$mountPR))-(($mountFK+$mountNP)-($mountNR+$mountRE)),
                    'actual_stock' => $totalActualStockPerBranch,
                    'active' => 'Y',
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
