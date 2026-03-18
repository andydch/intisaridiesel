<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>DebtOverduePerBranch</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $totCols = 9;
                    $timezoneNow = new DateTimeZone('Asia/Jakarta');
                    $date_local_now = new DateTime();
                    $date_local_now->setTimeZone($timezoneNow);
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">DEBT OVERDUE PER BRANCH</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ $date_local_now->format('d/m/Y') }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SUPPLIER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">INV NO.</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DUE DATE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">OVERDUE</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;width:100px;">PIC</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAmount = 0;
                        $totalVatAmount = 0;
                        $grandTotalAmount = 0;
                        $branches = \App\Models\Mst_branch::when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->where('active','=','Y')
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight:700;border-left:1px solid black;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $supplierName = '';

                            $q_tx_ro = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                            ->leftJoin('userdetails','tx_receipt_orders.created_by','=','userdetails.user_id')
                            ->select(
                                'tx_receipt_orders.id as ro_id',
                                'tx_receipt_orders.receipt_date',
                                'tx_receipt_orders.supplier_type_id',
                                'tx_receipt_orders.po_or_pm_no',
                                'tx_receipt_orders.invoice_no',
                                'tx_receipt_orders.invoice_amount',
                                'tx_receipt_orders.total_before_vat_rp',
                                'tx_receipt_orders.total_before_vat',
                                'tx_receipt_orders.total_vat_rp',
                                'tx_receipt_orders.total_vat',
                                'tx_receipt_orders.total_after_vat_rp',
                                'tx_receipt_orders.total_after_vat',
                                'tx_receipt_orders.exchange_rate',
                                'm_sp.name as supplier_name',
                                'm_sp.top',
                                'userdetails.initial',
                            )
                            ->whereNotIn('tx_receipt_orders.id', function ($q01) {
                                // buang RO yg PV detail berstatus full payment dan aktif
                                $q01->select('receipt_order_id')
                                ->from('tx_payment_voucher_invoices')
                                ->whereIn('payment_voucher_id', function ($q02) {
                                    // kumpulkan PV detail yg PV induk berstatus bukan draft, approved/rejected dan aktif
                                    $q02->select('id')
                                    ->from('tx_payment_vouchers')
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('active','=','Y');
                                })
                                ->where('is_full_payment','=','Y')
                                ->where('active','=','Y');
                            })
                            ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_receipt_orders.branch_id'=>$branch->id,
                                'tx_receipt_orders.active'=>'Y',
                            ])
                            ->orderBy('m_sp.name','ASC')
                            ->orderBy('tx_receipt_orders.receipt_date','ASC')
                            ->get();
                        @endphp
                        @foreach ($q_tx_ro as $ro)
                            <tr>
                                <td style="border-left:1px solid black;">
                                    @if ($supplierName!=$ro->supplier_name)
                                        @php
                                            $supplierName = $ro->supplier_name;
                                        @endphp
                                        {{ $supplierName }}
                                    @else
                                        {{ '' }}
                                    @endif
                                </td>
                                <td>{{ $ro->invoice_no }}</td>
                                <td style="text-align: right;">
                                    @php
                                        $invoice_amount = 0;
                                        $has_been_paid = \App\Models\Tx_payment_voucher_invoice::leftJoin('tx_payment_vouchers as tx_pv','tx_payment_voucher_invoices.payment_voucher_id','=','tx_pv.id')
                                        ->where([
                                            'tx_payment_voucher_invoices.receipt_order_id'=>$ro->ro_id,
                                            'tx_payment_voucher_invoices.active'=>'Y',
                                            'tx_pv.active'=>'Y',
                                        ])
                                        ->whereRaw('tx_pv.approved_by IS NOT NULL')
                                        ->sum('tx_payment_voucher_invoices.total_payment');

                                        // $invoice_amount = ($ro->invoice_amount-$has_been_paid)*($ro->exchange_rate>0?$ro->exchange_rate:1);
                                        if($ro->supplier_type_id==10){
                                            $invoice_amount = $ro->total_before_vat_rp;
                                        }
                                        if($ro->supplier_type_id==11){
                                            $invoice_amount = $ro->total_before_vat;
                                        }
                                    @endphp
                                    {{ number_format($invoice_amount,0,'.','') }}
                                </td>
                                {{-- @php
                                    $is_vat = '';
                                    $po_mo = explode(",",$ro->po_or_pm_no);
                                @endphp
                                @foreach ($po_mo as $p_m)
                                    @if ($p_m!='')
                                        @if (strpos("-".$p_m,env('P_PURCHASE_MEMO'))>0)
                                            @php
                                                $qMo = \App\Models\Tx_purchase_memo::where([
                                                    'memo_no'=>$p_m,
                                                ])
                                                ->first();
                                                if ($qMo){
                                                    $is_vat = $qMo->is_vat;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                        @if (strpos("-".$p_m,env('P_PURCHASE_ORDER'))>0)
                                            @php
                                                $qPo = \App\Models\Tx_purchase_order::where([
                                                    'purchase_no'=>$p_m,
                                                ])
                                                ->first();
                                                if ($qPo){
                                                    $is_vat = $qPo->is_vat;
                                                    break;
                                                }
                                            @endphp
                                        @endif
                                    @endif
                                @endforeach --}}
                                <td style="text-align: right;">
                                    {{-- {{ ($is_vat=='Y'?number_format((($invoice_amount*$vat)/100),0,'.',''):0) }} --}}
                                    @if ($ro->supplier_type_id==10)
                                        {{ number_format($ro->total_vat_rp,0,'.','') }}
                                    @endif
                                    @if ($ro->supplier_type_id==11)
                                        {{ number_format($ro->total_vat,0,'.','') }}
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    {{-- {{ ($is_vat=='Y'?number_format($invoice_amount+(($invoice_amount*$vat)/100),0,'.',''):number_format($invoice_amount,0,'.','')) }} --}}
                                    @if ($ro->supplier_type_id==10)
                                        {{ number_format($ro->total_after_vat_rp,0,'.','') }}
                                    @endif
                                    @if ($ro->supplier_type_id==11)
                                        {{ number_format($ro->total_after_vat,0,'.','') }}
                                    @endif
                                </td>
                                <td style="text-align: center;">{{ date_format(date_create($ro->receipt_date),"d/m/Y") }}</td>
                                @php
                                    $datedue = date_add(date_create($ro->receipt_date), date_interval_create_from_date_string($ro->top." days"));
                                @endphp
                                <td style="text-align: center;">{{ date_format($datedue,"d/m/Y") }}</td>
                                @php
                                    $dtDiff = date_diff($datedue,$date_local_now);
                                @endphp
                                <td style="text-align: right;">{{ $dtDiff->format("%r%a") }}</td>
                                <td style="text-align: center;border-right:1px solid black;">{{ $ro->initial }}</td>
                            </tr>
                            @php
                                $totalAmount += $invoice_amount;
                                // $totalVatAmount += ($is_vat=='Y'?(($invoice_amount*$vat)/100):0);
                                // $grandTotalAmount += ($is_vat=='Y'?($invoice_amount+(($invoice_amount*$vat)/100)):0);
                                if($ro->supplier_type_id==10){
                                    $totalVatAmount += $ro->total_vat_rp;
                                    $grandTotalAmount += ($invoice_amount+$ro->total_vat_rp);
                                }
                                if($ro->supplier_type_id==11){
                                    $totalVatAmount += $ro->total_vat;
                                    $grandTotalAmount += ($invoice_amount+$ro->total_vat);
                                }

                                $qPR = \App\Models\Tx_purchase_retur::whereRaw('approved_by IS NOT null')
                                ->where([
                                    'receipt_order_id'=>$ro->ro_id,
                                    'active'=>'Y',
                                ])
                                ->get();
                            @endphp
                            @foreach ($qPR as $qPr)
                                <tr>
                                    <td style="color: red;">&nbsp;</td>
                                    <td style="color: red;">{{ $qPr->purchase_retur_no }}</td>
                                    <td style="color: red;">-{{ number_format($qPr->total_before_vat,0,'.','') }}</td>
                                    <td style="color: red;">-{{ ($qPr->vat_val>0?number_format(($qPr->total_before_vat*$qPr->vat_val)/100,0,'.',''):0) }}</td>
                                    <td style="color: red;">-{{ ($qPr->vat_val>0?number_format($qPr->total_before_vat+(($qPr->total_before_vat*$qPr->vat_val)/100),0,'.',''):0) }}</td>
                                    <td style="color: red;text-align: center;">{{ date_format(date_create($ro->purchase_retur_date),"d/m/Y") }}</td>
                                    @php
                                        $datedue = date_add(date_create($ro->purchase_retur_date), date_interval_create_from_date_string($ro->top." days"));
                                    @endphp
                                    <td style="color: red;text-align: center;">{{ date_format($datedue,"d/m/Y") }}</td>
                                    @php
                                        $dtDiff = date_diff($datedue,$date_local_now);
                                    @endphp
                                    <td style="color: red;text-align: right;">{{ $dtDiff->format("%r%a") }}</td>
                                    <td style="color: red;text-align: center;border-right:1px solid black;">{{ $ro->initial }}</td>
                                </tr>
                                @php
                                    $totalAmount = $totalAmount-$qPr->total_before_vat;
                                    $totalVatAmount = $totalVatAmount-($qPr->vat_val>0?(($qPr->total_before_vat*$qPr->vat_val)/100):0);
                                    $grandTotalAmount = $grandTotalAmount-($qPr->vat_val>0?($qPr->total_before_vat+(($qPr->total_before_vat*$qPr->vat_val)/100)):$qPr->total_before_vat);
                                @endphp
                            @endforeach
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
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalVatAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format(($totalAmount+$totalVatAmount),0,'.','') }}</td>
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
