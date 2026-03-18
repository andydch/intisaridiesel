<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesActualVsTargetPerBranch</title>
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
                    $totCols = $month+2;
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
                        <th colspan="{{ $totCols }}">Sales Actual vs Target Per Branch {{ $period_year }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format(date_add(now(),date_interval_create_from_date_string(env('WAKTU_ID',7)." hours")), 'd/m/Y') }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
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
                        <th style="text-align: center;background-color:#daeef3;border:1px solid black;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight: bold;border-left:1px solid black;">{{ $branch->name }}</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td>&nbsp;</td>
                            @endfor
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">SALES TARGET</td>
                            @php
                                $target = 0;
                                $salesTargets = \App\Models\Mst_branch_target_detail::where([
                                    'year_per_branch'=>$period_year,
                                    'branch_id' => $branch->id,
                                    'active'=>'Y',
                                ])
                                ->first();
                                if($salesTargets){
                                    $target = $salesTargets->sales_target_per_branch;
                                }
                            @endphp
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: right;">{{ number_format(($target/12),0,'.','') }}</td>
                            @endfor
                            <td style="text-align: right;border-right:1px solid black;">{{ number_format($target,0,'.','') }}</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">SALES ACTUAL</td>
                            @php
                                $totalSalesActual = 0;
                                $arrSalesActual = [0,0,0,0,0,0,0,0,0,0,0,0];
                            @endphp
                            @for ($i=1;$i<=$month;$i++)
                                @php
                                    $all_sales = 0;
                                    $total_before_vat = 0;
                                    $total_price = 0;

                                    // faktur - begin
                                    $total_before_vat = \App\Models\Tx_delivery_order::leftJoin('mst_customers as mst_c','tx_delivery_orders.customer_id','=','mst_c.id')
                                    ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                    ->whereRaw('tx_delivery_orders.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('month(tx_delivery_orders.delivery_order_date)='.$i)
                                    ->whereRaw('year(tx_delivery_orders.delivery_order_date)='.$period_year)
                                    ->where([
                                        'tx_delivery_orders.active' => 'Y',
                                        'usr_s.branch_id' => $branch->id,
                                    ])
                                    ->sum('tx_delivery_orders.total_before_vat');
                                    // faktur - end

                                    // nota retur - begin
                                    $total_before_vat_nr = \App\Models\Tx_nota_retur::whereRaw('tx_nota_returs.nota_retur_no NOT LIKE \'%Draft%\'')
                                    ->whereIn('tx_nota_returs.delivery_order_id', function ($q) use($i,$period_year,$branch){
                                        $q->select('tx_do.id')
                                        ->from('tx_delivery_orders as tx_do')
                                        ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('month(tx_do.delivery_order_date)='.$i)
                                        ->whereRaw('year(tx_do.delivery_order_date)='.$period_year)
                                        ->where([
                                            'tx_do.active' => 'Y',
                                            'usr_s.branch_id' => $branch->id,
                                        ]);
                                    })
                                    ->whereRaw('tx_nota_returs.approved_by IS NOT null')
                                    ->where([
                                        'tx_nota_returs.active'=>'Y',
                                    ])
                                    ->sum('tx_nota_returs.total_before_vat');
                                    // nota retur - end

                                    // nota penjualan - begin
                                    $total_price = \App\Models\Tx_delivery_order_non_tax::leftJoin('mst_customers as mst_c','tx_delivery_order_non_taxes.customer_id','=','mst_c.id')
                                    ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                    ->whereRaw('tx_delivery_order_non_taxes.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('month(tx_delivery_order_non_taxes.delivery_order_date)='.$i)
                                    ->whereRaw('year(tx_delivery_order_non_taxes.delivery_order_date)='.$period_year)
                                    ->where([
                                        'tx_delivery_order_non_taxes.active' => 'Y',
                                        'usr_s.branch_id' => $branch->id,
                                    ])
                                    ->sum('tx_delivery_order_non_taxes.total_price');
                                    // nota penjualan - end

                                    // retur - begin
                                    $total_price_re = \App\Models\Tx_nota_retur_non_tax::where('nota_retur_no','NOT LIKE','%Draft%')
                                    ->whereIn('delivery_order_id', function ($q) use($i,$period_year,$branch){
                                        $q->select('tx_do.id')
                                        ->from('tx_delivery_order_non_taxes as tx_do')
                                        ->leftJoin('mst_customers as mst_c','tx_do.customer_id','=','mst_c.id')
                                        ->leftJoin('userdetails as usr_s','mst_c.salesman_id','=','usr_s.user_id')
                                        ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('month(tx_do.delivery_order_date)='.$i)
                                        ->whereRaw('year(tx_do.delivery_order_date)='.$period_year)
                                        ->where([
                                            'tx_do.active' => 'Y',
                                            'usr_s.branch_id' => $branch->id,
                                        ]);
                                    })
                                    ->whereRaw('approved_by IS NOT null')
                                    ->where([
                                        'active'=>'Y',
                                    ])
                                    ->sum('total_price');
                                    // retur - end

                                    $all_sales = ($total_before_vat+$total_price)-($total_before_vat_nr+$total_price_re);
                                    $totalSalesActual += $all_sales;
                                    $arrSalesActual[$i-1] = $all_sales;
                                @endphp
                                <td style="text-align: right;">{{ number_format(($all_sales),0,'.','') }}</td>
                            @endfor
                            <td style="text-align: right;border-right:1px solid black;">{{ number_format(($totalSalesActual),0,'.','') }}</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">ACHIEVEMENT</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: right;">{{ number_format(($target>0)?($arrSalesActual[$i-1]/($target/12))*100:0,0,'.','') }}%</td>
                            @endfor
                            <td style="text-align: right;border-right:1px solid black;">{{ number_format(($target>0)?($totalSalesActual/($target))*100:0,0,'.','') }}%</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">END STOCK</td>
                            @php
                                $arrActualStock = [0,0,0,0,0,0,0,0,0,0,0,0];
                            @endphp
                            @for ($i=1;$i<=$month;$i++)
                                @php
                                    $actualStock = 0;
                                    $invStock = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                        'branch_id' => $branch->id,
                                        'rpt_month' => $i,
                                        'rpt_year' => $period_year,
                                        'active' => 'Y',
                                    ])
                                    ->first();
                                    if($invStock){
                                        $actualStock = $invStock->actual_stock;
                                    }
                                    $arrActualStock[$i-1] = $actualStock;
                                @endphp
                                <td style="text-align: right;">{{ number_format($actualStock,0,'.','') }}</td>
                            @endfor
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;">AGE OF STOCK</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="text-align: right;">{{ number_format(($arrSalesActual[$i-1]>0)?($arrActualStock[$i-1]/$arrSalesActual[$i-1]):0,2,'.','') }}</td>
                            @endfor
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        <tr>
                            <td style="border-bottom:1px solid black;border-left:1px solid black;">&nbsp;</td>
                            @for ($i=1;$i<=$month;$i++)
                                <td style="border-bottom:1px solid black;">&nbsp;</td>
                            @endfor
                            <td style="border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
