<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Tanda Terima : {{ $qKwi->kwitansi_no }}</title>

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
        <!-- Define header and footer blocks before your content -->
        {{-- <header>
        </header>

        <footer>
        </footer> --}}

        <main>
            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($userLogin->branch)?$userLogin->branch->name:'') }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">&nbsp;</td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 10px;">Kepada Yth.</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($qKwi->customer->entity_type)?$qKwi->customer->entity_type->string_val:'').' '.$qKwi->customer->name }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($qKwi->customer)?$qKwi->customer->office_address:'') }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($qKwi->customer)?$qKwi->customer->city->city_name.', '.$qKwi->customer->city->country->country_name.' '.$qKwi->customer->post_code:'') }}</span><br/>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 150px;">
                        <span>&nbsp;</span><br/>
                        <span style="font-size: 10px;">TT No : {{ $qKwi->kwitansi_no }}</span><br/>
                        <span style="font-size: 10px;">Tanggal : {{ date_format(date_create($qKwi->created_at), 'd/m/Y') }}</span><br/>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">TANDA TERIMA</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:left;border:0px solid white !important;">
                        <span>{!! $qKwi->header !!}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;border: 0px solid black;">
                <thead>
                    <tr>
                        <th><span style="font-size: 10px;">Date</span></th>
                        <th><span style="font-size: 10px;">No NP</span></th>
                        <th><span style="font-size: 10px;">No SJ</span></th>
                        <th><span style="font-size: 10px;">Total({{ $qCurrency->string_val }})</span></th>
                        <th><span style="font-size: 10px;">No PO</span></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalValbeforeVAT=0;
                        $iRow=0;
                        $all_selected_NP=explode(",",$all_selected_NP_from_db);
                    @endphp
                    @for ($lastCounter=0;$lastCounter<count($all_selected_NP);$lastCounter++)
                        @if ($all_selected_NP[$lastCounter]!='')
                            @php
                                $iRow+=1;
                                $vat=0;
                                $qNP = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','=',$all_selected_NP[$lastCounter])
                                ->first();
                            @endphp
                            @if ($qNP)
                                @php
                                    // nota retur - begin
                                    $retur_total_price = 0;
                                    $nota_retur = \App\Models\Tx_nota_retur_non_tax::select(
                                        'total_price'
                                    )
                                    ->whereRaw('approved_by IS NOT null')
                                    ->where([
                                        'delivery_order_id'=>$qNP->id,
                                        'active'=>'Y',
                                    ])
                                    ->first();
                                    if ($nota_retur){
                                        $retur_total_price = $nota_retur->total_price;
                                    }
                                    // nota retur - end

                                    $all_cust_doc_no_arr=explode(",",$qNP->sales_order_no_all);
                                    $all_cust_doc_no='';
                                @endphp
                                @for ($c_doc=0;$c_doc<count($all_cust_doc_no_arr);$c_doc++)
                                    @if ($all_cust_doc_no_arr[$c_doc]!='')
                                        @php
                                            $so = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$all_cust_doc_no_arr[$c_doc])
                                            ->first();
                                        @endphp
                                        @if ($so)
                                            @php
                                                $all_cust_doc_no.=','.$so->customer_doc_no;
                                            @endphp
                                        @endif
                                    @endif
                                @endfor
                                <tr id="rowNPdetail{{ $iRow }}">
                                    <td scope="row" style="text-align:left;">
                                        <label for="" id="fk_date_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_date }}</label>
                                    </td>
                                    <td scope="row" style="text-align:left;">
                                        <label for="" id="fk_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->delivery_order_no }}</label>
                                    </td>
                                    {{-- <td scope="row" style="text-align:left;">
                                        <label for="" id="fp_no_dtl{{ $iRow }}" class="col-form-label">{{ $qNP->fp_no }}</label>
                                    </td> --}}
                                    <td scope="row" style="text-align:left;">
                                        <label for="" id="so_dtl{{ $iRow }}" class="col-form-label">{{ substr($qNP->sales_order_no_all,1,strlen($qNP->sales_order_no_all)) }}</label>
                                    </td>
                                    <td scope="row" style="text-align:right;">
                                        <label for="" id="total_before_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format(($qNP->total_price-$retur_total_price),0,'.',',') }}</label>
                                    </td>
                                    {{-- <td scope="row" style="text-align:right;">
                                        <label for="" id="vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($vat,0,'.',',') }}</label>
                                    </td>
                                    <td scope="row" style="text-align:right;">
                                        <label for="" id="total_after_vat_dtl{{ $iRow }}" class="col-form-label">{{ number_format($qNP->total_after_vat,0,'.',',') }}</label>
                                    </td> --}}
                                    <td scope="row" style="text-align:left;">
                                        <label for="" id="cust_doc_no_dtl{{ $iRow }}" class="col-form-label">{{ substr($all_cust_doc_no,1,strlen($all_cust_doc_no)) }}</label>
                                    </td>
                                </tr>
                                @php
                                    $totalValbeforeVAT+=($qNP->total_price-$retur_total_price);
                                @endphp
                            @endif
                        @endif
                    @endfor
                </tbody>
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
                <tfoot>
                    <tr id="rowNPdetail-grandtotal">
                        <td scope="row" colspan="2" style="text-align:left;vertical-align:top;">
                            <label for="" id="" class="col-form-label"><span style="font-weight: bold;">Terbilang:</span></label>&nbsp;
                            <label for="" id="" class="col-form-label">{{ terbilang($totalValbeforeVAT) }}</label>
                        </td>
                        <td scope="row" style="text-align:right;"><label for="" id="grandtotallbl" class="col-form-label">TOTAL</label></td>
                        <td scope="row" style="text-align:right;"><label for="" id="grandtotalnumlbl" class="col-form-label">{{ number_format($totalValbeforeVAT,0,'.',',') }}</label></td>
                        <td scope="row">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:left;border:0px solid white !important;">
                        <span>Jatuh Tempo: {{ date_format(date_create($qKwi->kwitansi_date), 'd/m/Y') }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:left;border:0px solid white !important;">
                        <span>{!! $qKwi->footer !!}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 40%;text-align: left;vertical-align: top;border:0px solid white !important;">
                        <span style="font-size: 10px;">Proposed by,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 10px;border-top:1px solid black;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($userLogin->branch)?$userLogin->branch->name:'') }}</span>
                    </td>
                    <td style="width: 20%;text-align: left;vertical-align: middle;">
                        <span style="font-size: 16px;font-weight:bold;">{{ is_null($company_bank_info)?'-':$company_bank_info->bank_name }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">{{ is_null($company_bank_info)?'-':$company_bank_info->bank_address }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">{{ is_null($company_bank_info)?'-':$company_bank_info->account_no }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">{{ is_null($company_bank_info)?'-':$company_bank_info->account_name }}</span><br/>
                    </td>
                    <td style="width: 40%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 80px;">
                        <span style="font-size: 10px;">Received by,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 10px;border-top:1px solid black;">{{ (!is_null($qKwi->customer->entity_type)?$qKwi->customer->entity_type->string_val:'').' '.$qKwi->customer->name }}</span>
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
