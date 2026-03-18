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

        <title>AVG Cost Change</title>
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
                    <th colspan="12" style="text-align:center;">{{ strtoupper($title) }}</th>
                </tr>
                <tr>
                    <th colspan="12">{{ date_format(date_create($date_start), 'd-M-Y').' s/d '.date_format(date_create($date_end), 'd-M-Y') }}</th>
                </tr>
                <tr>
                    <th>&nbsp;</th>
                </tr>
                <tr>
                    <th>PARTS NO</th>
                    <th>PARTS NAME</th>
                    <th>PARTS TYPE</th>
                    <th>DOC NO</th>
                    <th>SUPPLIER</th>
                    <th>DATE</th>
                    <th>QTY OH</th>
                    <th>QTY IN</th>
                    <th>AVG AWAL</th>
                    <th>COST BELI</th>
                    <th>AVG AKHIR</th>
                    <th>BRANCH</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $queryParts = \App\Models\V_avg_cost_change::select(
                        'doc_no',
                        'doc_date',
                        'qty',
                        'price',
                        'updated_at',
                        'part_id',
                        'part_number',
                        'part_name',
                        'part_type_name',
                        'supplier_or_customer',
                        'avg_cost_before',
                        'avg_cost_after',
                        'branch_initial',
                        'branch_id',
                    )
                    ->addSelect(['qty_before' => \App\Models\V_tx_qty_part::selectRaw('IFNULL(qty,0)')
                        ->whereColumn('v_tx_qty_parts.part_id','v_avg_cost_change.part_id')
                        ->whereColumn('v_tx_qty_parts.branch_id','v_avg_cost_change.branch_id')
                        ->whereRaw('v_tx_qty_parts.updated_at<v_avg_cost_change.updated_at')
                        ->orderBy('v_tx_qty_parts.updated_at', 'DESC')
                        ->limit(1)
                    ])
                    ->where('doc_date','>=',$date_start)
                    ->where('doc_date','<=',$date_end)
                    ->orderBy('part_number','ASC')
                    ->orderBy('doc_date','ASC')
                    ->orderBy('updated_at','ASC')
                    ->get();
                @endphp
                @foreach ($queryParts as $part)
                    <tr>
                        <td>
                            @php
                                $partNumber = strtoupper($part->part_number);
                                if(strlen($partNumber)<11){
                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                }else{
                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                }
                            @endphp
                            {{ strtoupper($partNumber) }}
                        </td>
                        <td>{{ strtoupper($part->part_name) }}</td>
                        <td>{{ $part->part_type_name }}</td>
                        <td>{{ $part->doc_no }}</td>
                        <td>{{ $part->supplier_or_customer }}</td>
                        <td>
                            @php
                                $date=date_create($part->doc_date);
                            @endphp
                            {{ date_format($date,"d/m/Y") }}
                        </td>
                        <td>
                            {{-- @php
                                // Qty OH
                                $totOH = 0;
                                $branches = \App\Models\Mst_branch::where('active','=','Y')
                                ->get();
                            @endphp
                            @foreach ($branches as $branch)
                                @php
                                    $qQty = \App\Models\V_tx_qty_part::where([
                                        'part_id' => $part->part_id,
                                        'branch_id' => $branch->id,
                                    ])
                                    ->where('updated_at','<',$part->updated_at)
                                    ->orderBy('updated_at','DESC')
                                    ->first();
                                    if ($qQty){
                                        $totOH += $qQty->qty;
                                    }
                                @endphp
                            @endforeach
                            {{ $totOH }} --}}
                             {{ $part->qty_before }}
                        </td>
                        <td>{{ $part->qty }}</td>
                        <td style="text-align: right;">{{ number_format($part->avg_cost_before,0,'.','') }}</td>
                        <td style="text-align: right;">{{ number_format($part->price,0,'.','') }}</td>
                        <td style="text-align: right;">{{ number_format($part->avg_cost_after,0,'.','') }}</td>
                        <td>{{ $part->branch_initial }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Optional JavaScript; choose one of the two! -->

        <!-- Option 1: Bootstrap Bundle with Popper -->
        {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> --}}
        <script src="{{ asset('assets/js/bootstrap2023.bundle.min.js') }}"></script>

        <!-- Option 2: Separate Popper and Bootstrap JS -->
        <!--
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
        -->
    </body>
</html>
