<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Kwitansi : {{ $qInv->invoice_no }}</title>

        <style>
            table thead,
            table tr,
            table th {
                text-align: center;
                font-size: 12px;
                font-weight: bold;
                /* padding: 5px; */
                /* border: 1px solid black !important; */
            }

            table tbody,
            table tr,
            table td {
                text-align: center;
                font-size: 10px;
                font-weight: 300;
                /* padding: 5px; */
                /* border: 1px solid black !important; */
            }

            table {
                border: 1px solid black !important;
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
            table tr,
            table td.footer {
                text-align: left;
                vertical-align: text-top;
                font-size: 10px;
                font-weight: 300;
                /* padding: 5px; */
                /* border: 1px solid black !important; */
            }

            @page {
                /* mengatur posisi relatif atas/bawah */
                margin: 50px 50px;
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

            div.a {
                transform: rotate(-90deg);

                /* Legacy vendor prefixes that you probably don't need... */

                /* Safari */
                -webkit-transform: rotate(-90deg);

                /* Firefox */
                -moz-transform: rotate(-90deg);

                /* IE */
                -ms-transform: rotate(-90deg);

                /* Opera */
                -o-transform: rotate(-90deg);

                /* Internet Explorer */
                filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
            }

            table.no_border {
                border:0px solid white;
            }

            .jajarangenjang {
                background: whitesmoke;
                /* padding: 20px; */
                float: left;
                /* margin: 50px; */
                /* width: 150px; */
                /* height: 150px; */
                /* border:0px solid black; */
                transform: skewX(20deg)!important;
                -ms-transform: skewX(20deg)!important;
                -webkit-transform: skewX(20deg)!important;
            }

        </style>
    </head>

    <body>
        <div class="container-fluid" style="border: 3px solid black;">
            <table style="width: 100%;" autosize="1.6">
                <tr style="border:1px solid black !important;">
                    <td style="width: 15%;text-align: center;">
                        <table>
                            <tr style="padding-top: 15px;background-color:whitesmoke;">
                                <td>&nbsp;</td>
                                <td text-rotate="90" style="font-size: 10px;padding-top: 15px;padding-bottom: 15px;height:200px;">{{ $companyName }}</td>
                                <td text-rotate="90" style="font-size: 10px;padding-top: 15px;padding-bottom: 15px;">{{ $company->office_address }}</td>
                                <td text-rotate="90" style="font-size: 10px;padding-top: 15px;padding-bottom: 15px;">{{ $company->city->city_name }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 85%;text-align: left;vertical-align: top;padding: 5px;">
                        <table class="no_border" style="width: 100%;">
                            <tr>
                                <td colspan="3" style="font-size: 27px;font-weight:bold;text-decoration:underline;">
                                    K W I T A N S I
                                </td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td style="text-align: left;width:18%;font-weight:bold;">Sudah terima dari</td>
                                <td style="text-align: left;width:2%;font-weight:bold;">:</td>
                                <td style="text-align: left;width:80%;font-weight:bold;">
                                    {{ (!is_null($qInv->customer->entity_type)?$qInv->customer->entity_type->string_val:'').' '.$qInv->customer->name }}
                                </td>
                            </tr>
                            @php
                                $grandTotalVal=0;
                                $totalValbeforeVAT=0;
                                $totalValafterVAT=0;
                                $iRow=0;
                                $all_selected_FK=explode(",",$all_selected_FK_from_db);
                            @endphp
                            @for ($lastCounter=0;$lastCounter<count($all_selected_FK);$lastCounter++)
                                @if ($all_selected_FK[$lastCounter]!='')
                                    @php
                                        $iRow+=1;
                                        $vat=0;
                                        $qFK = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices','tx_delivery_orders.tax_invoice_id','=','tx_tax_invoices.id')
                                        ->select(
                                            'tx_delivery_orders.*',
                                            'tx_delivery_orders.id as faktur_id',
                                            'tx_tax_invoices.fp_no'
                                        )
                                        ->where('tx_delivery_orders.delivery_order_no','=',$all_selected_FK[$lastCounter])
                                        ->first();
                                    @endphp
                                    @if ($qFK)
                                        @if ($qFK->is_vat=='Y')
                                            @php
                                                $vat=$qFK->total_after_vat-$qFK->total_before_vat;
                                            @endphp
                                        @endif
                                        @php
                                            // nota retur - begin
                                            $retur_total_before_vat = 0;
                                            $nota_retur = \App\Models\Tx_nota_retur::select(
                                                'total_before_vat'
                                            )
                                            ->whereRaw('approved_by IS NOT null')
                                            ->where([
                                                'delivery_order_id'=>$qFK->faktur_id,
                                                'active'=>'Y',
                                            ])
                                            ->first();
                                            if ($nota_retur){
                                                $retur_total_before_vat = $nota_retur->total_before_vat;
                                            }
                                            // nota retur - end

                                            $all_cust_doc_no_arr=explode(",",$qFK->sales_order_no_all);
                                            $all_cust_doc_no='';
                                            $grandTotalVal+=($qFK->total_after_vat-$retur_total_before_vat);
                                        @endphp
                                        @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                            @if ($all_cust_doc_no_arr[$c_doc]!='')
                                                @php
                                                    $so = \App\Models\Tx_sales_order::where('sales_order_no','=',$all_cust_doc_no_arr[$c_doc])
                                                    ->first();
                                                @endphp
                                                @if ($so)
                                                    @php
                                                        $all_cust_doc_no.=','.$so->customer_doc_no;
                                                    @endphp
                                                @endif
                                            @endif
                                        @endfor
                                        @php
                                            $totalValbeforeVAT+=($qFK->total_before_vat-$retur_total_before_vat);
                                            $totalValafterVAT+=($qFK->total_after_vat-$retur_total_before_vat);
                                        @endphp
                                    @endif
                                @endif
                            @endfor
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
                                        $temp = " Seratus".penyebut($nilai - 100);
                                    } else if ($nilai < 1000) {
                                        $temp = penyebut($nilai/100)." Ratus".penyebut($nilai % 100);
                                    } else if ($nilai < 2000) {
                                        $temp = " seribu".penyebut($nilai - 1000);
                                    } else if ($nilai < 1000000) {
                                        $temp = penyebut($nilai/1000)." Ribu".penyebut($nilai % 1000);
                                    } else if ($nilai < 1000000000) {
                                        $temp = penyebut($nilai/1000000)." Juta".penyebut($nilai % 1000000);
                                    } else if ($nilai < 1000000000000) {
                                        $temp = penyebut($nilai/1000000000)." Milyar".penyebut(fmod($nilai,1000000000));
                                    } else if ($nilai < 1000000000000000) {
                                        $temp = penyebut($nilai/1000000000000)." Trilyun".penyebut(fmod($nilai,1000000000000));
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
                                <td style="text-align: left;font-weight:bold;">Uang Sejumlah</td>
                                <td style="text-align: left;font-weight:bold;">:</td>
                                <td style="text-align: left;padding-top: 10px;padding-bottom: 10px;">
                                    <div class="jajarangenjang" style="font-size: 13px;font-weight:bold;font-style:italic;">
                                        #{{ terbilang(number_format($grandTotalVal,0,'','')).' '.$qCurrency->title_ind }}#
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align: left;font-weight:bold;">Untuk pembayaran</td>
                                <td style="text-align: left;font-weight:bold;">:</td>
                                <td style="text-align: left;font-weight:bold;">{{ $qInv->remark }}</td>
                            </tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr><td>&nbsp;</td></tr>
                            <tr>
                                <td colspan="2" style="vertical-align:bottom;text-align: left;font-weight:bold;">
                                    &nbsp;<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                                    Terbilang&nbsp;{{ $qCurrency->string_val }}&nbsp;#{{ number_format($grandTotalVal,0,'.',',') }}<br/>
                                </td>
                                <td style="font-weight:bold;padding-left: 175px;">
                                    <br/>{{ $company->city->city_name }}, {{ $date_local->format('d F Y') }}
                                    <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>{{ $companyName }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" style="text-align: left;font-size:8px;">
                                    Pembayaran dengan BG/Cek atau Transfer:<br/>
                                    {{ (!is_null($company_bank_info)?$company_bank_info->account_name:'').' - '.
                                        (!is_null($company_bank_info)?$company_bank_info->bank_name:'').' '.
                                        (!is_null($company_bank_info)?$company_bank_info->account_no:'') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

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
