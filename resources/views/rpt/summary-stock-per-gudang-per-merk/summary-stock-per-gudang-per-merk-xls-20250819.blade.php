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
                    {{-- <th>PART</th>
                    <th>QTY</th> --}}
                    <th>TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                    <th>TOTAL FINAL PRICE ({{ $qCurrency->string_val }})</th>
                </tr>
            </thead>
            <tbody>
            @php
                $grandtotal_avg_cost = 0;
                $grandtotal_final_price = 0;

                $branches = \App\Models\Mst_branch::where('active','=','Y')
                ->when($branch_id!='0', function($q) use($branch_id) {
                    $q->where('id','=',$branch_id);
                })
                ->orderBy('name','ASC')
                ->get();

                $branch_name = '';
            @endphp
            @foreach ($branches as $branch)
                @php
                    $total_avg_cost_per_branch = 0;
                    $total_final_price_per_branch = 0;

                    $brands=\App\Models\Mst_global::where([
                        'data_cat' => 'brand',
                        'active' => 'Y'
                    ])
                    ->when($brand_id!='0', function($q) use($brand_id) {
                        $q->where('id','=',$brand_id);
                    })
                    ->orderBy('title_ind', 'ASC')
                    ->get();
                @endphp
                @foreach ($brands as $brand)
                    @php
                        $total_avg_cost = 0;
                        $total_final_price = 0;

                        $qParts = \App\Models\V_last_avg_cost_and_qty::leftJoin('mst_parts as msp','v_last_avg_cost_and_qty.part_id','=','msp.id')
                        ->selectRaw('SUM(v_last_avg_cost_and_qty.qty*v_last_avg_cost_and_qty.avg_cost) as total_avg_cost')
                        ->selectRaw('SUM(v_last_avg_cost_and_qty.qty*msp.final_price) as total_final_price')
                        ->where([
                            'v_last_avg_cost_and_qty.branch_id' => $branch->id,
                            'msp.brand_id' => $brand->id,
                            'msp.active' => 'Y',
                        ])
                        ->whereRaw('DATE_ADD(v_last_avg_cost_and_qty.updated_at, INTERVAL 7 HOUR)<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                        ->whereIn('v_last_avg_cost_and_qty.updated_at', function($query) use ($branch, $brand, $perDate) {
                            $query->selectRaw('MAX(v_last_avg_cost_and_qty.updated_at)')
                            ->from('v_last_avg_cost_and_qty')
                            ->leftJoin('mst_parts as msp2','v_last_avg_cost_and_qty.part_id','=','msp2.id')
                            ->where([
                                'v_last_avg_cost_and_qty.branch_id' => $branch->id,
                                'msp2.brand_id' => $brand->id,
                            ])
                            ->whereRaw('DATE_ADD(v_last_avg_cost_and_qty.updated_at, INTERVAL 7 HOUR)<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                            ->groupBy('v_last_avg_cost_and_qty.part_id')
                            ->groupBy('v_last_avg_cost_and_qty.branch_id');
                        })
                        ->first();
                        if ($qParts) {
                            $total_avg_cost = $qParts->total_avg_cost;
                            $total_final_price = $qParts->total_final_price;
                        }

                        $total_avg_cost_per_branch += $total_avg_cost;
                        $total_final_price_per_branch += $total_final_price;

                        $grandtotal_avg_cost += $total_avg_cost;
                        $grandtotal_final_price += $total_final_price;
                    @endphp
                    <tr>
                        <td>{{ ($branch_name!=$branch->name?strtoupper($branch->name):'') }}</td>
                        <td>{{ strtoupper($brand->title_ind) }}</td>
                        <td>{{ number_format($total_avg_cost,0,'.','') }}</td>
                        <td>{{ number_format($total_final_price,0,'.','') }}</td>
                    </tr>
                    @php
                        $branch_name = $branch->name;
                    @endphp
                @endforeach
                <tr>
                    <td style="font-weight: bold;">TOTAL</td>
                    <td>&nbsp;</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($total_avg_cost_per_branch,0,'.','') }}</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($total_final_price_per_branch,0,'.','') }}</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="font-weight: bold;">GRAND TOTAL</td>
                    <td>&nbsp;</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($grandtotal_avg_cost,0,'.','') }}</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($grandtotal_final_price,0,'.','') }}</td>
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
