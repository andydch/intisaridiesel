<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PerSupplierPerYear</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    if ($period_year<date_format($date,"Y")){
                        $month = 12;
                    }
                    $totCols = $month+2;
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
                        <th colspan="{{ $totCols }}">Purchase Per Supplier Per Year {{ $period_year }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format(date_add(now(),date_interval_create_from_date_string(env('WAKTU_ID',7)." hours")), 'd/m/Y') }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SUPPLIER NAME</th>
                        @for ($i=1;$i<=$month;$i++)
                            @switch($i)
                                @case(1)
                                    @php
                                        $monthNm = 'JAN';
                                    @endphp
                                    @break
                                @case(2)
                                    @php
                                        $monthNm = 'FEB';
                                    @endphp
                                    @break
                                @case(3)
                                    @php
                                        $monthNm = 'MAR';
                                    @endphp
                                    @break
                                @case(4)
                                    @php
                                        $monthNm = 'APR';
                                    @endphp
                                    @break
                                @case(5)
                                    @php
                                        $monthNm = 'MAY';
                                    @endphp
                                    @break
                                @case(6)
                                    @php
                                        $monthNm = 'JUN';
                                    @endphp
                                    @break
                                @case(7)
                                    @php
                                        $monthNm = 'JUL';
                                    @endphp
                                    @break
                                @case(8)
                                    @php
                                        $monthNm = 'AUG';
                                    @endphp
                                    @break
                                @case(9)
                                    @php
                                        $monthNm = 'SEP';
                                    @endphp
                                    @break
                                @case(10)
                                    @php
                                        $monthNm = 'OCT';
                                    @endphp
                                    @break
                                @case(11)
                                    @php
                                        $monthNm = 'NOP';
                                    @endphp
                                    @break
                                @case(12)
                                    @php
                                        $monthNm = 'DEC';
                                    @endphp
                                    @break
                                @default
                                    @php
                                        $monthNm = '';
                                    @endphp
                            @endswitch
                            <th style="text-align: center;border:1px solid black;background-color:#daeef3;">{{ $monthNm }}</th>
                        @endfor
                        <th style="text-align: center;background-color:#daeef3;border:1px solid black;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $suppliers = \App\Models\Mst_supplier::leftJoin('mst_globals as ett_type','mst_suppliers.entity_type_id','=','ett_type.id')
                        ->select(
                            'mst_suppliers.id as supplier_id',
                        )
                        ->selectRaw('IF(ISNULL(ett_type.title_ind),
                            CONCAT(mst_suppliers.supplier_code,\' - \',mst_suppliers.name),
                            CONCAT(mst_suppliers.supplier_code,\' - \',ett_type.title_ind,\' \',mst_suppliers.name)) as supplier_name')
                        ->whereIn('mst_suppliers.id', function($q) use($period_year){
                            $q->select('supplier_id')
                            ->from('tx_receipt_orders')
                            ->whereRaw('YEAR(receipt_date)='.$period_year)
                            ->where('receipt_no','NOT LIKE','%Draft%')
                            ->where('active','=','Y');
                        })
                        ->where('mst_suppliers.active','=','Y')
                        ->orderBy('mst_suppliers.name','ASC')
                        ->get();

                        $totPerCol = [0,0,0,0,0,0,0,0,0,0,0,0];
                        $totPerColPR = [0,0,0,0,0,0,0,0,0,0,0,0];
                        $totPerRowAll = 0;
                    @endphp
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td style="border-left:1px solid black;">{{ $supplier->supplier_name }}</td>
                            @php
                                $totPerRow = 0;
                            @endphp
                            @for ($iMonth=1;$iMonth<=$month;$iMonth++)
                                @php
                                    $po_mo = [];
                                    $is_vat = 'N';
                                    $vat_val = 0;
                                    $exchange_rate = 1;
                                    $exchange_rate_vat = 1;
                                    $ro_total = 0;
                                    $pr_total = 0;
                                    $vat_tmp = '';

                                    $qRO_per_month = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                    ->where([
                                        'supplier_id'=>$supplier->supplier_id,
                                        'active'=>'Y',
                                    ])
                                    ->whereRaw('YEAR(receipt_date)='.$period_year)
                                    ->whereRaw('MONTH(receipt_date)='.$iMonth)
                                    ->get();
                                @endphp
                                @foreach ($qRO_per_month as $qRO)
                                    @php
                                        $per_RO_total = 0;
                                        // if ($qRO->supplier->supplier_type_id==10){
                                        //     // international
                                        //     $exchange_rate = (!is_null($qRO->exchange_rate)?$qRO->exchange_rate:1);
                                        //     $exchange_rate_vat = (!is_null($qRO->exc_rate_for_vat)?$qRO->exc_rate_for_vat:1);
                                        // }else{
                                        //     // lokal
                                        //     $exchange_rate = 1;
                                        //     $exchange_rate_vat = 1;
                                        // }

                                        $qRO_parts = \App\Models\Tx_receipt_order_part::where([
                                            'receipt_order_id'=>$qRO->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        $vatPerPart = 0;
                                    @endphp
                                    @foreach ($qRO_parts as $qRO_p)
                                        @php
                                            if ($qRO_p->supplier_type_id==10){
                                                // internasional
                                                $vatPerPart = ($qRO_p->total_price/$qRO->total_before_vat_rp)*$qRO->total_vat_rp;
                                            }
                                            if ($qRO_p->supplier_type_id==11){
                                                // lokal
                                                $vatPerPart = ($qRO_p->total_price/$qRO->total_before_vat)*$qRO->total_vat;
                                            }
                                            // $ro_total += ($qRO_p->total_price+$vatPerPart);
                                            // $per_RO_total += ($qRO_p->total_price+$vatPerPart);
                                            $ro_total += $qRO_p->total_price;
                                            $per_RO_total += $qRO_p->total_price;

                                            $qPR_parts = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr',
                                                'tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                            ->select(
                                                'tx_purchase_retur_parts.total_retur',
                                                'tx_pr.purchase_retur_no',
                                                'tx_pr.vat_val',
                                            )
                                            ->where([
                                                'tx_purchase_retur_parts.part_id'=>$qRO_p->part_id,
                                                'tx_purchase_retur_parts.active'=>'Y',
                                                'tx_pr.receipt_order_id'=>$qRO->id,
                                                'tx_pr.supplier_id'=>$supplier->supplier_id,
                                                'tx_pr.active'=>'Y',
                                            ])
                                            ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                            ->whereRaw('YEAR(tx_pr.purchase_retur_date)='.$period_year)
                                            ->whereRaw('MONTH(tx_pr.purchase_retur_date)='.$iMonth)
                                            ->whereRaw('tx_pr.approved_by IS NOT NULL')
                                            ->get();
                                        @endphp
                                        @foreach ($qPR_parts as $qPR_p)
                                            @php
                                                // $pr_total += ($qPR_p->vat_val>0?($qPR_p->total_retur+(($qPR_p->total_retur*$qPR_p->vat_val)/100)):$qPR_p->total_retur);
                                                $pr_total += $qPR_p->total_retur;
                                            @endphp
                                        @endforeach
                                    @endforeach
                                @endforeach
                                @php
                                    $qPR_parts = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                    ->leftJoin('tx_receipt_orders as tx_ro','tx_pr.receipt_order_id','=','tx_ro.id')
                                    ->select(
                                        'tx_purchase_retur_parts.total_retur',
                                        'tx_pr.purchase_retur_no',
                                        'tx_pr.vat_val',
                                        'tx_ro.po_or_pm_no',
                                    )
                                    ->where([
                                        'tx_purchase_retur_parts.active'=>'Y',
                                        'tx_pr.supplier_id'=>$supplier->supplier_id,
                                        'tx_pr.active'=>'Y',
                                    ])
                                    ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('YEAR(tx_pr.purchase_retur_date)='.$period_year)
                                    ->whereRaw('MONTH(tx_pr.purchase_retur_date)='.$iMonth)
                                    ->whereRaw('tx_ro.receipt_date<\''.$period_year.'-01-01\'')
                                    ->whereRaw('tx_pr.approved_by IS NOT NULL')
                                    ->get();
                                @endphp
                                @foreach ($qPR_parts as $qPR_p)
                                    @php
                                        // $pr_total += ($qPR_p->vat_val>0?($qPR_p->total_retur+(($qPR_p->total_retur*$qPR_p->vat_val)/100)):$qPR_p->total_retur);
                                        $pr_total += $qPR_p->total_retur;
                                    @endphp
                                @endforeach
                                <td style="text-align: right;border-right:1px solid black;">
                                    {{ number_format(($ro_total-$pr_total),0,'.','') }}
                                </td>
                                @php
                                    $totPerRow += ($ro_total-$pr_total);
                                    $totPerCol[$iMonth-1] += ($ro_total-$pr_total);
                                @endphp
                            @endfor
                            <td style="text-align: right;border-right:1px solid black;">
                                {{ number_format($totPerRow,0,'.','') }}
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="font-weight: 700;text-align: right;border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        @for ($i=1;$i<=$month;$i++)
                            <td style="font-weight: 700;text-align: right;border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        @endfor
                        <td style="font-weight: 700;text-align: right;border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 700;text-align: right;border-left:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">TOTAL</td>
                        @for ($i=1;$i<=$month;$i++)
                            <td style="font-weight: 700;text-align: right;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">
                                {{ (($totPerCol[$i-1])>0?number_format(($totPerCol[$i-1]),0,'.',''):'') }}
                            </td>
                            @php
                                $totPerRowAll += $totPerCol[$i-1];
                            @endphp
                        @endfor
                        <td style="font-weight: 700;text-align: right;border-right:1px solid black;border-bottom:1px solid black;border-top:1px solid black;">
                            {{ ($totPerRowAll>0?number_format($totPerRowAll,0,'.',''):'') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
