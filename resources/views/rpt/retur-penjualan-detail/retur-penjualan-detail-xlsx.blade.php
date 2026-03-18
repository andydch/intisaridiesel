<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Retur Penjualan Detail</title>
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
                        <th colspan="{{ $totCols }}">RETUR PENJUALAN DETAIL</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO NOTA RETUR</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA CUSTOMER</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">QTY</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">HARGA DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG COST ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">CUST DOC NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $nota_retur_no = '';
                        $nota_retur_date = '';
                        $cust_name = '';
                        $totalAllDPP = 0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td colspan="11" style="text-align: left;font-weight: 700;border-left:1px solid black;border-right:1px solid black;">{{ $branch->name }}</td>
                        </tr>
                        {{-- with tax --}}
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || strtoupper($lokal_input)=='X' || $lokal_input=='')
                            @php
                                $qNotaRetur = \App\Models\Tx_nota_retur::leftJoin('mst_customers as msc','tx_nota_returs.customer_id','=','msc.id')
                                ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                ->leftJoin('tx_delivery_orders as fk','tx_nota_returs.delivery_order_id','=','fk.id')
                                ->select(
                                    'tx_nota_returs.id as nr_id',
                                    'tx_nota_returs.nota_retur_no',
                                    'tx_nota_returs.nota_retur_date',
                                    'msc.name as cust_name',
                                    'userdetails.initial',
                                    'fk.delivery_order_no as faktur_no',
                                )
                                ->where('tx_nota_returs.nota_retur_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_nota_returs.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_nota_returs.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_nota_returs.approved_by IS NOT null')
                                ->where([
                                    'tx_nota_returs.active'=>'Y',
                                    'userdetails.branch_id'=>$branch->id,
                                ])
                                ->orderBy('tx_nota_returs.nota_retur_date','ASC')
                                ->get();
                            @endphp
                            @foreach ($qNotaRetur as $qNR)
                                @php
                                    $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('mst_parts as msp','tx_nota_retur_parts.part_id','=','msp.id')
                                    ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                    ->leftJoin('tx_sales_orders as tx_sp','tx_sop.order_id','=','tx_sp.id')
                                    ->select(
                                        'tx_nota_retur_parts.part_id',
                                        'tx_nota_retur_parts.qty_retur',
                                        'tx_nota_retur_parts.final_price',
                                        'tx_nota_retur_parts.total_price',
                                        'tx_nota_retur_parts.updated_at as updatedat',
                                        'msp.part_name',
                                        'msp.part_number',
                                        'tx_sp.customer_doc_no',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_nota_retur_parts.nota_retur_id'=>$qNR->nr_id,
                                        'tx_nota_retur_parts.active'=>'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qNotaReturPart as $qNRp)
                                    @php
                                        $totalAllDPP += $qNRp->total_price;
                                    @endphp
                                    <tr>
                                        <td style="text-align: center;border-left:1px solid black;">{{ ($nota_retur_date!=$qNR->nota_retur_date)?date_format(date_create($qNR->nota_retur_date),"d/m/Y"):'' }}</td>
                                        <td style="text-align: center;">{{ ($nota_retur_no!=$qNR->nota_retur_no)?$qNR->nota_retur_no:'' }}</td>
                                        <td>{{ ($cust_name!=$qNR->cust_name)?$qNR->cust_name:'' }}</td>
                                        <td>{{ $qNRp->part_number }}</td>
                                        <td>{{ $qNRp->part_name }}</td>
                                        <td style="text-align: right;">-{{ $qNRp->qty_retur }}</td>
                                        <td style="text-align: right;">{{ number_format($qNRp->final_price,0,'.','') }}</td>
                                        <td style="text-align: right;">-{{ number_format($qNRp->total_price,0,'.','') }}</td>
                                        <td style="text-align: right;">
                                            -{{ number_format(($qNRp->last_avg_cost*$qNRp->qty_retur),0,'.','') }}
                                        </td>
                                        <td>{{ $qNRp->customer_doc_no }}</td>
                                        <td style="border-right:1px solid black;">{{ $qNR->faktur_no }}</td>
                                    </tr>
                                    @php
                                        $nota_retur_no = $qNR->nota_retur_no;
                                        $nota_retur_date = $qNR->nota_retur_date;
                                        $cust_name = $qNR->cust_name;
                                    @endphp
                                @endforeach
                            @endforeach
                        @endif

                        {{-- non tax --}}
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N' || $lokal_input=='')
                            @php
                                $qRetur = \App\Models\Tx_nota_retur_non_tax::leftJoin('mst_customers as msc','tx_nota_retur_non_taxes.customer_id','=','msc.id')
                                ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                ->leftJoin('tx_delivery_order_non_taxes as np','tx_nota_retur_non_taxes.delivery_order_id','=','np.id')
                                ->select(
                                    'tx_nota_retur_non_taxes.id as nr_id',
                                    'tx_nota_retur_non_taxes.nota_retur_no',
                                    'tx_nota_retur_non_taxes.nota_retur_date',
                                    'msc.name as cust_name',
                                    'userdetails.initial',
                                    'np.delivery_order_no as faktur_no',
                                )
                                ->where('tx_nota_retur_non_taxes.nota_retur_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_nota_retur_non_taxes.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_nota_retur_non_taxes.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_nota_retur_non_taxes.approved_by IS NOT null')
                                ->where([
                                    'tx_nota_retur_non_taxes.active'=>'Y',
                                    'userdetails.branch_id'=>$branch->id,
                                ])
                                ->orderBy('tx_nota_retur_non_taxes.nota_retur_date','ASC')
                                ->get();
                            @endphp
                            @foreach ($qRetur as $qRE)
                                @php
                                    $qReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('mst_parts as msp','tx_nota_retur_part_non_taxes.part_id','=','msp.id')
                                    ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                    ->leftJoin('tx_surat_jalans as tx_sj','tx_sjp.surat_jalan_id','=','tx_sj.id')
                                    ->select(
                                        'tx_nota_retur_part_non_taxes.part_id',
                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                        'tx_nota_retur_part_non_taxes.final_price',
                                        'tx_nota_retur_part_non_taxes.total_price',
                                        'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                        'msp.part_name',
                                        'msp.part_number',
                                        'tx_sj.customer_doc_no',
                                        'tx_sjp.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qRE->nr_id,
                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qReturPart as $qREp)
                                    @php
                                        $totalAllDPP += $qREp->total_price;
                                    @endphp
                                    <tr>
                                        <td style="text-align: center;border-left:1px solid black;">{{ ($nota_retur_date!=$qRE->nota_retur_date)?date_format(date_create($qRE->nota_retur_date),"d/m/Y"):'' }}</td>
                                        <td style="text-align: center;">{{ ($nota_retur_no!=$qRE->nota_retur_no)?$qRE->nota_retur_no:'' }}</td>
                                        <td>{{ ($cust_name!=$qRE->cust_name)?$qRE->cust_name:'' }}</td>
                                        <td>{{ $qREp->part_number }}</td>
                                        <td>{{ $qREp->part_name }}</td>
                                        <td style="text-align: right;">-{{ $qREp->qty_retur }}</td>
                                        <td style="text-align: right;">{{ number_format($qREp->final_price,0,'.','') }}</td>
                                        <td style="text-align: right;">-{{ number_format($qREp->total_price,0,'.','') }}</td>
                                        <td style="text-align: right;">
                                            -{{ number_format(($qREp->last_avg_cost*$qREp->qty_retur),0,'.','') }}
                                        </td>
                                        <td>{{ $qREp->customer_doc_no }}</td>
                                        <td style="border-right:1px solid black;">{{ $qRE->faktur_no }}</td>
                                    </tr>
                                    @php
                                        $nota_retur_no = $qRE->nota_retur_no;
                                        $nota_retur_date = $qRE->nota_retur_date;
                                        $cust_name = $qRE->cust_name;
                                    @endphp
                                @endforeach
                            @endforeach
                        @endif
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="text-align: center;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;border-top:1px solid black;border-bottom:1px solid black;">-{{ number_format($totalAllDPP,0,'.','') }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
