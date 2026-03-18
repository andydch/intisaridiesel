<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Sales Quotation : {{ $salesQuos->sales_quotation_no }}</title>

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

            table#kop tr,
            table#kop td {
                text-align: center;
                font-size: 10;
                font-weight: 300;
                padding: 5px;
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
            <table id="kop" style="width: 100%;background-color: white;margin-bottom: 20px;border:1px solid white;">
                <tr>
                    <td style="width: 34%;text-align: left;vertical-align: top;border:1px solid white !important;">
                        <img src="{{ $_SERVER['DOCUMENT_ROOT'].'/assets/images/logo_UID.png' }}" style="width: 150px;" alt="">
                    </td>
                    <td style="width: 24%;text-align: left;vertical-align: top;font-size: 10px;">
                        Jl. Komp. Karang Anyar Permai<br/>53-54, Blok B No. 1<br/>Jakarta 10740
                    </td>
                    <td style="width: 22%;text-align: left;vertical-align: top;font-size: 10px;">
                        T. 62.21 625 4490, 625 4491<br/>62.21 624 3706<br/>F. 62.21 624 3706
                    </td>
                    <td style="width: 20%;text-align: left;vertical-align: top;font-size: 10px;">
                        www.intisaridiesel.com
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 60%;text-align: left;border:0px solid white !important;">
                        To : <br/><span style="font-size: 10px;font-weight:bold;">{{ (!is_null($salesQuos->customer->entity_type)?$salesQuos->customer->entity_type->string_val:'').' '.$salesQuos->customer->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $salesQuos->customer->office_address }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($salesQuos->sub_district)?ucwords(strtolower($salesQuos->sub_district->sub_district_name)):'').', '.(!is_null($salesQuos->district)?ucwords(strtolower($salesQuos->district->district_name)):'') }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($salesQuos->customer->city)?$salesQuos->customer->city->city_name:'').' '.$salesQuos->customer->post_code }}</span><br/>
                        <span style="font-size: 10px;">Att: {{ $pic }}</span><br/><br/>
                    </td>
                    <td style="width: 40%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 150px;">
                        <span style="font-size: 10px;">{{ (!is_null($salesQuos->branch)?$salesQuos->branch->name.', ':'')  }}{{ date_format(date_create($salesQuos->sales_order_date),"d/m/Y") }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">{{ $salesQuos->sales_quotation_no }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">SALES QUOTATION</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:left;border:0px solid white !important;">
                        <span style="font-size: 10;">{!! $salesQuos->header !!}</span>
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
                        <th><span style="font-size: 10px;">Remarks</span></th>
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
                            <td class="val-num"><span style="font-size: 10px;">{{ number_format($p->price_part,0,'.',',') }}</span></td>
                            <td class="val-num"><span style="font-size: 10px;">{{ number_format($p->qty*$p->price_part,0,'.',',') }}</span></td>
                            <td class="val-num"><span style="font-size: 10px;">{{ $p->description }}</span></td>
                        </tr>
                        @php
                            $i+=1;
                            $tot+=($p->qty*$p->price_part);
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
                        <td colspan="6" rowspan="3" style="border: 0px solid black;text-align:left;vertical-align:top;">
                            <span style="font-size: 10px;font-weight:bold;">Terbilang:</span>
                            @if ($salesQuos->vat_val>0)
                                <span style="font-size: 10px;">{{ terbilang($tot+($tot*$salesQuos->vat_val/100)) }}</span>
                            @else
                                <span style="font-size: 10px;">{{ terbilang($tot) }}</span>
                            @endif
                        </td>
                        <td class="val-num" style="border: 1px solid black;">Total</td>
                        <td class="val-num" style="border: 0px solid black;">{{ number_format($tot,0,'.',',') }}</td>
                    </tr>
                    @if ($salesQuos->vat_val>0)
                        <tr style="border: 1px solid black;">
                            {{-- <td colspan="6" style="border: 0px solid black;"></td> --}}
                            <td class="val-num" style="border: 1px solid black;">VAT</td>
                            <td class="val-num" style="border: 0px solid black;">{{ number_format($tot*$salesQuos->vat_val/100,0,'.',',') }}</td>
                        </tr>
                        <tr style="border: 1px solid black;">
                            {{-- <td colspan="6" style="border: 0px solid black;"></td> --}}
                            <td class="val-num" style="border: 1px solid black;">Grand Total</td>
                            <td class="val-num" style="border: 0px solid black;">{{ number_format($tot+($tot*$salesQuos->vat_val/100),0,'.',',') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 33%;text-align: left;border:0px solid white !important;vertical-align: top;">
                        <span style="font-size: 10px;">{!! $salesQuos->footer !!}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;margin-top: 50px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 10px;">Proposed by,</span><br/>
                        @if (!is_null($salesQuos->createdBy->userDetail->signage_pic))
                            <img src="{{ $_SERVER['DOCUMENT_ROOT'].'/upl/employees/'.$salesQuos->createdBy->userDetail->signage_pic }}" style="width: 150px;" alt=""><br/>
                        @else
                            {!! '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>' !!}
                        @endif
                        <span style="font-size: 10px;border-bottom:1px solid black;">{{ $salesQuos->createdBy->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($salesQuos->branch)?$salesQuos->branch->name:'')  }}</span>
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
