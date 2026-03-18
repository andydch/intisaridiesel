<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="{{ asset('assets/css/bootstrap2023.min.css') }}" rel="stylesheet" />
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}

        <title>SURAT JALAN : {{ $surat_jalans->surat_jalan_no }}</title>

        <style>
            html body main {
                font-family: Tahoma;
                /* letter-spacing: 3px; */
                /* line-height: 1.6; */
                /* padding-right: 0 !important; */
            }

            table thead tr th {
                font-size: 11px;
                text-align: center;
                padding: 2px;
            }

            table tbody tr td {
                font-size: 11px;
                padding: 2px;
            }

            tr td.title-po {
                font-size: 12px;
                font-weight: bold;
            }

            .no-idx, .val-num {
                text-align: right;
                border-right: 1px solid black;
            }

            .val-str {
                text-align: left;
                border-right: 1px solid black;
            }

            .border-header {
                border-top:1px solid black;
                border-right:1px solid black;
                border-bottom:1px solid black;
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
            <table style="width: 100%;background-color: white;margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td style="width: 50%;text-align: left;">
                            {{ $companyName }}<br/>
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->city->city_name:'') }}
                        </td>
                        <td style="width: 50%;text-align: left;vertical-align: top;padding-left: 175px;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;margin-bottom: 2px;">
                <tbody>
                    <tr>
                        <td style="width: 70%;">
                            <table style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td class="title-po" colspan="2">
                                            {{ $surat_jalans->surat_jalan_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;width: 15%;vertical-align: top;">
                                            Date
                                        </td>
                                        <td style="text-align: left;width: 85%;vertical-align: top;">
                                            : {{ date_format(date_create($surat_jalans->surat_jalan_date),"d/m/Y") }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;width: 15%;vertical-align: top;">
                                            PO No
                                        </td>
                                        <td style="text-align: left;width: 85%;vertical-align: top;">
                                            : {{ $surat_jalans->customer_doc_no }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: left;width: 15%;vertical-align: top;">
                                            Sales
                                        </td>
                                        <td style="text-align: left;width: 85%;vertical-align: top;">
                                            : {{ $surat_jalans->createdBy->userDetail->initial }}
                                        </td>
                                    </tr>
                                    @php
                                        $due_date = date_create($surat_jalans->surat_jalan_date);
                                        date_add($due_date, date_interval_create_from_date_string($surat_jalans->customer->top." days"));
                                    @endphp
                                    <tr>
                                        <td style="text-align: left;width: 15%;vertical-align: top;">
                                            Due Date
                                        </td>
                                        <td style="text-align: left;width: 85%;vertical-align: top;">
                                            : {{ date_format($due_date,"d/m/Y") }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                        <td style="width: 30%;">
                            <table style="width: 100%;">
                                <tbody>
                                    <tr>
                                        <td>
                                            To:<br/>
                                            <span style="font-weight: bold;">
                                                {{ (!is_null($surat_jalans->customer->entity_type)?$surat_jalans->customer->entity_type->string_val:'').' '.$surat_jalans->customer->name }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="line-height:1.3rem;">
                                            {{ $surat_jalans->cust_office_address }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            {{ (!is_null($surat_jalans->city)?$surat_jalans->city->city_name:'').' '.($surat_jalans->post_code=='00000'?'':$surat_jalans->post_code) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- <table style="width: 100%;margin-bottom: 2px;">
                <tbody>
                    <tr>
                        <td class="title-po" colspan="2" style="text-align: left;width:70%;vertical-align: top;">
                            {{ $surat_jalans->surat_jalan_no }}
                        </td>
                        <td style="text-align: left;width: 30%;vertical-align: top;">
                            To:<br/>
                            <span style="font-weight: bold;">
                                {{ (!is_null($surat_jalans->customer->entity_type)?$surat_jalans->customer->entity_type->string_val:'').' '.$surat_jalans->customer->name }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;width: 10%;vertical-align: top;">
                            Date
                        </td>
                        <td style="text-align: left;width: 60%;vertical-align: top;">
                            : {{ date_format(date_create($surat_jalans->surat_jalan_date),"d/m/Y") }}
                        </td>
                        <td style="text-align: left;width: 30%;vertical-align: top;line-height:1.2rem;">
                            {{ $surat_jalans->cust_office_address }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;width: 10%;vertical-align: top;">
                            PO No
                        </td>
                        <td style="text-align: left;width: 60%;vertical-align: top;">
                            : {{ $surat_jalans->customer_doc_no }}
                        </td>
                        <td style="text-align: left;width: 30%;vertical-align: top;">
                            {{ (!is_null($surat_jalans->city)?$surat_jalans->city->city_name:'').' '.($surat_jalans->post_code=='00000'?'':$surat_jalans->post_code) }}
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;width: 10%;vertical-align: top;">
                            Sales
                        </td>
                        <td style="text-align: left;width: 60%;vertical-align: top;">
                            : {{ $surat_jalans->createdBy->userDetail->initial }}
                        </td>
                        <td style="text-align: left;width: 30%;vertical-align: top;">
                            NPWP: {{ (!is_null($surat_jalans->customer)?$surat_jalans->customer->npwp_no:'') }}
                        </td>
                    </tr>
                    @php
                        $due_date = date_create($surat_jalans->surat_jalan_date);
                        date_add($due_date, date_interval_create_from_date_string($surat_jalans->customer->top." days"));
                    @endphp
                    <tr>
                        <td style="text-align: left;width: 10%;vertical-align: top;">
                            Due Date
                        </td>
                        <td style="text-align: left;width: 60%;vertical-align: top;">
                            : {{ date_format($due_date,"d/m/Y") }}
                        </td>
                        <td style="text-align: left;width: 30%;vertical-align: top;">
                            &nbsp;
                        </td>
                    </tr>
                </tbody>
            </table> --}}

            <table style="width: 100%;background-color: white;margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td class="title-po" style="width: 100%;text-align:center;">SURAT JALAN</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr style="border: 1px solid black;">
                        <th class="border-header" style="width:5%;border-left:1px solid black;">No.</th>
                        <th class="border-header" style="width:15%;">Parts No</th>
                        <th class="border-header" style="width:30%;">Parts Name</th>
                        <th class="border-header" style="width:13%;">Parts Type</th>
                        <th class="border-header" style="width:7%;">Qty</th>
                        <th class="border-header" style="width:15%;">Price({{ $qCurrency->string_val }})</th>
                        <th class="border-header" style="width:15%;">Total({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody style="border-bottom: 1px solid black;">
                    @php
                        $i=1;
                        $tot=0;
                        $totWeight=0;
                    @endphp
                    @foreach($parts AS $p)
                        <tr style="border-left: 1px solid black;border-right: 1px solid black;">
                            <td style="border-left: 1px solid black;" class="no-idx">{{ $i }}.</td>
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
                            <td class="val-str">{{ $p->part->part_type->title_ind }}</td>
                            <td class="val-str" style="text-align: right;">{!! number_format($p->qty,0,'.',',').'&nbsp;'.$p->part->quantity_type->string_val !!}</td>
                            <td class="val-num">&nbsp;</td>
                            <td class="val-num">&nbsp;</td>
                        </tr>
                        @php
                            $i+=1;
                            $tot+=($p->qty*$p->price);
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
                    <tr style="border-top: 1px solid black;">
                        <td colspan="7">&nbsp;</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td style="width: 5%;vertical-align:top;">Ship By</td>
                        <td style="width: 2%;vertical-align:top;">:</td>
                        <td style="width: 33%;vertical-align:top;">{{ (!is_null($surat_jalans->courier)?$surat_jalans->courier->name:'') }}</td>
                        <td style="width: 30%;vertical-align:top;">Received by,</td>
                        <td style="width: 30%;vertical-align:top;">Proposed by,</td>
                    </tr>
                    <tr>
                        <td style="width: 5%;vertical-align:top;">
                            Weight
                        </td>
                        <td style="width: 2%;vertical-align:top;">
                            :
                        </td>
                        <td style="width: 33%;vertical-align:top;">
                            {{ $totWeight }}
                        </td>
                        {{-- <td style="width: 30%;vertical-align:top;">
                            Received by,
                        </td>
                        <td style="width: 30%;vertical-align:top;">
                            Proposed by,
                        </td> --}}
                    </tr>
                    <tr>
                        <td style="width: 5%;vertical-align:top;">
                            Ship To
                        </td>
                        <td style="width: 2%;vertical-align:top;">
                            :
                        </td>
                        <td style="width: 33%;vertical-align:top;">
                            {!! (!is_null($surat_jalans->customer_shipment)?$surat_jalans->customer_shipment->address.
                                '<br/>'.$surat_jalans->customer_shipment->city->city_name:'') !!}
                        </td>
                        {{-- <td style="width: 30%;vertical-align:top;">
                            Received by,
                        </td>
                        <td style="width: 30%;vertical-align:top;">
                            Proposed by,
                        </td> --}}
                    </tr>
                    <tr>
                        <td style="width: 5%;vertical-align:top;">
                            Remark
                        </td>
                        <td style="width: 2%;vertical-align:top;">
                            :
                        </td>
                        <td style="width: 33%;vertical-align:top;">
                            {{ $totWeight }}
                        </td>
                        <td style="width: 30%;vertical-align:top;">
                            {{ (!is_null($surat_jalans->customer->entity_type)?$surat_jalans->customer->entity_type->string_val:'').' '.$surat_jalans->customer->name }}
                        </td>
                        <td style="width: 30%;vertical-align:top;">
                            {{ $companyName }}<br/>
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->name:'').' ('.$surat_jalans->number_of_prints.')' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </main>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous">
        </script> --}}
        <link href="{{ asset('assets/js/bootstrap2023.bundle.min.js') }}" rel="stylesheet" />

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
