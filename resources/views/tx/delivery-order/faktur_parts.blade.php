<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  </head>
  <body>
    <table>
        <thead>
            <tr>
                <th>Baris</th>
                <th>Barang/Jasa</th>
                <th>Kode Barang Jasa</th>
                <th>Nama Barang/Jasa</th>
                <th>Nama Satuan Ukur</th>
                <th>Harga Satuan</th>
                <th>Jumlah Barang Jasa</th>
                <th>Total Diskon</th>
                <th>DPP</th>
                <th>DPP Nilai Lain</th>
                <th>Tarif PPN</th>
                <th>PPN</th>
                <th>Tarif PPnBM</th>
                <th>PPnBM</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fakturRow = 1;
            @endphp
            @foreach ($fakturs as $faktur)
                @php
                    $parts = \App\Models\Tx_delivery_order_part::leftJoin('mst_parts as m_sp','tx_delivery_order_parts.part_id','=','m_sp.id')
                    ->leftJoin('mst_globals as qt_type', 'm_sp.quantity_type_id', '=', 'qt_type.id')
                    ->select(
                        'tx_delivery_order_parts.qty',
                        'tx_delivery_order_parts.final_price',
                        'tx_delivery_order_parts.total_price',
                        'm_sp.part_name',
                        'qt_type.title_ind AS quantity_type_name',
                    )
                    ->where([
                        'tx_delivery_order_parts.delivery_order_id'=>$faktur->faktur_id,
                        'tx_delivery_order_parts.active'=>'Y',
                    ])
                    ->orderBy('m_sp.part_name','ASC')
                    ->get();

                    $totDiskon = 0;
                    $ppnBm = 0;
                    $p11 = 11;
                    $p12 = 12;
                @endphp
                @foreach ($parts as $part)
                    <tr>
                        <td>{{ $fakturRow }}</td>
                        <td>A</td>
                        <td>000000</td>
                        <td>{{ $part->part_name }}</td>
                        @php
                            $qty_code = 'UM.0018';
                        @endphp     
                        @switch(strtolower($part->quantity_type_name))
                            @case('unit')
                                @php
                                    $qty_code = 'UM.0018';
                                @endphp
                                @break
                            @case('set')
                                @php
                                    $qty_code = 'UM.0019';
                                @endphp
                                @break

                            @case('pcs')
                                @php
                                    $qty_code = 'UM.0021';
                                @endphp
                                @break

                            @case('box')
                                @php
                                    $qty_code = 'UM.0022';
                                @endphp
                                @break

                            @default
                                @php
                                    $qty_code = 'UM.0018';
                                @endphp
                        @endswitch
                        <td>{{ $qty_code }}</td>
                        {{-- <td>UM.0018</td> --}}
                        <td style="text-align: right;">{{ number_format($part->final_price,2,'.','') }}</td>
                        <td style="text-align: right;">{{ $part->qty }}</td>
                        <td style="text-align: right;">{{ number_format($totDiskon,2,'.','') }}</td>
                        <td style="text-align: right;">{{ number_format($part->total_price-$totDiskon,2,'.','') }}</td>
                        @php
                            $dppNilaiLain = ($p11/$p12)*($part->total_price-$totDiskon);
                        @endphp
                        <td style="text-align: right;">{{ number_format($dppNilaiLain,2,'.','') }}</td>
                        <td style="text-align: right;">{{ $p12 }}</td>
                        <td style="text-align: right;">{{ number_format($dppNilaiLain*$p12/100,2,'.','') }}</td>
                        <td style="text-align: right;">&nbsp;</td>
                        <td style="text-align: right;">{{ number_format($ppnBm,2,'.','') }}</td>
                    </tr>
                @endforeach
                @php
                    $fakturRow += 1;
                @endphp
            @endforeach

            <tr>
                <td style="font-weight: 700;">END</td>
            </tr>
        </tbody>
    </table>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  </body>
</html>
