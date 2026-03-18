<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesPerBranchPerBrand</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 8;
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
                        <th colspan="{{ $totCols }}">SUMMARY SALES PER BRANCH PER BRAND</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRAND</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL HARGA ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL RETUR ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL NETTO ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PROFIT</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $lokal_input = $lokal_input;

                        $branch_name='';
                        $allTotalHarga=0;
                        $allTotalDPP=0;
                        $allTotalRetur=0;
                        $allTotalNetto=0;
                        $allTotalCostAVG=0;
                        $allTotalProfit=0;
                        $allTotalGP=0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $subTotalHarga=0;
                            $subTotalDPP=0;
                            $subTotalRetur=0;
                            $subTotalNetto=0;
                            $subTotalCostAVG=0;
                            $subTotalProfit=0;
                            $subTotalGP=0;
                            $brands = \App\Models\Mst_global::where([
                                'data_cat' => 'brand',
                                'active' => 'Y',
                            ])
                            ->orderBy('title_ind','ASC')
                            ->get();
                        @endphp
                        @foreach ($brands as $brand)
                            <tr>
                                <td style="border-left: 1px solid black;">{{ ($branch_name!=$branch->name)?$branch->name:'' }}</td>
                                <td>{{ $brand->title_ind }}</td>
                                @php
                                    $dppSUM=0;
                                    $dppVatSUM=0;
                                    $dppSUMNonTax=0;
                                @endphp
                                @if ($lokal_input=='A' || $lokal_input=='P' || $lokal_input=='x')
                                    @php
                                        $dppSUM = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
                                        ->leftJoin('mst_parts as msp','tx_delivery_order_parts.part_id','=','msp.id')
                                        ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('tx_do.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_do.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'tx_delivery_order_parts.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                            'msp.brand_id'=>$brand->id,
                                            'usr_s.branch_id'=>$branch->id,
                                        ])
                                        ->sum('tx_delivery_order_parts.total_price');

                                        $qVAT = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
                                        ->leftJoin('mst_parts as msp','tx_delivery_order_parts.part_id','=','msp.id')
                                        ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                        ->selectRaw('SUM(tx_delivery_order_parts.total_price*tx_do.vat_val/100) as total_vat')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('tx_do.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_do.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'tx_delivery_order_parts.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                            'msp.brand_id'=>$brand->id,
                                            'usr_s.branch_id'=>$branch->id,
                                        ])
                                        ->first();
                                        if ($qVAT){
                                            $dppVatSUM=$qVAT->total_vat;
                                        }
                                    @endphp
                                @endif
                                @if ($lokal_input=='A' || $lokal_input=='N')
                                    @php
                                        $dppSUMNonTax = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes as tx_do','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_do.id')
                                        ->leftJoin('mst_parts as msp','tx_delivery_order_non_tax_parts.part_id','=','msp.id')
                                        ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('tx_do.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_do.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'tx_delivery_order_non_tax_parts.active'=>'Y',
                                            'tx_do.active'=>'Y',
                                            'msp.brand_id'=>$brand->id,
                                            'usr_s.branch_id'=>$branch->id,
                                        ])
                                        ->sum('tx_delivery_order_non_tax_parts.total_price');
                                    @endphp
                                @endif
                                <td style="text-align: right;">
                                    {{ number_format(($dppSUM+$dppVatSUM+$dppSUMNonTax),0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format(($dppSUM+$dppSUMNonTax),0,'.','') }}
                                    @php
                                        $subTotalHarga+=$dppSUM+$dppVatSUM+$dppSUMNonTax;
                                        $subTotalDPP+=$dppSUM+$dppSUMNonTax;
                                    @endphp
                                </td>
                                <td style="color:red;text-align: right;">
                                    @php
                                        $returSUM=0;
                                        $returSUMNonTax=0;
                                    @endphp
                                    @if ($lokal_input=='A' || $lokal_input=='P' || $lokal_input=='x')
                                        @php
                                            $returSUM = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('mst_parts as msp','tx_nota_retur_parts.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_nr.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                            ->where([
                                                'tx_nota_retur_parts.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->sum('tx_nota_retur_parts.total_price');
                                        @endphp
                                    @endif
                                    @if ($lokal_input=='A' || $lokal_input=='N')
                                        @php
                                            $returSUMNonTax = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('mst_parts as msp','tx_nota_retur_part_non_taxes.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_nr.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                            ->where([
                                                'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->sum('tx_nota_retur_part_non_taxes.total_price');
                                        @endphp
                                    @endif
                                    {{ number_format(($returSUM+$returSUMNonTax),0,'.','') }}
                                    @php
                                        $subTotalRetur+=($returSUM+$returSUMNonTax);
                                    @endphp
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format(($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax),0,'.','') }}
                                    @php
                                        $subTotalNetto+=(($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax));
                                    @endphp
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $totAvgFK=0;
                                        $totAvgRetur=0;
                                        $totAvgFKNonTax=0;
                                        $totAvgReturNonTax=0;
                                    @endphp
                                    @if ($lokal_input=='A' || $lokal_input=='P' || $lokal_input=='x')
                                        @php
                                            $qFKpart = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as tx_do','tx_delivery_order_parts.delivery_order_id','=','tx_do.id')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_delivery_order_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('mst_parts as msp','tx_delivery_order_parts.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->select(
                                                'tx_delivery_order_parts.part_id',
                                                'tx_delivery_order_parts.qty',
                                                'tx_delivery_order_parts.updated_at as updatedat',
                                                'tx_sop.last_avg_cost',
                                            )
                                            ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_do.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_do.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'tx_delivery_order_parts.active'=>'Y',
                                                'tx_do.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qFKpart as $pFK)
                                            @php
                                                $totAvgFK+=($pFK->last_avg_cost*$pFK->qty);
                                            @endphp
                                        @endforeach
                                        @php
                                            $qReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('mst_parts as msp','tx_nota_retur_parts.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_nr.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->select(
                                                'tx_nota_retur_parts.part_id',
                                                'tx_nota_retur_parts.qty_retur',
                                                'tx_nota_retur_parts.updated_at as updatedat',
                                                'tx_sop.last_avg_cost',
                                            )
                                            ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                            ->where([
                                                'tx_nota_retur_parts.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qReturPart as $pNR)
                                            @php
                                                $totAvgRetur+=($pNR->last_avg_cost*$pNR->qty_retur);
                                            @endphp
                                        @endforeach
                                    @endif
                                    @if ($lokal_input=='A' || $lokal_input=='N')
                                        @php
                                            $qFKpartNonTax = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes as tx_do','tx_delivery_order_non_tax_parts.delivery_order_id','=','tx_do.id')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_delivery_order_non_tax_parts.sales_order_part_id','=','tx_sjp.id')
                                            ->leftJoin('mst_parts as msp','tx_delivery_order_non_tax_parts.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->select(
                                                'tx_delivery_order_non_tax_parts.part_id',
                                                'tx_delivery_order_non_tax_parts.qty',
                                                'tx_delivery_order_non_tax_parts.updated_at as updatedat',
                                                'tx_sjp.last_avg_cost',
                                            )
                                            ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_do.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_do.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'tx_delivery_order_non_tax_parts.active'=>'Y',
                                                'tx_do.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qFKpartNonTax as $pFK)
                                            @php
                                                $totAvgFKNonTax+=($pFK->last_avg_cost*$pFK->qty);
                                            @endphp
                                        @endforeach
                                        @php
                                            $qReturNonTaxPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                            ->leftJoin('mst_parts as msp','tx_nota_retur_part_non_taxes.part_id','=','msp.id')
                                            ->leftJoin('mst_customers as mst_c','tx_nr.customer_id','=','mst_c.id')
                                            ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                            ->select(
                                                'tx_nota_retur_part_non_taxes.part_id',
                                                'tx_nota_retur_part_non_taxes.qty_retur',
                                                'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                                'tx_sjp.last_avg_cost',
                                            )
                                            ->whereRaw('tx_nr.nota_retur_no NOT LIKE \'%Draft%\'')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                            ->where([
                                                'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                                'msp.brand_id'=>$brand->id,
                                                'usr_s.branch_id'=>$branch->id,
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qReturNonTaxPart as $pRE)
                                            @php
                                                $totAvgReturNonTax+=($pRE->last_avg_cost*$pRE->qty_retur);
                                            @endphp
                                        @endforeach
                                    @endif
                                    {{ number_format(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax),0,'.','') }}
                                    @php
                                        $subTotalCostAVG+=(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax));
                                    @endphp
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))-(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax)),0,'.','') }}
                                    @php
                                        $subTotalProfit+=((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))-(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax)));
                                    @endphp
                                </td>
                                <td style="text-align: right;border-right: 1px solid black;">
                                    {{ number_format((((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax)))>0)?(((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))-(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax)))/((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))))*100:0,0,'.','') }}%
                                    @php
                                        $subTotalGP+=((((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax)))>0)?(((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))-(($totAvgFK+$totAvgFKNonTax)-($totAvgRetur+$totAvgReturNonTax)))/((($dppSUM+$dppSUMNonTax)-($returSUM+$returSUMNonTax))))*100:0);
                                    @endphp
                                </td>
                            </tr>
                            @php
                                $branch_name=$branch->name;
                            @endphp
                        @endforeach
                        <tr>
                            <td style="border-left: 1px solid black;border-top: 1px solid black;">&nbsp;</td>
                            <td style="font-weight: 700;border-top: 1px solid black;">SUB TOTAL</td>
                            <td style="text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalHarga,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalDPP,0,'.','') }}</td>
                            <td style="color: red;text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalRetur,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalNetto,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalCostAVG,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border-top: 1px solid black;">{{ number_format($subTotalProfit,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border-right: 1px solid black;border-top: 1px solid black;">{{ number_format((($subTotalNetto>0?$subTotalProfit/$subTotalNetto:0))*100,0,'.','') }}%</td>
                            @php
                                $allTotalHarga+=$subTotalHarga;
                                $allTotalDPP+=$subTotalDPP;
                                $allTotalRetur+=$subTotalRetur;
                                $allTotalNetto+=$subTotalNetto;
                                $allTotalCostAVG+=$subTotalCostAVG;
                                $allTotalProfit+=$subTotalProfit;
                                $allTotalGP+=$subTotalGP;
                            @endphp
                        </tr>
                        <tr>
                            <td style="border-left: 1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right: 1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="border-left: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="font-weight: 700;border-bottom: 1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalHarga,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalDPP,0,'.','') }}</td>
                        <td style="text-align: right;color:red;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalRetur,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalNetto,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalCostAVG,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;border-bottom: 1px solid black;">{{ number_format($allTotalProfit,0,'.','') }}</td>
                        <td style="text-align: right;font-weight: 700;border-right: 1px solid black;border-bottom: 1px solid black;">{{ $allTotalNetto>0?number_format(($allTotalProfit/$allTotalNetto)*100,0,'.',''):0 }}%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
