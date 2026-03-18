<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Sales Per Faktur Per SO</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 10;
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
                        <th colspan="{{ $totCols }}">REPORT SALES PER FAKTUR</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                    @endphp
                    @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P')
                        <tr>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FK/NR</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO SO</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA CUST</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FP</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALES</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                        </tr>
                        @php
                            // faktur
                            $totalDppPajak = 0;
                            $totalAvgPajak = 0;
                            $qFaktur = \App\Models\Tx_delivery_order::leftJoin('mst_customers as ms_cust','tx_delivery_orders.customer_id','=','ms_cust.id')
                            ->select(
                                'tx_delivery_orders.id AS faktur_id',
                                'tx_delivery_orders.delivery_order_no',
                                'tx_delivery_orders.delivery_order_date',
                                'tx_delivery_orders.tax_invoice_id',
                                'tx_delivery_orders.created_by',
                                'ms_cust.name AS cust_name',
                            )
                            ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_delivery_orders.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_orders.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('tx_delivery_orders.active','=','Y')
                            ->orderBy('tx_delivery_orders.delivery_order_date','DESC')
                            ->get();
                        @endphp
                        @foreach ($qFaktur as $qFk)
                            @php
                                $qFakturParts = \App\Models\Tx_delivery_order_part::leftJoin('tx_sales_orders AS tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                ->select(
                                    'tx_so.id AS sales_order_id',
                                    'tx_so.sales_order_no',
                                    'tx_so.total_before_vat',
                                    )
                                ->where('tx_delivery_order_parts.delivery_order_id','=',$qFk->faktur_id)
                                ->where('tx_delivery_order_parts.active','=','Y')
                                ->get();
                            @endphp
                            @foreach ($qFakturParts as $qFkParts)
                                @php
                                    $totalDppPajak += $qFkParts->total_before_vat;
                                @endphp
                                <tr>
                                    <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->delivery_order_no }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFkParts->sales_order_no }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->cust_name }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($qFkParts->total_before_vat,0,'.',',') }}</td>
                                    @php
                                        $avg = 0;
                                        $qFKavg = \App\Models\Tx_delivery_order_part::select('qty')
                                        ->addSelect([
                                            'avg_cost' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                            ->whereColumn('v_log_avg_cost.part_id','=','tx_delivery_order_parts.part_id')
                                            ->whereRaw('v_log_avg_cost.updated_at<=\''.$qFk->delivery_order_date.' 23:59:59\'')
                                            ->where('v_log_avg_cost.avg_cost','>',0)
                                            ->orderBy('v_log_avg_cost.updated_at','DESC')
                                            ->limit(1)
                                        ])
                                        ->addSelect([
                                            'avg_cost2' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                            ->whereColumn('v_log_avg_cost.part_id','=','tx_delivery_order_parts.part_id')
                                            ->whereRaw('v_log_avg_cost.updated_at>\''.$qFk->delivery_order_date.' 23:59:59\'')
                                            ->where('v_log_avg_cost.avg_cost','>',0)
                                            ->orderBy('v_log_avg_cost.updated_at','ASC')
                                            ->limit(1)
                                        ])
                                        ->where('sales_order_id','=',$qFkParts->sales_order_id)
                                        ->get();
                                        foreach ($qFKavg as $qAvg) {
                                            $avg += ($qAvg->qty*(($qAvg->avg_cost>0)?$qAvg->avg_cost:$qAvg->avg_cost2));
                                        }
                                        $totalAvgPajak += $avg;
                                    @endphp
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($avg,0,'.',',') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format((($qFkParts->total_before_vat-$avg)/$qFkParts->total_before_vat)*100,0,'.',',') }}%</td>
                                    <td style="text-align: center;border:1px solid black;">{{ (!is_null($qFk->tax_invoice)?'\''.$qFk->tax_invoice->fp_no:'') }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->createdBy->userDetail->initial }}</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                </tr>
                            @endforeach
                        @endforeach
                        @php
                            // nota retur
                            $qNotaRetur = \App\Models\Tx_nota_retur::select(
                                'id as nr_id',
                                'nota_retur_no',
                                'nota_retur_date',
                                'total_before_vat',
                                'delivery_order_id',
                                'customer_id',
                                'created_by',
                            )
                            ->where('nota_retur_no','NOT LIKE','%Draft%')
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('active','=','Y')
                            ->orderBy('nota_retur_date','DESC')
                            ->get();
                        @endphp
                        @foreach ($qNotaRetur as $qNR)
                            @php
                                $totalDppPajak = $totalDppPajak-$qNR->total_before_vat;
                            @endphp
                            <tr>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ date_format(date_create($qNR->nota_retur_date),"d/m/Y") }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->nota_retur_no }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">
                                @php
                                    $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts AS tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                    ->leftJoin('tx_sales_orders AS tx_so','tx_sop.order_id','=','tx_so.id')
                                    ->select(
                                        'tx_so.sales_order_no',
                                        )
                                    ->where('tx_nota_retur_parts.nota_retur_id','=',$qNR->nr_id)
                                    ->first();
                                @endphp
                                {{ (!is_null($qNotaReturPart)?$qNotaReturPart->sales_order_no:'') }}
                                </td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->name }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">({{ number_format($qNR->total_before_vat,0,'.',',') }})</td>
                                <td style="text-align: right;color:red;border:1px solid black;">
                                @php
                                    $avg = 0;
                                    $qNRavg = \App\Models\Tx_nota_retur_part::select('qty_retur')
                                    ->addSelect([
                                        'avg_cost' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                        ->whereColumn('v_log_avg_cost.part_id','=','tx_nota_retur_parts.part_id')
                                        ->whereRaw('v_log_avg_cost.updated_at<=\''.$qNR->nota_retur_date.' 23:59:59\'')
                                        ->where('v_log_avg_cost.avg_cost','>',0)
                                        ->orderBy('v_log_avg_cost.updated_at','DESC')
                                        ->limit(1)
                                    ])
                                    ->addSelect([
                                        'avg_cost2' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                        ->whereColumn('v_log_avg_cost.part_id','=','tx_nota_retur_parts.part_id')
                                        ->whereRaw('v_log_avg_cost.updated_at>\''.$qNR->nota_retur_date.' 23:59:59\'')
                                        ->where('v_log_avg_cost.avg_cost','>',0)
                                        ->orderBy('v_log_avg_cost.updated_at','ASC')
                                        ->limit(1)
                                    ])
                                    ->where('nota_retur_id','=',$qNR->nr_id)
                                    ->get();
                                    foreach ($qNRavg as $qAvg) {
                                        $avg += ($qAvg->qty_retur*(($qAvg->avg_cost>0)?$qAvg->avg_cost:$qAvg->avg_cost2));
                                    }
                                    $totalAvgPajak = $totalAvgPajak-$avg;
                                @endphp
                                ({{ number_format($avg,0,'.',',') }})
                                </td>
                                <td style="text-align: right;color:red;border:1px solid black;">({{ number_format((($qNR->total_before_vat-$avg)/$qNR->total_before_vat)*100,0,'.',',') }}%)</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->createdBy->userDetail->initial }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" style="text-align: center;font-weight:bold;border-left:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                            <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;">{{ number_format($totalDppPajak,0,'.',',') }}</td>
                            <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;">{{ number_format($totalAvgPajak,0,'.',',') }}</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
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
                    @endif

                    @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N')
                        <tr>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FK/NR</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO SO</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA CUST</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FP</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALES</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                        </tr>
                        @php
                            $totalDppNonPajak = 0;
                            $totalAvgNonPajak = 0;

                            // faktur non pajak
                            $qFakturNonPajak = \App\Models\Tx_delivery_order_non_tax::leftJoin('mst_customers as ms_cust','tx_delivery_order_non_taxes.customer_id','=','ms_cust.id')
                            ->select(
                                'tx_delivery_order_non_taxes.id AS faktur_id',
                                'tx_delivery_order_non_taxes.delivery_order_no',
                                'tx_delivery_order_non_taxes.delivery_order_date',
                                'tx_delivery_order_non_taxes.created_by',
                                'ms_cust.name AS cust_name',
                            )
                            ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('tx_delivery_order_non_taxes.active','=','Y')
                            ->orderBy('tx_delivery_order_non_taxes.delivery_order_date','DESC')
                            ->get();
                        @endphp
                        @foreach ($qFakturNonPajak as $qFk)
                            @php
                                $qFakturNonPajakParts = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_surat_jalans AS tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                ->select(
                                    'tx_sj.id AS sales_order_id',
                                    'tx_sj.surat_jalan_no',
                                    'tx_sj.total',
                                    )
                                ->where('tx_delivery_order_non_tax_parts.delivery_order_id','=',$qFk->faktur_id)
                                ->where('tx_delivery_order_non_tax_parts.active','=','Y')
                                ->get();
                            @endphp
                            @foreach ($qFakturNonPajakParts as $qFkParts)
                                @php
                                    $totalDppNonPajak += $qFkParts->total;
                                @endphp
                                <tr>
                                    <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->delivery_order_no }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFkParts->surat_jalan_no }}</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->cust_name }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($qFkParts->total,0,'.',',') }}</td>
                                    @php
                                        $avg = 0;
                                        $qFKavg = \App\Models\Tx_delivery_order_non_tax_part::select('qty')
                                        ->addSelect([
                                            'avg_cost' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                            ->whereColumn('v_log_avg_cost.part_id','=','tx_delivery_order_non_tax_parts.part_id')
                                            ->whereRaw('v_log_avg_cost.updated_at<=\''.$qFk->delivery_order_date.' 23:59:59\'')
                                            ->where('v_log_avg_cost.avg_cost','>',0)
                                            ->orderBy('v_log_avg_cost.updated_at','DESC')
                                            ->limit(1)
                                        ])
                                        ->addSelect([
                                            'avg_cost2' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                            ->whereColumn('v_log_avg_cost.part_id','=','tx_delivery_order_non_tax_parts.part_id')
                                            ->whereRaw('v_log_avg_cost.updated_at>\''.$qFk->delivery_order_date.' 23:59:59\'')
                                            ->where('v_log_avg_cost.avg_cost','>',0)
                                            ->orderBy('v_log_avg_cost.updated_at','ASC')
                                            ->limit(1)
                                        ])
                                        ->where('sales_order_id','=',$qFkParts->sales_order_id)
                                        ->get();
                                        foreach ($qFKavg as $qAvg) {
                                            $avg += ($qAvg->qty*(($qAvg->avg_cost>0)?$qAvg->avg_cost:$qAvg->avg_cost2));
                                        }
                                        $totalAvgNonPajak += $avg;
                                    @endphp
                                    <td style="text-align: right;border:1px solid black;">{{ number_format($avg,0,'.',',') }}</td>
                                    <td style="text-align: right;border:1px solid black;">{{ number_format((($qFkParts->total-$avg)/$qFkParts->total)*100,0,'.',',') }}%</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                    <td style="text-align: center;border:1px solid black;">{{ $qFk->createdBy->userDetail->initial }}</td>
                                    <td style="border:1px solid black;">&nbsp;</td>
                                </tr>
                            @endforeach
                        @endforeach
                        @php
                            // retur
                            $qRetur = \App\Models\Tx_nota_retur_non_tax::select(
                                'id as nr_id',
                                'nota_retur_no',
                                'nota_retur_date',
                                'total_price',
                                'delivery_order_id',
                                'customer_id',
                                'created_by',
                            )
                            ->where('nota_retur_no','NOT LIKE','%Draft%')
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('active','=','Y')
                            ->orderBy('nota_retur_date','DESC')
                            ->get();
                        @endphp
                        @foreach ($qRetur as $qNR)
                            @php
                                $totalDppNonPajak = $totalDppNonPajak-$qNR->total_price;
                            @endphp
                            <tr>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ date_format(date_create($qNR->nota_retur_date),"d/m/Y") }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->nota_retur_no }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">
                                @php
                                    $qNotaReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts AS tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                    ->leftJoin('tx_surat_jalans AS tx_sj','tx_sjp.surat_jalan_id','=','tx_sj.id')
                                    ->select(
                                        'tx_sj.surat_jalan_no',
                                        )
                                    ->where('tx_nota_retur_part_non_taxes.nota_retur_id','=',$qNR->nr_id)
                                    ->first();
                                @endphp
                                {{ (!is_null($qNotaReturPart)?$qNotaReturPart->surat_jalan_no:'') }}
                                </td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->name }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">({{ number_format($qNR->total_price,0,'.',',') }})</td>
                                <td style="text-align: right;color:red;border:1px solid black;">
                                @php
                                    $avg = 0;
                                    $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::select('qty_retur')
                                    ->addSelect([
                                        'avg_cost' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                        ->whereColumn('v_log_avg_cost.part_id','=','tx_nota_retur_part_non_taxes.part_id')
                                        ->whereRaw('v_log_avg_cost.updated_at<=\''.$qNR->nota_retur_date.' 23:59:59\'')
                                        ->where('v_log_avg_cost.avg_cost','>',0)
                                        ->orderBy('v_log_avg_cost.updated_at','DESC')
                                        ->limit(1)
                                    ])
                                    ->addSelect([
                                        'avg_cost2' => \App\Models\V_log_avg_cost::select('v_log_avg_cost.avg_cost')
                                        ->whereColumn('v_log_avg_cost.part_id','=','tx_nota_retur_part_non_taxes.part_id')
                                        ->whereRaw('v_log_avg_cost.updated_at>\''.$qNR->nota_retur_date.' 23:59:59\'')
                                        ->where('v_log_avg_cost.avg_cost','>',0)
                                        ->orderBy('v_log_avg_cost.updated_at','ASC')
                                        ->limit(1)
                                    ])
                                    ->where('nota_retur_id','=',$qNR->nr_id)
                                    ->get();
                                    foreach ($qNRavg as $qAvg) {
                                        $avg += ($qAvg->qty_retur*(($qAvg->avg_cost>0)?$qAvg->avg_cost:$qAvg->avg_cost2));
                                    }
                                    $totalAvgNonPajak = $totalAvgNonPajak-$avg;
                                @endphp
                                ({{ number_format($avg,0,'.',',') }})
                                </td>
                                <td style="text-align: right;color:red;border:1px solid black;">({{ number_format((($qNR->total_price-$avg)/$qNR->total_price)*100,0,'.',',') }}%)</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->createdBy->userDetail->initial }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="4" style="text-align: center;font-weight:bold;border-left:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                            <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;">{{ number_format($totalDppNonPajak,0,'.',',') }}</td>
                            <td style="text-align: right;font-weight:bold;border-bottom:1px solid black;">{{ number_format($totalAvgNonPajak,0,'.',',') }}</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
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
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
