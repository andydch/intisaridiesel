<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SummReturPenjualan</title>
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
                        <th colspan="{{ $totCols }}">SUMMARY RETUR PENJUALAN</th>
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
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DPP AMOUNT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">COST AVG ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $nota_retur_no = '';
                        $nota_retur_date = '';
                        $cust_name = '';
                        $totalDPP = 0;
                        $totalPPN = 0;
                        $totalAVG = 0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="text-align: center;font-weight: 700;border-left:1px black solid;">Cabang</td>
                            <td style="text-align: center;font-weight: 700;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px black solid;">&nbsp;</td>
                        </tr>
                        {{-- with tax --}}
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || strtoupper($lokal_input)=='X')
                            @php
                                $qNotaRetur = \App\Models\Tx_nota_retur::leftJoin('mst_customers as msc','tx_nota_returs.customer_id','=','msc.id')
                                ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                ->leftJoin('tx_delivery_orders as fk','tx_nota_returs.delivery_order_id','=','fk.id')
                                ->select(
                                    'tx_nota_returs.id as nr_id',
                                    'tx_nota_returs.nota_retur_no',
                                    'tx_nota_returs.nota_retur_date',
                                    'tx_nota_returs.total_before_vat',
                                    'tx_nota_returs.vat_val',
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
                                    $totalDPP+= $qNR->total_before_vat;
                                    $totalPPN+= (($qNR->total_before_vat*$qNR->vat_val)/100);
                                @endphp
                                <tr>
                                    <td style="text-align: center;border-left:1px black solid;">{{ ($nota_retur_date!=$qNR->nota_retur_date)?date_format(date_create($qNR->nota_retur_date),"d/m/Y"):'' }}</td>
                                    <td style="text-align: center;">{{ ($nota_retur_no!=$qNR->nota_retur_no)?$qNR->nota_retur_no:'' }}</td>
                                    <td>{{ ($cust_name!=$qNR->cust_name)?$qNR->cust_name:'' }}</td>
                                    <td style="text-align: right;">{{ number_format($qNR->total_before_vat,0,'.','') }}</td>
                                    <td style="text-align: right;color:red;">{{ number_format((($qNR->total_before_vat*$qNR->vat_val)/100),0,'.','') }}</td>
                                    <td style="text-align: right;">
                                        @php
                                            $totAVG=0;
                                            $customer_doc_no='';
                                            $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_sales_orders as tx_sp','tx_sop.order_id','=','tx_sp.id')
                                            ->select(
                                                'tx_nota_retur_parts.part_id',
                                                'tx_nota_retur_parts.qty_retur',
                                                'tx_nota_retur_parts.updated_at as updatedat',
                                                'tx_sp.customer_doc_no',
                                                'tx_sop.last_avg_cost',
                                            )
                                            ->where([
                                                'tx_nota_retur_parts.nota_retur_id'=>$qNR->nr_id,
                                                'tx_nota_retur_parts.active'=>'Y',
                                            ])
                                            ->get()
                                        @endphp
                                        @foreach ($qNotaReturPart as $qNRp)
                                            @php
                                                $totAVG+=($qNRp->last_avg_cost*$qNRp->qty_retur);
                                            @endphp
                                        @endforeach
                                        {{ number_format($totAVG,0,'.','') }}
                                        @php
                                            $totalAVG+= $totAVG;
                                        @endphp
                                    </td>
                                    <td>{{ $qNR->faktur_no }}</td>
                                    <td style="text-align: center;border-right:1px black solid;">{{ $qNR->initial }}</td>
                                </tr>
                                @php
                                    $nota_retur_no = $qNR->nota_retur_no;
                                    $nota_retur_date = $qNR->nota_retur_date;
                                    $cust_name = $qNR->cust_name;
                                @endphp
                            @endforeach
                        @endif

                        {{-- non tax --}}
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N')
                            @php
                                $qRetur = \App\Models\Tx_nota_retur_non_tax::leftJoin('mst_customers as msc','tx_nota_retur_non_taxes.customer_id','=','msc.id')
                                ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                ->leftJoin('tx_delivery_order_non_taxes as np','tx_nota_retur_non_taxes.delivery_order_id','=','np.id')
                                ->select(
                                    'tx_nota_retur_non_taxes.id as nr_id',
                                    'tx_nota_retur_non_taxes.nota_retur_no',
                                    'tx_nota_retur_non_taxes.nota_retur_date',
                                    'tx_nota_retur_non_taxes.total_price',
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
                                    $totalDPP += $qRE->total_price;
                                    $totalPPN += 0;
                                @endphp
                                <tr>
                                    <td style="text-align: center;border-left:1px black solid;">{{ ($nota_retur_date!=$qRE->nota_retur_date)?date_format(date_create($qRE->nota_retur_date),"d/m/Y"):'' }}</td>
                                    <td style="text-align: center;">{{ ($nota_retur_no!=$qRE->nota_retur_no)?$qRE->nota_retur_no:'' }}</td>
                                    <td>{{ ($cust_name!=$qRE->cust_name)?$qRE->cust_name:'' }}</td>
                                    <td style="text-align: right;">{{ number_format($qRE->total_price,0,'.','') }}</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;">
                                        @php
                                            $totAVG=0;
                                            $customer_doc_no='';
                                            $qReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                            ->leftJoin('tx_surat_jalans as tx_sj','tx_sjp.surat_jalan_id','=','tx_sj.id')
                                            ->select(
                                                'tx_nota_retur_part_non_taxes.part_id',
                                                'tx_nota_retur_part_non_taxes.qty_retur',
                                                'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                                'tx_sj.surat_jalan_no',
                                                'tx_sjp.last_avg_cost',
                                            )
                                            ->where([
                                                'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qRE->nr_id,
                                                'tx_nota_retur_part_non_taxes.active'=>'Y',
                                            ])
                                            ->get();
                                        @endphp
                                        @foreach ($qReturPart as $qREp)
                                            @php
                                                $totAVG+=($qREp->last_avg_cost*$qREp->qty_retur);
                                            @endphp
                                        @endforeach
                                        {{ number_format($totAVG,0,'.','') }}
                                        @php
                                            $totalAVG+= $totAVG;
                                        @endphp
                                    </td>
                                    <td>{{ $qRE->faktur_no }}</td>
                                    <td style="text-align: center;border-right:1px black solid;">{{ $qRE->initial }}</td>
                                </tr>
                                @php
                                    $nota_retur_no = $qRE->nota_retur_no;
                                    $nota_retur_date = $qRE->nota_retur_date;
                                    $cust_name = $qRE->cust_name;
                                @endphp
                            @endforeach
                        @endif
                        <tr>
                            <td style="border-left:1px black solid;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px black solid;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px black solid;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalDPP,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalPPN,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalAVG,0,'.','') }}</td>
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
