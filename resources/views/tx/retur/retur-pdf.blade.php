<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Retur : {{ $returs->nota_retur_no }}</title>

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

        <main>
            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->name:'') }}<br/>
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->city->city_name:'') }}&nbsp;{{ (!is_null($userLogin->branch)?$userLogin->branch->post_code:'') }}
                        </span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">&nbsp;</td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;font-weight: bold;">{{ $returs->nota_retur_no }}</span><br/><br/>
                        <span style="font-size: 12px;">Date : {{ date_format(date_create($returs->nota_retur_date),"d/m/Y") }}</span><br/>
                        <span style="font-size: 12px;">NP No : {{ (!is_null($returs->delivery_order)?$returs->delivery_order->delivery_order_no:'') }}</span>
                        {{-- <span style="font-size: 12px;">Supplier  : {{ (!is_null($returs->supplier_entity_type)?$returs->supplier_entity_type->title_ind:'').' '.(!is_null($returs->supplier)?$returs->supplier->name:'') }}</span><br/> --}}
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        To : <br/><span style="font-size: 12px;">{{ (!is_null($returs->customer->entity_type)?$returs->customer->entity_type->string_val:'').' '.$returs->customer->name }}</span><br/>
                        <span style="font-size: 12px;">{{ $returs->customer->office_address }}</span><br/>
                        <span style="font-size: 12px;">{{ $returs->customer->city->city_name.' '.$returs->customer->post_code }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">RETUR</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Parts No</th>
                        <th>Parts Name</th>
                        <th colspan="2">Qty</th>
                        <th>Price({{ $qCurrency->string_val }})</th>
                        <th>Total({{ $qCurrency->string_val }})</th>
                        <th>SJ No</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                        $qty=0;
                        $tot=0;
                        $total_price = 0;
                    @endphp
                    @foreach($parts AS $p)
                        <tr>
                            <td class="no-idx">{{ $i }}</td>
                            <td class="val-str">
                                @php
                                    $partNumber = $p->part->part_number;
                                    if(strlen($partNumber)<11){
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                    }else{
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                    }
                                @endphp
                                {{ $partNumber }}
                            </td>
                            <td class="val-str">{{ $p->part->part_name }}</td>
                            <td class="val-num">{{ $p->qty_retur }}</td>
                            <td style="text-align: center;">{{ (!is_null($p->part->quantity_type)?$p->part->quantity_type->title_ind:'') }}</td>
                            <td class="val-str" style="text-align: right;">{{ number_format($p->final_price,0,'.',',') }}</td>
                            <td class="val-str" style="text-align: right;">{{ number_format($p->final_price*$p->qty_retur,0,'.',',') }}</td>
                            <td class="val-str">{{ $p->surat_jalan->order->surat_jalan_no }}</td>
                            {{-- <td class="val-str">{{ $p->surat_jalan->order->surat_jalan_no }}</td> --}}
                            @php
                                $total_price += ($p->final_price*$p->qty_retur);
                            @endphp
                        </tr>
                        @php
                            $i+=1;
                            $qty+=$p->qty;
                            $tot+=($p->qty*$p->price);
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
                    <tr>
                        <td colspan="2" style="text-align: left;vertical-align: top;">
                            Terbilang:<br/>{{ terbilang($total_price) }}
                        </td>
                        <td colspan="3" style="text-align: right;vertical-align: top;">
                            Grand Total
                        </td>
                        <td style="text-align: right;vertical-align: top;">{{ $qCurrency->string_val.number_format($total_price,0,'.',',') }}</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;">
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">Remark:<br/>{{ $returs->remark }}</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">&nbsp;</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;vertical-align: top;">
                        <span style="font-size: 12px;">Proposed By,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;">&nbsp;</span><br/>
                        <span style="font-size: 10px;border-top:#000 solid 1px;">{{ (!is_null($returs->customer->entity_type)?$returs->customer->entity_type->string_val:'').' '.$returs->customer->name }}</span><br/>
                        {{-- <span style="font-size: 10px;">{{ !is_null($returs->createdBy->userDetail->branch)?$returs->createdBy->userDetail->branch->name:'' }}</span><br/>
                        <span style="font-size: 10px;">Approved by: {{ !is_null($returs->approvedBy)?$returs->approvedBy->name:'' }}</span><br/> --}}
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 12px;">Approved By,</span><br/>
                        <img src="{{ url('upl/employees/'.$owner->userDetail->signage_pic) }}" style="width: 100px;" alt=""><br/>
                        <span style="font-size: 12px;text-decoration:underline">{{ $owner->name }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span>
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
