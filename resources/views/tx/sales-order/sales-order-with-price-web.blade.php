<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sales Order : {{ $sales_orders->sales_order_no }}</title>

        <style>
            @font-face {
                font-family: dotMatrix;
                src: url({{ url('assets/fonts/DOTMATRI.TTF') }});
            }

            html body {
                font-family: "Calibri";
                font-size: 17px;
            }

            thead tr th {
                border-top: 1px solid black;
                border-bottom: 1px solid black;
                /* padding: 5px; */
                font-size: 17px;
                font-family: 'Calibri';
            }

            .no-idx, .val-num {
                text-align: right;
            }

            @media print {
                html, body {
                    width: 8.5in; /* was 8.5in */
                    height: 8.5in; /* was 5.5in */
                    /* font-family: "Courier 10 cpi"; */
                    /* font-family: "Courier New"; */
                    /* font-family: "Calibri"; */
                    /* font-family: dotMatrix !important; */
                    /*font-size: auto; NOT A VALID PROPERTY */
                    /* padding-top: 3px; */
                    margin: auto;
                    display: block;
                }

                @page {
                    size: 8.5in 8.5in /* . Random dot? */;
                }
            }

        </style>
    </head>

    <body>
        <main>
            <table style="width: 100%;background-color: white;margin-bottom: 25px;">
                <tr>
                    <td style="width: 50%;text-align: left;">
                        {{ $companyName }}<br/>
                        {{ (!is_null($userLogin->branch)?$userLogin->branch->city->city_name:'') }}
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;padding-left: 175px;">&nbsp;</td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;margin-bottom: 5px;">
                <tr>
                    <td style="width: 50%;text-align: left;">
                        <h2>{{ $sales_orders->sales_order_no }}</h2>
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 20%;text-align:left;">Date</td>
                                    <td style="width: 80%;text-align:left;">: {{ date_format(date_create($sales_orders->sales_order_date),"d/m/Y") }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 20%;text-align:left;">PO No</td>
                                    <td style="width: 80%;text-align:left;">: {{ $sales_orders->customer_doc_no }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 20%;text-align:left;">Sales</td>
                                    <td style="width: 80%;text-align:left;">: {{ $sales_orders->createdBy->userDetail->initial }}</td>
                                </tr>
                                @php
                                    $due_date = date_create($sales_orders->sales_order_date);
                                    date_add($due_date, date_interval_create_from_date_string($sales_orders->customer->top." days"));
                                @endphp
                                <tr>
                                    <td style="width: 20%;text-align:left;">Due Date</td>
                                    <td style="width: 80%;text-align:left;">: {{ date_format($due_date,"d/m/Y") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;padding-left: 150px;">
                        To : <br/>
                        {{ (!is_null($sales_orders->customer->entity_type)?$sales_orders->customer->entity_type->string_val:'').' '.$sales_orders->customer->name }}<br/>
                        {{ $sales_orders->cust_office_address }}<br/>
                        {{ (!is_null($sales_orders->city)?$sales_orders->city->city_name:'').' '.$sales_orders->post_code }}<br/>
                        NPWP: {{ (!is_null($sales_orders->customer)?$sales_orders->customer->npwp_no:'') }}
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;margin-bottom: 5px;">
                <tr>
                    <td style="width: 100%;text-align:center;">
                        <h1>SALES ORDER</h1>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th style="width:3%;">No.</th>
                        <th style="width:20%;">Parts No</th>
                        <th style="width:25%;">Parts Name</th>
                        <th style="width:15%;">Parts Type</th>
                        <th style="width:7%;">Qty</th>
                        <th style="width:15%;">Price({{ $qCurrency->string_val }})</th>
                        <th style="width:15%;">Total({{ $qCurrency->string_val }})</th>
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
                            <td class="no-idx">{{ $i }}.</td>
                            <td class="val-str">
                                @php
                                    $partNumber = $p->part?$p->part->part_number:'';
                                    if(strlen($partNumber)<11){
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                    }else{
                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                    }
                                @endphp
                                {{ $partNumber }}
                            </td>
                            <td class="val-str">{{ $p->part?$p->part->part_name:'' }}</td>
                            <td class="val-str" style="text-align: center;">{{ $p->part?$p->part->part_type->title_ind:'' }}</td>
                            <td class="val-num">{!! number_format($p->qty,0,'.',',').'&nbsp;'.($p->part?$p->part->quantity_type->string_val:'') !!}</td>
                            <td class="val-num">{{ number_format($p->price,0,'.',',') }}</td>
                            <td class="val-num">{{ number_format($p->qty*$p->price,0,'.',',') }}</td>
                        </tr>
                        @php
                            $i+=1;
                            $tot+=($p->qty*$p->price);
                            $totWeight+=($p->part?$p->part->weight:0);
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
                        <td colspan="5" rowspan="3" style="text-align:left;vertical-align:top;border-top: 1px solid black;">
                            Terbilang:
                            @if ($sales_orders->is_vat=='Y')
                                {{ terbilang(number_format($tot+($tot*$sales_orders->vat_val/100),0,'.','')) }}
                            @else
                                {{ terbilang(number_format($tot,0,'.','')) }}
                            @endif
                        </td>
                        <td class="val-num" style="border-top: 1px solid black;">Total</td>
                        <td class="val-num" style="border-top: 1px solid black;">{{ number_format($tot,0,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td class="val-num">VAT</td>
                        <td class="val-num">{{ number_format($tot*$sales_orders->vat_val/100,0,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td class="val-num">Grand Total</td>
                        <td class="val-num">{{ number_format($tot+($tot*$sales_orders->vat_val/100),0,'.',',') }}</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;margin-bottom: 5px;">
                <tr>
                    <td style="width: 45%;text-align: left;vertical-align: top;">
                        <table style="width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 27%;text-align:left;">Ship By</td>
                                    <td style="width: 3%;text-align:left;">:</td>
                                    <td style="width: 70%;text-align:left;">{{ (!is_null($sales_orders->courier)?$sales_orders->courier->name:'') }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 27%;text-align:left;">Weight</td>
                                    <td style="width: 3%;text-align:left;">:</td>
                                    <td style="width: 70%;text-align:left;">{{ $totWeight }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 27%;text-align:left;vertical-align: top;">Ship To</td>
                                    <td style="width: 3%;text-align:left;vertical-align: top;">:</td>
                                    <td style="width: 70%;text-align:left;vertical-align: top;">
                                        {!! (!is_null($sales_orders->customer_shipment)?$sales_orders->customer_shipment->address.' '.$sales_orders->customer_shipment->city->city_name:'') !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 27%;text-align:left;">Remark</td>
                                    <td style="width: 3%;text-align:left;">:</td>
                                    <td style="width: 70%;text-align:left;">{{ $sales_orders->remark }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="width: 30%;text-align: left;vertical-align: top;">
                        Received by,<br/><br/><br/><br/><br/><br/>
                        {{ (!is_null($sales_orders->customer->entity_type)?$sales_orders->customer->entity_type->string_val:'').' '.$sales_orders->customer->name }}
                    </td>
                    <td style="width: 25%;text-align: left;vertical-align: top;">
                        Proposed by,<br/><br/><br/><br/><br/><br/>
                        {{ $companyName }}<br/>
                        {{ (!is_null($userLogin->branch)?$userLogin->branch->name:'').' ('.$sales_orders->number_of_prints.')' }}
                    </td>
                </tr>
            </table>
        </main>

        <script>
            window.print();
        </script>
    </body>
</html>
