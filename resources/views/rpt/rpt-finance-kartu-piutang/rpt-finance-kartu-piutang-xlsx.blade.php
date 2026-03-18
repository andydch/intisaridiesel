<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>kartupiutang</title>
    </head>
    @php
        $customer = \App\Models\Mst_customer::where([
            'id' => $customer_id,
            'active' => 'Y',
        ])
        ->first();
            
        // $branch_name = '';
        // $branch = \App\Models\Mst_branch::where([
        //     'id' => $branch_id,
        //     'active' => 'Y',
        // ])
        // ->first();
        // if ($branch){
        //     $branch_name = $branch->name;
        // }
    @endphp
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="7">KARTU PIUTANG</th>
                    </tr>
                    <tr>
                        <th>customer:</th>
                        <th colspan="5">{{ $customer?($customer->customer_unique_code.' - '.$customer->name):'' }}</th>
                    </tr>
                    <tr>
                        <th>Period:</th>
                        <th colspan="5">{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    {{-- <tr>
                        <th>Branch:</th>
                        <th colspan="5">{{ $branch_id=='#'?'All':$branch_name }}</th>
                    </tr> --}}
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;">Tanggal</th>
                        <th style="text-align: center;border:1px solid black;">No Bukti</th>
                        <th style="text-align: center;border:1px solid black;">Description</th>
                        <th style="text-align: center;border:1px solid black;">Adjust/Disc</th>
                        <th style="text-align: center;border:1px solid black;">Debet ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;">Kredit ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;">Saldo ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastSaldo = $beginingBalance;
                    @endphp
                    <tr>
                        <td style="text-align: center;border:1px solid black;">{{ $date_start }}</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;border:1px solid black;">{{ number_format($lastSaldo,0,',','') }}</td>
                    </tr>
                    @foreach ($qKartuPiutang as $q)
                        <tr>
                            <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($q->doc_date),"d/m/Y") }}</td>
                            <td style="text-align: center;border:1px solid black;">{{ $q->doc_no }}</td>
                            <td style="text-align: center;border:1px solid black;">
                                @switch($q->order_no)
                                    @case(1)
                                        Sales
                                        @break
                                    @case(2)
                                        Sales
                                        @break
                                    @case(3)
                                        Retur
                                        @break
                                    @case(4)
                                        Retur
                                        @break
                                    @case(5)
                                        Receipt
                                        @break
                                    @default
                                        &nbsp;
                                @endswitch
                            </td>
                            <td style="text-align: right;border:1px solid black;">0</td>
                            <td style="text-align: right;border:1px solid black;">
                                @switch($q->order_no)
                                    @case(1)
                                        {{ number_format($q->total,0,',','') }}
                                        @php
                                            $lastSaldo += $q->total;
                                        @endphp
                                        @break
                                    @case(2)
                                        {{ number_format($q->total,0,',','') }}
                                        @php
                                            $lastSaldo += $q->total;
                                        @endphp
                                        @break
                                    @case(3)
                                        0
                                        @break
                                    @case(4)
                                        0
                                        @break
                                    @case(5)
                                        0
                                        @break
                                    @default
                                        &nbsp;
                                @endswitch
                            </td>
                            <td style="text-align: right;border:1px solid black;">
                                @switch($q->order_no)
                                    @case(1)
                                        0
                                        @break
                                    @case(2)
                                        0
                                        @break
                                    @case(3)
                                        {{ number_format($q->total,0,',','') }}
                                        @php
                                            $lastSaldo = $lastSaldo-$q->total;
                                        @endphp
                                        @break
                                    @case(4)
                                        {{ number_format($q->total,0,',','') }}
                                        @php
                                            $lastSaldo = $lastSaldo-$q->total;
                                        @endphp
                                        @break
                                    @case(5)
                                        {{ number_format($q->total,0,',','') }}
                                        @php
                                            $lastSaldo = $lastSaldo-$q->total;
                                        @endphp
                                        @break
                                    @default
                                        &nbsp;
                                @endswitch
                            </td>
                            <td style="text-align:right;border:1px solid black;">{{ number_format($lastSaldo,0,',','') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
