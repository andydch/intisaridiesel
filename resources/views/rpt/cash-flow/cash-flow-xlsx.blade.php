<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}

        <title>CashFlow</title>
    </head>
    <body>
        @php
            $startXlsDateTimeObj = new DateTime('now');
            $startXls_datetime = $startXlsDateTimeObj->format('Y-m-d H:i:s');

            $periodXls = explode("-", $period);
            $emptyLine = '';
        @endphp
        @for ($col=1;$col<=($monthDays+2);$col++)
            @php
                $emptyLine .= '&lt;td&gt;&lt;/td&gt;';
            @endphp
        @endfor
        @php
            $emptyLine = '&lt;tr&gt;'.$emptyLine.'&lt;/tr&gt;';
        @endphp
        <table>
            <thead>
                <tr>
                    <th class="header-01"></th>
                    <th colspan="{{ 2+$monthDays }}">{{ $companyName }}</th>
                </tr>
                <tr>
                    <th class="header-01"></th>
                    <th colspan="{{ 2+$monthDays }}">{{ 'Period: '.ucwords(strtolower($MonthName[$periodXls[0]-1])).' '.$periodXls[1] }}</th>
                </tr>
                <tr>
                    <th class="header-01"></th>
                    <th class="header-01">NAMA RELASI</th>
                    @for ($iDays=1;$iDays<=$monthDays;$iDays++)
                    <th class="header-02">{{ $iDays }}</th>                        
                    @endfor
                    <th class="header-01">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                {!! html_entity_decode($emptyLine) !!}
                @php
                    $maxRow = \App\Models\Tx_cash_flow::where('report_code', $randomString)
                    ->max('row_number');

                    $maxCol = \App\Models\Tx_cash_flow::where('report_code', $randomString)
                    ->max('col_number');
                @endphp
                @for ($row=1; $row<=$maxRow; $row++)
                    @php
                        $dataPerRow = \App\Models\Tx_cash_flow::where([
                            'report_code' => $randomString,
                            'row_number' => $row,
                        ])
                        ->first();
                    @endphp
                    @if ($dataPerRow)
                        <tr>                        
                            @for ($col=1; $col<=$maxCol; $col++)
                                @php
                                    $dataPerCol = \App\Models\Tx_cash_flow::where([
                                        'report_code' => $randomString,
                                        'row_number' => $row,
                                        'col_number' => $col,
                                    ])
                                    ->first();
                                @endphp
                                @if ($dataPerCol)
                                    @if ($col==1)
                                        <td></td>                                    
                                    @else                                    
                                        <td style="font-size: {{ $dataPerCol->font_size }}px; font-weight: {{ $dataPerCol->font_weight }}px; background-color: {{ $dataPerCol->b_color }}; 
                                            color: {{ $dataPerCol->f_color }}; text_align: {{ $dataPerCol->text_align }};">
                                            {{ $dataPerCol->cell_values!=0?$dataPerCol->cell_values:'' }}
                                        </td>
                                    @endif
                                @else
                                    <td></td>
                                @endif
                            @endfor
                        </tr>
                    @endif
                @endfor
            </tbody>
            {{-- <tfooter></tfooter> --}}
        </table>

        {{-- <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}
    </body>
</html>
