<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesTargetCust</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    if ($period_year<date_format($date,"Y")){
                        $month = 12;
                    }
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
                        <th colspan="{{ $totCols }}">Sales Target Customer Per Branch {{ $period_year }}</th>
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
                        $totSubTotalAllMonthAllBranch = [0,0,0,0,0,0,0,0,0,0,0,0];
                        $s_sales_total_all_branch_val = 0;
                        $s_sales_total_all_branch_lastyear_val = 0;

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
                            $totSubTotalPerMonthPerBranch = [0,0,0,0,0,0,0,0,0,0,0,0];
                            $s_sales_total_per_branch_val = 0;
                            $s_sales_total_per_branch_lastyear_val = 0;

                            $customers = \App\Models\Mst_customer::leftJoin('userdetails AS user_d','mst_customers.salesman_id','=','user_d.user_id')
                            ->leftJoin('users','user_d.user_id','=','users.id')
                            ->select(
                                'mst_customers.id AS cust_id',
                                'mst_customers.name AS cust_name',
                                'users.id AS salesman_id',
                                'users.name AS salesman_name',
                                )
                            ->where([
                                'mst_customers.active' => 'Y',
                                'user_d.branch_id' => $branch->id,
                                'user_d.is_salesman' => 'Y',
                                'user_d.active' => 'Y',
                            ])
                            ->get();
                        @endphp
                        @foreach ($customers as $customer)
                            <tr>
                                <td style="border-left:1px solid black;">{{ $customer->cust_name }}</td>
                                @php
                                    $s_target_val = 0;
                                    $s_target_total_val = 0;
                                    $salesman_target = \App\Models\Mst_salesman_target_detail::leftJoin('mst_salesman_targets AS m_target','mst_salesman_target_details.salesman_target_id','=','m_target.id')
                                    ->where([
                                        'mst_salesman_target_details.salesman_id' => $customer->salesman_id,
                                        'mst_salesman_target_details.year_per_branch' => $period_year,
                                        'mst_salesman_target_details.active' => 'Y',
                                        'm_target.year' => $period_year,
                                        'm_target.branch_id' => $branch->id,
                                        'm_target.active' => 'Y',
                                    ])
                                    ->first();
                                    if($salesman_target){
                                        $s_target_val = $salesman_target->sales_target_per_branch/12;
                                    }
                                @endphp
                                @for ($i=1;$i<=$month;$i++)
                                    @php
                                        $s_target_total_val += $s_target_val;
                                        $totSubTotalPerMonthPerBranch[$i-1] += $s_target_val;
                                    @endphp
                                    <td style="text-align: right;">{{ number_format($s_target_val,0,'.','') }}</td>
                                @endfor
                                <td style="text-align: right;">{{ number_format($s_target_total_val,0,'.','') }}</td>
                                @php
                                    // faktur - begin
                                    $fk = \App\Models\Tx_delivery_order::where('delivery_order_no','NOT LIKE','%Draft%')
                                    ->whereRaw('year(delivery_order_date)='.($period_year-1))
                                    ->where([
                                        'customer_id' => $customer->cust_id,
                                        'active' => 'Y',
                                    ])
                                    ->sum('total_before_vat');
                                    // faktur - end

                                    // nota penjualan - begin
                                    $np = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                    ->whereRaw('year(delivery_order_date)='.($period_year-1))
                                    ->where([
                                        'customer_id' => $customer->cust_id,
                                        'active' => 'Y',
                                    ])
                                    ->sum('total_price');
                                    // nota penjualan - end

                                    $all_sales = $fk+$np;
                                    $s_sales_total_per_branch_lastyear_val += $all_sales;
                                @endphp
                                <td style="text-align: right;border-right:1px solid black;">{{ number_format($all_sales,0,'.','') }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;border-top:1px solid black;">SUB TOTAL</td>
                            @for ($i=1;$i<=$month;$i++)
                                @php
                                    $s_sales_total_per_branch_val += $totSubTotalPerMonthPerBranch[$i-1];
                                    $totSubTotalAllMonthAllBranch[$i-1] += $totSubTotalPerMonthPerBranch[$i-1];
                                    $s_sales_total_all_branch_val += $totSubTotalPerMonthPerBranch[$i-1];
                                    $s_sales_total_all_branch_lastyear_val += $s_sales_total_per_branch_lastyear_val;
                                @endphp
                                <td style="text-align: right;border-top:1px solid black;">{{ number_format($totSubTotalPerMonthPerBranch[$i-1],0,'.','') }}</td>
                            @endfor
                            <td style="text-align: right;border-top:1px solid black;">{{ number_format($s_sales_total_per_branch_val,0,'.','') }}</td>
                            <td style="text-align: right;border-right:1px solid black;border-top:1px solid black;">{{ number_format($s_sales_total_per_branch_lastyear_val,0,'.','') }}</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: center;">&nbsp;</td>
                            @endfor
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: bold;border-bottom:1px solid black;border-left:1px solid black;">TOTAL</td>
                        @for ($i=1;$i<=$month;$i++)
                            <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;">{{ number_format($totSubTotalAllMonthAllBranch[$i-1],0,'.','') }}</td>
                        @endfor
                        <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;">{{ number_format($s_sales_total_all_branch_val,0,'.','') }}</td>
                        <td style="font-weight: bold;text-align: right;border-bottom:1px solid black;border-right:1px solid black;">{{ number_format($s_sales_total_all_branch_lastyear_val,0,'.','') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
