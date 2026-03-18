<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}
        <link href="{{ asset('assets/css/bootstrap2023.min.css') }}" rel="stylesheet" />

        <title>Faktur : {{ $fakturs->delivery_order_no }}</title>

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

            tr td.title-fk {
                font-size: 12px;
                font-weight: bold;
            }

            tr td.title-main {
                font-size: 14px;
                font-weight: bold;
            }

            .no-idx, .val-num {
                text-align: right;
                vertical-align: top;
                border-right: 1px solid black;
            }

            .val-str {
                text-align: left;
                vertical-align: top;
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

        @php
            $rowsPerPage = 13;
            $pageTotal = ceil($partsCount/$rowsPerPage);      // jumlah halaman
            $lastPageTotalRows = fmod($partsCount,$rowsPerPage);
            $lastPageEmptyTotalRows = ($rowsPerPage-$lastPageTotalRows)+8;

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

        <main>
            @php
                $i = 1;
                $tot = 0;
                $totWeight = 0;
            @endphp
            {{-- @for ($page=1;$page<=$pageTotal;$page++) --}}
                <table style="width: 100%;margin-bottom: 5px;">
                    <tbody>
                        <tr>
                            <td style="width: 100%;text-align: left;">
                                {{ $companyName }}<br/>
                                {{ (!is_null($userLogin->branch)?$userLogin->branch->address:'') }}<br/>
                                {{ (!is_null($userLogin->branch)?$userLogin->branch->city->city_name.' '.$userLogin->branch->post_code:'') }}<br/>
                                NPWP: {{ $npwpNo }}
                            </td>
                            {{-- <td style="width: 50%;text-align: left;vertical-align: top;padding-left: 175px;">&nbsp;</td> --}}
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
                                            <td class="title-fk" colspan="2">
                                                {{ $fakturs->delivery_order_no }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left;width: 8%;vertical-align: top;">
                                                Date
                                            </td>
                                            <td style="text-align: left;width: 92%;vertical-align: top;">
                                                : {{ date_format(date_create($fakturs->delivery_order_date),"d/m/Y") }}
                                            </td>
                                        </tr>
                                        @php
                                            $fakturPajak = (!is_null($fakturs->tax_invoice)?$fakturs->tax_invoice->prefiks_code.$fakturs->tax_invoice->fp_no:'');
                                            if ($fakturPajak!=''){
                                                $fakturPajak = substr($fakturPajak,0,3).'.'.substr($fakturPajak,3,3).'-'.substr($fakturPajak,6,2).'.'.substr($fakturPajak,8,strlen($fakturPajak));
                                            }
                                        @endphp
                                        <tr>
                                            <td style="text-align: left;width: 8%;vertical-align: top;">
                                                FP No
                                            </td>
                                            <td style="text-align: left;width: 92%;vertical-align: top;">
                                                : {{ $fakturPajak }}
                                            </td>
                                        </tr>
                                        @php
                                            $so = explode(",",substr($fakturs->sales_order_no_all,1,strlen($fakturs->sales_order_no_all)));
                                            $cust_doc_no = '';
                                        @endphp
                                        @foreach ($so as $s)
                                            @php
                                                $qDocNo = \App\Models\Tx_sales_order::where('sales_order_no','=',$s)
                                                ->where('active','=','Y')
                                                ->first();
                                            @endphp
                                            @if ($qDocNo)
                                                @php
                                                    $cust_doc_no .= ','.$qDocNo->customer_doc_no;
                                                @endphp
                                            @endif
                                        @endforeach
                                        @if ($cust_doc_no!='')
                                            @php
                                                $cust_doc_noStr = '';
                                                $cust_doc_noArr = explode(",",substr(substr($cust_doc_no,0,strlen($cust_doc_no)),0,strlen(substr($cust_doc_no,0,strlen($cust_doc_no)))));
                                                for($i=0;$i<count($cust_doc_noArr);$i++){
                                                    $cust_doc_noStr .= ($cust_doc_noArr[$i]!=''?
                                                        ($cust_doc_noArr[$i].(($i+1)%5==0?
                                                            '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':
                                                            ','))
                                                        :'');
                                                }
                                                $cust_doc_no = substr($cust_doc_noStr,0,strlen($cust_doc_noStr)-1);
                                            @endphp
                                        @endif
                                        <tr>
                                            <td style="text-align: left;width: 8%;vertical-align: top;">
                                                PO No
                                            </td>
                                            <td style="text-align: left;width: 92%;vertical-align: top;">
                                                : {{ $cust_doc_no }}
                                            </td>
                                        </tr>
                                        @php
                                            $soStr = '';
                                            $soArr = explode(",",substr($fakturs->sales_order_no_all,1,strlen($fakturs->sales_order_no_all)));
                                            for($i=0;$i<count($soArr);$i++){
                                                $soStr .= $soArr[$i].(($i+1)%5==0?'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':',');
                                            }
                                            $soStr = substr($soStr,0,strlen($soStr)-1);
                                        @endphp
                                        <tr>
                                            <td style="text-align: left;width: 8%;vertical-align: top;">
                                                SO No
                                            </td>
                                            <td style="text-align: left;width: 92%;vertical-align: top;">
                                                : {!! $soStr !!}
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
                                                To : <br/>
                                                <span style="font-weight: bold;">
                                                    {{ (!is_null($fakturs->customer->entity_type)?$fakturs->customer->entity_type->string_val:'').' '.$fakturs->customer->name }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:1.3rem;">
                                                {{ $fakturs->customer->office_address }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                {{ (!is_null($fakturs->customer->city)?$fakturs->customer->city->city_name:'').' '.$fakturs->customer->post_code }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                NPWP: {{ $fakturs->customer->npwp_no }}
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
                            <td class="title-fk" colspan="2" style="text-align: left;width:70%;vertical-align: top;">
                                {{ $fakturs->delivery_order_no }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                To : <br/>
                                <span style="font-weight: bold;">
                                    {{ (!is_null($fakturs->customer->entity_type)?$fakturs->customer->entity_type->string_val:'').' '.$fakturs->customer->name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                Date
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ date_format(date_create($fakturs->delivery_order_date),"d/m/Y") }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                {{ $fakturs->customer->office_address }}
                            </td>
                        </tr>
                        @php
                            $fakturPajak = (!is_null($fakturs->tax_invoice)?$fakturs->tax_invoice->prefiks_code.$fakturs->tax_invoice->fp_no:'');
                            if ($fakturPajak!=''){
                                $fakturPajak = substr($fakturPajak,0,3).'.'.substr($fakturPajak,3,3).'-'.substr($fakturPajak,6,2).'.'.substr($fakturPajak,8,strlen($fakturPajak));
                            }
                        @endphp
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                FP No
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ $fakturPajak }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                {{ (!is_null($fakturs->customer->city)?$fakturs->customer->city->city_name:'').' '.$fakturs->customer->post_code }}
                            </td>
                        </tr>
                        @php
                            $so = explode(",",substr($fakturs->sales_order_no_all,1,strlen($fakturs->sales_order_no_all)));
                            $cust_doc_no = '';
                        @endphp
                        @foreach ($so as $s)
                            @php
                                $qDocNo = \App\Models\Tx_sales_order::where('sales_order_no','=',$s)
                                ->where('active','=','Y')
                                ->first();
                            @endphp
                            @if ($qDocNo)
                                @php
                                    $cust_doc_no .= ','.$qDocNo->customer_doc_no;
                                @endphp
                            @endif
                        @endforeach
                        @if ($cust_doc_no!='')
                            @php
                                $cust_doc_noStr = '';
                                $i = 0;
                                $cust_doc_noArr = explode(",",substr(substr($cust_doc_no,0,strlen($cust_doc_no)),0,strlen(substr($cust_doc_no,0,strlen($cust_doc_no)))));
                                for($i=0;$i<count($cust_doc_noArr);$i++){
                                    $cust_doc_noStr .= ($cust_doc_noArr[$i]!=''?
                                        ($cust_doc_noArr[$i].(($i+1)%5==0?
                                            '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':
                                            ','))
                                        :'');
                                }
                                $cust_doc_no = substr($cust_doc_noStr,0,strlen($cust_doc_noStr)-1);
                            @endphp
                        @endif
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                PO No
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ $cust_doc_no }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                NPWP: {{ $fakturs->customer->npwp_no }}
                            </td>
                        </tr>
                        @php
                            $soStr = '';
                            $i = 0;
                            $soArr = explode(",",substr($fakturs->sales_order_no_all,1,strlen($fakturs->sales_order_no_all)));
                            for($i=0;$i<count($soArr);$i++){
                                $soStr .= $soArr[$i].(($i+1)%5==0?'<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;':',');
                            }
                            $soStr = substr($soStr,0,strlen($soStr)-1);
                        @endphp
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                SO No
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {!! $soStr !!}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">&nbsp;</td>
                        </tr>
                    </tbody>
                </table> --}}

                <table style="width: 100%;margin-bottom: 5px;">
                    <tbody>
                        <tr>
                            <td class="title-main" style="width: 100%;text-align:center;">FAKTUR</td>
                        </tr>
                    </tbody>
                </table>

                <table style="width: 100%;margin-bottom: 10px;">
                    <thead>
                        <tr style="border: 1px solid black;">
                            <th class="border-header" style="width: 5%;">No</th>
                            <th class="border-header" style="width: 15%;">Parts No</th>
                            <th class="border-header" style="width: 30%;">Parts Name</th>
                            <th class="border-header" style="width: 13%;">Parts Type</th>
                            <th class="border-header" style="width: 7%;">Qty</th>
                            <th class="border-header" style="width: 15%;">Price({{ $qCurrency->string_val }})</th>
                            <th class="border-header" style="width: 15%;">Total({{ $qCurrency->string_val }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $i=1;
                            $tot=0;
                            $totWeight=0;
                        @endphp
                        @foreach($parts AS $p)
                            @if ($p->part)
                                <tr style="border-left: 1px solid black;border-right: 1px solid black;">
                                    <td class="no-idx">{{ $i }}.</td>
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
                                    <td class="val-str" style="text-align:center;">{{ $p->part->part_type->title_ind }}</td>
                                    <td class="val-num">{!! number_format($p->qty,0,'.',',').'&nbsp;'.$p->part->quantity_type->string_val !!}</td>
                                    <td class="val-num">{{ number_format($p->final_price,0,'.',',') }}</td>
                                    <td class="val-num">{{ number_format($p->qty*$p->final_price,0,'.',',') }}</td>
                                </tr>
                            @endif
                            @php
                                $i += 1;
                                $tot += ($p->qty*$p->final_price);
                                $totWeight += ($p->part?$p->part->weight:0);
                            @endphp
                        @endforeach
                        {{-- @if ($page==$pageTotal) --}}
                            {{-- @if ($lastPageTotalRows>8)
                                @for ($emptyRows=0;$emptyRows<$lastPageEmptyTotalRows;$emptyRows++) --}}
                                    {{-- <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr> --}}
                                {{-- @endfor
                            @endif --}}
                            <tr style="border-top: 1px solid black;border-right: 1px solid black;">
                                <td colspan="5" rowspan="3" style="text-align:left;vertical-align:top;">
                                    Terbilang:
                                    @if ($fakturs->is_vat=='Y')
                                        {{ terbilang($tot+($tot*$fakturs->vat_val/100)) }}
                                    @else
                                        {{ terbilang($tot) }}
                                    @endif
                                </td>
                                <td class="val-num" style="border-left: 1px solid black;">Total</td>
                                <td class="val-num">{{ number_format($tot,0,'.',',') }}</td>
                            </tr>
                            @if ($fakturs->is_vat=='Y')
                                <tr style="border-right: 1px solid black;">
                                    <td class="val-num" style="border-left: 1px solid black;">VAT</td>
                                    <td class="val-num">{{ number_format($tot*$fakturs->vat_val/100,0,'.',',') }}</td>
                                </tr>
                                <tr style="border-right: 1px solid black;border-bottom: 1px solid black;">
                                    <td class="val-num" style="border-left: 1px solid black;">Grand Total</td>
                                    <td class="val-num">{{ number_format($tot+($tot*$fakturs->vat_val/100),0,'.',',') }}</td>
                                </tr>
                            @endif
                        {{-- @endif --}}
                    </tbody>
                </table>
            {{-- @endfor --}}

            <table style="width: 100%;margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td style="width: 40%;text-align: left;vertical-align: top;">
                            Remark:<br/>{{ $fakturs->remark }}
                        </td>
                        <td style="width: 30%;text-align: left;vertical-align: top;">
                            Received by,<br/><br/><br/><br/><br/><br/>
                            {{ (!is_null($fakturs->customer->entity_type)?$fakturs->customer->entity_type->string_val:'').' '.$fakturs->customer->name }}
                        </td>
                        <td style="width: 30%;text-align: left;vertical-align: top;">
                            Proposed by,<br/><br/><br/><br/><br/><br/>
                            {{ $companyName }}<br/>
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->name:'').' ('.$num_of_print.')' }}
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
        <script src="{{ asset('assets/js/bootstrap2023.bundle.min.js') }}"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
