<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PurchaseSummPerSupplier</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 8;
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
                        <th colspan="{{ $totCols }}">PURCHASE SUMMARY PER SUPPLIER</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SUPPLIER CODE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SUPPLIER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP RETUR ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN RETUR ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL AMOUNT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DUE DATE</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $totalAllDpp=0;
                        $totalAllVat=0;
                        $totalAllRetur=0;
                        $totalAllReturVAT=0;
                        $totalAllAmount=0;

                        $branches = \App\Models\Mst_branch::when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="border-left:1px solid black;font-weight:700;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $suppliers = \App\Models\Mst_supplier::where('active','=','Y')
                            ->orderBy('name','ASC')
                            ->get();
                        @endphp
                        @foreach ($suppliers as $supplier)
                            @php
                                $sumTotDpp = 0;
                                $sumTotDppPpn = 0;
                                $sumTotReturDpp = 0;
                                $sumTotReturDppPpn = 0;

                                $qRO_dpp = \App\Models\Tx_receipt_order::whereRaw('(receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                    AND receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\')')
                                ->where('supplier_id', '=', $supplier->id)
                                ->where('branch_id', '=', $branch->id)
                                ->when(strtoupper($lokal_input)=='P' || (strtoupper($lokal_input)!='P' && strtoupper($lokal_input)!='N' && strtoupper($lokal_input)!='A'), function($q){
                                    $q->where('vat_val', '>', 0);
                                })
                                ->when(strtoupper($lokal_input)=='N', function($q){
                                    $q->where('vat_val', '=', 0);
                                })
                                ->where('is_draft', '=', 'N')
                                ->where('active', '=', 'Y')
                                ->get();
                            @endphp
                            @foreach ($qRO_dpp as $qRO_d)
                                @php
                                    $sumTotDpp += $qRO_d->supplier_type_id==11?$qRO_d->total_before_vat:$qRO_d->total_before_vat_rp;
                                    $sumTotDppPpn += $qRO_d->supplier_type_id==11?$qRO_d->total_vat:$qRO_d->total_vat_rp;
                                @endphp

                                {{-- retur --}}
                                @php
                                    $total_before_vat_retur = 0;
                                    $qPReturs = \App\Models\Tx_purchase_retur::whereRaw('(purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                        AND purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\')')
                                    ->where('supplier_id', '=', $supplier->id)
                                    ->where('receipt_order_id', '=', $qRO_d->id)
                                    ->when(strtoupper($lokal_input)=='P' || (strtoupper($lokal_input)!='P' && strtoupper($lokal_input)!='N' && strtoupper($lokal_input)!='A'), function($q){
                                        $q->where('vat_val', '>', 0);
                                    })
                                    ->when(strtoupper($lokal_input)=='N', function($q){
                                        $q->where('vat_val', '=', 0);
                                    })
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->get();
                                    foreach($qPReturs as $qPRet) {
                                        $sumTotReturDpp += $qPRet->total_before_vat;
                                        $sumTotReturDppPpn += ($qPRet->vat_val>0)?(($qPRet->total_before_vat*$qPRet->vat_val)/100):0;
                                    }
                                @endphp
                            @endforeach
                            {{-- other retur --}}
                            @php
                                $qPR_other = \App\Models\Tx_purchase_retur::leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
                                ->select(
                                    'tx_purchase_returs.total_before_vat',
                                    'tx_purchase_returs.purchase_retur_no',
                                    'tx_purchase_returs.vat_val',
                                    'tx_ro.po_or_pm_no',
                                )
                                ->where('tx_purchase_returs.supplier_id', '=', $supplier->id)
                                ->whereRaw('(tx_purchase_returs.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                    AND tx_purchase_returs.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\')')
                                ->whereRaw('tx_purchase_returs.approved_by IS NOT NULL')
                                ->when(strtoupper($lokal_input)=='P' || (strtoupper($lokal_input)!='P' && strtoupper($lokal_input)!='N' && strtoupper($lokal_input)!='A'), function($q){
                                    $q->where('tx_purchase_returs.vat_val', '>', 0);
                                })
                                ->when(strtoupper($lokal_input)=='N', function($q){
                                    $q->where('tx_purchase_returs.vat_val', '=', 0);
                                })
                                ->where('tx_purchase_returs.is_draft', '=', 'N')
                                ->where('tx_purchase_returs.active', '=', 'Y')
                                ->whereRaw('tx_ro.receipt_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->when(strtoupper($lokal_input)=='P' || (strtoupper($lokal_input)!='P' && strtoupper($lokal_input)!='N' && strtoupper($lokal_input)!='A'), function($q){
                                    $q->where('tx_ro.vat_val', '>', 0);
                                })
                                ->when(strtoupper($lokal_input)=='N', function($q){
                                    $q->where('tx_ro.vat_val', '=', 0);
                                })
                                ->where('tx_ro.active', '=', 'Y')
                                ->get();
                            @endphp
                            @foreach ($qPR_other as $qPR_o)
                                @php
                                    $sumTotReturDpp += $qPR_o->total_before_vat;
                                    $sumTotReturDppPpn += ($qPR_o->vat_val>0)?(($qPR_o->total_before_vat*$qPR_o->vat_val)/100):0;
                                @endphp
                            @endforeach
                            @if ($sumTotDpp>0)
                                @php
                                    $totalAllDpp += $sumTotDpp;
                                    $totalAllVat += $sumTotDppPpn;
                                    $totalAllRetur += $sumTotReturDpp;
                                    $totalAllReturVAT += $sumTotReturDppPpn;
                                    $totalAmount = (($sumTotDpp+$sumTotDppPpn)-($sumTotReturDpp+$sumTotReturDppPpn));
                                    $totalAllAmount += $totalAmount;
                                @endphp
                                <tr>
                                    <td style="border-left:1px solid black;">{{ $supplier->supplier_code }}</td>
                                    <td>{{ $supplier->name }}</td>
                                    <td style="text-align: right;">{{ number_format($sumTotDpp,0,'.','') }}</td>
                                    <td style="text-align: right;">{{ number_format($sumTotDppPpn,0,'.','') }}</td>
                                    <td style="text-align: right;color:red;">-{{ number_format($sumTotReturDpp,0,'.','') }}</td>
                                    <td style="text-align: right;color:red;">-{{ number_format($sumTotReturDppPpn,0,'.','') }}</td>
                                    <td style="text-align: right;">{{ number_format($totalAmount,0,'.','') }}</td>
                                    <td style="text-align: center;border-right:1px solid black;">
                                        @php
                                            $lastEstDate = \App\Models\Tx_receipt_order::whereRaw('(receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' 
                                                AND receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\')')
                                            ->where('supplier_id', '=', $supplier->id)
                                            ->where('branch_id', '=', $branch->id)
                                            ->when(strtoupper($lokal_input)=='P' || (strtoupper($lokal_input)!='P' && strtoupper($lokal_input)!='N' && strtoupper($lokal_input)!='A'), function($q){
                                                $q->where('vat_val', '>', 0);
                                            })
                                            ->when(strtoupper($lokal_input)=='N', function($q){
                                                $q->where('vat_val', '=', 0);
                                            })
                                            ->where('is_draft', '=', 'N')
                                            ->where('active', '=', 'Y')
                                            ->orderBy('receipt_date','DESC')
                                            ->first();
                                        @endphp
                                        @if ($lastEstDate)
                                            @php
                                                $date = date_create($lastEstDate->receipt_date);
                                                date_add($date, date_interval_create_from_date_string($supplier->top." days"));
                                                echo date_format($date, "d/m/Y");
                                            @endphp
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;font-weight:700;border-bottom:1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalAllDpp,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalAllVat,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;color:red;border-bottom:1px solid black;">-{{ number_format($totalAllRetur,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;color:red;border-bottom:1px solid black;">-{{ number_format($totalAllReturVAT,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-bottom:1px solid black;">{{ number_format($totalAllAmount,0,'.','') }}</td>
                        <td style="border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
