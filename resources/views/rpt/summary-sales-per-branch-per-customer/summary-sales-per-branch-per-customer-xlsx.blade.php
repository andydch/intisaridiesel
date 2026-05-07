<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>summ sales</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 7;
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
                        <th colspan="{{ $totCols }}">REPORT SUMMARY SALES PER BRANCH PER CUSTOMER</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">CUSTOMER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AMOUNT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL GP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $lokal_input = ($lokal_input=='x'?'P':$lokal_input);

                        $custName = '';
                        $custInit = '';
                        $grandtotal_dpp_per_branch = 0;
                        $grandtotal_ppn_per_branch = 0;
                        $grandtotal_amount_per_branch = 0;
                        $grandtotal_avg_per_branch = 0;
                        $grandtotal_gp_per_branch = 0;
                        $grandtotal_gp_percent_per_branch = 0;
                        
                        $branches = \App\Models\Mst_branch::where('active', 'Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $total_dpp_per_branch = 0;
                            $total_ppn_per_branch = 0;
                            $total_amount_per_branch = 0;
                            $total_avg_per_branch = 0;
                            $gp_per_branch = 0;
                            $gp_percent_per_branch = 0;
                        @endphp
                        <tr>
                            <td colspan="{{ $totCols }}" style="font-weight: bold;border-left:1px solid black;border-right:1px solid black;">{{ $branch->name }}</td>
                        </tr>
                        @php
                            $qSales = DB::table('v_sales_all AS v')
                            ->leftJoin('mst_customers AS c', 'c.id', '=', 'v.customer_id')
                            ->select(
                                'c.name as name',
                                'c.customer_unique_code as customer_unique_code',
                                DB::raw('SUM(v.total_dpp) AS total_dpp'),
                                DB::raw('SUM(v.total_after_vat) AS total_after_vat'),
                                DB::raw('SUM(v.total_avg) AS total_avg'),
                            )
                            ->addSelect([
                                'total_dpp_retur' => DB::table('v_nota_retur_all AS v_retur')
                                ->selectRaw('SUM(v_retur.total_dpp)')
                                ->whereColumn('v_retur.customer_id', 'v.customer_id')
                                ->whereRaw('v_retur.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('v_retur.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('v_retur.approved_by IS NOT NULL')
                            ])
                            ->addSelect([
                                'total_after_vat_retur' => DB::table('v_nota_retur_all AS v_retur')
                                ->selectRaw('SUM(v_retur.total_after_vat)')
                                ->whereColumn('v_retur.customer_id', 'v.customer_id')
                                ->whereRaw('v_retur.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('v_retur.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('v_retur.approved_by IS NOT NULL')
                            ])
                            ->addSelect([
                                'total_avg_retur' => DB::table('v_nota_retur_all AS v_retur')
                                ->selectRaw('SUM(v_retur.total_avg)')
                                ->whereColumn('v_retur.customer_id', 'v.customer_id')
                                ->whereRaw('v_retur.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('v_retur.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('v_retur.approved_by IS NOT NULL')
                            ])
                            ->when(strtoupper($lokal_input)=='A', function($q){
                                $q->whereIn('v.tax_code', ['P', 'N']);
                            })
                            ->when(strtoupper($lokal_input)=='P', function($q){
                                $q->where('v.tax_code', 'P');
                            })
                            ->when(strtoupper($lokal_input)=='N', function($q){
                                $q->where('v.tax_code', 'N');
                            })
                            ->whereRaw('v.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('v.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where('v.branch_id', $branch->id)
                            ->where('c.active', 'Y')
                            ->orderBy('c.name','ASC')
                            ->groupBy('v.customer_id')
                            ->groupBy('c.name')
                            ->groupBy('c.customer_unique_code')
                            ->get();
                        @endphp
                        @foreach ($qSales as $qS)
                            <tr>
                                <td style="border-left:1px solid black;">
                                    {{ ($custInit.$custName!=$qS->customer_unique_code.$qS->name)?$qS->customer_unique_code.' - '.$qS->name:'' }}
                                </td>
                                @php
                                    $total_dpp = $qS->total_dpp - $qS->total_dpp_retur;
                                    $total_ppn = ($qS->total_after_vat-$qS->total_dpp) - ($qS->total_after_vat_retur-$qS->total_dpp_retur);
                                    $total_amount = $qS->total_after_vat - $qS->total_after_vat_retur;
                                    $total_avg = $qS->total_avg - $qS->total_avg_retur;
                                    $gp = $total_dpp - $total_avg;
                                    // $gp = $total_dpp - $total_avg - $qS->total_after_vat_retur - $qS->total_avg_retur;
                                    $gp_percent = ($gp/$total_dpp)*100;
                                    // $gp_percent = ($gp/($total_dpp - $qS->total_after_vat_retur))*100;

                                    $total_dpp_per_branch += $total_dpp;
                                    $total_ppn_per_branch += $total_ppn;
                                    $total_amount_per_branch += $total_amount;
                                    $total_avg_per_branch += $total_avg;
                                    $gp_per_branch += $gp;
                                    $gp_percent_per_branch = ($gp_per_branch/$total_dpp_per_branch)*100;
                                @endphp
                                <td style="text-align: right;">{{ number_format($total_dpp, 0, '.', '') }}</td>
                                <td style="text-align: right;">{{ $total_ppn>0?number_format($total_ppn, 0, '.', ''):'' }}</td>
                                <td style="text-align: right;">{{ number_format($total_amount, 0, '.', '') }}</td>
                                <td style="text-align: right;">{{ number_format($total_avg, 0, '.', '') }}</td>
                                <td style="text-align: right;">{{ number_format($gp, 0, '.', '') }}</td>
                                <td style="text-align: right;border-right:1px solid black;">{{ number_format($gp_percent, 0, '.', '') }}%</td>
                            </tr>
                            @if ($custInit.$custName!=$qS->customer_unique_code.$qS->name)
                                @php
                                    $custName = $qS->name;
                                    $custInit = $qS->customer_unique_code;
                                @endphp
                            @endif
                        @endforeach
                        <tr>
                            <td style="font-weight: bold;border-top:1px solid black;border-left:1px solid black;">Total</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;">{{ number_format($total_dpp_per_branch, 0, '.', '') }}</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;">{{ number_format($total_ppn_per_branch, 0, '.', '') }}</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;">{{ number_format($total_amount_per_branch, 0, '.', '') }}</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;">{{ number_format($total_avg_per_branch, 0, '.', '') }}</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;">{{ number_format($gp_per_branch, 0, '.', '') }}</td>
                            <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-right:1px solid black;">{{ number_format($gp_percent_per_branch, 0, '.', '') }}%</td>
                        </tr>
                        <tr>
                            <td style="border-left:1px solid black;border-right:1px solid black;" colspan="{{ $totCols }}">&nbsp;</td>
                        </tr>
                        @php
                            $grandtotal_dpp_per_branch += $total_dpp_per_branch;
                            $grandtotal_ppn_per_branch += $total_ppn_per_branch;
                            $grandtotal_amount_per_branch += $total_amount_per_branch;
                            $grandtotal_avg_per_branch += $total_avg_per_branch;
                            $grandtotal_gp_per_branch += $gp_per_branch;
                            $grandtotal_gp_percent_per_branch = ($grandtotal_gp_per_branch/$grandtotal_dpp_per_branch)*100;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;border-left:1px solid black;">Grand Total</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandtotal_dpp_per_branch, 0, '.', '') }}</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandtotal_ppn_per_branch, 0, '.', '') }}</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandtotal_amount_per_branch, 0, '.', '') }}</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandtotal_avg_per_branch, 0, '.', '') }}</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandtotal_gp_per_branch, 0, '.', '') }}</td>
                        <td style="text-align: right;font-weight: bold;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">{{ number_format($grandtotal_gp_percent_per_branch, 0, '.', '') }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
