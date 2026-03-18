<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>OvduRecPerBranch</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = date_create(now());
                    date_add($date, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours"));
                    $month = date_format($date,"m");
                    $totCols = 10;
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
                        <th colspan="{{ $totCols }}">Overdue Receivables Per Branch</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format($date,"d/m/Y") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NAMA CUSTOMER</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO INV</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO FAKTUR</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">INV DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">DUE DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">OVERDUE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                    </tr>
                    @php
                        $totGrandBeforeVatPerBranch = 0;
                        $totGrandVatPerBranch = 0;
                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id){
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        @php
                            $totBeforeVatPerBranch = 0;
                            $totVatPerBranch = 0;                        
                        @endphp
                        <tr>
                            <td style="font-weight: bold;border-left:1px solid black;">CABANG</td>
                            <td colspan="9" style="font-weight: bold;border-right:1px solid black;">{{ strtoupper($branch->name) }}</td>
                        </tr>
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || $lokal_input=='' || $lokal_input=='x')
                            {{-- ppn --}}
                            @php
                                $faktur = \App\Models\Tx_delivery_order::leftJoin('mst_customers as cust','tx_delivery_orders.customer_id','=','cust.id')
                                ->leftJoin('tx_invoice_details as inv_dtl','tx_delivery_orders.id','=','inv_dtl.fk_id')
                                ->leftJoin('tx_invoices as inv','inv_dtl.invoice_id','=','inv.id')
                                ->leftJoin('userdetails','cust.salesman_id','=','userdetails.user_id')
                                ->select(
                                    'tx_delivery_orders.id as order_id',
                                    'tx_delivery_orders.delivery_order_no',
                                    'tx_delivery_orders.total_before_vat',
                                    'tx_delivery_orders.total_after_vat',
                                    'tx_delivery_orders.vat_val',
                                    'cust.name as customer_name',
                                    'cust.customer_unique_code',
                                    'cust.top as customer_top',
                                    'inv.invoice_no',
                                    'inv.invoice_date',
                                    'inv.created_at AS inv_created_at',
                                    'userdetails.initial',
                                )
                                ->where([
                                    'tx_delivery_orders.branch_id'=>$branch->id,
                                    'tx_delivery_orders.is_draft'=>'N',
                                    'tx_delivery_orders.active'=>'Y',
                                    'inv.is_draft'=>'N',
                                    'inv.active'=>'Y',
                                ])
                                ->whereNotIn('inv.invoice_no', function($q){
                                    $q->select('tx_pri.invoice_no')
                                    ->from('tx_payment_receipt_invoices AS tx_pri')
                                    ->leftJoin('tx_payment_receipts AS tx_pr','tx_pri.payment_receipt_id','=','tx_pr.id')
                                    ->where([
                                        'tx_pri.is_full_payment'=>'Y',
                                        'tx_pri.active'=>'Y',
                                        'tx_pr.is_draft'=>'N',
                                        'tx_pr.active'=>'Y',
                                    ]);
                                })
                                ->orderBy('cust.name','ASC')
                                ->orderBy('inv.invoice_no','ASC')
                                ->orderBy('tx_delivery_orders.delivery_order_no','ASC')
                                ->orderBy('inv.created_at','ASC')
                                ->get();
                            @endphp
                            @foreach ($faktur as $fk)
                                @php
                                    $dateTax = date_create($fk->inv_created_at);
                                    date_add($dateTax, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours"));
                                    $dateTaxOD = clone $dateTax;
                                    date_add($dateTaxOD, date_interval_create_from_date_string($fk->customer_top." days"));
                                    $dtDiff = date_diff($dateTaxOD, $date);
                                @endphp
                                @if ($dtDiff->format("%r%a")>0)
                                    <tr>
                                        <td style="border-left:1px solid black;">{{ $fk->customer_unique_code.' - '.$fk->customer_name }}</td>
                                        <td>{{ $fk->invoice_no }}</td>
                                        <td>{{ $fk->delivery_order_no }}</td>
                                        @php
                                            $sumRetur = \App\Models\Tx_nota_retur::whereRaw('approved_by IS NOT null')
                                            ->where([
                                                'delivery_order_id'=>$fk->order_id,
                                                'is_draft'=>'N',
                                                'active'=>'Y',
                                            ])
                                            ->sum('total_before_vat');
                                            $totBeforeVatPerBranch += ($fk->total_before_vat-$sumRetur);
                                            $totVatPerBranch += ((($fk->total_before_vat-$sumRetur)*$fk->vat_val)/100);
                                        @endphp
                                        <td>{{ $fk->total_before_vat-$sumRetur }}</td>
                                        <td>{{ ((($fk->total_before_vat-$sumRetur)*$fk->vat_val)/100) }}</td>
                                        <td>{{ (($fk->total_before_vat-$sumRetur)+((($fk->total_before_vat-$sumRetur)*$fk->vat_val)/100)) }}</td>
                                        <td>{{ date_format($dateTax,"d/m/Y") }}</td>
                                        <td>{{ date_format($dateTaxOD,"d/m/Y") }}</td>
                                        <td>{{ $dtDiff->format("%r%a") }}</td>
                                        <td style="text-align: center;border-right:1px solid black;">{{ $fk->initial }}</td>
                                    </tr>                                    
                                @endif
                            @endforeach
                        @endif
                        @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N' || $lokal_input=='' || $lokal_input=='x')
                            {{-- non ppn --}}
                            @php
                                $notaPenjualan = \App\Models\Tx_delivery_order_non_tax::leftJoin('mst_customers as cust','tx_delivery_order_non_taxes.customer_id','=','cust.id')
                                ->leftJoin('tx_kwitansi_details as inv_dtl','tx_delivery_order_non_taxes.id','=','inv_dtl.np_id')
                                ->leftJoin('tx_kwitansis as inv','inv_dtl.kwitansi_id','=','inv.id')
                                ->leftJoin('userdetails','cust.salesman_id','=','userdetails.user_id')
                                ->select(
                                    'tx_delivery_order_non_taxes.id as order_id',
                                    'tx_delivery_order_non_taxes.delivery_order_no',
                                    'tx_delivery_order_non_taxes.total_price',
                                    'cust.name as customer_name',
                                    'cust.customer_unique_code',
                                    'cust.top as customer_top',
                                    'inv.id as inv_id',
                                    'inv.kwitansi_no',
                                    'inv.kwitansi_date',
                                    'inv.created_at AS inv_created_at',
                                    'userdetails.initial',
                                )
                                ->where([
                                    'tx_delivery_order_non_taxes.branch_id'=>$branch->id,
                                    'tx_delivery_order_non_taxes.is_draft'=>'N',
                                    'tx_delivery_order_non_taxes.active'=>'Y',
                                    'inv.is_draft' => 'N',
                                    'inv.active' => 'Y',
                                ])
                                ->whereNotIn('inv.kwitansi_no', function($q){
                                    $q->select('tx_pri.invoice_no')
                                    ->from('tx_payment_receipt_invoices AS tx_pri')
                                    ->leftJoin('tx_payment_receipts AS tx_pr','tx_pri.payment_receipt_id','=','tx_pr.id')
                                    ->where([
                                        'tx_pri.is_full_payment'=>'Y',
                                        'tx_pri.active'=>'Y',
                                        'tx_pr.is_draft'=>'N',
                                        'tx_pr.active'=>'Y',
                                    ]);
                                })
                                ->orderBy('cust.name','ASC')
                                ->orderBy('inv.kwitansi_no','ASC')
                                ->orderBy('tx_delivery_order_non_taxes.delivery_order_no','ASC')
                                ->orderBy('inv.kwitansi_date','ASC')
                                ->get();
                            @endphp
                            @foreach ($notaPenjualan as $np)
                                @php
                                    $dateNonTax = date_create($np->inv_created_at);
                                    date_add($dateNonTax, date_interval_create_from_date_string(ENV("WAKTU_ID")." hours"));
                                    $dateNonTaxOD = clone $dateNonTax;
                                    date_add($dateNonTaxOD, date_interval_create_from_date_string($np->customer_top." days"));
                                    $dtDiff = date_diff($dateNonTaxOD, $date);
                                @endphp
                                @if ($dtDiff->format("%r%a")>0)
                                    <tr>
                                        <td style="border-left:1px solid black;">{{ $np->customer_unique_code.' - '.$np->customer_name }}</td>
                                        <td>{{ $np->kwitansi_no }}</td>
                                        <td>{{ $np->delivery_order_no }}</td>
                                        @php
                                            $sumRetur = \App\Models\Tx_nota_retur_non_tax::whereRaw('approved_by IS NOT null')
                                            ->where([
                                                'delivery_order_id'=>$np->order_id,
                                                'is_draft'=>'N',
                                                'active'=>'Y',
                                            ])
                                            ->sum('total_price');
                                            $totBeforeVatPerBranch += ($np->total_price-$sumRetur);
                                        @endphp
                                        <td>{{ ($np->total_price-$sumRetur) }}</td>
                                        <td>&nbsp;</td>
                                        <td>{{ ($np->total_price-$sumRetur) }}</td>
                                        <td>{{ date_format($dateNonTax,"d/m/Y") }}</td>
                                        <td>{{ date_format($dateNonTaxOD,"d/m/Y") }}</td>
                                        <td>{{ $dtDiff->format("%r%a") }}</td>
                                        <td style="text-align: center;border-right:1px solid black;">{{ $np->initial }}</td>
                                    </tr>                                    
                                @endif
                            @endforeach
                        @endif
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="font-weight: 700;">{{ ($totBeforeVatPerBranch>0?$totBeforeVatPerBranch:'') }}</td>
                            <td style="font-weight: 700;">{{ ($totVatPerBranch>0?$totVatPerBranch:'') }}</td>
                            <td style="font-weight: 700;">{{ (($totBeforeVatPerBranch+$totVatPerBranch)>0?($totBeforeVatPerBranch+$totVatPerBranch):'') }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $totGrandBeforeVatPerBranch += $totBeforeVatPerBranch;
                            $totGrandVatPerBranch += $totVatPerBranch;
                        @endphp
                    @endforeach
                    <tr>
                        <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;text-align:center;font-weight:700;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;font-weight:700;">{{ $totGrandBeforeVatPerBranch }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;font-weight:700;">{{ $totGrandVatPerBranch }}</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;font-weight:700;">{{ $totGrandBeforeVatPerBranch+$totGrandVatPerBranch }}</td>
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
