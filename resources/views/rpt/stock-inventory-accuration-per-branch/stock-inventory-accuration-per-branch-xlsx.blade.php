<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Stock Inv Per Branch</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    if($year_id<>date_format($date,"Y")){
                        $month = 12;
                    }
                    $totCols = $month+1;
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
                        <th colspan="{{ $totCols }}">STOCK INVENTORY ACCURATION PER BRANCH</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $year_id }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format(now(), 'd-M-Y') }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="font-weight: bold;background-color: #eaf1dd;border:1px solid black;">BRANCH</th>
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
                            <th style="font-weight: bold;background-color: #eaf1dd;border:1px solid black;text-align:center;">{{ $monthNm }}</th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @php
                        $branches_f = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) {
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches_f as $branch)
                    <tr>
                        <td colspan="{{ $totCols }}" style="font-weight:bold;border:1px solid black;">{{ strtoupper($branch->name) }}</td>
                        {{-- @for ($i=1;$i<=$month;$i++)<td>&nbsp;</td>@endfor --}}
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">BEGINING STOCK</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $date_x = date_create($year_id."-".$i."-01");
                            date_add($date_x,date_interval_create_from_date_string("-1 months"));
                            // echo date_format($date,"Y-m-d");

                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                'branch_id' => $branch->id,
                                'rpt_month' => date_format($date_x,"m"),
                                'rpt_year' => date_format($date_x,"Y"),
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        <td style="text-align: right;border:1px solid black;">{{ number_format((($qRpt)?$qRpt->actual_stock:0),0,'.',',') }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">PURCHASE (IN)</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                'branch_id' => $branch->id,
                                'rpt_month' => $i,
                                'rpt_year' => $year_id,
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        <td style="text-align: right;border:1px solid black;">{{ number_format((($qRpt)?$qRpt->purchase_in:0),0,'.',',') }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">SALES (OUT)</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                'branch_id' => $branch->id,
                                'rpt_month' => $i,
                                'rpt_year' => $year_id,
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        <td style="text-align: right;border:1px solid black;">{{ number_format((($qRpt)?$qRpt->sales_out:0),0,'.',',') }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">END STOCK</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                'branch_id' => $branch->id,
                                'rpt_month' => $i,
                                'rpt_year' => $year_id,
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        @if (($qRpt))
                            @if ($qRpt->end_stock>=0)
                                <td style="text-align: right;border:1px solid black;">
                                {{ number_format($qRpt->end_stock,0,'.',',') }}
                                </td>
                            @else
                                <td style="text-align: right;border:1px solid black;color:red;">
                                ({{ number_format((($qRpt)?($qRpt->end_stock*-1):0),0,'.',',') }})
                                </td>
                            @endif
                        @else
                            <td style="text-align: right;border:1px solid black;">
                                {{ 0 }}
                            </td>
                        @endif
                        @endfor
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">ACTUAL STOCK</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::where([
                                'branch_id' => $branch->id,
                                'rpt_month' => $i,
                                'rpt_year' => $year_id,
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        <td style="text-align: right;border:1px solid black;">{{ number_format((($qRpt)?$qRpt->actual_stock:0),0,'.',',') }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td style="font-weight:bold;border:1px solid black;">DIFF STOCK</td>
                        @for ($i=1;$i<=$month;$i++)
                        @php
                            $qRpt = \App\Models\Rpt_stock_inventory_acc_per_branch::selectRaw('(actual_stock-end_stock) AS diff_stock')
                            ->where([
                                'branch_id' => $branch->id,
                                'rpt_month' => $i,
                                'rpt_year' => $year_id,
                                'active' => 'Y',
                            ])
                            ->first();
                        @endphp
                        <td style="text-align: right;border:1px solid black;">{{ number_format((($qRpt)?$qRpt->diff_stock:0),0,'.',',') }}</td>
                        @endfor
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        @for ($i=1;$i<=$month;$i++)
                        <td>&nbsp;</td>
                        @endfor
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
