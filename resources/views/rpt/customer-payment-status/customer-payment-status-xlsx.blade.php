<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>CustPaymentStatus</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 12;
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
                        <th colspan="{{ $totCols }}">CUSTOMER PAYMENT STATUS</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NAMA CUSTOMER</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">MONTH TX</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO INV</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">INV DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">DUE DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PAID DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PA NO</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PAID AMOUNT ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALDO ({{ $qCurrency->string_val }})</th>
                    </tr>
                    @php
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $grandTotalDPP = 0;
                        $grandTotalPPN = 0;
                        $grandTotalDPPplusPPN = 0;
                        $grandTotalPaidAmount = 0;
                        $grandTotalSaldo = 0;

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight: 700;border-left:1px solid black;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
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
                            $totalDPP = 0;
                            $totalPPN = 0;
                            $totalDPPplusPPN = 0;
                            $totalAVG = 0;
                            $cust_name = '';
                            $invoice_no = '';
                            $date_tx = '';

                            $qCust = \App\Models\Mst_customer::leftJoin('mst_globals as ett_type','mst_customers.entity_type_id','=','ett_type.id')
                            ->leftJoin('v_invoices as v_inv','mst_customers.id','=','v_inv.customer_id')
                            // ->leftJoin('v_invoice_details as v_inv_d','v_inv.invoice_no','=','v_inv_d.invoice_no')
                            ->leftJoin('tx_payment_receipt_invoices as tx_pri','v_inv.invoice_no','=','tx_pri.invoice_no')
                            ->leftJoin('tx_payment_receipts as tx_pr','tx_pri.payment_receipt_id','=','tx_pr.id')
                            ->select(
                                'mst_customers.id as cust_id',
                                'mst_customers.name as name',
                                'v_inv.invoice_no as invoice_no',
                                'v_inv.tagihan_dpp as total_dpp',
                                'v_inv.tagihan as total',
                                DB::raw('DATE_FORMAT(DATE_ADD(v_inv.invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR), "%d/%m/%Y") as inv_date'),
                                DB::raw('DATE_FORMAT(DATE_ADD(DATE_ADD(v_inv.invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR), INTERVAL mst_customers.top DAY), "%d/%m/%Y") as due_date'),
                                // 'v_inv_d.delivery_order_no as delivery_order_no',
                                DB::raw('DATE_FORMAT(DATE_ADD(tx_pr.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR), "%d/%m/%Y") as paid_date'),
                                'tx_pr.payment_receipt_no as pa_no',
                                'tx_pri.total_payment_full_after_vat as total_payment_full_after_vat',
                                'tx_pri.total_payment_after_vat as paid_amount',
                                'tx_pri.active as paid_active',
                            )
                            ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mst_customers.customer_unique_code,\' - \',mst_customers.name),
                                CONCAT(mst_customers.customer_unique_code,\' - \',ett_type.title_ind,\' \',mst_customers.name)) as cust_name')
                            ->selectRaw('(SELECT v_invd.delivery_order_date FROM v_invoice_details AS v_invd
                                WHERE v_invd.invoice_no=v_inv.invoice_no
                                ORDER BY v_invd.delivery_order_date ASC
                                LIMIT 1) as last_invoice_no_date')
                            ->whereIn('mst_customers.id', function($q) use($branch, $dt_s, $dt_e, $lokal_input) {
                                $q->select('customer_id')
                                ->from('v_invoices')
                                // ->whereRaw('DATE_ADD(invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR)>\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].' 0:0:0\'')
                                // ->whereRaw('DATE_ADD(invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR)<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].' 23:59:59\'')
                                ->where('branch_id','=',$branch->id)
                                ->when($lokal_input!='A' && $lokal_input!='', function($q1) use($lokal_input) {
                                    if(strtoupper($lokal_input)=='P'){
                                        $q1->where('inv_identity','=','I');
                                    } elseif (strtoupper($lokal_input)=='N'){
                                        $q1->where('inv_identity','=','K');
                                    }
                                });
                            })
                            // ->where('mst_customers.id','=',60)
                            ->where('mst_customers.active','=','Y')
                            ->whereIn('v_inv.invoice_no', function($q) use($dt_s, $dt_e) {
                                $q->select('invoice_no')
                                ->from('v_invoice_details')
                                ->whereRaw('delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                ->whereRaw('delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'');
                            })
                            // ->whereRaw('DATE_ADD(v_inv.invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR)>\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].' 0:0:0\'')
                            // ->whereRaw('DATE_ADD(v_inv.invoice_date, INTERVAL '.ENV("WAKTU_ID").' HOUR)<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].' 23:59:59\'')
                            ->orderByRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mst_customers.customer_unique_code,\' - \',mst_customers.name),
                                CONCAT(mst_customers.customer_unique_code,\' - \',ett_type.title_ind,\' \',mst_customers.name)) ASC')
                            ->orderBy('v_inv.invoice_no','ASC')
                            ->orderBy('tx_pr.created_at','ASC')
                            ->get();
                        @endphp
                        @foreach ($qCust as $qC)
                            @if ($qC->paid_active!='N')
                                @if ($cust_name!=$qC->cust_name)
                                    <tr>
                                        <td style="border-left:1px solid black;">&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="font-weight: 300;border-left:1px solid black;">@if($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                    <td style="font-weight: 300;text-align: center;">
                                        @if($cust_name!=$qC->cust_name)
                                            {{ date_format(date_create($qC->last_invoice_no_date),"d/m/Y") }}
                                        @elseif ($date_tx != $qC->last_invoice_no_date)
                                            {{ date_format(date_create($qC->last_invoice_no_date),"d/m/Y") }}
                                        @endif
                                    </td>
                                    <td style="font-weight: 300;text-align: center;">@if($invoice_no!=$qC->invoice_no){{ $qC->invoice_no }}@endif</td>
                                    {{-- <td style="font-weight: 300;text-align: center;">{{ $qC->delivery_order_no }}</td> --}}
                                    <td style="text-align: right;">@if($invoice_no!=$qC->invoice_no){{ $qC->inv_date }}@endif</td>
                                    <td style="text-align: right;">@if($invoice_no!=$qC->invoice_no){{ $qC->due_date }}@endif</td>
                                    <td style="text-align: right;">@if($invoice_no!=$qC->invoice_no){{ number_format($qC->total_dpp,0,'.','') }}@endif</td>
                                    <td style="text-align: right;">@if($invoice_no!=$qC->invoice_no){{ number_format($qC->total-$qC->total_dpp,0,'.','') }}@endif</td>
                                    <td style="text-align: right;">@if($invoice_no!=$qC->invoice_no){{ number_format($qC->total,0,'.','') }}@endif</td>
                                    <td style="text-align: right;">{{ $qC->paid_date }}</td>
                                    <td style="text-align: right;">{{ $qC->pa_no }}</td>
                                    <td style="text-align: right;">{{ number_format($qC->paid_amount,0,'.','') }}</td>
                                    <td style="text-align: right;border-right:1px solid black;">
                                        @if ($invoice_no!=$qC->invoice_no)
                                            {{ $qC->paid_amount>0?number_format($qC->total-$qC->paid_amount,0,'.',''):number_format($qC->total,0,'.','') }}
                                        @else
                                            {{ $qC->paid_amount>0?number_format($qC->total_payment_full_after_vat-$qC->paid_amount,0,'.',''):number_format($qC->total,0,'.','') }}
                                        @endif
                                        {{-- {{ $qC->paid_amount>0?number_format($qC->total_payment_full_after_vat-$qC->paid_amount,0,'.',''):number_format($qC->total,0,'.','') }} --}}
                                    </td>
                                </tr>
                                
                                @php
                                    $grandTotalDPP += ($invoice_no!=$qC->invoice_no?$qC->total_dpp:0);
                                    $grandTotalPPN += ($invoice_no!=$qC->invoice_no?$qC->total-$qC->total_dpp:0);
                                    $grandTotalDPPplusPPN += ($invoice_no!=$qC->invoice_no?$qC->total:0);
                                    $grandTotalPaidAmount += $qC->paid_amount;
                                    $grandTotalSaldo += ($invoice_no!=$qC->invoice_no?$qC->total-$qC->paid_amount:$qC->total_payment_full_after_vat-$qC->paid_amount);
                                    
                                    $cust_name = $qC->cust_name;
                                    $invoice_no = $qC->invoice_no;
                                    $date_tx = $qC->last_invoice_no_date;
                                @endphp                                
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
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: center;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandTotalDPP,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandTotalPPN,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandTotalDPPplusPPN,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($grandTotalPaidAmount,0,'.','') }}</td>
                        {{-- <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td> --}}
                        <td style="text-align: right;font-weight:bold;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
                            {{ number_format($grandTotalDPPplusPPN-$grandTotalPaidAmount,0,'.','') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
