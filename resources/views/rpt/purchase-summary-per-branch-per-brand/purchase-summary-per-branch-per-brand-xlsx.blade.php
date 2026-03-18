<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PurchaseSummPerBranchPerBrand</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 5;
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
                        <th colspan="{{ $totCols }}">PURCHASE SUMMARY PER BRANCH PER BRAND</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRANCH</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">BRAND</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL RETUR DPP({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL NET DPP ({{ $qCurrency->string_val }})</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $branchNm='';
                        $TotDpp=0;
                        $TotRetur=0;
                        $TotNetDpp=0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $subTotDpp = 0;
                            $subTotRetur = 0;
                            $subTotNetDpp = 0;
                            $brands = \App\Models\Mst_global::where([
                                'data_cat'=>'brand',
                                'active'=>'Y',
                            ])
                            ->get();
                        @endphp
                        @foreach ($brands as $brand)
                            @php
                                $sumTotDpp = 0;
                                $sumTotRetur = 0;
                                $qRO_parts = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                ->leftJoin('mst_parts as msp','tx_receipt_order_parts.part_id','=','msp.id')
                                ->select(
                                    'tx_receipt_order_parts.po_mo_no',
                                    'tx_receipt_order_parts.qty',
                                    'tx_receipt_order_parts.final_fob',
                                    'tx_receipt_order_parts.final_cost',
                                    'tx_receipt_order_parts.total_fob_price',
                                    'tx_ro.supplier_type_id',
                                    'tx_ro.exchange_rate',
                                    'tx_ro.exc_rate_for_vat',
                                )
                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where([
                                    'tx_receipt_order_parts.active'=>'Y',
                                    'tx_ro.branch_id'=>$branch->id,
                                    'tx_ro.active'=>'Y',
                                    'msp.brand_id'=>$brand->id,
                                ])
                                ->get();
                            @endphp
                            @foreach ($qRO_parts as $qRO_p)
                                @php
                                    $is_vat = 'N';
                                    $vat_val = 0;
                                    $exchange_rate = !is_null($qRO_p->exchange_rate)?$qRO_p->exchange_rate:1;
                                    $exc_rate_for_vat = !is_null($qRO_p->exc_rate_for_vat)?$qRO_p->exc_rate_for_vat:1;
                                    if (strpos("-".$qRO_p->po_mo_no,env('P_PURCHASE_MEMO'))>0){
                                        $qO = \App\Models\Tx_purchase_memo::where([
                                            'memo_no'=>$qRO_p->po_mo_no,
                                        ])
                                        ->first();
                                    }
                                    if (strpos("-".$qRO_p->po_mo_no,env('P_PURCHASE_ORDER'))>0){
                                        $qO = \App\Models\Tx_purchase_order::where([
                                            'purchase_no'=>$qRO_p->po_mo_no,
                                        ])
                                        ->first();
                                    }
                                    if ($qO){
                                        $is_vat = $qO->is_vat;
                                        $vat_val = $qO->vat_val;
                                    }
                                    if ($qRO_p->supplier_type_id==10){
                                        // if ($qRO_p->total_fob_price>0 && !is_null($qRO_p->exc_rate_for_vat)){
                                        // }else{
                                        // }
                                        if ($qRO_p->total_fob_price>0){
                                            $sumTotDpp += ($qRO_p->qty*$qRO_p->final_fob*$exchange_rate);
                                        }else{
                                            $sumTotDpp += ($qRO_p->qty*$qRO_p->final_cost);
                                        }
                                    }else{
                                        $sumTotDpp += ($qRO_p->qty*$qRO_p->final_cost);
                                    }
                                @endphp
                            @endforeach
                            @php
                                $qPR_part = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                ->leftJoin('mst_parts as msp','tx_purchase_retur_parts.part_id','=','msp.id')
                                ->select(
                                    'tx_purchase_retur_parts.total_retur',
                                )
                                ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                ->whereRaw('tx_pr.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('tx_pr.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->whereRaw('tx_pr.approved_by IS NOT null')
                                ->where([
                                    'tx_purchase_retur_parts.active'=>'Y',
                                    'tx_pr.branch_id'=>$branch->id,
                                    'tx_pr.active'=>'Y',
                                    'msp.brand_id'=>$brand->id,
                                ])
                                ->get();
                            @endphp
                            @foreach ($qPR_part as $qPR_p)
                                @php
                                    $sumTotRetur += $qPR_p->total_retur;
                                @endphp
                            @endforeach
                            @if ($sumTotDpp>0 || $sumTotRetur>0)
                                <tr>
                                    <td style="text-align: center;border-left:1px solid black;">{{ ($branchNm!=$branch->name)?$branch->name:'' }}</td>
                                    <td>{{ $brand->title_ind }}</td>
                                    <td style="text-align:right;">{{ number_format($sumTotDpp,0,'.','') }}</td>
                                    <td style="text-align:right;color:red;">{{ number_format($sumTotRetur,0,'.','') }}</td>
                                    <td style="text-align:right;border-right:1px solid black;">{{ number_format(($sumTotDpp-$sumTotRetur),0,'.','') }}</td>
                                </tr>
                            @endif
                            @php
                                $subTotDpp += $sumTotDpp;
                                $subTotRetur += $sumTotRetur;
                                $subTotNetDpp += ($sumTotDpp-$sumTotRetur);
                                $branchNm = $branch->name;
                            @endphp
                        @endforeach
                        @if ($subTotDpp>0 || $subTotRetur>0)
                            <tr>
                                <td style="border-left:1px solid black;">&nbsp;</td>
                                <td style="text-align: center;font-weight:700;border-top:1px solid black;">SUB TOTAL</td>
                                <td style="text-align: right;border-top:1px solid black;">{{ number_format($subTotDpp,0,'.','') }}</td>
                                <td style="text-align: right;color:red;border-top:1px solid black;">{{ number_format($subTotRetur,0,'.','') }}</td>
                                <td style="text-align: right;border-right:1px solid black;border-top:1px solid black;">{{ number_format($subTotNetDpp,0,'.','') }}</td>
                            </tr>
                            <tr>
                                <td style="border-left:1px solid black;">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-right:1px solid black;">&nbsp;</td>
                            </tr>
                            @php
                                $TotDpp += $subTotDpp;
                                $TotRetur += $subTotRetur;
                                $TotNetDpp += $subTotNetDpp;
                                $branchNm = $branch->name;
                            @endphp
                        @endif
                    @endforeach
                    <tr>
                        <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;font-weight:700;border-bottom:1px solid black;">TOTAL</td>
                        <td style="text-align: right;border-bottom:1px solid black;">{{ number_format($TotDpp,0,'.','') }}</td>
                        <td style="text-align: right;color:red;border-bottom:1px solid black;">{{ number_format($TotRetur,0,'.','') }}</td>
                        <td style="text-align: right;border-right:1px solid black;border-bottom:1px solid black;">{{ number_format($TotNetDpp,0,'.','') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
