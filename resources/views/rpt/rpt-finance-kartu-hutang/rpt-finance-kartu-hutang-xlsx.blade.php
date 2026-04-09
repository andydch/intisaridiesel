<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>kartuhutang</title>
    </head>
    @php
        $supplier = \App\Models\Mst_supplier::where([
            'id' => $supplier_id,
            'active' => 'Y',
        ])
        ->first();
            
        $branch_name = '';
        $branch = \App\Models\Mst_branch::where([
            'id' => $branch_id,
            'active' => 'Y',
        ])
        ->first();
        if ($branch){
            $branch_name = $branch->name;
        }
    @endphp
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                <thead>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="7">KARTU HUTANG</th>
                    </tr>
                    <tr>
                        <th>Supplier:</th>
                        <th colspan="5">{{ $supplier?($supplier->supplier_code.' - '.$supplier->name):'' }}</th>
                    </tr>
                    <tr>
                        <th>Period:</th>
                        <th colspan="5">{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>Branch:</th>
                        <th colspan="5">{{ $branch_id=='#'?'All':$branch_name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;">Tanggal</th>
                        <th style="text-align: center;border:1px solid black;">No Bukti</th>
                        <th style="text-align: center;border:1px solid black;">Description</th>
                        <th style="text-align: center;border:1px solid black;">Debet ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;">Kredit ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;">Saldo ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $lastSaldo = $beginning_balance_supplier+$sumRO-$sumPR-$sumPV;
                    @endphp
                    <tr>
                        <td style="text-align: center;border:1px solid black;">{{ $date_start }}</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;border:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;border:1px solid black;">{{ number_format($lastSaldo,2,'.',',') }}</td>
                    </tr>
                    @foreach ($qKartuHutang as $q)
                        <tr>
                            <td style="text-align: center;border:1px solid black;">{{ date_format(date_create($q->doc_date),"d-m-Y") }}</td>
                            <td style="text-align: center;border:1px solid black;">
                                @if (strpos('x'.$q->doc_no, ENV('P_RECEIPT_ORDER'))>0)
                                    @php
                                        $qRO = \App\Models\Tx_receipt_order::where([
                                            'receipt_no' => $q->doc_no,
                                            'active' => 'Y',
                                        ])
                                        ->first();
                                        if ($qRO){
                                            echo $qRO->invoice_no;
                                        }
                                    @endphp
                                @else
                                    {{ $q->doc_no }}
                                @endif
                            </td>
                            <td style="text-align: center;border:1px solid black;">
                                @switch($q->order_no)
                                    @case(1)
                                        Purchase
                                        @break
                                    @case(2)
                                        Retur
                                        @break
                                    @case(3)
                                        Payment
                                        @break
                                    @default
                                        &nbsp;
                                @endswitch
                            </td>
                            <td style="text-align: right;border:1px solid black;">
                                @switch($q->order_no)
                                    @case(1)
                                        {{ number_format($q->total,0,'','') }}
                                        @php
                                            $lastSaldo += $q->total;
                                        @endphp
                                        @break
                                    @case(2)
                                        0
                                        @break
                                    @case(3)
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
                                        {{ number_format($q->total,0,'','') }}
                                        @php
                                            $lastSaldo = $lastSaldo-$q->total;
                                        @endphp
                                        @break
                                    @case(3)
                                        {{ number_format($q->total,0,'','') }}
                                        @php
                                            $lastSaldo = $lastSaldo-$q->total;
                                        @endphp
                                        @break
                                    @default
                                        &nbsp;
                                @endswitch
                            </td>
                            <td style="text-align:right;border:1px solid black;">{{ number_format($lastSaldo,0,'','') }}</td>
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
