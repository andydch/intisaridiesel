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
                    {{-- <th>&nbsp;</th> --}}
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th>BRANCH</th>
                    <th>BRAND</th>
                    <th>TOTAL COST AVG ({{ $qCurrency->string_val }})</th>
                    {{-- <th>TOTAL FINAL PRICE ({{ $qCurrency->string_val }})</th> --}}
                    <th>TOTAL PART NUMBER</th>
                </tr>
            </thead>
            <tbody>
            @php
                $grandtotal_avg_cost = 0;
                $grandtotal_final_price = 0;

                // branch
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

                    // brand
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
                        $part_id = 0;
                        $total_part_per_brand_per_branch = 0;

                        $qParts = \App\Models\Tx_qty_part::leftJoin('mst_parts as pr', 'tx_qty_parts.part_id', '=', 'pr.id')
                        ->select(
                            'tx_qty_parts.part_id',
                            'tx_qty_parts.qty AS last_qty',
                            'pr.part_number',
                            'pr.part_name',
                        )
                        ->addSelect(['last_avg_cost' => \App\Models\V_log_avg_cost::selectRaw('IFNULL(avg_cost,0)')
                            ->whereColumn('part_id', 'tx_qty_parts.part_id')
                            ->whereRaw('updated_at<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                            ->orderBy('updated_at','DESC')          // ambil harga rata2 terbaru dari
                            ->limit(1)                              // data di baris pertama
                        ])
                        // ->addSelect(['last_final_price' => \App\Models\V_sales_all_part::selectRaw('SUM(price*qty) AS tot_price')
                        //     ->whereColumn('part_id', 'tx_qty_parts.part_id')
                        //     ->where('branch_id', $branch->id)
                        //     ->whereRaw('updated_at<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                        //     // ->orderBy('updated_at','DESC')          // ambil harga terbaru dari
                        //     // ->limit(1)                              // data di baris pertama
                        // ])
                        ->addSelect(['in_transit_qty' => \App\Models\Tx_stock_transfer_part::selectRaw('IFNULL(SUM(tx_stock_transfer_parts.qty),0)')
                            ->leftJoin('tx_stock_transfers as tx_stock','tx_stock_transfer_parts.stock_transfer_id','=','tx_stock.id')
                            ->whereColumn('tx_stock_transfer_parts.part_id','tx_qty_parts.part_id')
                            ->where('tx_stock.branch_to_id', $branch->id)
                            ->where('tx_stock_transfer_parts.active','=','Y')
                            ->where('tx_stock.approved_by','<>',null)
                            ->where('tx_stock.received_by','=',null)
                            ->where('tx_stock.active','=','Y')
                            ->whereRaw('tx_stock.updated_at<=\''.$perDate[2].'-'.$perDate[1].'-'.$perDate[0].' 23:59:59\'')
                            // ->orderBy('tx_stock.updated_at','DESC')
                            // ->limit(1)   
                        ])
                        ->where([
                            'tx_qty_parts.branch_id' => $branch->id,
                            'pr.brand_id' => $brand->id,
                            'pr.active' => 'Y',
                        ])
                        ->get();
                        foreach ($qParts as $qP) {
                            $total_avg_cost += ($qP->last_avg_cost*$qP->last_qty)+($qP->last_avg_cost*$qP->in_transit_qty);
                            // $total_final_price += $qP->last_final_price;
                            if ($qP->last_qty>0 || $qP->in_transit_qty>0){
                                $total_part_per_brand_per_branch++;
                            }
                        }

                        $total_avg_cost_per_branch += $total_avg_cost;
                        // $total_final_price_per_branch += $total_final_price;
                        $grandtotal_avg_cost += $total_avg_cost;
                        // $grandtotal_final_price += $total_final_price;
                    @endphp
                    <tr>
                        <td>{{ ($branch_name!=$branch->name?strtoupper($branch->name):'') }}</td>
                        <td>{{ strtoupper($brand->title_ind) }}</td>
                        <td>{{ number_format($total_avg_cost,0,'.','') }}</td>
                        {{-- <td>{{ number_format($total_final_price,0,'.','') }}</td> --}}
                        <td>{{ $total_part_per_brand_per_branch }}</td>
                    </tr>
                    @php
                        $branch_name = $branch->name;
                    @endphp
                @endforeach
                <tr>
                    <td style="font-weight: bold;">TOTAL</td>
                    <td>&nbsp;</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($total_avg_cost_per_branch,0,'.','') }}</td>
                    {{-- <td style="text-align: right;font-weight: bold;">{{ number_format($total_final_price_per_branch,0,'.','') }}</td> --}}
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    {{-- <td>&nbsp;</td> --}}
                    <td>&nbsp;</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td style="font-weight: bold;">GRAND TOTAL</td>
                    <td>&nbsp;</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($grandtotal_avg_cost,0,'.','') }}</td>
                    {{-- <td style="text-align: right;font-weight: bold;">{{ number_format($grandtotal_final_price,0,'.','') }}</td> --}}
                    <td>&nbsp;</td>
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
