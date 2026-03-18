<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Mst_branch;
use App\Models\Rpt_stock_inventory_acc_per_branch;
use App\Models\Tx_delivery_order_non_tax_part;
use App\Models\Tx_delivery_order_part;
use App\Models\Tx_nota_retur_part;
use App\Models\Tx_nota_retur_part_non_tax;
use App\Models\Tx_purchase_retur_part;
use App\Models\Tx_qty_part;
use App\Models\Tx_receipt_order_part;

class ReportUIDX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'GenReport:StockInventoryAccurationPerBranchX';

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
            echo $branch->name.$br;
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
            ->addSelect(['in_transit_qty' => \App\Models\Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
                ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
                ->where('tx_stock_transfer_parts.active','=','Y')
                ->where('tx_stock.approved_by','<>',null)
                ->where('tx_stock.received_by','=',null)
                ->where('tx_stock.active','=','Y')
            ])
            // ->whereRaw('tx_qty_parts.qty>0')
            ->where('br.id','=',$branch->id)
            ->where('pr.active','=','Y')
            ->get();
            foreach($q as $s){
                $totalActualStockPerBranch += ((round($s->avg_cost)*$s->qty_per_branch)+(round($s->avg_cost)*$s->in_transit_qty));
            }
            echo 'actual stock '.$totalActualStockPerBranch.$br;
            // total actual stock - end

            // purchase in - RO - begin
            $qRO = Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
            ->leftJoin('userdetails as usr','tx_ro.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_receipt_order_parts.qty*ceiling(tx_receipt_order_parts.final_cost)) as tot_ro_per_branch')
            ->where('tx_receipt_order_parts.active','=','Y')
            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
            ->where('tx_ro.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_ro.branch_id IS null) OR tx_ro.branch_id='.$branch->id.')')
            ->where('tx_ro.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_ro.receipt_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_ro.receipt_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qRO){
                $mountRO = $qRO->tot_ro_per_branch;
                echo 'RO '.$qRO->tot_ro_per_branch.$br;
            }else{
                $mountRO = 0;
                echo 'RO 0'.$br;
            }
            // purchase in - RO - end

            // purchase in - PR - begin
            $qPR = Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
            ->leftJoin('userdetails as usr','tx_pr.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_purchase_retur_parts.qty_retur*ceiling(tx_purchase_retur_parts.final_cost)) as tot_pr_per_branch')
            ->where('tx_purchase_retur_parts.active','=','Y')
            ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
            ->where('tx_pr.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_pr.branch_id IS null) OR tx_pr.branch_id='.$branch->id.')')
            ->where('tx_pr.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_pr.purchase_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_pr.purchase_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qPR){
                $mountPR = $qPR->tot_pr_per_branch;
                echo 'PR '.$qPR->tot_pr_per_branch.$br;
            }else{
                $mountPR = 0;
                echo 'PR 0'.$br;
            }
            // purchase in - PR - end

            // faktur - begin
            $qFK = Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
            ->leftJoin('userdetails as usr','tx_do.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_delivery_order_parts.qty*ceiling(tx_delivery_order_parts.final_price)) as tot_pr_per_branch')
            ->where('tx_delivery_order_parts.active','=','Y')
            ->where('tx_do.delivery_order_no','NOT LIKE','%Draft%')
            ->where('tx_do.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_do.branch_id IS null) OR tx_do.branch_id='.$branch->id.')')
            ->where('tx_do.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_do.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_do.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qFK){
                $mountFK = $qFK->tot_pr_per_branch;
                echo 'FK '.$qFK->tot_pr_per_branch.$br;
            }else{
                $mountFK = 0;
                echo 'FK 0'.$br;
            }
            // faktur - end

            // nota penjualan - begin
            $qNP = Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes as tx_np','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_np.id')
            ->leftJoin('userdetails as usr','tx_np.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_delivery_order_non_tax_parts.qty*ceiling(tx_delivery_order_non_tax_parts.final_price)) as tot_pr_per_branch')
            ->where('tx_delivery_order_non_tax_parts.active','=','Y')
            ->where('tx_np.delivery_order_no','NOT LIKE','%Draft%')
            ->where('tx_np.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_np.branch_id IS null) OR tx_np.branch_id='.$branch->id.')')
            ->where('tx_np.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_np.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_np.delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qNP){
                $mountNP = $qNP->tot_pr_per_branch;
                echo 'NP '.$qNP->tot_pr_per_branch.$br;
            }else{
                $mountNP = 0;
                echo 'NP 0'.$br;
            }
            // nota penjualan - end

            // nota retur - begin
            $qNR = Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
            ->leftJoin('userdetails as usr','tx_nr.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_nota_retur_parts.qty_retur*ceiling(tx_nota_retur_parts.final_price)) as tot_pr_per_branch')
            ->where('tx_nota_retur_parts.active','=','Y')
            ->where('tx_nr.nota_retur_no','NOT LIKE','%Draft%')
            ->where('tx_nr.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_nr.branch_id IS null) OR tx_nr.branch_id='.$branch->id.')')
            ->where('tx_nr.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_nr.nota_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_nr.nota_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qNR){
                $mountNR = $qNR->tot_pr_per_branch;
                echo 'NR '.$qNR->tot_pr_per_branch.$br;
            }else{
                $mountNR = 0;
                echo 'NR 0'.$br;
            }
            // nota retur - end

            // nota retur non tax - begin
            $qRE = Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
            ->leftJoin('userdetails as usr','tx_nr.created_by','=','usr.user_id')
            ->selectRaw('SUM(tx_nota_retur_part_non_taxes.qty_retur*ceiling(tx_nota_retur_part_non_taxes.final_price)) as tot_pr_per_branch')
            ->where('tx_nota_retur_part_non_taxes.active','=','Y')
            ->where('tx_nr.nota_retur_no','NOT LIKE','%Draft%')
            ->where('tx_nr.active','=','Y')
            // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_nr.branch_id IS null) OR tx_nr.branch_id='.$branch->id.')')
            ->where('tx_nr.branch_id','=',$branch->id)
            ->whereRaw('MONTH(DATE_ADD(tx_nr.nota_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$month)
            ->whereRaw('YEAR(DATE_ADD(tx_nr.nota_retur_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.$year)
            ->first();
            if($qRE){
                $mountRE = $qRE->tot_pr_per_branch;
                echo 'RE '.$qRE->tot_pr_per_branch.$br;
            }else{
                $mountRE = 0;
                echo 'RE 0'.$br;
            }
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
                    'end_stock' => ($totalActualStockPerBranch+($mountRO-$mountPR))-(($mountFK+$mountNP)-($mountNR+$mountRE)),
                    // 'end_stock' => $totalActualStockPerBranch-($mountRO-$mountPR)-(($mountFK+$mountNP)-($mountNR+$mountRE)),
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
