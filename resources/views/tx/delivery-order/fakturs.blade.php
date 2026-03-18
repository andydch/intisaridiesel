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
        <tbody>
            <tr>
                <td colspan="2" style="font-weight: 700;text-align:center;">NPWP Penjual</td>
                <td colspan="2">{{ '0'.$npwpNo }}</td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td style="font-weight: 700;">Baris</td>
                <td style="font-weight: 700;">Tanggal Faktur</td>
                <td style="font-weight: 700;">Jenis Faktur</td>
                <td style="font-weight: 700;">Kode Transaksi</td>
                <td style="font-weight: 700;">Keterangan Tambahan</td>
                <td style="font-weight: 700;">Dokumen Pendukung</td>
                <td style="font-weight: 700;">Referensi</td>
                <td style="font-weight: 700;">Cap Fasilitas</td>
                <td style="font-weight: 700;">ID TKU Penjual</td>
                <td style="font-weight: 700;">NPWP/NIK Pembeli</td>
                <td style="font-weight: 700;">Jenis ID Pembeli</td>
                <td style="font-weight: 700;">Negara Pembeli</td>
                <td style="font-weight: 700;">Nomor Dokumen Pembeli</td>
                <td style="font-weight: 700;">Nama Pembeli</td>
                <td style="font-weight: 700;">Alamat Pembeli</td>
                <td style="font-weight: 700;">Email Pembeli</td>
                <td style="font-weight: 700;">ID TKU Pembeli</td>
            </tr>
            @php
                $row = 1;
            @endphp
            @foreach ($fakturs as $faktur)
                <tr>
                    <td style="text-align: left;">{{ $row }}</td>
                    @php
                        $delivery_order_date = date_create($faktur->delivery_order_date);
                    @endphp
                    <td>{{ date_format($delivery_order_date,"d/m/Y") }}</td>
                    <td>Normal</td>
                    <td>04</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>{{ $faktur->delivery_order_no }}</td>
                    <td>&nbsp;</td>
                    <td>{{ '0'.$npwpNo.'000000' }}</td>
                    @php
                        $npwp_no_cust = '';
                        $npwp_no_cust_id_tku_pembeli = '';
                        if($faktur->npwp_no_cust!=null){
                            $npwp_no_cust = str_replace(".","",$faktur->npwp_no_cust);
                            $npwp_no_cust = '0'.str_replace("-","",$npwp_no_cust);
                            $npwp_no_cust_id_tku_pembeli = $npwp_no_cust.'000000';
                        }
                    @endphp
                    <td>{{ $npwp_no_cust }}</td>
                    <td>TIN</td>
                    <td>IDN</td>
                    <td>&nbsp;</td>
                    <td>{{ ($faktur->ett_name!=null?$faktur->ett_name.' ':'').$faktur->customer_name }}</td>
                    @php
                        $address = $faktur->npwp_address.
                            ($faktur->sub_district_name!=null?' '.ucfirst($faktur->sub_district_name):'').
                            ($faktur->district_name!=null?' '.$faktur->district_name:'').
                            ($faktur->city_name!=null?' '.$faktur->city_name:'').
                            ($faktur->province_name!=''?' '.$faktur->province_name:'').
                            ($faktur->country_id!=9999?' '.$faktur->country_name:'');
                        $address = str_replace('Other','',$address);
                    @endphp
                    <td>{{ $address }}</td>
                    <td>{{ $faktur->cust_email }}</td>
                    <td>{{ $npwp_no_cust_id_tku_pembeli }}</td>
                </tr>
                @php
                    $row += 1;
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
