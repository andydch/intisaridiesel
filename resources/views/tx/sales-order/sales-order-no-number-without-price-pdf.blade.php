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

        <title>Sales Order : {{ $sales_orders->sales_order_no }}</title>

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
                vertical-align: top
                /* border-right: 1px solid black; */
            }

            .val-str {
                text-align: left;
                vertical-align: top
                /* border-right: 1px solid black; */
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
        @endphp

        <main>
            @php
                $i=1;
                $tot=0;
                $totWeight=0;
            @endphp
            @for ($page=1;$page<=$pageTotal;$page++)
                <table style="width: 100%;margin-bottom: 5px;margin-top: 5px; @if(fmod($page,2)==0){{ 'margin-top:100px;' }}@else{{ 'margin-top:50px;' }}@endif">
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
                                        {{-- <tr>
                                            <td class="title-po" colspan="2">
                                                {{ $sales_orders->sales_order_no }}
                                            </td>
                                        </tr> --}}
                                        <tr>
                                            <td style="text-align: left;width: 15%;vertical-align: top;">
                                                Date
                                            </td>
                                            <td style="text-align: left;width: 85%;vertical-align: top;">
                                                : {{ date_format(date_create($sales_orders->sales_order_date),"d/m/Y") }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left;width: 15%;vertical-align: top;">
                                                PO No
                                            </td>
                                            <td style="text-align: left;width: 85%;vertical-align: top;">
                                                : {{ $sales_orders->customer_doc_no }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left;width: 15%;vertical-align: top;">
                                                Sales
                                            </td>
                                            <td style="text-align: left;width: 85%;vertical-align: top;">
                                                : {{ $sales_orders->createdBy->userDetail->initial }}
                                            </td>
                                        </tr>
                                        @php
                                            $due_date = date_create($sales_orders->sales_order_date);
                                            date_add($due_date, date_interval_create_from_date_string($sales_orders->customer->top." days"));
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
                                                    {{ (!is_null($sales_orders->customer->entity_type)?$sales_orders->customer->entity_type->string_val:'').' '.$sales_orders->customer->name }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="line-height:1.3rem;">
                                                {{ $sales_orders->cust_office_address }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                {{ (!is_null($sales_orders->city)?$sales_orders->city->city_name:'').' '.($sales_orders->post_code=='00000'?'':$sales_orders->post_code) }}
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
                                {{ $sales_orders->sales_order_no }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                To:<br/>
                                <span style="font-weight: bold;">
                                    {{ (!is_null($sales_orders->customer->entity_type)?$sales_orders->customer->entity_type->string_val:'').' '.$sales_orders->customer->name }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                Date
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ date_format(date_create($sales_orders->sales_order_date),"d/m/Y") }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;line-height:1.2rem;">
                                {{ $sales_orders->cust_office_address }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                PO No
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ $sales_orders->customer_doc_no }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                {{ (!is_null($sales_orders->city)?$sales_orders->city->city_name:'').' '.$sales_orders->post_code }}
                            </td>
                        </tr>
                        <tr>
                            <td style="text-align: left;width: 10%;vertical-align: top;">
                                Sales
                            </td>
                            <td style="text-align: left;width: 60%;vertical-align: top;">
                                : {{ $sales_orders->createdBy->userDetail->initial }}
                            </td>
                            <td style="text-align: left;width: 30%;vertical-align: top;">
                                NPWP: {{ (!is_null($sales_orders->customer)?$sales_orders->customer->npwp_no:'') }}
                            </td>
                        </tr>
                        @php
                            $due_date = date_create($sales_orders->sales_order_date);
                            date_add($due_date, date_interval_create_from_date_string($sales_orders->customer->top." days"));
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

                <table style="width: 100%;margin-bottom: 5px;">
                    <tr>
                        <td class="title-po" style="width: 100%;text-align:center;">SALES ORDER</td>
                    </tr>
                </table>

                @php
                    $qSoPart = \App\Models\Tx_sales_order_part::where([
                        'order_id' => $sales_orders->id,
                        'active' => 'Y'
                    ])
                    ->offset($rowsPerPage*($page-1))
                    ->limit($rowsPerPage)
                    ->get();
                @endphp
                <table cellspacing=0 cellpadding=0 style="width: 100%;margin-bottom: 5px;border-bottom:1px solid black;">
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
                    <tbody>
                        @foreach($qSoPart AS $p)
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
                                <td class="val-str">
                                    @php
                                        $partNmLen = 25;
                                        $partNm = $p->part?$p->part->part_name:'';
                                    @endphp
                                    @if (strlen($partNm)<=$partNmLen)
                                        {{ $partNm }}
                                    @else
                                        {{ substr($partNm,0,$partNmLen) }}...
                                    @endif
                                </td>
                                <td class="val-str" style="text-align: center;">{{ $p->part?$p->part->part_type->title_ind:'' }}</td>
                                <td class="val-num">{!! number_format($p->qty,0,'.',',').'&nbsp;'.($p->part?$p->part->quantity_type->string_val:'') !!}</td>
                                <td class="val-num">&nbsp;</td>
                                <td class="val-num">&nbsp;</td>
                            </tr>
                            @php
                                $i+=1;
                                $tot+=($p->qty*$p->price);
                                $totWeight+=($p->part?$p->part->weight:0);
                            @endphp
                        @endforeach
                        @if ($page==$pageTotal)
                            @if ($lastPageTotalRows>8)
                                @for ($emptyRows=0;$emptyRows<$lastPageEmptyTotalRows;$emptyRows++)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                @endfor
                            @endif
                        @endif
                    </tbody>
                </table>
            @endfor

            <table style="width: 100%;margin-bottom: 5px;">
                <tbody>
                    <tr>
                        <td style="width: 5%;vertical-align:top;">Ship By</td>
                        <td style="width: 2%;vertical-align:top;">:</td>
                        <td style="width: 33%;vertical-align:top;">{{ (!is_null($sales_orders->courier)?$sales_orders->courier->name:'') }}</td>
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
                            {!! (!is_null($sales_orders->customer_shipment)?$sales_orders->customer_shipment->address.
                                '<br/>'.$sales_orders->customer_shipment->city->city_name:'') !!}
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
                            {{ (!is_null($sales_orders->customer->entity_type)?$sales_orders->customer->entity_type->string_val:'').' '.$sales_orders->customer->name }}
                        </td>
                        <td style="width: 30%;vertical-align:top;">
                            {{ $companyName }}<br/>
                            {{ (!is_null($userLogin->branch)?$userLogin->branch->name:'').' ('.$sales_orders->number_of_prints.')' }}
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
