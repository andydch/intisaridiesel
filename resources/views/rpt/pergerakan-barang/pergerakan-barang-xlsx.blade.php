<!doctype html>
<html lang="en">
    <head>
            <!-- Required meta tags -->
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">

            <!-- Bootstrap CSS -->
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
                integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

            <title>{{ $title }}</title>
    </head>
    <body>
        <table>
            <thead>
                <tr>
                    <th>{{ $company->name }}</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th colspan="11" style="font-weight: bold;font-size: 16px;text-align:center;">REPORT PARTS MOVEMENT</th>
                </tr>
                <tr>
                    @php
                        $date_start=date_create($date_start);
                        $date_end=date_create($date_end);
                    @endphp
                    <th colspan="11" style="text-align:center;">{{ date_format($date_start,"d-M-Y").' s/d '.date_format($date_end,"d-M-Y") }}</th>
                </tr>
                <tr>
                    <th colspan="11">&nbsp;</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>{{ date_format(now(), 'd-M-Y') }}</th>
                </tr>
                <tr>
                    <th>PARTS NO</th>
                    <th>PARTS NAME</th>
                    <th>PARTS TYPE</th>
                    <th>CUSTOMER/SUPPLIER</th>
                    <th>DATE</th>
                    <th>DOC NO</th>
                    <th>PRICE ({{ $qCurrency->string_val }})</th>
                    <th>BRANCH</th>
                    <th>QTY IN</th>
                    <th>QTY OUT</th>
                    <th>QTY OH</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                    ->when($branch_id!='0', function($q) use($branch_id) {
                        $q->where('id','=',$branch_id);
                    })
                    ->orderBy('name','ASC')
                    ->get();

                    $grandtotal = 0;
                @endphp
                @foreach ($branches as $branch)
                    {{-- <tr>
                        <th colspan="10" style="font-weight: bold;">{{ strtoupper($branch->name) }}</th>
                    </tr> --}}
                    @php
                        $queryStockCard = \App\Models\V_stock_card::leftJoin('mst_parts as msp','v_stock_cards.part_id','=','msp.id')
                        ->leftJoin('mst_globals as part_type','msp.part_type_id','=','part_type.id')
                        ->select(
                            'v_stock_cards.tx_date',
                            'v_stock_cards.customer_or_supplier',
                            'v_stock_cards.doc_no',
                            'v_stock_cards.price',
                            'v_stock_cards.status',
                            'v_stock_cards.qty',
                            'v_stock_cards.updated_at as updatedat',
                            'v_stock_cards.branch_id',
                            'msp.id as part_id',
                            'msp.part_number',
                            'msp.part_name',
                            'part_type.title_ind as part_type_name',
                        )
                        ->selectRaw('IF(LEFT(v_stock_cards.doc_no, 3)=\''.ENV('P_RECEIPT_ORDER').'\', 
                            (SELECT invoice_no FROM tx_receipt_orders WHERE receipt_no=v_stock_cards.doc_no), 
                            v_stock_cards.doc_no) AS doc_no_v')
                        ->addSelect([
                            'oh_qty' => \App\Models\V_tx_qty_part::select('qty')
                                ->whereColumn('part_id', 'v_stock_cards.part_id')
                                ->whereColumn('branch_id', 'v_stock_cards.branch_id')
                                ->whereRaw('updated_at<v_stock_cards.updated_at')
                                ->orderBy('updated_at','DESC')
                                ->limit(1)
                        ])
                        ->where('v_stock_cards.branch_id','=', $branch->id)
                        ->where('v_stock_cards.tx_date','>=',$date_start)
                        ->where('v_stock_cards.tx_date','<=',$date_end)
                        ->where('v_stock_cards.doc_no','NOT LIKE','%Draft%')
                        ->orderBy('msp.part_number','ASC')
                        ->orderBy('v_stock_cards.updated_at','ASC')
                        ->get();
                    @endphp
                    @if ($queryStockCard)
                        @foreach ($queryStockCard as $q)
                            <tr>
                                <td>
                                    @php
                                        $partNumber = strtoupper($q->part_number);
                                        if(strlen($partNumber)<11){
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                        }else{
                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                        }
                                    @endphp
                                    {{ $partNumber }}
                                </td>
                                <td>{{ $q->part_name }}</td>
                                <td>{{ $q->part_type_name }}</td>
                                <td>{{ $q->customer_or_supplier }}</td>
                                <td>
                                    @php
                                        $date=date_create($q->tx_date);
                                    @endphp
                                    {{ date_format($date,"d/m/Y") }}
                                </td>
                                <td>{{ $q->doc_no_v }}</td>
                                {{-- <td>
                                    @if (substr($q->doc_no,0,3)=='ROU')
                                        @php
                                            $qRO = \App\Models\Tx_receipt_order::select('invoice_no')
                                            ->where('receipt_no','=',$q->doc_no)
                                            ->first();
                                            if($qRO){
                                                echo $qRO->invoice_no;
                                            }
                                        @endphp
                                    @else
                                        {{ $q->doc_no }}
                                    @endif
                                </td> --}}
                                <td style="text-align: right;">{{ number_format($q->price,0,'.','') }}</td>
                                <td style="text-align: right;">{{ strtoupper($branch->initial) }}</td>
                                <td style="text-align: right;">
                                    @if ($q->status=='IN')
                                        {{ $q->qty }}
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    @if ($q->status=='OUT')
                                        {{ $q->qty }}
                                    @endif
                                </td>
                                <td>{{ $q->oh_qty }}</td>
                                {{-- <td>
                                    @php
                                        $qQty = \App\Models\V_tx_qty_part::select('qty')
                                        ->where([
                                            'part_id' => $q->part_id,
                                            'branch_id' => $q->branch_id,
                                        ])
                                        ->whereRaw('updated_at<\''.$q->updatedat.'\'')
                                        ->orderBy('updated_at','DESC')
                                        ->first();
                                        if ($qQty){
                                            echo $qQty->qty;
                                        }else{
                                            echo 0;
                                        }
                                    @endphp
                                </td> --}}
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="11">&nbsp;</td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
