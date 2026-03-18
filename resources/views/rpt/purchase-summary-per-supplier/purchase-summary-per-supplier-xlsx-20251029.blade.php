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

                                $qRO_dpp = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where([
                                    'supplier_id'=>$supplier->id,
                                    'branch_id'=>$branch->id,
                                    'active'=>'Y',
                                ])
                                ->get();
                            @endphp
                            @foreach ($qRO_dpp as $qRO_d)
                                @php
                                    $is_vat = 'N';
                                    $vat_val = 0;
                                    $exchange_rate = !is_null($qRO_d->exchange_rate)?$qRO_d->exchange_rate:1;
                                    $exc_rate_for_vat = !is_null($qRO_d->exc_rate_for_vat)?$qRO_d->exc_rate_for_vat:1;
                                    $po_mo_Arr = explode(",",$qRO_d->po_or_pm_no);
                                @endphp
                                @foreach ($po_mo_Arr as $po_mo)
                                    @if ($po_mo!='')
                                        @if (strpos("-".$po_mo,env('P_PURCHASE_MEMO'))>0)
                                            @php
                                                $qO = \App\Models\Tx_purchase_memo::where([
                                                    'memo_no'=>$po_mo,
                                                ])
                                                ->first();
                                                if ($qO){
                                                    $is_vat = $qO->is_vat;
                                                    $vat_val = $qO->vat_val;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                        @if (strpos("-".$po_mo,env('P_PURCHASE_ORDER'))>0)
                                            @php
                                                $qO = \App\Models\Tx_purchase_order::where([
                                                    'purchase_no'=>$po_mo,
                                                ])
                                                ->first();
                                                if ($qO){
                                                    $is_vat = $qO->is_vat;
                                                    $vat_val = $qO->vat_val;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                    @endif
                                @endforeach
                                @if ($qRO_d->supplier_type_id==10)
                                    {{-- international --}}
                                    @php
                                        $sumTotDpp += $qRO_d->total_before_vat_rp;
                                        $sumTotDppPpn += ($is_vat=='Y'?$qRO_d->total_vat_rp:0);
                                    @endphp
                                @else
                                    {{-- lokal --}}
                                    @php
                                        $sumTotDpp += $qRO_d->total_before_vat;
                                        $sumTotDppPpn += ($is_vat=='Y')?(($qRO_d->total_before_vat*$vat_val)/100):0;
                                    @endphp
                                @endif

                                {{-- retur --}}
                                @php
                                    $p_returs = \App\Models\Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
                                    ->where([
                                        'receipt_order_id'=>$qRO_d->id,
                                        'active'=>'Y',
                                    ])
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->sum('total_before_vat');

                                    $sumTotReturDpp += $p_returs;
                                    $sumTotReturDppPpn += ($is_vat=='Y')?(($p_returs*$vat_val)/100):0;
                                @endphp
                            @endforeach
                            {{-- other retur --}}
                            @php
                                $qPR_other = \App\Models\Tx_purchase_retur::leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
                                ->select(
                                    'tx_purchase_returs.total_before_vat',
                                    'tx_purchase_returs.purchase_retur_no',
                                    'tx_ro.po_or_pm_no',
                                )
                                ->where('purchase_retur_no','NOT LIKE','%Draft%')
                                ->where([
                                    'tx_purchase_returs.active'=>'Y',
                                ])
                                ->whereRaw('tx_purchase_returs.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_purchase_returs.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_purchase_returs.approved_by IS NOT NULL')
                                ->whereRaw('tx_ro.receipt_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->get();
                            @endphp
                            @foreach ($qPR_other as $qPR_o)
                                @php
                                    $is_vat = 'N';
                                    $vat_val = 0;
                                    $po_mo = explode(",",$qPR_o->po_or_pm_no)
                                @endphp
                                @foreach ($po_mo as $p_m)
                                    @if ($p_m!='')
                                        @if (strpos("-".$p_m,env("P_PURCHASE_ORDER"))>0)
                                            @php
                                                $po = \App\Models\Tx_purchase_order::where([
                                                    'purchase_no'=>$p_m,
                                                ])
                                                ->first();
                                                if ($po){
                                                    $is_vat = $po->is_vat;
                                                    $vat_val = $po->vat_val;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                        @if (strpos("-".$p_m,env("P_PURCHASE_MEMO"))>0)
                                            @php
                                                $mo = \App\Models\Tx_purchase_memo::where([
                                                    'memo_no'=>$p_m,
                                                ])
                                                ->first();
                                                if ($mo){
                                                    $is_vat = $mo->is_vat;
                                                    $vat_val = $mo->vat_val;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                    @endif
                                @endforeach
                                @php
                                    $sumTotReturDpp += $qPR_o->total_before_vat;
                                    $sumTotReturDppPpn += ($is_vat=='Y')?(($qPR_o->total_before_vat*$vat_val)/100):0;
                                @endphp
                            @endforeach
                            @if ($sumTotDpp>0 || $sumTotReturDpp>0)
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
                                            $lastEstDate = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                            ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'supplier_id'=>$supplier->id,
                                                'branch_id'=>$branch->id,
                                                'active'=>'Y',
                                            ])
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
                        <td style="text-align: right;font-weight:700;color:red;border-bottom:1px solid black;">{{ number_format($totalAllRetur,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;color:red;border-bottom:1px solid black;">{{ number_format($totalAllReturVAT,0,'.','') }}</td>
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
