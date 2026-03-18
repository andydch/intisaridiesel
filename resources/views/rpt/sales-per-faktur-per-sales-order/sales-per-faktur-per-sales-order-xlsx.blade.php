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
                    $totCols = 11;
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
                    @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || $lokal_input=='x')
                        <tr>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FK/NR</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO SO</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA CUST</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP+PPN ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FP</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALES</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                        </tr>
                        @php
                            $totalDppPajak = 0;
                            $totalDppPlusPpnPajak = 0;
                            $totalAvgPajak = 0;

                            // faktur
                            $qFaktur = \App\Models\Tx_delivery_order::leftJoin('mst_customers as ms_cust','tx_delivery_orders.customer_id','=','ms_cust.id')
                            ->select(
                                'tx_delivery_orders.id AS faktur_id',
                                'tx_delivery_orders.delivery_order_no',
                                'tx_delivery_orders.delivery_order_date',
                                'tx_delivery_orders.total_before_vat',
                                'tx_delivery_orders.total_after_vat',
                                'tx_delivery_orders.customer_id',
                                'tx_delivery_orders.tax_invoice_id',
                                'tx_delivery_orders.created_by',
                                'tx_delivery_orders.updated_by as updatedby',
                                'ms_cust.name AS cust_name',
                                'ms_cust.customer_unique_code',
                            )
                            ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_delivery_orders.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_orders.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('tx_delivery_orders.active','=','Y')
                            ->orderBy('tx_delivery_orders.delivery_order_no','ASC')
                            ->orderBy('tx_delivery_orders.delivery_order_date','ASC')
                            ->get();
                        @endphp
                        @foreach ($qFaktur as $qFk)
                            @php
                                $totalDppPajak += $qFk->total_before_vat;
                                $totalDppPlusPpnPajak += $qFk->total_after_vat;

                                $qFakturParts = \App\Models\Tx_delivery_order_part::leftJoin('tx_sales_orders AS tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                ->select(
                                    'tx_delivery_order_parts.sales_order_part_id',
                                    'tx_delivery_order_parts.updated_at AS tx_do_updated_at',
                                    'tx_so.id AS sales_order_id',
                                    'tx_so.sales_order_no',
                                    'tx_so.total_before_vat',
                                    'tx_so.updated_by as so_updated_by',
                                )
                                ->where([
                                    'tx_delivery_order_parts.delivery_order_id'=>$qFk->faktur_id,
                                    'tx_delivery_order_parts.active'=>'Y',
                                    'tx_so.need_approval'=>'N',
                                ]);

                                $sales_order_no = '';
                                $avg = 0;

                                $qFkSo = $qFakturParts->get();
                                foreach ($qFkSo as $qFkSo) {
                                    $sales_order_no = $qFkSo->sales_order_no;

                                    $qSoPart = \App\Models\Tx_sales_order_part::where([
                                        'id' => $qFkSo->sales_order_part_id,
                                        'active' => 'Y',
                                    ])
                                    ->first();
                                    if ($qSoPart){
                                        $avg += ($qSoPart->last_avg_cost*$qSoPart->qty);
                                    }
                                }
                                $totalAvgPajak += $avg;
                            @endphp
                            <tr>
                                <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qFk->delivery_order_no }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $sales_order_no }}</td>
                                <td style="text-align: left;border:1px solid black;">{{ $qFk->customer_unique_code.' - '.$qFk->cust_name }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format($qFk->total_after_vat,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format($qFk->total_before_vat,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format($avg,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">
                                    {{ ($qFk->total_before_vat>0?number_format((($qFk->total_before_vat-$avg)/$qFk->total_before_vat)*100,0,'.',''):0) }}%
                                </td>
                                <td style="text-align: center;border:1px solid black;">{{ (!is_null($qFk->tax_invoice)?$qFk->tax_invoice->prefiks_code.$qFk->tax_invoice->fp_no:'') }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qFk->customer->salesman01->initial }}</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                            </tr>
                        @endforeach
                        @php
                            // nota retur
                            $qNotaRetur = \App\Models\Tx_nota_retur::select(
                                'id as nr_id',
                                'nota_retur_no',
                                'nota_retur_date',
                                'total_before_vat',
                                'total_after_vat',
                                'delivery_order_id',
                                'customer_id',
                                'created_by',
                                'updated_by',
                            )
                            ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->whereRaw('approved_by IS NOT NULL')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('nota_retur_no','ASC')
                            ->orderBy('nota_retur_date','ASC')
                            ->get();
                        @endphp
                        @foreach ($qNotaRetur as $qNR)
                            @php
                                $totalDppPajak = $totalDppPajak-$qNR->total_before_vat;
                                $totalDppPlusPpnPajak = $totalDppPlusPpnPajak-$qNR->total_after_vat;

                                $sales_order_no = '';
                                $so_updated_by = null;
                                $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts AS tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                ->leftJoin('tx_sales_orders AS tx_so','tx_sop.order_id','=','tx_so.id')
                                ->leftJoin('tx_delivery_order_parts as tx_do_part','tx_sop.id','=','tx_do_part.sales_order_part_id')
                                ->select(
                                    'tx_do_part.updated_at AS tx_do_updated_at',
                                    'tx_so.id AS sales_order_id',
                                    'tx_so.sales_order_no',
                                    'tx_so.total_before_vat',
                                    'tx_so.updated_by as so_updated_by',
                                    )
                                ->where('tx_nota_retur_parts.nota_retur_id','=',$qNR->nr_id)
                                ->first();
                                if ($qNotaReturPart){
                                    $sales_order_no = $qNotaReturPart->sales_order_no;
                                    $so_updated_by = $qNotaReturPart->so_updated_by;
                                }
                            @endphp
                            <tr>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ date_format(date_create($qNR->nota_retur_date),"d/m/Y") }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->nota_retur_no }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $sales_order_no }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->name }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">-{{ number_format($qNR->total_after_vat,0,'.','') }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">-{{ number_format($qNR->total_before_vat,0,'.','') }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">
                                @php
                                    $avg = 0;
                                    $qNRavg = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                    ->select(
                                        'tx_nota_retur_parts.qty_retur',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_nota_retur_parts.nota_retur_id'=>$qNR->nr_id,
                                        'tx_nota_retur_parts.active'=>'Y',
                                    ])
                                    ->get();
                                    foreach ($qNRavg as $qAvg) {
                                        $avg += ($qAvg->last_avg_cost*$qAvg->qty_retur);
                                    }
                                    $totalAvgPajak = $totalAvgPajak-$avg;
                                @endphp
                                -{{ number_format($avg,0,'.','') }}
                                </td>
                                <td style="text-align: right;color:red;border:1px solid black;">{{ ($qNR->total_before_vat>0?number_format((($qNR->total_before_vat-$avg)/$qNR->total_before_vat)*100,0,'.',','):'') }}%</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->salesman01->initial }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="text-align: center;font-weight: 700;border:1px solid black;">TOTAL</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format($totalDppPlusPpnPajak,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format($totalDppPajak,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format($totalAvgPajak,0,'.','') }}</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
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
                            <td>&nbsp;</td>
                        </tr>
                    @endif

                    @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N')
                        <tr>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">TANGGAL</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">NO NP/RE</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">NO SJ</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">NAMA CUST</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">&nbsp;</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">GP %</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">NO FP</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">SALES</th>
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;font-weight:bold;">EX FAKTUR</th>
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
                                'tx_delivery_order_non_taxes.total_price',
                                'tx_delivery_order_non_taxes.customer_id',
                                'tx_delivery_order_non_taxes.created_by',
                                'tx_delivery_order_non_taxes.updated_by',
                                'ms_cust.name AS cust_name',
                                'ms_cust.customer_unique_code',
                            )
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where([
                                'tx_delivery_order_non_taxes.active'=>'Y',
                            ])
                            ->orderBy('tx_delivery_order_non_taxes.delivery_order_no','ASC')
                            ->orderBy('tx_delivery_order_non_taxes.delivery_order_date','ASC')
                            ->get();
                        @endphp
                        @foreach ($qFakturNonPajak as $qFk)
                            @php
                                $totalDppNonPajak += $qFk->total_price;

                                $qFakturNonPajakParts = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_surat_jalans AS tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                ->select(
                                    'tx_delivery_order_non_tax_parts.sales_order_part_id',
                                    'tx_delivery_order_non_tax_parts.updated_at AS tx_do_updated_at',
                                    'tx_sj.id AS surat_jalan_id',
                                    'tx_sj.surat_jalan_no',
                                    'tx_sj.total',
                                    'tx_sj.updated_by as so_updated_by',
                                    )
                                ->where([
                                    'tx_delivery_order_non_tax_parts.delivery_order_id'=>$qFk->faktur_id,
                                    'tx_delivery_order_non_tax_parts.active'=>'Y',
                                    'tx_sj.need_approval'=>'N',
                                ]);

                                $avg = 0;
                                $surat_jalan_no = '';
                                $qNpSj = $qFakturNonPajakParts->get();
                                foreach ($qNpSj as $qNpSj) {
                                    $surat_jalan_no = $qNpSj->surat_jalan_no;

                                    $qFkPart = \App\Models\Tx_surat_jalan_part::where([
                                        'id' => $qNpSj->sales_order_part_id,
                                        'active' => 'Y',
                                    ])
                                    ->first();
                                    if ($qFkPart){
                                        $avg += ($qFkPart->last_avg_cost*$qFkPart->qty);
                                    }
                                }
                                $totalAvgNonPajak += $avg;
                            @endphp
                            <tr>
                                <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qFk->delivery_order_no }}</td>
                                <td style="text-align: center;border:1px solid black;">{{ $surat_jalan_no }}</td>
                                <td style="text-align: left;border:1px solid black;">{{ $qFk->customer_unique_code.' - '.$qFk->cust_name }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format(0,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format($qFk->total_price,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ number_format($avg,0,'.','') }}</td>
                                <td style="text-align: right;border:1px solid black;">{{ ($qFk->total_price>0?number_format((($qFk->total_price-$avg)/$qFk->total_price)*100,0,'.',''):0) }}%</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qFk->customer->salesman01->initial }}</td>
                                <td style="border:1px solid black;">&nbsp;</td>
                            </tr>
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
                                'updated_by',
                            )
                            ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->whereRaw('approved_by IS NOT NULL')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('nota_retur_no','ASC')
                            ->orderBy('nota_retur_date','ASC')
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
                                    ->leftJoin('tx_delivery_order_non_tax_parts as tx_do_part','tx_sjp.id','=','tx_do_part.sales_order_part_id')
                                    ->select(
                                        'tx_do_part.updated_at AS tx_do_updated_at',
                                        'tx_sj.surat_jalan_no',
                                        'tx_sj.total',
                                        'tx_sj.updated_by as so_updated_by',
                                    )
                                    ->where('tx_nota_retur_part_non_taxes.nota_retur_id','=',$qNR->nr_id)
                                    ->first();
                                @endphp
                                {{ (!is_null($qNotaReturPart)?$qNotaReturPart->surat_jalan_no:'') }}
                                </td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->name }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">{{ number_format(0,0,'.','') }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">-{{ number_format($qNR->total_price,0,'.','') }}</td>
                                <td style="text-align: right;color:red;border:1px solid black;">
                                @php
                                    $avg = 0;
                                    $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                    ->select(
                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                        'tx_sjp.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qNR->nr_id,
                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                    ])
                                    ->get();
                                    foreach ($qNRavg as $qAvg) {
                                        $avg += ($qAvg->last_avg_cost*$qAvg->qty_retur);
                                    }
                                    $totalAvgNonPajak = $totalAvgNonPajak-$avg;
                                @endphp
                                -{{ number_format($avg,0,'.','') }}
                                </td>
                                <td style="text-align: right;color:red;border:1px solid black;">{{ ($qNR->total_price>0?number_format((($qNR->total_price-$avg)/$qNR->total_price)*100,0,'.',''):0) }}%</td>
                                <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;border:1px solid black;">{{ $qNR->customer->salesman01->initial }}</td>
                                <td style="text-align: center;color:red;border:1px solid black;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="text-align: center;font-weight: 700;border:1px solid black;">TOTAL</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format(0,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format($totalDppNonPajak,0,'.','') }}</td>
                            <td style="text-align: right;font-weight: 700;border:1px solid black;">{{ number_format($totalAvgNonPajak,0,'.','') }}</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
                            <td style="border:1px solid black;">&nbsp;</td>
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
