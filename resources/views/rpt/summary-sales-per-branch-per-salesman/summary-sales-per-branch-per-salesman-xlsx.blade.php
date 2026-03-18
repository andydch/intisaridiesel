<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesPerBranchPerSalesman</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 9;
                    $monthNm = '';
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">SUMMARY SALES PER BRANCH PER SALESMAN</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TARGET TAHUN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TARGET BULAN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PROFIT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TARGET %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $lokal_input = $lokal_input;
                        $branch_name = '';

                        $totAllTargetVal = 0;
                        $totPerMonthAllTargetVal = 0;
                        $totAllDppPerBranch = 0;
                        $totAllDppPerBranchVat = 0;
                        $totAllAvgPerBranch = 0;
                        $totAllProfitBranch = 0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $totTargetVal = 0;
                            $totTargetPerMonthVal = 0;
                            $totDppPerBranch = 0;
                            $totDppPerBranchVat = 0;
                            $totAvgPerBranch = 0;
                            $totProfitBranch = 0;

                            $sales = \App\Models\User::leftJoin('userdetails','users.id','=','userdetails.user_id')
                            ->select(
                                'users.id as user_id',
                                'users.name as sales_name',
                            )
                            ->whereIn('users.id', function($q){
                                $q->select('salesman_id')
                                ->from('mst_customers')
                                ->where([
                                    'active'=>'Y',
                                ]);
                            })
                            ->where([
                                'userdetails.branch_id'=>$branch->id,
                            ]);
                        @endphp
                        @foreach ($sales->get() as $s)
                            <tr>
                                <td>{{ ($branch_name!=$branch->name)?$branch->name:'' }}</td>
                                @if ($branch_name!=$branch->name)
                                    @php
                                        $branch_name = $branch->name;
                                    @endphp
                                @endif
                                <td>{{ $s->sales_name }}</td>
                                @php
                                    $totDppPerSalesman = 0;
                                    $totDppPerSalesmanVat = 0;
                                    $totAvgPerSalesman = 0;

                                    $targetVal = 0;
                                    $salesman_target = \App\Models\Mst_salesman_target::leftJoin('mst_salesman_target_details AS s_dtl','mst_salesman_targets.id','=','s_dtl.salesman_target_id')
                                    ->select(
                                        'mst_salesman_targets.sales_target',
                                        's_dtl.sales_target_per_branch',
                                    )
                                    ->where([
                                        'mst_salesman_targets.branch_id' => $branch->id,
                                        'mst_salesman_targets.active' => 'Y',
                                        's_dtl.salesman_id' => $s->user_id,
                                        's_dtl.year_per_branch' => $dt_s[2],
                                        's_dtl.active' => 'Y',
                                    ])
                                    ->first();
                                    if($salesman_target){
                                        $targetVal = $salesman_target->sales_target_per_branch;
                                    }
                                    $totTargetPerMonthVal += ($targetVal/12);
                                    $totTargetVal += $targetVal;
                                @endphp
                                <td style="text-align: right;">{{ number_format($targetVal,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format($targetVal/12,0,'.','') }}</td>
                                @php
                                    // FK - begin
                                    $priceSO_tot_price = \App\Models\Tx_sales_order::leftJoin('mst_customers as mst_c','tx_sales_orders.customer_id','=','mst_c.id')
                                    ->leftJoin('userdetails AS usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                    ->whereIn('tx_sales_orders.id', function($q) {
                                        $q->select('tx_dop.sales_order_id')
                                        ->from('tx_delivery_order_parts as tx_dop')
                                        ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->where([
                                            'tx_dop.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_sales_orders.sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'mst_c.salesman_id'=>$s->user_id,
                                        'tx_sales_orders.need_approval'=>'N',
                                        'tx_sales_orders.active'=>'Y',
                                        'usr_s.branch_id'=>$branch->id,
                                    ])
                                    ->sum('tx_sales_orders.total_before_vat');

                                    $priceSO_tot_price_vat = \App\Models\Tx_sales_order::leftJoin('mst_customers as mst_c','tx_sales_orders.customer_id','=','mst_c.id')
                                    ->leftJoin('userdetails AS usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                    ->whereIn('tx_sales_orders.id', function($q) {
                                        $q->select('tx_dop.sales_order_id')
                                        ->from('tx_delivery_order_parts as tx_dop')
                                        ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->where([
                                            'tx_dop.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_sales_orders.sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'mst_c.salesman_id'=>$s->user_id,
                                        'tx_sales_orders.need_approval'=>'N',
                                        'tx_sales_orders.active'=>'Y',
                                        'usr_s.branch_id'=>$branch->id,
                                    ])
                                    ->sum('tx_sales_orders.total_after_vat');
                                    // FK - end

                                    // NR - begin
                                    $priceNR_tot_price = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_nr.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr_s','m_cust.salesman_id','=','usr_s.user_id')
                                    ->where('tx_nr.nota_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_nota_retur_parts.active'=>'Y',
                                        'usr_s.branch_id'=>$branch->id,
                                        'tx_nr.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                    ])
                                    ->sum('tx_nota_retur_parts.total_price');
                                    // NR - end

                                    // NP - begin
                                    $priceSJ_tot_price = \App\Models\Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
                                    ->leftJoin('mst_customers as mst_c','tx_surat_jalans.customer_id','=','mst_c.id')
                                    ->leftJoin('userdetails AS usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                    ->whereIn('tx_surat_jalans.id', function($q) {
                                        $q->select('tx_dop.sales_order_id')
                                        ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                        ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->where([
                                            'tx_dop.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_surat_jalans.surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_surat_jalans.surat_jalan_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_surat_jalans.surat_jalan_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'mst_c.salesman_id'=>$s->user_id,
                                        'tx_surat_jalans.need_approval'=>'N',
                                        'tx_surat_jalans.active'=>'Y',
                                        'usr_s.branch_id'=>$branch->id,
                                    ])
                                    ->orderBy('tx_surat_jalans.surat_jalan_date','ASC')
                                    ->sum('tx_surat_jalans.total');
                                    // NP - end

                                    // RE - begin
                                    $priceRE_tot_price = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_nr.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr_s','m_cust.salesman_id','=','usr_s.user_id')
                                    ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                        'tx_nr.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                        'usr_s.branch_id'=>$branch->id,
                                    ])
                                    ->sum('tx_nota_retur_part_non_taxes.total_price');
                                    // RE - end

                                    switch (strtoupper($lokal_input)) {
                                        case 'A':
                                            $totDppPerSalesman = ($priceSO_tot_price+$priceSJ_tot_price)-($priceNR_tot_price+$priceRE_tot_price);
                                            $totDppPerSalesmanVat = ($priceSO_tot_price_vat+$priceSJ_tot_price)-($priceNR_tot_price+$priceRE_tot_price);
                                            break;
                                        case 'P':
                                            $totDppPerSalesman = $priceSO_tot_price-$priceNR_tot_price;
                                            $totDppPerSalesmanVat = $priceSO_tot_price_vat-$priceNR_tot_price;
                                            break;
                                        case 'N':
                                            $totDppPerSalesman = $priceSJ_tot_price-$priceRE_tot_price;
                                            $totDppPerSalesmanVat = 0;
                                            break;
                                        default:
                                            $totDppPerSalesman = $priceSO_tot_price-$priceNR_tot_price;
                                            $totDppPerSalesmanVat = $priceSO_tot_price_vat-$priceNR_tot_price;
                                            break;
                                    }
                                    $totDppPerBranch += $totDppPerSalesman;
                                    $totDppPerBranchVat += $totDppPerSalesmanVat;
                                @endphp
                                <td style="text-align: right;">{{ number_format($totDppPerSalesmanVat,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format($totDppPerSalesman,0,'.','') }}</td>
                                @php
                                    $avgSOtot = 0;
                                    $avgNRtot = 0;
                                    $avgSJtot = 0;
                                    $avgREtot = 0;
                                    $avgTmp = 0;

                                    // faktur - start
                                    $qDOrderPart = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
                                    ->leftJoin('tx_sales_orders as tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                    ->leftJoin('tx_sales_order_parts as tx_sop','tx_delivery_order_parts.sales_order_part_id','=','tx_sop.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_so.customer_id','=','m_cust.id')
                                    ->select(
                                        'tx_delivery_order_parts.part_id as partid',
                                        'tx_delivery_order_parts.qty as tx_dop_qty',
                                        'tx_delivery_order_parts.updated_at as updatedat',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->whereRaw('tx_so.sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_so.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_so.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_delivery_order_parts.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                        'tx_so.need_approval'=>'N',
                                        'tx_so.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                    ])
                                    ->get();
                                    foreach ($qDOrderPart as $q) {
                                        $avgSOtot += ($q->last_avg_cost*$q->tx_dop_qty);
                                    }
                                    $avgTmp = $avgSOtot;
                                    // faktur - end

                                    // nota retur - start
                                    $qNReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_nr.customer_id','=','m_cust.id')
                                    ->select(
                                        'tx_nota_retur_parts.part_id as partid',
                                        'tx_nota_retur_parts.qty_retur',
                                        'tx_nota_retur_parts.updated_at as updatedat',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'tx_nota_retur_parts.active'=>'Y',
                                        'tx_sop.active'=>'Y',
                                        'tx_nr.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                    ])
                                    ->get();
                                    foreach ($qNReturPart as $q) {
                                        $avgNRtot += ($q->last_avg_cost*$q->qty_retur);
                                    }
                                    // nota retur - end

                                    // nota penjualan - start
                                    $qDOrderPart = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes as tx_do','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_do.id')
                                    ->leftJoin('tx_surat_jalans as tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                    ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_delivery_order_non_tax_parts.sales_order_part_id','=','tx_sjp.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_sj.customer_id','=','m_cust.id')
                                    ->select(
                                        'tx_delivery_order_non_tax_parts.part_id as partid',
                                        'tx_delivery_order_non_tax_parts.qty as tx_dop_qty',
                                        'tx_delivery_order_non_tax_parts.updated_at as updatedat',
                                        'tx_sjp.last_avg_cost',
                                    )
                                    ->whereRaw('tx_sj.surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_sj.surat_jalan_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_sj.surat_jalan_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_delivery_order_non_tax_parts.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                        'tx_sj.need_approval'=>'N',
                                        'tx_sj.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                    ])
                                    ->get();
                                    foreach ($qDOrderPart as $q) {
                                        $avgSJtot += ($q->last_avg_cost*$q->tx_dop_qty);
                                    }
                                    // nota penjualan - end

                                    // retur - start
                                    $qNReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                    ->leftJoin('mst_customers AS m_cust','tx_nr.customer_id','=','m_cust.id')
                                    ->select(
                                        'tx_nota_retur_part_non_taxes.part_id as partid',
                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                        'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                        'tx_sjp.last_avg_cost',
                                    )
                                    ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                        'tx_sjp.active'=>'Y',
                                        'tx_nr.active'=>'Y',
                                        'm_cust.salesman_id'=>$s->user_id,
                                    ])
                                    ->get();
                                    foreach ($qNReturPart as $q) {
                                        $avgREtot += ($q->last_avg_cost*$q->qty_retur);
                                    }
                                    // retur - end

                                    switch (strtoupper($lokal_input)) {
                                        case 'A':
                                            $totAvgPerSalesman = ($avgSOtot+$avgSJtot)-($avgNRtot+$avgREtot);
                                            break;

                                        case 'P':
                                            $totAvgPerSalesman = $avgSOtot-$avgNRtot;
                                            break;

                                        case 'N':
                                            $totAvgPerSalesman = $avgSJtot-$avgREtot;
                                            break;

                                        default:
                                            $totAvgPerSalesman = $avgSOtot-$avgNRtot;
                                            break;
                                    }

                                    $totAvgPerBranch += $totAvgPerSalesman;
                                    $totProfitBranch += ($totDppPerSalesman-$totAvgPerSalesman);
                                @endphp
                                <td style="text-align: right;">
                                    {{ number_format($totAvgPerSalesman,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format($totDppPerSalesman-$totAvgPerSalesman,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format(($totDppPerSalesman>0)?(($totDppPerSalesman-$totAvgPerSalesman)/$totDppPerSalesman)*100:0,0,'.','') }}%
                                </td>
                                <td style="text-align: right;border-left:1px solid black;border-right:1px solid black;">
                                    {{ number_format(($targetVal>0)?($totDppPerSalesman/($targetVal/12))*100:0,0,'.','') }}%
                                </td>
                            </tr>
                        @endforeach
                        @if ($sales->count()>0)
                            <tr>
                                <td style="border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">&nbsp;</td>
                                <td style="font-weight: bold;border-bottom:1px solid black;border-top:1px solid black;">SUB TOTAL</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totTargetVal,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totTargetPerMonthVal,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totDppPerBranchVat,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totDppPerBranch,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totAvgPerBranch,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totProfitBranch,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format(($totDppPerBranch>0)?($totProfitBranch/$totDppPerBranch)*100:0,0,'.','') }}%</td>
                                <td style="text-align: right;font-weight:bold;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">
                                    {{ number_format(($totTargetVal>0)?($totDppPerBranch/$totTargetPerMonthVal)*100:0,0,'.','') }}%
                                </td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                            @php
                                $totPerMonthAllTargetVal += $totTargetPerMonthVal;
                                $totAllTargetVal += $totTargetVal;
                                $totAllDppPerBranchVat += $totDppPerBranchVat;
                                $totAllDppPerBranch += $totDppPerBranch;
                                $totAllAvgPerBranch += $totAvgPerBranch;
                                $totAllProfitBranch += $totProfitBranch;
                            @endphp
                        @endif
                    @endforeach
                    <tr>
                        <td style="border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">&nbsp;</td>
                        <td style="font-weight: bold;border-bottom:1px solid black;border-top:1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totAllTargetVal,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totPerMonthAllTargetVal,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format(0,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totAllDppPerBranch,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totAllAvgPerBranch,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format($totAllProfitBranch,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;border-top:1px solid black;">{{ number_format(($totAllDppPerBranch>0)?($totAllProfitBranch/$totAllDppPerBranch)*100:0,0,'.','') }}%</td>
                        <td style="text-align: right;font-weight:bold;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">
                            {{ number_format(($totAllTargetVal>0)?($totAllDppPerBranch/$totPerMonthAllTargetVal)*100:0,0,'.','') }}%
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
