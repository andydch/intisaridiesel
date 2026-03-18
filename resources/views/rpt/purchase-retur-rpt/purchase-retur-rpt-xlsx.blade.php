<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Purchase Retur</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 9;
                    $monthNm = '';
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">PURCHASE RETUR</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NAMA SUPPLIER</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PR NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP RETUR ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN RETUR ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INV NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">RO NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalDPP=0;
                        $totalDPPppn=0;

                        $purchase_retur = \App\Models\Tx_purchase_retur::leftJoin('mst_suppliers as msspp','tx_purchase_returs.supplier_id','=','msspp.id')
                        ->leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
                        ->leftJoin('mst_branches as msb','tx_ro.branch_id','=','msb.id')
                        ->leftJoin('users','tx_purchase_returs.created_by','=','users.id')
                        ->select(
                            'tx_purchase_returs.purchase_retur_no',
                            'tx_purchase_returs.purchase_retur_date',
                            'tx_purchase_returs.total_before_vat',
                            'msspp.name as supplier_name',
                            'users.name as pic_name',
                            'tx_ro.id as ro_id',
                            'tx_ro.receipt_no',
                            'tx_ro.invoice_no',
                            'msb.name as branch_name',
                        )
                        ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
                        ->whereRaw('tx_purchase_returs.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                        ->whereRaw('tx_purchase_returs.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                        ->whereRaw('tx_purchase_returs.approved_by is not null')
                        ->where([
                            'tx_purchase_returs.active'=>'Y',
                        ])
                        ->orderBy('tx_purchase_returs.purchase_retur_date','ASC')
                        ->get();
                    @endphp
                    @foreach ($purchase_retur as $pr)
                        @php
                            $po_mo_no = '';
                            $is_vat = 'N';
                            $vat_val = 0;
                            $qROparts = \App\Models\Tx_receipt_order_part::where([
                                'receipt_order_id'=>$pr->ro_id,
                                'active'=>'Y',
                            ])
                            ->first();
                            if ($qROparts){
                                $po_mo_no = $qROparts->po_mo_no;
                                if (strpos("-".$qROparts->po_mo_no,env('P_PURCHASE_MEMO'))>0){
                                    $qO = \App\Models\Tx_purchase_memo::where([
                                        'memo_no'=>$qROparts->po_mo_no,
                                    ])
                                    ->first();
                                }
                                if (strpos("-".$qROparts->po_mo_no,env('P_PURCHASE_ORDER'))>0){
                                    $qO = \App\Models\Tx_purchase_order::where([
                                        'purchase_no'=>$qROparts->po_mo_no,
                                    ])
                                    ->first();
                                }
                                if ($qO){
                                    $is_vat = $qO->is_vat;
                                    $vat_val = $qO->vat_val;
                                }
                            }

                            $totalDPP += $pr->total_before_vat;
                            $totalDPPppn += ($is_vat=='Y'?(($pr->total_before_vat*$vat_val)/100):0);
                        @endphp
                        <tr>
                            <td style="text-align: center;border-left:1px solid black;">{{ date_format(date_create($pr->purchase_retur_date),"d/m/Y") }}</td>
                            <td>{{ $pr->supplier_name }}</td>
                            <td style="text-align: center;">{{ $pr->purchase_retur_no }}</td>
                            <td style="text-align: right;">-{{ number_format($pr->total_before_vat,0,'.','') }}</td>
                            <td style="text-align: right;">-{{ number_format(($is_vat=='Y'?(($pr->total_before_vat*$vat_val)/100):0),0,'.','') }}</td>
                            <td style="text-align: center;">{{ $pr->invoice_no }}</td>
                            <td style="text-align: center;">{{ $pr->receipt_no }}</td>
                            <td style="text-align: center;">{{ $pr->branch_name }}</td>
                            <td style="text-align: center;border-right:1px solid black;">{{ $pr->pic_name }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="border-left:1px solid black;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td style="border-right:1px solid black;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">-{{ number_format($totalDPP,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">-{{ number_format($totalDPPppn,0,'.','') }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
