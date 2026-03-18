<?php

namespace App\Http\Controllers\dbg;

use DateTime;
// use App\Models\Log_tx_qty_part;
use App\Models\Mst_branch;
use App\Models\Tx_qty_part;
use Illuminate\Http\Request;
use App\Models\Tx_nota_retur;
use App\Models\V_tx_qty_part;
use App\Models\Tx_receipt_order;
use App\Models\Tx_delivery_order;
use App\Models\Tx_purchase_retur;
// use App\Models\Tx_receipt_order_part;
use App\Http\Controllers\Controller;
use App\Models\Mst_part;
use App\Models\Tx_nota_retur_non_tax;
use App\Models\Tx_delivery_order_non_tax;
use App\Models\Tx_part_tmp;

class UpdateQtyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        ini_set('memory_limit', '64M');
        ini_set('max_execution_time', 1800);

        $memory_start = memory_get_usage();
        $date_start = new DateTime(date('Y-m-d H:i:s'));

        // $br = $br;
        $br = '<br/>';

        // // $date = now();
        // $date=date_create("2024-01-01");
        // // echo date_format($date, "Y-m-d H:i:s").$br;
        // echo date_format($date, "Y-m-d").$br;
        // date_add($date, date_interval_create_from_date_string("-1 months"));
        // echo date_format($date, "Y-m-d").$br;
        // $month = date_format($date, "m");
        // $year = date_format($date, "Y");

        $month = $request->month;
        $year = $request->year;
        echo $month.$br;
        echo $year.$br;

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
                'br.id as branch_id',
                'br.name as branch_name',
                'tx_qty_parts.qty as qty_per_branch',
                'pr.id as part_id',
                'pr.avg_cost',
            )
            // in transit
            // ->addSelect(['in_transit_qty' => \App\Models\Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
            //     ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
            //     ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
            //     ->whereColumn('tx_stock.branch_to_id','tx_qty_parts.branch_id')
            //     ->where('tx_stock_transfer_parts.active','=','Y')
            //     ->where('tx_stock.approved_by','<>',null)
            //     ->where('tx_stock.received_by','=',null)
            //     ->where('tx_stock.active','=','Y')
            // ])
            // ->whereRaw('tx_qty_parts.qty>0')
            ->where('br.id','=',$branch->id)
            ->where('pr.active','=','Y')
            ->get();
            foreach($q as $s){
                $lastQtyNum = 0;
                $lastQty = V_tx_qty_part::where([
                    'part_id'=>$s->part_id,
                    'branch_id'=>$s->branch_id,
                ])
                ->whereRaw('MONTH(updated_at)='.$month)
                ->whereRaw('YEAR(updated_at)='.$year)
                ->orderBy('updated_at','DESC')
                ->first();
                if ($lastQty){$lastQtyNum = $lastQty->qty;}
                $totalActualStockPerBranch += (round($s->avg_cost)*$lastQtyNum);
                // $totalActualStockPerBranch += ((round($s->avg_cost)*$lastQtyNum)+(round($s->avg_cost)*$s->in_transit_qty));
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
            $sumFK = Tx_delivery_order::where('delivery_order_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(delivery_order_date)='.$month)
            ->whereRaw('YEAR(delivery_order_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_before_vat');
            $mountFK = $sumFK;
            echo 'FK '.$sumFK.$br;
            // faktur - end

            // nota penjualan - begin
            $sumNP = Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(delivery_order_date)='.$month)
            ->whereRaw('YEAR(delivery_order_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_price');
            $mountNP = $sumNP;
            echo 'NP '.$sumNP.$br;
            // nota penjualan - end

            // nota retur - begin
            $sumNR = Tx_nota_retur::where('nota_retur_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(nota_retur_date)='.$month)
            ->whereRaw('YEAR(nota_retur_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_before_vat');
            $mountNR = $sumNR;
            echo 'NR '.$sumNR.$br;
            // nota retur - end

            // nota retur non tax - begin
            $sumRE = Tx_nota_retur_non_tax::where('nota_retur_no','NOT LIKE','%Draft%')
            ->whereRaw('MONTH(nota_retur_date)='.$month)
            ->whereRaw('YEAR(nota_retur_date)='.$year)
            ->where([
                'branch_id'=>$branch->id,
                'active'=>'Y',
            ])
            ->sum('total_price');
            $mountRE = $sumRE;
            echo 'RE '.$sumRE.$br;
            // nota retur non tax - end

            echo 'purchase_in: '.($mountRO-$mountPR).$br;
            echo 'sales_out: '.(($mountFK+$mountNP)-($mountNR+$mountRE)).$br;
            echo 'end_stock: '.(($totalActualStockPerBranch-($mountRO-$mountPR))-(($mountFK+$mountNP)-($mountNR+$mountRE))).$br;
            echo 'actual_stock: '.($totalActualStockPerBranch).$br.$br;
        }

        $memory_end = memory_get_usage();
        $date_end = new DateTime(date('Y-m-d H:i:s'));
        $interval_main = $date_end->diff($date_start);
        echo 'Penggunaan RAM yang dibutuhkan:'.$br.'RAM awal '.number_format((($memory_start/1024)/1024),2,".",",").' MB'.$br;
        echo 'RAM akhir '.number_format((($memory_end/1024)/1024),2,".",",").' MB'.$br;
        echo 'Total waktu yang dibutuhkan: '.$interval_main->format('%hjam %imenit %sdetik').$br.$br;
    }

    public function index_upd(){
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 1800);

        // $br = $br;
        $br = '<br/>';

        $memory_start = memory_get_usage();
        $date_start = new DateTime(date('Y-m-d H:i:s'));

        $month = 12;
        $year = 2023;

        $branches = Mst_branch::where([
            'id'=>1,
            'active'=>'Y',
        ])
        ->orderBy('name','ASC')
        ->get();
        foreach($branches as $branch){
            echo $br.$branch->name.$br.$br;

            $parts = Mst_part::where([
                'active'=>'Y',
            ])
            // ->limit(10000)
            ->get();
            foreach($parts as $p){

                $vQty = V_tx_qty_part::where([
                    'part_id'=>$p->id,
                    'branch_id'=>$branch->id,
                ])
                ->whereRaw('MONTH(updated_at)='.$month)
                ->whereRaw('YEAR(updated_at)='.$year)
                ->orderBy('updated_at','DESC')
                ->first();
                if ($vQty){
                    // echo $p->part_number.'<br/>';
                    // echo $vQty->qty.'<br/>';
                    // echo $p->avg_cost.'<br/><br/>';

                    $tmp = Tx_part_tmp::where([
                        'part_id'=>$p->id,
                        'branch_id'=>$branch->id,
                    ])
                    ->first();
                    if(!$tmp){
                        $ins = Tx_part_tmp::create([
                            'part_id'=>$p->id,
                            'branch_id'=>$branch->id,
                            'qty'=>$vQty->qty,
                            'avg_cost'=>$p->avg_cost,
                        ]);
                    }
                }

            }
        }

        $memory_end = memory_get_usage();
        $date_end = new DateTime(date('Y-m-d H:i:s'));
        $interval_main = $date_end->diff($date_start);
        echo 'Penggunaan RAM yang dibutuhkan:'.$br.'RAM awal '.number_format((($memory_start/1024)/1024),2,".",",").' MB'.$br;
        echo 'RAM akhir '.number_format((($memory_end/1024)/1024),2,".",",").' MB'.$br;
        echo 'Total waktu yang dibutuhkan: '.$interval_main->format('%hjam %imenit %sdetik').$br.$br;
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
