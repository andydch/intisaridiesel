<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Purchase Quotation : {{ $purchase_quotations->quotation_no }}</title>

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
                        To : <br/><span style="font-size: 12px;">{{ (!is_null($purchase_quotations->supplier_entity_type)?$purchase_quotations->supplier_entity_type->title_ind:'').' '.(!is_null($purchase_quotations->supplier)?$purchase_quotations->supplier->name:'') }}</span><br/>
                        <span style="font-size: 12px;">Att: {{ $purchase_quotations->supplier?($purchase_quotations->pic_idx==1?$purchase_quotations->supplier->pic1_name:$purchase_quotations->supplier->pic2_name):'' }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 12px;">{{ date_format(date_create($purchase_quotations->quotation_date),"d/m/Y") }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">{{ $purchase_quotations->quotation_no }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">PURCHASE QUOTATION</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;margin-bottom: 15px;">
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;font-size: 12px;">{!! $purchase_quotations->header !!}</td>
                </tr>
            </table>

            @php
                $currency_name = '';
            @endphp
            @if (!is_null($purchase_quotations->currency))
                @php
                    $currency_name = $purchase_quotations->currency->string_val;
                @endphp
            @endif

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Parts No</th>
                        <th>Parts Name</th>
                        <th colspan="2">Qty</th>
                        <th style="width: 30%;">Description</th>
                        <th style="width: 10%;">Net Price</th>
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
                        @if ($p->part)
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
                                <td class="val-num">{{ $p->qty }}</td>
                                <td style="text-align: center;">{{ (!is_null($p->part->quantity_type)?$p->part->quantity_type->title_ind:'') }}</td>
                                <td class="val-str">{{ $p->description }}</td>
                                <td class="val-str">&nbsp;</td>
                            </tr>
                        @endif
                        @php
                            $i+=1;
                        @endphp
                    @endforeach
                </tbody>
            </table>

            @if (!is_null($purchase_quotations->remark))
                <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;">
                    <tr style="border:0px solid white !important;">
                        <td colspan="2" style="text-align: left;border:0px solid white !important;font-size: 12px;">Remark:<br/>{{ $purchase_quotations->remark }}</td>
                    </tr>
                    <tr style="border:0px solid white !important;">
                        <td colspan="2" style="text-align: left;border:0px solid white !important;">&nbsp;</td>
                    </tr>
                </table>
            @endif

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;margin-bottom: 15px;">
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;font-size: 12px;">{!! $purchase_quotations->footer !!}</td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;">Regards,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;text-decoration:underline">{{ $purchase_quotations->createdBy->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ !is_null($purchase_quotations->createdBy->userDetail->branch)?$purchase_quotations->createdBy->userDetail->branch->name:'' }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">&nbsp;</td>
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
