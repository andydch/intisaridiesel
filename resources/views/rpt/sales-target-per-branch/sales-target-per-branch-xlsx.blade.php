<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesTargetPerBranch</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = $month+3;
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
                        <th colspan="{{ $totCols }}">Sales Target Per Branch {{ $period_year }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                        @for ($i=1;$i<=$month;$i++)
                            @switch($i)
                                @case(1)
                                    @php
                                        $monthNm = 'JAN';
                                    @endphp
                                    @break
                                @case(2)
                                    @php
                                        $monthNm = 'FEB';
                                    @endphp
                                    @break
                                @case(3)
                                    @php
                                        $monthNm = 'MAR';
                                    @endphp
                                    @break
                                @case(4)
                                    @php
                                        $monthNm = 'APR';
                                    @endphp
                                    @break
                                @case(5)
                                    @php
                                        $monthNm = 'MAY';
                                    @endphp
                                    @break
                                @case(6)
                                    @php
                                        $monthNm = 'JUN';
                                    @endphp
                                    @break
                                @case(7)
                                    @php
                                        $monthNm = 'JUL';
                                    @endphp
                                    @break
                                @case(8)
                                    @php
                                        $monthNm = 'AUG';
                                    @endphp
                                    @break
                                @case(9)
                                    @php
                                        $monthNm = 'SEP';
                                    @endphp
                                    @break
                                @case(10)
                                    @php
                                        $monthNm = 'OCT';
                                    @endphp
                                    @break
                                @case(11)
                                    @php
                                        $monthNm = 'NOP';
                                    @endphp
                                    @break
                                @case(12)
                                    @php
                                        $monthNm = 'DEC';
                                    @endphp
                                    @break
                                @default
                                    @php
                                        $monthNm = '';
                                    @endphp
                            @endswitch
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">{{ $monthNm }}</th>
                        @endfor
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALES LAST YEAR</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totAllSalesPerMonth = [0,0,0,0,0,0,0,0,0,0,0,0];
                        $totAllSalesPerYear = 0;
                        $totAllSalesLastYear = 0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight: bold;border-left:1px solid black;">{{ $branch->name }}</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: center;">&nbsp;</td>
                            @endfor
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $totSalesPerMonth = [0,0,0,0,0,0,0,0,0,0,0,0];
                            $totSalesPerYear = 0;
                            $totSalesLastYear = 0;

                            $salesman = \App\Models\Userdetail::leftJoin('users','userdetails.user_id','=','users.id')
                            ->select(
                                'users.name',
                                'userdetails.user_id',
                                )
                            ->where([
                                'userdetails.branch_id' => $branch->id,
                                'userdetails.active' => 'Y',
                            ])
                            ->orderBy('users.name','ASC')
                            ->get();
                        @endphp
                        @foreach ($salesman as $sales)
                            <tr>
                                <td style="border-left:1px solid black;">{{ $sales->name }}</td>
                                @php
                                    $target_per_month = 0;
                                    $target_per_year = 0;
                                    $sales_target = \App\Models\Mst_salesman_target_detail::where([
                                        'salesman_id' => $sales->user_id,
                                        'year_per_branch' => $period_year,
                                        'active' => 'Y',
                                    ])
                                    ->first();
                                    if($sales_target){
                                        $target_per_month = $sales_target->sales_target_per_branch/12;
                                        $target_per_year = $sales_target->sales_target_per_branch;
                                    }
                                    $totSalesPerYear += $target_per_year;
                                @endphp
                                @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $totSalesPerMonth[$i-1] += round($target_per_month);
                                    @endphp
                                    <td style="text-align: right;">{{ number_format($target_per_month,0,'.','') }}</td>
                                @endfor
                                <td style="text-align: right;">{{ number_format($target_per_year,0,'.','') }}</td>
                                @php
                                    // faktur - begin
                                    $fk = \App\Models\Tx_delivery_order::where('delivery_order_no','NOT LIKE','%Draft%')
                                    ->whereRaw('year(DATE_ADD(delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.($period_year-1))
                                    ->where([
                                        'active' => 'Y',
                                        'created_by' => $sales->user_id,
                                    ])
                                    ->sum('total_before_vat');
                                    // faktur - end

                                    // nota penjualan - begin
                                    $np = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                    ->whereRaw('year(DATE_ADD(delivery_order_date, INTERVAL '.env('WAKTU_ID',7).' HOUR))='.($period_year-1))
                                    ->where([
                                        'active' => 'Y',
                                        'created_by' => $sales->user_id,
                                    ])
                                    ->sum('total_price');
                                    // nota penjualan - end

                                    $all_sales = $fk+$np;
                                    $totSalesLastYear += $all_sales;
                                @endphp
                                <td style="text-align: right;border-right:1px solid black;">{{ number_format($all_sales,0,'.','') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;border-top:1px solid black;">SUB TOTAL</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: right;border-top:1px solid black;">{{ number_format($totSalesPerMonth[$i-1],0,'.','') }}</td>
                                @php
                                    $totAllSalesPerMonth[$i-1] += $totSalesPerMonth[$i-1];
                                @endphp
                            @endfor
                            <td style="text-align: right;border-top:1px solid black;">{{ number_format($totSalesPerYear,0,'.','') }}</td>
                            <td style="text-align: right;border-top:1px solid black;border-right:1px solid black;">{{ number_format($totSalesLastYear,0,'.','') }}</td>
                            @php
                                $totAllSalesPerYear += $totSalesPerYear;
                                $totAllSalesLastYear += $totSalesLastYear;
                            @endphp
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td>&nbsp;</td>
                            @endfor
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold;border-left:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        @for ($i=1;$i<=$month;$i++)
                            <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;">{{ number_format($totAllSalesPerMonth[$i-1],0,'.','') }}</td>
                        @endfor
                        <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;">{{ number_format($totAllSalesPerYear,0,'.','') }}</td>
                        <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;border-right:1px solid black;">{{ number_format($totAllSalesLastYear,0,'.','') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
