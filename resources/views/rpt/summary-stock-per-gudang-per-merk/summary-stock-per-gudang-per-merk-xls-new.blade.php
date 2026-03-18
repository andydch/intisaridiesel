<!doctype html>
<html lang="en">
    <head>
            <!-- Required meta tags -->
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
                integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <title>Summ Stock PerBranch-PerMerk</title>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th colspan="4">{{ $company->name }}</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:center;">{{ $title }}</th>
                </tr>
                @php
                    $perDate = explode("-",$date);
                    $dateToShow = date_create($perDate[2].'-'.$perDate[1].'-'.$perDate[0]);
                @endphp
                <tr>
                    <th>PER DATE</th>
                    <th>{{ date_format($dateToShow, 'd-M-Y') }}</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th>BRANCH</th>
                    <th>BRAND</th>
                    <th>TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                    <th>TOTAL FINAL PRICE ({{ $qCurrency->string_val }})</th>
                </tr>
            </thead>
            <tbody>
            @php
                $branches = \App\Models\Mst_branch::where('active','=','Y')
                ->when($branch_id!='0', function($q) use($branch_id) {
                    $q->where('id','=',$branch_id);
                })
                ->orderBy('name','ASC')
                ->get();

                $grandTotalAVG=0;
                $grandTotalFinalPrice=0;
                $branch_name = '';
            @endphp
            @foreach ($branches as $branch)
                @php
                    $row=0;
                    $brands=\App\Models\Mst_global::where([
                        'data_cat' => 'brand',
                        'active' => 'Y'
                    ])
                    ->when($brand_id!='0', function($q) use($brand_id) {
                        $q->where('id','=',$brand_id);
                    })
                    ->orderBy('title_ind', 'ASC')
                    ->get();

                    $totalAVG=0;
                    $totalFinalPrice=0;
                @endphp
                @foreach ($brands as $brand)
                    @php
                        // $rpts = \App\Models\Log_tx_qty_part::leftJoin('mst_parts as msp','log_tx_qty_parts.part_id','=','msp.id')
                        // ->selectRaw('SUM(log_tx_qty_parts.qty*log_tx_qty_parts.avg_cost) as total_avg_cost,SUM(log_tx_qty_parts.qty*msp.final_price) as total_final_price')
                        // ->where('log_tx_qty_parts.qty','>',0)
                        // ->whereRaw('DATE_ADD(log_tx_qty_parts.updated_at, INTERVAL 7 HOUR)<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                        // ->where([
                        //     'log_tx_qty_parts.branch_id' => $branch->id,
                        //     'msp.brand_id' => $brand->id,
                        //     'msp.active' => 'Y',
                        // ])
                        // ->orderBy('log_tx_qty_parts.updated_at','DESC')
                        // ->first();

                        $rpts = \App\Models\V_last_avg_cost_and_qty::leftJoin('mst_parts as msp','v_last_avg_cost_and_qty.part_id','=','msp.id')
                        ->selectRaw('SUM(v_last_avg_cost_and_qty.qty*v_last_avg_cost_and_qty.avg_cost) as total_avg_cost,SUM(v_last_avg_cost_and_qty.qty*msp.final_price) as total_final_price')
                        // ->select(
                        //     'v_last_avg_cost_and_qty.qty as qty',
                        //     'v_last_avg_cost_and_qty.avg_cost as avg_cost',
                        // )
                        ->whereRaw('DATE_ADD(v_last_avg_cost_and_qty.updated_at, INTERVAL 7 HOUR)<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                        ->where([
                            // 'v_last_avg_cost_and_qty.part_id' => $part->id,
                            'v_last_avg_cost_and_qty.branch_id' => $branch->id,
                            'msp.brand_id' => $brand->id,
                            'msp.active' => 'Y',
                        ])
                        ->orderBy('v_last_avg_cost_and_qty.updated_at','DESC')
                        ->first();
                    @endphp
                    @if ($rpts)
                        @if ($rpts->total_avg_cost>0 || $rpts->total_final_price>0)
                            @if ($row==0)
                            <tr>
                                <th style="font-weight: bold;">{{ ($branch_name!=$branch->name?strtoupper($branch->name):'') }}</th>
                                <td style="font-weight: bold;">{{ strtoupper($brand->title_ind) }}</td>
                                <td style="text-align: right;">{{ number_format($rpts->total_avg_cost,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format($rpts->total_final_price,0,'.','') }}</td>
                            </tr>
                            @else
                            <tr>
                                <td style="font-weight: bold;">{{ ($branch_name!=$branch->name?strtoupper($branch->name):'') }}</td>
                                <td style="font-weight: bold;">{{ strtoupper($brand->title_ind) }}</td>
                                <td style="text-align: right;">{{ number_format($rpts->total_avg_cost,0,'.','') }}</td>
                                <td style="text-align: right;">{{ number_format($rpts->total_final_price,0,'.','') }}</td>
                            </tr>
                            @endif
                            @php
                                $branch_name = $branch->name;
                            @endphp
                        @endif
                        @php
                            $totalAVG+=$rpts->total_avg_cost;
                            $totalFinalPrice+=$rpts->total_final_price;
                        @endphp
                    @endif
                    @php
                        $row+=1;
                    @endphp
                @endforeach
                @if ($totalAVG>0 || $totalFinalPrice>0)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <th style="text-align: right;font-weight: bold;">{{ number_format($totalAVG,0,'.','') }}</th>
                        <th style="text-align: right;font-weight: bold;">{{ number_format($totalFinalPrice,0,'.','') }}</th>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endif
                @php
                    $grandTotalAVG+=$totalAVG;
                    $grandTotalFinalPrice+=$totalFinalPrice;

                    $branch_name = $branch->name;
                @endphp
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>&nbsp;</th>
                    <th style="font-weight: bold;">GRAND TOTAL</th>
                    <th style="text-align: right;font-weight: bold;">{{ number_format($grandTotalAVG,0,'.','') }}</th>
                    <th style="text-align: right;font-weight: bold;">{{ number_format($grandTotalFinalPrice,0,'.','') }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
