<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SO_SJ</title>
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
                        <th colspan="{{ $totCols }}">REPORT PENJUALAN PER CUSTOMER PER PARTS NO (SO - SJ)</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">CUSTOMER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">QTY</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $lokal_input = ($lokal_input=='x'?'P':$lokal_input);

                        $custName = '';
                        $custInit = '';
                        $totDPP = 0;
                        $totAVG = 0;

                        $customers = \App\Models\Mst_customer::when($customer_id>0, function($q) use($customer_id) {
                            $q->where([
                                'id'=>$customer_id,
                            ]);
                        })
                        ->where([
                            'active'=>'Y',
                        ])
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($customers as $customer)
                        @php
                            $totalDPPperCust = 0;
                            $totalAVGperCust = 0;
                            $howManyParts = 0;

                            // tampilkan semua part
                            $qParts = \App\Models\V_sales_per_part_so_sj::select(
                                'part_id',
                                'part_number',
                                'part_name',
                            )
                            ->where([
                                'customer_id'=>$customer->id,
                            ])
                            ->whereRaw('sales_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('sales_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->when(strtoupper($lokal_input)=='P', function($q) {
                                $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                            })
                            ->when(strtoupper($lokal_input)=='N', function($q) {
                                $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                            })
                            ->groupBy('part_id')
                            ->groupBy('part_number')
                            ->groupBy('part_name')
                            ->orderBy('part_name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qParts as $part)
                            <tr>
                                <td style="font-weight: bold;border-left:1px solid black;border-left: 1px solid black;">
                                    {{ ($custInit.$custName!=$customer->customer_unique_code.$customer->name)?$customer->customer_unique_code.' - '.$customer->name:'' }}
                                </td>
                                <td>
                                    @php
                                        $partNumber = $part->part_number;
                                        if(strlen($partNumber)<11){
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                        }else{
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                        }
                                    @endphp
                                    {{ $partNumber }}
                                </td>
                                <td>{{ $part->part_name }}</td>
                                <td style="text-align: right;">
                                    @php
                                        $qQty = \App\Models\V_sales_per_part_so_sj::selectRaw('SUM(qty) as qty')
                                        ->where([
                                            'part_id'=>$part->part_id,
                                        ])
                                        ->whereRaw('sales_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('sales_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->first();
                                        $sumQty = $qQty?$qQty->qty:0;
                                    @endphp
                                    {{ $sumQty }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $qDpp = \App\Models\V_sales_per_part_so_sj::selectRaw('SUM(qty*last_price) as price')
                                        ->where([
                                            'part_id'=>$part->part_id,
                                        ])
                                        ->whereRaw('sales_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('sales_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->first();
                                        $sumPrice = $qDpp?$qDpp->price:0;
                                    @endphp
                                    {{ number_format($sumPrice,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $qAvg = \App\Models\V_sales_per_part_so_sj::selectRaw('SUM(qty*last_avg_cost) as avg')
                                        ->where([
                                            'part_id'=>$part->part_id,
                                        ])
                                        ->whereRaw('sales_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('sales_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->first();
                                        $sumAvg = $qAvg?$qAvg->avg:0;
                                    @endphp
                                    {{ number_format($sumAvg,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $gp = $sumPrice!=0?(($sumPrice-$sumAvg)/$sumPrice)*100:0;
                                    @endphp
                                    {{ number_format($gp,0,'.','') }}%
                                </td>
                                <td style="text-align: center;border-right: 1px solid black;">
                                    @php
                                        $qSalesman = \App\Models\Userdetail::select('initial as salesman_initial')
                                        ->where([
                                            'user_id'=>$customer->salesman_id,
                                        ])
                                        ->first();
                                    @endphp
                                    {{ $qSalesman?$qSalesman->salesman_initial:'' }}
                                </td>
                            </tr>
                            @php
                                $totalDPPperCust += $sumPrice;
                                $totalAVGperCust += $sumAvg;
                            @endphp
                            @if ($custInit.$custName!=$customer->customer_unique_code.$customer->name)
                                @php
                                    $custName = $customer->name;
                                    $custInit = $customer->customer_unique_code;
                                @endphp
                            @endif
                        @endforeach
                        @php
                            $totDPP += $totalDPPperCust;
                            $totAVG += $totalAVGperCust;
                        @endphp
                        @if ($totalDPPperCust>0 || $totalAVGperCust>0)
                            <tr>
                                <td style="border-left: 1px solid black;">&nbsp;</td>
                                <td style="font-weight: 700;">Sub Total</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPperCust,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalAVGperCust,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ $totalDPPperCust>0?number_format((($totalDPPperCust-$totalAVGperCust)/$totalDPPperCust)*100,0,'.',''):0 }}%</td>
                                <td style="border-right: 1px solid black;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="border-left: 1px solid black;">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-right: 1px solid black;">&nbsp;</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="font-weight: 700;border-top: 1px solid black;border-bottom: 1px solid black;">Total</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;">&nbsp;</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight:700;">{{ number_format($totDPP,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight:700;">{{ number_format($totAVG,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight:700;">{{ $totDPP>0?number_format((($totDPP-$totAVG)/$totDPP)*100,0,'.',''):0 }}%</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
