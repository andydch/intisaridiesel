<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Master Inventory</title>
    </head>
    <body>
        <div class="table-responsive">
            <table id="master-inventory" style="width:1024px;">
                <thead>
                    <tr>
                        <th colspan="13">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="13" style="font-weight: bold;font-size: 16px;text-align:center;">LAPORAN MASTER INVENTORY</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id', $branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();

                        $allRpts = \App\Models\Tx_qty_part::leftJoin('mst_parts as pr', function($join) {
                            $join->on('tx_qty_parts.part_id','=','pr.id')
                            ->where('pr.active', 'Y');
                        })
                        ->leftJoin('mst_branches as br', function($join) {
                            $join->on('tx_qty_parts.branch_id','=','br.id')
                            ->where('br.active', 'Y');
                        })
                        ->leftJoin('mst_globals as bd', function($join) {
                            $join->on('pr.brand_id','=','bd.id')
                            ->where('bd.active', 'Y')
                            ->where('bd.data_cat', 'brand');
                        })
                        ->leftJoin('mst_globals as pr_type', function($join) {
                            $join->on('pr.part_type_id','=','pr_type.id')
                            ->where('pr_type.active', 'Y')
                            ->where('pr_type.data_cat', 'part_type');
                        })
                        ->select(
                            'br.id as branch_id',
                            'br.name as branch_name',
                            'bd.title_ind as brand_name',
                            'bd.id as bd_id',
                            'pr.id as mpart_id',
                            'pr.part_number',
                            'pr.part_name',
                            'pr_type.title_ind as part_type_name',
                            DB::raw('IFNULL(pr.final_price, 0) as last_final_price'),
                            DB::raw('IFNULL(pr.avg_cost, 0) as avg_cost'),
                            'tx_qty_parts.qty as qty_per_branch',
                            'tx_qty_parts.part_id as qty_part_id',
                            'tx_qty_parts.branch_id',
                            'tx_qty_parts.id as qty_id',
                        )
                        ->addSelect(['so_qty' => DB::table('tx_sales_order_parts AS txsop')
                            ->leftJoin('tx_sales_orders AS txso', function($join) {
                                $join->on('txsop.order_id', '=', 'txso.id')
                                ->where('txso.active', 'Y')
                                ->where('txso.is_draft', 'N')
                                ->where('txso.need_approval', 'N');
                            })
                            ->selectRaw('IFNULL(SUM(txsop.qty),0)')
                            ->where('txsop.active', 'Y')
                            ->where('txsop.part_id', 'tx_qty_parts.part_id')
                            ->where('txso.branch_id', 'tx_qty_parts.branch_id')
                            ->whereNotExists(function ($q1) {
                                $q1->selectRaw(1)
                                ->from('tx_delivery_order_parts as tx_do_parts')
                                ->leftJoin('tx_delivery_orders as tx_do', function($join) {
                                    $join->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                                    ->where('tx_do.is_draft', 'N')
                                    ->where('tx_do.active', 'Y');
                                })
                                ->whereColumn('tx_do_parts.sales_order_id', 'txso.id')
                                ->where('tx_do_parts.active', 'Y');
                            })])
                        ->addSelect(['sj_qty' => DB::table('tx_surat_jalan_parts AS txsjp')
                            ->leftJoin('tx_surat_jalans AS txsj', function($join) {
                                $join->on('txsjp.surat_jalan_id', '=', 'txsj.id')
                                ->where('txsj.active', 'Y')
                                ->where('txsj.need_approval', 'N')
                                ->where('txsj.is_draft', 'N');
                            })
                            ->selectRaw('IFNULL(SUM(txsjp.qty),0)')
                            ->where('txsjp.part_id', 'tx_qty_parts.part_id')
                            ->where('txsjp.active', 'Y')
                            ->where('txsj.branch_id', 'tx_qty_parts.branch_id')
                            ->whereNotExists(function ($q1) {
                                $q1->selectRaw(1)
                                ->from('tx_delivery_order_non_tax_parts as tx_do_parts')
                                ->leftJoin('tx_delivery_order_non_taxes as tx_do', function($join) {
                                    $join->on('tx_do_parts.delivery_order_id', '=', 'tx_do.id')
                                    ->where('tx_do.is_draft', 'N')
                                    ->where('tx_do.active', 'Y');
                                })
                                ->whereColumn('tx_do_parts.sales_order_id', 'txsj.id')
                                ->where('tx_do_parts.active', 'Y');
                            })])
                        ->addSelect(['pmemo_qty' => DB::table('tx_purchase_memo_parts AS tx_mop')
                            ->leftJoin('tx_purchase_memos as tx_mo', function($join) {
                                $join->on('tx_mop.memo_id', '=', 'tx_mo.id')
                                ->where('tx_mo.is_draft', 'N')
                                ->where('tx_mo.active', 'Y');
                            })
                            ->where('tx_mop.part_id', 'tx_qty_parts.part_id')
                            ->where('tx_mop.active', 'Y')
                            ->where('tx_mo.branch_id', 'tx_qty_parts.branch_id')
                            ->selectRaw('IFNULL(SUM(tx_mop.qty),0)')])
                        ->addSelect(['porder_qty' => DB::table('tx_purchase_order_parts AS tx_pop')
                            ->leftJoin('tx_purchase_orders as tx_po', function($join) {
                                $join->on('tx_pop.order_id', '=', 'tx_po.id')
                                ->where('tx_po.is_draft', 'N')
                                ->where('tx_po.active', 'Y')
                                ->whereNotNull('tx_po.approved_by');
                            })
                            ->selectRaw('IFNULL(SUM(tx_pop.qty),0)')
                            ->where('tx_pop.part_id', 'tx_qty_parts.part_id')
                            ->where('tx_pop.active', 'Y')
                            ->where('tx_po.branch_id', 'tx_qty_parts.branch_id')])
                        ->addSelect(['receiptorder_qty' => DB::table('tx_receipt_order_parts AS tx_ro_parts')
                            ->leftJoin('tx_receipt_orders as tx_ro', function($join) {
                                $join->on('tx_ro_parts.receipt_order_id', '=', 'tx_ro.id')
                                ->where('tx_ro.is_draft', 'N')
                                ->where('tx_ro.active', 'Y');
                            })
                            ->where('tx_ro_parts.part_id', 'tx_qty_parts.part_id')
                            ->where('tx_ro_parts.active', 'Y')
                            ->where('tx_ro.branch_id', 'tx_qty_parts.branch_id')
                            ->selectRaw('IFNULL(SUM(tx_ro_parts.qty),0)')])
                        ->addSelect(['in_transit_qty' => DB::table('tx_stock_transfer_parts AS tx_stockp')
                            ->leftJoin('tx_stock_transfers as tx_stock', function($join) {
                                $join->on('tx_stockp.stock_transfer_id', '=', 'tx_stock.id')
                                ->where('tx_stock.approved_by', '!=', null)
                                ->where('tx_stock.received_by', null)
                                ->where('tx_stock.active', 'Y');
                            })
                            ->where('tx_stockp.part_id', 'tx_qty_parts.part_id')
                            ->where('tx_stockp.active', 'Y')
                            ->where('tx_stock.branch_to_id', 'tx_qty_parts.branch_id')
                            ->selectRaw('IFNULL(SUM(tx_stockp.qty),0)')])
                        ->addSelect(['brand_type_name' => DB::table('mst_part_brand_types AS mpbt')
                            ->leftJoin('mst_brand_types AS mbt', function($join) {
                                $join->on('mpbt.brand_type_id', '=', 'mbt.id')
                                ->where('mbt.active', 'Y');
                            })
                            ->whereColumn('mpbt.part_id', 'tx_qty_parts.part_id')
                            ->where('mpbt.active', 'Y')
                            ->selectRaw('GROUP_CONCAT(mbt.brand_type SEPARATOR ", ")')])
                        ->when($oh_is_zero!='on', function($q) {
                            $q->whereRaw('tx_qty_parts.qty>0');
                        })
                        // ->where('tx_qty_parts.branch_id', $branch->id)
                        ->when($brand_id!=0 && is_numeric($brand_id), function($q) use($brand_id) {
                            $q->where('pr.brand_id', $brand_id);
                        })
                        ->orderBy('br.name','ASC')
                        ->orderBy('bd.title_ind','ASC')
                        ->orderBy('pr.part_number','ASC')
                        ->get()
                        ->groupBy('branch_id');

                        $grandTotal = 0;
                        $grandFinalPriceTotal = 0;
                        $brand_id_tmp = 0;
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            // Ambil data yang sudah ditarik berdasarkan branch_id
                            // Jika tidak ada data di cabang ini, berikan collection kosong
                            $rpts = $allRpts->get($branch->id, collect());

                            // Jika tidak ada transaksi di cabang ini, Anda bisa skip agar baris Excel tidak kosong
                            if($rpts->isEmpty()) continue;

                            $totalPerBranch = 0;
                            $totalFinalPricePerBranch = 0;
                            $totalPerBrand = 0;
                            $totalFinalPricePerBrand = 0;
                            $totalPerBrandTmp = 0;
                            $totalFinalPricePerBrandTmp = 0;
                            $displayTotalPerBrand = false;
                        @endphp
                        <tr>
                            <th style="font-weight: bold;">{{ strtoupper($branch->name) }}</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>{{ date_format(now(), 'd-M-Y') }}</th>
                        </tr>
                        <tr>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">PARTS NO</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">PARTS NAME</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">PARTS TYPE</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">BRAND</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">BRAND TYPE</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">COST AVG ({{ $qCurrency->string_val }})</th>
                            <th colspan="4" style="text-align: center;background-color: #92d050;border:1px solid black;">QTY</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">FINAL PRICE ({{ $qCurrency->string_val }})</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                            <th rowspan="2" style="text-align: center;background-color: #eaf1dd;border:1px solid black;">TOTAL FINAL PRICE ({{ $qCurrency->string_val }})</th>
                        </tr>
                        <tr>
                            <th style="text-align: center;background-color: #eaf1dd;border:1px solid black;">OH</th>
                            <th style="text-align: center;background-color: #eaf1dd;border:1px solid black;">SO</th>
                            <th style="text-align: center;background-color: #eaf1dd;border:1px solid black;">OO</th>
                            <th style="text-align: center;background-color: #eaf1dd;border:1px solid black;">IT</th>
                        </tr>
                        @foreach ($rpts as $rpt)
                            @if ($brand_id_tmp!=$rpt->bd_id)
                                @php
                                    $brand_id_tmp = $rpt->bd_id;
                                    $totalPerBrandTmp = $totalPerBrand;
                                    $totalFinalPricePerBrandTmp = $totalFinalPricePerBrand;
                                    $displayTotalPerBrand = true;

                                    $totalPerBrand = 0;
                                    $totalFinalPricePerBrand = 0;
                                @endphp
                            @endif
                            @if ($displayTotalPerBrand && ($totalPerBrandTmp>0 || $totalFinalPricePerBrandTmp>0))
                                <tr>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($totalPerBrandTmp,0,'.','') }}</th>
                                    <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($totalFinalPricePerBrandTmp,0,'.','') }}</th>
                                </tr>
                                @php
                                    $displayTotalPerBrand = false;
                                @endphp
                            @endif
                            @php
                                $totSO = $rpt->so_qty + $rpt->sj_qty;
                                $totOO = $rpt->pmemo_qty + $rpt->porder_qty - $rpt->receiptorder_qty;
                            @endphp
                            @if ($rpt->part_number!='')
                                {{-- hindari part number kosong --}}
                                <tr>
                                    <td style="border:1px solid black;">
                                        @php
                                            $partNumber = $rpt->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ $partNumber }}
                                    </td>
                                    <td style="border:1px solid black;">{{ $rpt->part_name }}</td>
                                    <td style="border:1px solid black;">{{ $rpt->part_type_name }}</td>
                                    <td style="border:1px solid black;">{{ $rpt->brand_name }}</td>
                                    <td style="border:1px solid black;">{{ $rpt->brand_type_name }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($rpt->avg_cost,0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $rpt->qty_per_branch }}</td>
                                    <td style="text-align: right;border:1px solid black;">
                                        {{ ($rpt->sales_order_qty+$rpt->surat_jalan_qty) }}
                                    </td>
                                    <td style="text-align: right;border:1px solid black;">{{ $totOO }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ $rpt->in_transit_qty }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($rpt->last_final_price,0,'.','') }}</td>
                                    <td style="text-align: right;border:1px solid black;">
                                        {{ number_format(($rpt->qty_per_branch*$rpt->avg_cost)+($rpt->in_transit_qty*$rpt->avg_cost),0,'.','') }}
                                    </td>
                                    <td style="text-align: right;border:1px solid black;">
                                        {{ number_format($rpt->qty_per_branch*$rpt->last_final_price,0,'.','') }}
                                    </td>
                                </tr>
                            @endif
                            @php
                                $totalPerBrand += ($rpt->qty_per_branch*$rpt->avg_cost)+($rpt->in_transit_qty*$rpt->avg_cost);
                                $totalFinalPricePerBrand += ($rpt->qty_per_branch*$rpt->last_final_price);

                                $totalPerBranch += ($rpt->qty_per_branch*$rpt->avg_cost)+($rpt->in_transit_qty*$rpt->avg_cost);
                                $totalFinalPricePerBranch += ($rpt->qty_per_branch*$rpt->last_final_price);
                            @endphp
                        @endforeach
                        <tr>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <th style="font-weight: bold;border:1px solid black;">TOTAL</th>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($totalPerBranch,0,'.','') }}</th>
                            <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($totalFinalPricePerBranch,0,'.','') }}</th>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        <tr>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                            <th>&nbsp;</th>
                        </tr>
                        @php
                            $grandTotal += $totalPerBranch;
                            $grandFinalPriceTotal += $totalFinalPricePerBranch;
                        @endphp
                    @endforeach
                    <tr>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <th style="font-weight: bold;border:1px solid black;">GRAND TOTAL</th>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandTotal,0,'.','') }}</th>
                        <th style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandFinalPriceTotal,0,'.','') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
