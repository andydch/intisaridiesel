<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>NOTA PENJUALAN : {{ $dos->delivery_order_no }}</title>

        <style>
            table thead,
            table tr,
            table th {
                text-align: center;
                font-size: 12px;
                font-weight: bold;
                padding: 5px;
                border: 1px solid black !important;
            }

            table tbody,
            table tr,
            table td {
                text-align: center;
                font-size: 10px;
                font-weight: 300;
                padding: 5px;
                border: 1px solid black !important;
            }

            table.infoCust tbody,
            table.infoCust tr,
            table.infoCust td {
                text-align: center;
                font-size: 10px;
                font-weight: 300;
                padding: 1px;
                line-height:1.2rem;
                border: 0px solid black !important;
            }

            table {
                border: 1px solid black;
                background-color: white;
            }

            table td.no-idx,
            table td.val-num {
                text-align: right;
                font-size: 10px;
                font-weight: 300;
                /* border: 1px solid black !important; */
            }

            table td.val-str {
                text-align: left;
                font-size: 10px;
                font-weight: 300;
            }

            table tfoot,
            /* table tr, */
            table td.footer {
                text-align: left;
                vertical-align: text-top;
                font-size: 10px;
                font-weight: 300;
                padding: 5px;
                border: 0px solid black !important;
            }

            @page {
                /* mengatur posisi relatif atas/bawah */
                margin: 25px 25px;
            }

            header {
                position: fixed;
                top: -60px;
                left: 0px;
                right: 0px;
                height: 50px;
                font-size: 20px !important;
                background-color: #fff;
                color: white;
                text-align: center;
                line-height: 35px;
            }

            footer {
                position: fixed;
                bottom: -60px;
                left: 0px;
                right: 0px;
                height: 50px;
                font-size: 20px !important;
                background-color: #fff;
                color: white;
                text-align: center;
                line-height: 35px;
            }

        </style>
    </head>

    <body>
        <!-- Define header and footer blocks before your content -->
        {{-- <header>
        </header>

        <footer>
        </footer> --}}

        <main>
            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;font-weight:bold;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($userLogin->branch)?$userLogin->branch->address:'') }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($userLogin->branch)?$userLogin->branch->city->city_name.' '.$userLogin->branch->post_code:'') }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">&nbsp;</td>
                </tr>
            </table>

            <table class="infoCust" style="width: 100%;margin-bottom: 2px;vertical-align:top;">
                <tbody>
                    <tr style="border:0px solid black !important;">
                        <td style="width: 72%;border:0px solid black !important;">
                            <table class="infoCust" style="width: 100%;border:0px solid black !important;">
                                <tbody>
                                    <tr>
                                        <td colspan="2" style="font-size: 12px;font-weight:bold;text-align:left;">
                                            {{ $dos->delivery_order_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;width: 6%;vertical-align: top;">
                                            Date
                                        </td>
                                        <td style="text-align: left;width: 94%;vertical-align: top;">
                                            : {{ date_format(date_create($dos->delivery_order_date),"d/m/Y") }}
                                        </td>
                                    </tr>
                                    @php
                                        $all_po_arr = explode(',', substr($dos->sales_order_no_all,1,strlen($dos->sales_order_no_all)));
                                        $all_po = '';
                                        $qSJ = \App\Models\Tx_surat_jalan::whereIn('surat_jalan_no', $all_po_arr)
                                        ->where([
                                            'active' => 'Y',
                                        ])
                                        ->get();
                                        foreach($qSJ as $q){
                                            $all_po .= ','.$q->customer_doc_no;
                                        }
                                    @endphp
                                    <tr>
                                        <td style="text-align: left;width: 6%;vertical-align: top;">
                                            PO No
                                        </td>
                                        <td style="text-align: left;width: 94%;vertical-align: top;">
                                            : {{ substr($all_po,1,strlen($all_po)) }}
                                        </td>
                                    </tr>
                                    @php
                                        $soStr = '';
                                        $soArr = explode(",",substr($dos->sales_order_no_all,1,strlen($dos->sales_order_no_all)));
                                        for($i=0;$i<count($soArr);$i++){
                                            $soStr .= $soArr[$i].(($i+1)%5==0?'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':',');
                                        }
                                        $soStr = substr($soStr,0,strlen($soStr)-1);
                                    @endphp
                                    <tr>
                                        <td style="text-align: left;width: 6%;vertical-align: top;">
                                            SJ No
                                        </td>
                                        <td style="text-align: left;width: 94%;vertical-align: top;">
                                            : {!! substr($dos->sales_order_no_all,1,strlen($dos->sales_order_no_all)) !!}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 28%;border:0px solid black;">
                            <table class="infoCust" style="width: 100%;border:0px solid black;">
                                <tbody>
                                    <tr>
                                        <td style="text-align: left;vertical-align:top;">
                                            To : <br/>
                                            <span style="font-weight: bold;">
                                                {{ (!is_null($dos->customer->entity_type)?$dos->customer->entity_type->string_val:'').' '.$dos->customer->name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;vertical-align:top;">
                                            {{ $dos->customer->office_address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;vertical-align:top;">
                                            {{ (!is_null($dos->customer->city)?$dos->customer->city->city_name:'').' '.$dos->customer->post_code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;vertical-align:top;">
                                            NPWP: {{ $dos->customer->npwp_no }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;font-weight:bold;">{{ $dos->delivery_order_no }}</span><br/>
                        <span style="font-size: 10px;">Date : {{ date_format(date_create($dos->delivery_order_date),"d/m/Y") }}</span><br/>
                        @php
                            $all_po_arr = explode(',', substr($dos->sales_order_no_all,1,strlen($dos->sales_order_no_all)));
                            $all_po = '';
                            $qSJ = \App\Models\Tx_surat_jalan::whereIn('surat_jalan_no', $all_po_arr)
                            ->where([
                                'active' => 'Y',
                            ])
                            ->get();
                            foreach($qSJ as $q){
                                $all_po .= ','.$q->customer_doc_no;
                            }
                        @endphp
                        <span style="font-size: 10px;">PO No : {{ substr($all_po,1,strlen($all_po)) }}</span><br/>
                        <span style="font-size: 10px;">SJ No : {{ substr($dos->sales_order_no_all,1,strlen($dos->sales_order_no_all)) }}</span><br/>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 150px;">
                        To : <br/><span style="font-size: 12px;font-weight:bold;">{{ (!is_null($dos->customer->entity_type)?$dos->customer->entity_type->string_val:'').' '.$dos->customer->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $dos->customer->office_address }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($dos->customer->city)?$dos->customer->city->city_name:'').' '.$dos->customer->post_code }}</span><br/>
                        <span style="font-size: 10px;">NPWP: {{ $dos->customer->npwp_no }}</span>
                    </td>
                </tr>
            </table> --}}

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">NOTA PENJUALAN</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;border: 0px solid black;">
                <thead>
                    <tr>
                        <th><span style="font-size: 10px;">No</span></th>
                        <th><span style="font-size: 10px;">Parts No</span></th>
                        <th><span style="font-size: 10px;">Parts Name</span></th>
                        <th><span style="font-size: 10px;">Parts Type</span></th>
                        <th colspan="2"><span style="font-size: 10px;">Qty</span></th>
                        <th><span style="font-size: 10px;">Price({{ $qCurrency->string_val }})</span></th>
                        <th><span style="font-size: 10px;">Total({{ $qCurrency->string_val }})</span></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                        $tot=0;
                        $totWeight=0;
                    @endphp
                    @foreach($parts AS $p)
                        <tr>
                            <td class="no-idx"><span style="font-size: 10px;">{{ $i }}</span></td>
                            <td class="val-str">
                                @php
                                    $partNumber = $p->part->part_number;
                                    if(strlen($partNumber)<11){
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                    }else{
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                    }
                                @endphp
                                <span style="font-size: 10px;">{{ $partNumber }}</span>
                            </td>
                            <td class="val-str"><span style="font-size: 10px;">{{ $p->part->part_name }}</span></td>
                            <td class="val-str"><span style="font-size: 10px;">{{ $p->part->part_type->title_ind }}</span></td>
                            <td class="val-num"><span style="font-size: 10px;">{{ number_format($p->qty,0,'.',',') }}</span></td>
                            <td class="val-str"><span style="font-size: 10px;">{{ $p->part->quantity_type->string_val }}</span></td>
                            <td class="val-num"><span style="font-size: 10px;">{{ number_format($p->final_price,0,'.',',') }}</span></td>
                            <td class="val-num"><span style="font-size: 10px;">{{ number_format($p->qty*$p->final_price,0,'.',',') }}</span></td>
                        </tr>
                        @php
                            $i+=1;
                            $tot+=($p->qty*$p->final_price);
                            $totWeight+=$p->part->weight;
                        @endphp
                    @endforeach
                    @php
                        function penyebut($nilai) {
                            $nilai = abs($nilai);
                            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
                            $temp = "";
                            if ($nilai < 12) {
                                $temp = " ". $huruf[$nilai];
                            } else if ($nilai <20) {
                                $temp = penyebut($nilai - 10). " Belas";
                            } else if ($nilai < 100) {
                                $temp = penyebut($nilai/10)." Puluh". penyebut($nilai % 10);
                            } else if ($nilai < 200) {
                                $temp = " Seratus" . penyebut($nilai - 100);
                            } else if ($nilai < 1000) {
                                $temp = penyebut($nilai/100) . " Ratus" . penyebut($nilai % 100);
                            } else if ($nilai < 2000) {
                                $temp = " seribu" . penyebut($nilai - 1000);
                            } else if ($nilai < 1000000) {
                                $temp = penyebut($nilai/1000) . " Ribu" . penyebut($nilai % 1000);
                            } else if ($nilai < 1000000000) {
                                $temp = penyebut($nilai/1000000) . " Juta" . penyebut($nilai % 1000000);
                            } else if ($nilai < 1000000000000) {
                                $temp = penyebut($nilai/1000000000) . " Milyar" . penyebut(fmod($nilai,1000000000));
                            } else if ($nilai < 1000000000000000) {
                                $temp = penyebut($nilai/1000000000000) . " Trilyun" . penyebut(fmod($nilai,1000000000000));
                            }
                            return $temp;
                        }
                        function terbilang($nilai) {
                            if($nilai<0) {
                                $hasil = "minus ". trim(penyebut($nilai));
                            } else {
                                $hasil = trim(penyebut($nilai));
                            }
                            return $hasil;
                        }
                    @endphp
                    <tr style="border: 1px solid black;">
                        <td colspan="6" style="border: 1px solid black;text-align:left;">
                            <span style="font-size: 10px;font-weight:bold;">Terbilang:</span>
                            <span style="font-size: 10px;">{{ terbilang($tot) }}</span>
                            {{-- @if ($dos->is_vat=='Y')
                                <span style="font-size: 10px;">{{ terbilang($tot+($tot*$vat/100)) }}</span>
                            @else
                            @endif --}}
                        </td>
                        <td class="val-num" style="border: 1px solid black;">Total</td>
                        <td class="val-num" style="border: 1px solid black;">{{ number_format($tot,0,'.',',') }}</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 40%;text-align: left;border:0px solid white !important;vertical-align: top;">
                        <span style="font-size: 10px;">Remark:<br/>{{ $dos->remark }}</span>
                    </td>
                    <td style="width: 32%;text-align: left;vertical-align: top;border:0px solid white !important;">
                        <span style="font-size: 12px;">Received by,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 10px;border-top:1px solid black;">{{ (!is_null($dos->customer->entity_type)?$dos->customer->entity_type->string_val:'').' '.$dos->customer->name }}</span>
                    </td>
                    <td style="width: 28%;text-align: left;vertical-align: top;border:0px solid white !important;">
                        <span style="font-size: 12px;">Proposed by,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 10px;border-top:1px solid black;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($userLogin->branch)?$userLogin->branch->name:'') }}</span>
                    </td>
                </tr>
            </table>
        </main>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
