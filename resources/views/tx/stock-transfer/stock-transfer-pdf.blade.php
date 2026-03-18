<!doctype html>
<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Stock Transfer : {{ $stock_transfers->stock_transfer_no }}</title>

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
                        <span style="font-size: 10px;">Branch From:&nbsp;{{ $stock_transfers->branch_from?$stock_transfers->branch_from->name:'' }}</span><br/>
                        <span style="font-size: 10px;">Branch To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $stock_transfers->branch_from?$stock_transfers->branch_to->name:'' }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 10px;">No:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $stock_transfers->stock_transfer_no }}</span><br/>
                        <span style="font-size: 10px;">Date:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ date_format(date_create($stock_transfers->stock_transfer_date),"d/m/Y") }}</span><br/>
                        <span style="font-size: 10px;">Date Received: {{ (!is_null($stock_transfers->received_at)?date_format(date_create($stock_transfers->received_at),"d/m/Y"):'') }}
                    </td>
                </tr>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-bottom: 25px;">
                <tr style="border:0px solid white !important;">
                    <td style="width: 100%;text-align:center;border:0px solid white !important;">
                        <span style="font-size: 15;font-weight:bold;">STOCK TRANSFER</span>
                    </td>
                </tr>
            </table>

            <table style="width: 100%;margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Part No</th>
                        <th>Part Name</th>
                        <th>Part Type</th>
                        <th colspan="2">Transfer</th>
                        <th>AVG Cost ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i=1;
                    @endphp
                    @foreach($parts AS $p)
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
                            <td class="val-str">{{ (!is_null($p->part->part_type)?$p->part->part_type->title_ind:'') }}</td>
                            <td class="val-str" style="text-align: right;">{{ $p->qty }}</td>
                            <td class="val-str" style="text-align: center;">{{ (!is_null($p->part->part_category)?$p->part->part_category->title_ind:'') }}</td>
                            <td class="val-str" style="text-align: right;">
                                @if ($p->last_avg_cost!=null)
                                    {{ number_format($p->last_avg_cost,0,"",".") }}                                    
                                @else
                                    @php
                                        $qPart = \App\Models\V_log_avg_cost::where('part_id', '=', $p->part_id)
                                        ->whereRaw('updated_at<(SELECT created_at 
                                            FROM tx_general_journals 
                                            WHERE module_no=\''.$stock_transfers->stock_transfer_no.'\')')
                                        // ->whereRaw('updated_at<\''.$p->updated_at.'\'')
                                        ->orderBy('updated_at', 'DESC')
                                        ->orderBy('row_id', 'ASC')
                                        ->limit(1)
                                        ->first();
                                    @endphp
                                    @if ($qPart)
                                        {{ number_format($qPart->avg_cost,0,"",".") }}
                                    @endif
                                @endif

                            </td>
                        </tr>
                        @php
                            $i+=1;
                        @endphp
                    @endforeach
                </tbody>
            </table>

            <table style="width: 100%;background-color: white;border:0px solid white !important;margin-top: 15px;">
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">Remark:<br/>{{ $stock_transfers->remark }}</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td colspan="2" style="text-align: left;border:0px solid white !important;">&nbsp;</td>
                </tr>
                <tr style="border:0px solid white !important;">
                    <td style="width: 50%;text-align: left;border:0px solid white !important;vertical-align: top;">
                        <span style="font-size: 12px;">Proposed By,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;">{{ $stock_transfers->createdBy->name }}</span><br/>
                        <span style="font-size: 10px;border-top:#000 solid 1px;">{{ $companyName }}</span>
                    </td>
                    <td style="width: 50%;text-align: left;vertical-align: top;border:0px solid white !important;padding-left: 175px;">
                        <span style="font-size: 12px;">Received By,</span><br/><br/><br/><br/><br/><br/>
                        <span style="font-size: 12px;text-decoration:underline">{{ (!is_null($stock_transfers->receivedBy)?$stock_transfers->receivedBy->name:'') }}</span><br/>
                        <span style="font-size: 10px;">{{ $stock_transfers->branch_to?$stock_transfers->branch_to->name:'' }}</span>
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
