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

        <title>Stock Disassembly : {{ $stock_disassemblies->stock_disassembly_no }}</title>

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

        <main>
            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;">
                        <span style="font-size: 10px;">Branch :&nbsp;{{ ($stock_disassemblies->branch_id!=null)?$stock_disassemblies->branch->initial:$stock_disassemblies->createdBy->userDetail->branch->initial }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 10px;">No:&nbsp;&nbsp;&nbsp;&nbsp;{{ $stock_disassemblies->stock_disassembly_no }}</span><br/>
                        <span style="font-size: 10px;">Date:&nbsp;{{ date_format(date_create($stock_disassemblies->stock_disassembly_date),"d/m/Y") }}</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">STOCK DISASSEMBLY</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th style="width:17%;">Part No</th>
                        <th style="width:25%;">Part Name</th>
                        <th style="width:8%;" colspan="2">Qty</th>
                        <th style="width:15%;">Cost AVG ({{ $qCurrency->string_val }})</th>
                        <th style="width:15%;">Total Cost ({{ $qCurrency->string_val }})</th>
                        <th style="width:15%;">New AVG ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="no-idx">1.</td>
                        <td class="val-str">
                            @php
                                $partNumber = $stock_disassemblies->part->part_number;
                                if(strlen($partNumber)<11){
                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                }else{
                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                }
                            @endphp
                            {{ $partNumber }}
                        </td>
                        <td class="val-str">{{ $stock_disassemblies->part->part_name }}</td>
                        <td class="val-str" style="text-align: right;">{{ $stock_disassemblies->qty }}</td>
                        <td class="val-str" style="text-align: center;">{{ (!is_null($stock_disassemblies->part->part_type)?$stock_disassemblies->part->part_type->string_val:'') }}</td>
                        <td class="val-num">{{ number_format($stock_disassemblies->avg_cost,0,'.',',') }}</td>
                        <td class="val-num">{{ number_format(($stock_disassemblies->avg_cost*$stock_disassemblies->qty),0,'.',',') }}</td>
                        <td class="val-num">{{ number_format($stock_disassemblies->part->avg_cost,0,'.',',') }}</td>
                    </tr>
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">TO BE PART</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th style="width:5%;">No</th>
                        <th style="width:17%;">Part No</th>
                        <th style="width:25%;">Part Name</th>
                        <th style="width:8%;" colspan="2">Qty</th>
                        <th style="width:15%;">Cost AVG ({{ $qCurrency->string_val }})</th>
                        <th style="width:15%;">Total Cost ({{ $qCurrency->string_val }})</th>
                        <th style="width:15%;">New AVG ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                        $totPrice=0;
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
                            <td class="val-str" style="text-align: right;">{{ $p->qty }}</td>
                            <td class="val-str" style="text-align: center;">{{ (!is_null($p->part->part_type)?$p->part->part_type->string_val:'') }}</td>
                            <td class="val-num">{{ number_format($p->cost,0,'.',',') }}</td>
                            <td class="val-num">{{ number_format(($p->cost*$p->qty),0,'.',',') }}</td>
                            <td class="val-num">{{ number_format($p->avg_cost,0,'.',',') }}</td>
                        </tr>
                        @php
                            $totPrice+=($p->cost*$p->qty);
                            $i+=1;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="6" class="val-num">Total</td>
                        <td class="val-num">{{ number_format($totPrice,0,'.',',') }}</td>
                    </tr>
                </tfoot>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;">
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">Remark:<br/>{{ $stock_disassemblies->remark }}</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">&nbsp;</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;vertical-align: top;">
                        <span style="font-size: 12px;">Created By,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;border-bottom:#000 solid 1px;">{{ $stock_disassemblies->createdBy->name }}</span><br/>
                        <span style="font-size: 10px;">{{ $companyName }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">&nbsp;</td>
                </tr>
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
