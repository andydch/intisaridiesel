<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> --}}
            <link href="{{ asset('assets/css/bootstrap2023.min.css') }}" rel="stylesheet">

        <title>Purchase Order : {{ $orders->purchase_no }}</title>

        <style>
            table thead,
            table tr,
            table th {
                text-align: center;
                font-size: 12;
                font-weight: bold;
                padding: 5px;
                border: 1px solid black !important;
            }

            table tbody,
            table tr,
            table td {
                text-align: center;
                font-size: 5;
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
                vertical-align: top;
                font-size: 10;
                font-weight: 300;
                /* border: 1px solid black !important; */
            }

            table td.val-str {
                text-align: left;
                vertical-align: top;
                font-size: 10;
                font-weight: 300;
            }

            table tfoot,
            /* table tr, */
            table td.footer {
                text-align: left;
                vertical-align: text-top;
                font-size: 10;
                font-weight: 300;
                padding: 5px;
                border: 0px solid black !important;
            }

            @page {
                /* mengatur posisi relatif atas/bawah */
                margin: 100px 25px;
                margin-left: 3cm;
                /* margin-right: 1cm; */

                header: page-header;
                footer: page-footer;
            }

            header {
                position: fixed;
                top: -60px;
                left: 0px;
                right: 0px;
                height: 20px;
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
        <htmlpageheader name="page-header">
            &nbsp;
        </htmlpageheader>

        <htmlpagefooter name="page-footer">
            &nbsp;
        </htmlpagefooter>

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

            <table style="width: 100%;background-color: white;margin-bottom: 10px;border:0px solid white !important;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 35%;text-align: left;vertical-align: top;">
                        @php
                            $to = (!is_null($orders->supplier_entity_type)?$orders->supplier_entity_type->title_ind:'').' '.
                                $orders->supplier_name.'<br/>'.$orders->supplier_office_address.
                                '<br/>'.($orders->supplier_city->city_name!='Other'?$orders->supplier_city->city_name:'').', '.
                                ($orders->supplier_province->province_name!='Other'?$orders->supplier_province->province_name:'');
                        @endphp
                        <span style="font-size: 12px;">To :</span><br/><span style="font-size: 12px;">{!! $to !!}</span><br/><br/>
                        <span style="font-size: 12px;">Att: {{ $orders->supplier?($orders->pic_idx==1?$orders->supplier->pic1_name:$orders->supplier->pic2_name):'' }}</span>
                    </td>
                    <td style="width: 10%;border:1px solid white !important;">&nbsp;</td>
                    <td style="width: 25%;text-align: left;padding-left: 130px;vertical-align: top;border:0px solid black;">
                        <span style="font-size: 12px;">{{ $orders->branch?$orders->branch->name:'' }}, {{ date_format(date_create($orders->purchase_date),"d/m/Y") }}</span><br/>
                        {{-- <span style="font-size: 12px;font-weight:bold;">PO No : {{ $orders->purchase_no }}</span> --}}
                    </td>
                </tr>
            </table>
            <table style="border:0px solid white !important;">
                <tr style="border:0px solid white !important;">
                    <td style="width:35%;border:0px solid white !important;">
                        <span style="font-size: 12px;font-weight:bold;">PO No : {{ $orders->purchase_no.($qIsDirector?'-REV':'') }}</span>
                    </td>
                    <td colspan="2" style="border:0px solid white !important;">&nbsp;</td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 5px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">PURCHASE ORDER</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 25px;">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Parts No</th>
                        <th style="width: 14%;">Parts Name</th>
                        <th style="width: 12%;">Parts Type</th>
                        <th colspan="2" style="width: 12%;">Qty</th>
                        @php
                            $supplierCurrency = '';
                            $qSupplierBankInfo = \App\Models\Mst_supplier_bank_information::where('supplier_id','=',$orders->supplier_id)
                            ->first();
                            if ($qSupplierBankInfo){
                                $supplierCurrency = $qSupplierBankInfo->currency->string_val;
                            }
                        @endphp
                        <th style="width: 13%;">Price ({{ $supplierCurrency }})</th>
                        <th style="width: 13%;">Total</th>
                        <th style="width: 15%;">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                        $qty=0;
                        $tot=0;
                    @endphp
                    @foreach($parts AS $p)
                        <tr>
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
                            <td class="val-str" style="text-align: center;">{{ $p->part->part_type->title_ind }}</td>
                            <td class="val-num" style="width: 7%;">{{ $p->qty }}</td>
                            <td class="val-str" style="text-align: center;width: 5%;">{{ $p->part->quantity_type->title_ind }}</td>
                            <td class="val-num">{{ number_format($p->price,$orders->supplier?($orders->supplier->supplier_type_id==10?2:0):0,".",",") }}</td>
                            <td class="val-num">{{ number_format($p->qty * $p->price,$orders->supplier?($orders->supplier->supplier_type_id==10?2:0):0,".",",") }}</td>
                            <td class="val-str">{{ $p->description }}</td>
                        </tr>
                        @php
                            $i+=1;
                            $qty+=$p->qty;
                            $tot+=($p->qty*$p->price);
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="7" class="val-num">Total</td>
                        <td class="val-num">{{ number_format($tot,$orders->supplier?($orders->supplier->supplier_type_id==10?2:0):0,".",",") }}</td>
                        <td rowspan="3">&nbsp;</td>
                    </tr>
                    @if ($orders->supplier)
                        @if ($orders->supplier->supplier_type_id==11)
                            <tr>
                                <td colspan="7" class="val-num">VAT</td>
                                <td class="val-num">{{ $orders->is_vat=='Y'?number_format($tot*$orders->vat_val/100,($orders->supplier->supplier_type_id==10?2:0),".",","):'' }}</td>
                            </tr>
                            <tr>
                                <td colspan="7" class="val-num">Grand Total</td>
                                <td class="val-num">{{ $orders->is_vat=='Y'?number_format($tot+($tot*$orders->vat_val/100),($orders->supplier->supplier_type_id==10?2:0),".",","):number_format($tot,($orders->supplier->supplier_type_id==10?2:0),".",",") }}</td>
                            </tr>
                        @endif
                    @endif
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;">
                        @php
                            $deliver_to = $companyName.'<br/>'.($orders->branch?$orders->branch->name:'').'<br/>'.($orders->branch?$orders->branch->address:'').'<br/>'.
                            ($orders->branch?$orders->branch->city->city_name:'').', '.($orders->branch?$orders->branch->province->province_name:'');
                        @endphp
                        <span style="font-size: 12px;">Deliver To:<br/>{!! $deliver_to !!}</span><br/><br/>
                    </td>
                    <td style="width: 50%;text-align: left;border:0px solid white !important;padding-left: 150px;vertical-align: top;">
                        @php
                            $deliver_by = (!is_null($orders->courier)?$orders->courier->name:'-');
                        @endphp
                        <span style="font-size: 12px;">Deliver By:<br/>{{ (!is_null($orders->courier)?$orders->courier->name:'') }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 12px;">Approved By,</span><br/>
                        @if (!is_null($orders->approved_by_info->userDetail->signage_pic))
                            <img src="{{ $_SERVER['DOCUMENT_ROOT'].'/upl/employees/'.$orders->approved_by_info->userDetail->signage_pic }}" style="width: 150px;" alt=""><br/>
                        @else
                            {!! '<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>' !!}
                        @endif
                        <span style="font-size: 12px;text-decoration:underline;">{{ $orders->approved_by_info->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span><br/>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 150px;">
                        <span style="font-size: 12px;">Confirm By,</span><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;text-decoration:underline;">{{ $orders->supplier?($orders->pic_idx==1?$orders->supplier->pic1_name:$orders->supplier->pic2_name):'' }}</span><br/>
                        <span style="font-size: 10px;">{{ (!is_null($orders->supplier_entity_type)?$orders->supplier_entity_type->title_ind:'').' '.$orders->supplier_name }}</span><br/>
                    </td>
                </tr>
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
