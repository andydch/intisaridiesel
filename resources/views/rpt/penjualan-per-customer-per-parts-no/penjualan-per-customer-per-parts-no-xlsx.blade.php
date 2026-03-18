<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>FK_NP</title>
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
                        <th colspan="{{ $totCols }}">REPORT PENJUALAN PER CUSTOMER PER PARTS NO (FK - NP)</th>
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
                            $qParts = \App\Models\V_sales_per_part::select(
                                'part_id',
                                'part_number',
                                'part_name',
                            )
                            ->where([
                                'customer_id'=>$customer->id,
                            ])
                            ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
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
                                        // total QTY sebelum ada retur - faktur dan nota penjualan
                                        $sumQty = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_FAKTUR').'%\' OR module_no LIKE \'%'.env('P_NOTA_PENJUALAN').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->sum('qty');

                                        // total QTY dari retur PPN dan retur Non PPN
                                        $sumReturQty = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_NOTA_RETUR').'%\' OR module_no LIKE \'%'.env('P_RETUR').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->sum('qty');

                                        // total QTY dari retur PPN dan retur Non PPN dengan SO sebelum pilihan tanggal mulai
                                        $sumReturBeforeSOdateQty = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_NOTA_RETUR').'%\' OR module_no LIKE \'%'.env('P_RETUR').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('sales_order_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->sum('qty');
                                    @endphp
                                    {{ $sumQty-$sumReturQty-$sumReturBeforeSOdateQty }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $totDPPperPart = 0;
                                        $totDPPperPartRetur = 0;
                                        $totDPPperPartReturBeforeSOdate = 0;
                                        $totAVGperPart = 0;
                                        $totAVGperPartRetur = 0;
                                        $totAVGperPartReturBeforeSOdate = 0;

                                        // total DPP sebelum ada retur - faktur dan nota penjualan
                                        $qDPP = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_FAKTUR').'%\' OR module_no LIKE \'%'.env('P_NOTA_PENJUALAN').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->get();

                                        // total DPP dari retur PPN dan retur Non PPN
                                        $qDPP_ReturQty = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_NOTA_RETUR').'%\' OR module_no LIKE \'%'.env('P_RETUR').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->get();

                                        // total DPP dari retur PPN dan retur Non PPN dengan SO sebelum pilihan tanggal mulai
                                        $qDPP_ReturBeforeSOdateQty = \App\Models\V_sales_per_part::where([
                                            'part_id'=>$part->part_id,
                                            'customer_id'=>$customer->id,
                                        ])
                                        ->where(function($q){
                                            $q->whereRaw('module_no LIKE \'%'.env('P_NOTA_RETUR').'%\' OR module_no LIKE \'%'.env('P_RETUR').'%\'');
                                        })
                                        ->whereRaw('order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('sales_order_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->when(strtoupper($lokal_input)=='P', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'P\'');
                                        })
                                        ->when(strtoupper($lokal_input)=='N', function($q) {
                                            $q->whereRaw('CONVERT(tax_code USING utf8)=\'N\'');
                                        })
                                        ->get();
                                    @endphp
                                    @foreach ($qDPP as $q)
                                        @php
                                            $totDPPperPart += ($q->qty*$q->final_price);
                                            $totAVGperPart += ($q->qty*$q->last_avg_cost);
                                        @endphp
                                    @endforeach
                                    @foreach ($qDPP_ReturQty as $q)
                                        @php
                                            $totDPPperPartRetur += ($q->qty*$q->final_price);
                                            $totAVGperPartRetur += ($q->qty*$q->last_avg_cost);
                                        @endphp
                                    @endforeach
                                    @foreach ($qDPP_ReturBeforeSOdateQty as $q)
                                        @php
                                            $totDPPperPartReturBeforeSOdate += ($q->qty*$q->final_price);
                                            $totAVGperPartReturBeforeSOdate += ($q->qty*$q->last_avg_cost);
                                        @endphp
                                    @endforeach
                                    {{ number_format(($totDPPperPart-$totDPPperPartRetur-$totDPPperPartReturBeforeSOdate),0,'.','') }}
                                    @php
                                        $totalDPPperCust += ($totDPPperPart-$totDPPperPartRetur-$totDPPperPartReturBeforeSOdate);
                                    @endphp
                                </td>
                                <td style="text-align: right;">
                                    {{ number_format(($totAVGperPart-$totAVGperPartRetur-$totAVGperPartReturBeforeSOdate),0,'.','') }}
                                    @php
                                        $totalAVGperCust += ($totAVGperPart-$totAVGperPartRetur-$totAVGperPartReturBeforeSOdate);
                                    @endphp
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $gp = ($totDPPperPart-$totDPPperPartRetur-$totDPPperPartReturBeforeSOdate)!=0?((($totDPPperPart-$totDPPperPartRetur-$totDPPperPartReturBeforeSOdate)-
                                            ($totAVGperPart-$totAVGperPartRetur-$totAVGperPartReturBeforeSOdate))/($totDPPperPart-$totDPPperPartRetur-$totDPPperPartReturBeforeSOdate))*100:0;
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
                            @if ($custInit.$custName!=$customer->customer_unique_code.$customer->name)
                                @php
                                    $custName = $customer->name;
                                    $custInit = $customer->customer_unique_code;
                                    $howManyParts += 1;
                                @endphp
                            @endif
                        @endforeach
                        @if ($totalDPPperCust!=0 || $howManyParts>0)
                            @php
                                $totDPP += $totalDPPperCust;
                                $totAVG += $totalAVGperCust;
                            @endphp
                            <tr>
                                <td style="border-left: 1px solid black;">&nbsp;</td>
                                <td style="font-weight: 700;">Sub Total</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPperCust,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalAVGperCust,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ $totalDPPperCust!=0?number_format((($totalDPPperCust-$totalAVGperCust)/$totalDPPperCust)*100,0,'.',''):0 }}%</td>
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
