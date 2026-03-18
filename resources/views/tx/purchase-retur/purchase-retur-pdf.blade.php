<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Purchase Retur : {{ $returs->purchase_retur_no }}</title>

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
                        <span style="font-size: 12px;">To : <br/>{{ (!is_null($returs->supplier_entity_type)?$returs->supplier_entity_type->title_ind:'').' '.(!is_null($returs->supplier)?$returs->supplier->name:'') }}</span><br/>
                        <span style="font-size: 12px;">Att: {{ $returs->supplier?$returs->supplier->pic1_name:'' }}</span><br/>
                        @php
                            $po_mo = '';
                        @endphp
                        @if (!is_null($returs->receipt_order))
                            @php
                                $arr = explode(",",$returs->receipt_order->po_or_pm_no);
                            @endphp
                            @foreach ($arr as $a)
                                @if($a!='')
                                    @php
                                        $po_mo .= $a.', ';
                                    @endphp
                                @endif
                            @endforeach
                        @endif
                        <span style="font-size: 12px;">PO/MO No : {{ substr($po_mo,0,strlen($po_mo)-2) }}</span><br/>
                        <span style="font-size: 12px;">Inv No : {{ (!is_null($returs->receipt_order)?$returs->receipt_order->invoice_no:'') }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        {{-- <span style="font-weight: bold;font-size: 13px;">PURCHASE RETUR</span><br/> --}}
                        <span style="font-size: 12px;">{{ date_format(date_create($returs->purchase_retur_date),"d/m/Y") }}</span><br/>
                        <span style="font-size: 12px;font-weight:bold;">Retur No : {{ $returs->purchase_retur_no }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">PURCHASE RETUR</span>
                    </td>
                </tr>
            </table>

            @php
                $currency_name = '';
            @endphp
            @if (!is_null($returs->currency))
                @php
                    $currency_name = $returs->currency->string_val;
                @endphp
            @endif

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Parts No</th>
                        <th>Parts Name</th>
                        <th colspan="2">Qty</th>
                        <th>Price({{ $currency_name }})</th>
                        <th>Total({{ $currency_name }})</th>
                        <th>Remarks</th>
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
                                <td class="val-num">{{ $p->qty_retur }}</td>
                                <td style="text-align: center;">{{ (!is_null($p->part->quantity_type)?$p->part->quantity_type->title_ind:'') }}</td>
                                <td class="val-str" style="text-align: right;">{{ number_format($p->final_cost,0,'.',',') }}</td>
                                <td class="val-str" style="text-align: right;">{{ number_format($p->final_cost*$p->qty_retur,0,'.',',') }}</td>
                                <td class="val-str">{{ $p->description }}</td>
                                @php
                                    $total_price += ($p->final_cost*$p->qty_retur);
                                @endphp
                            </tr>
                        @endif
                        @php
                            $i+=1;
                            $qty+=$p->qty;
                            $tot+=($p->qty*$p->price);
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="6" style="text-align: right;">
                            Total
                        </td>
                        <td style="text-align: right;">{{ number_format($total_price,0,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;">
                            VAT
                        </td>
                        <td style="text-align: right;">{{ number_format($returs->vat_val*$total_price/100,0,'.',',') }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" style="text-align: right;">
                            Grand Total
                        </td>
                        <td style="text-align: right;">{{ number_format(($returs->vat_val*$total_price/100)+$total_price,0,'.',',') }}</td>
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
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;">Retur By,</span><br/><br/><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;text-decoration:underline">{{ $returs->createdBy->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                        <span style="font-size: 10px;">{{ !is_null($returs->createdBy->userDetail->branch)?$returs->createdBy->userDetail->branch->name:'' }}</span><br/>
                        <span style="font-size: 10px;">Approved by: {{ !is_null($returs->approvedBy)?$returs->approvedBy->name:'' }}</span><br/>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 12px;">Receipt By,</span><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                        {{-- <span style="font-size: 12px;text-decoration:underline">&nbsp;</span><br/> --}}
                        <span style="font-size: 10px;border-top:1px solid black;">{{ (!is_null($returs->supplier_entity_type)?$returs->supplier_entity_type->title_ind:'').' '.(!is_null($returs->supplier)?$returs->supplier->name:'') }}</span><br/>
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
