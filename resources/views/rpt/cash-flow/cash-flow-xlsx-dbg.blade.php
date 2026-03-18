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
                    $maxRow = \App\Models\Tx_cash_flow_2026::where('report_code','=',$randomString)
                    ->max('row_number');

                    $maxCol = \App\Models\Tx_cash_flow_2026::where('report_code','=',$randomString)
                    ->max('col_number');
                @endphp
                @for ($row=1;$row<=$maxRow;$row++)
                    <tr>                        
                        @for ($col=1;$col<=$maxCol;$col++)
                            @php
                                $data = \App\Models\Tx_cash_flow_2026::where([
                                    'report_code' => $randomString,
                                    'row_number' => $row,
                                    'col_number' => $col,
                                ])
                                ->first();
                            @endphp
                            @if ($data)
                                @if ($col==1)
                                    <td></td>                                    
                                @else                                    
                                    <td style="font-size: {{ $data->font_size }}px; font-weight: {{ $data->font_weight }}px; background-color: {{ $data->b_color }}; 
                                        color: {{ $data->f_color }}; text_align: {{ $data->text_align }};">
                                        {{ $data->cell_values!=0?$data->cell_values:'' }}
                                    </td>
                                @endif
                            @else
                                <td></td>
                            @endif
                        @endfor
                    </tr>
                @endfor
                {{-- <tr>
                    <td colspan="2">{{ $start_datetime }}</td>
                </tr>
                @php
                    $date1 = new DateTime($start_datetime); // Start Date
                    $date2 = new DateTime($startCustomer_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2">Customer: {{ $startCustomer_datetime.' ('.$interval->i.' menit '.$interval->s.' detik'.')' }}</td>
                </tr>
                @php
                    $date1 = new DateTime($startCustomer_datetime); // Start Date
                    $date2 = new DateTime($startGjLj01_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2">GJ::LJ 01: {{ $startGjLj01_datetime.' ('.$interval->i.' menit '.$interval->s.' detik'.')' }}</td>
                </tr>
                @php
                    $date1 = new DateTime($startGjLj01_datetime); // Start Date
                    $date2 = new DateTime($startGjLj02_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2">GJ::LJ 02: {{ $startGjLj02_datetime.' ('.$interval->i.' menit '.$interval->s.' detik'.')' }}</td>
                </tr>
                @php
                    $date1 = new DateTime($startGjLj02_datetime); // Start Date
                    $date2 = new DateTime($startSupplier_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2">Supplier: {{ $startSupplier_datetime.' ('.$interval->i.' menit '.$interval->s.' detik'.')' }}</td>
                </tr>
                @php
                    $stopDateTimeObj = new DateTime('now');
                    $stop_datetime = $stopDateTimeObj->format('Y-m-d H:i:s');

                    $date1 = new DateTime($startSupplier_datetime); // Start Date
                    $date2 = new DateTime($stop_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2">{{ $stop_datetime.' ('.$interval->i.' menit '.$interval->s.' detik'.')' }}</td>
                </tr>
                @php
                    $date1 = new DateTime($start_datetime); // Start Date
                    $date2 = new DateTime($stop_datetime); // End Date
                    $interval = $date1->diff($date2);
                @endphp
                <tr>
                    <td colspan="2" style="text-align: left !important;">{{ 'Total waktu proses: '.$interval->i.' menit '.$interval->s.' detik' }}</td>
                </tr> --}}
            </tbody>
            {{-- <tfooter></tfooter> --}}
        </table>

        {{-- <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}
    </body>
</html>
