<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>PosisiHutangHariIni</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $totCols = 7;
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
                        <th colspan="{{ $totCols }}">POSISI HUTANG HARI INI</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ $date_local_now->format('d/m/Y') }}</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SUPPLIER NAME</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL THIS MONTH ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">LAST MO ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">LAST 2 MO ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">LAST 3 MO ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">MORE 3 MO ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        {{-- <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PAYMENT ({{ $qCurrency->string_val }})</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalAmount = 0;
                        $totalPaymentAmount = 0;
                        $totalEndBalanceAmount = 0;
                        $totalThisMonthAmount = 0;
                        $totalLastMonthAmount = 0;
                        $totalLast2MonthAmount = 0;
                        $totalLast3MonthAmount = 0;
                        $totalMore3MonthAmount = 0;
                        $sumPvTotalAllTmp = 0;
                        $qRO_lastmore3month_amountTmp = 0;
                        $qRO_last3month_amountTmp = 0;
                        $qRO_last2month_amountTmp = 0;
                        $qRO_lastmonth_amountTmp = 0;
                        $qRO_thismonth_amountTmp = 0;

                        $timezoneNow = new DateTimeZone('Asia/Jakarta');
                        $date_local_now = new DateTime();
                        $date_local_now->setTimeZone($timezoneNow);
                        $date_local_lastmonth = new DateTime();
                        $date_local_lastmonth->setTimeZone($timezoneNow);
                        $date_local_last2month = new DateTime();
                        $date_local_last2month->setTimeZone($timezoneNow);
                        $date_local_last3month = new DateTime();
                        $date_local_last3month->setTimeZone($timezoneNow);
                        $date_local_lastMore3month = new DateTime();
                        $date_local_lastMore3month->setTimeZone($timezoneNow);
                        $this_month = $date_local_now;
                        $last_month = $date_local_lastmonth;
                        $last_2month = $date_local_last2month;
                        $last_3month = $date_local_last3month;
                        $last_more3month = $date_local_lastMore3month;
                        date_add($this_month, date_interval_create_from_date_string("0 months"));
                        date_add($last_month, date_interval_create_from_date_string("-1 months"));
                        date_add($last_2month, date_interval_create_from_date_string("-2 months"));
                        date_add($last_3month, date_interval_create_from_date_string("-3 months"));
                        date_add($last_more3month, date_interval_create_from_date_string("-4 months"));

                        // $branches = \App\Models\Mst_branch::when($branch_id!='0', function($q) use($branch_id) {
                        //     $q->where('id','=',$branch_id);
                        // })
                        // ->where('active','=','Y')
                        // ->orderBy('name','ASC')
                        // ->get();
                    @endphp
                    {{-- @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight:700;border-left:1px solid black;">{{ $branch->name }}</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr> --}}
                        @php
                            $q_tx_ro = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                            ->select(
                                'm_sp.id as supplier_id',
                                'm_sp.name as supplier_name',
                                'm_sp.supplier_code as supplier_code',
                            )
                            ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                            ->where([
                                // 'tx_receipt_orders.branch_id'=>$branch->id,
                                'tx_receipt_orders.active'=>'Y',
                                // 'm_sp.id'=>40,
                            ])
                            ->orderBy('m_sp.name','ASC')
                            ->groupBy('m_sp.id')
                            ->groupBy('m_sp.name')
                            ->groupBy('m_sp.supplier_code')
                            ->get();
                        @endphp
                        @foreach ($q_tx_ro as $ro)
                            @php
                                $sumPvTotalAll = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat
                                    +IFNULL(admin_bank,0)
                                    +IFNULL(biaya_kirim,0)
                                    +IFNULL(biaya_asuransi,0)
                                    -IFNULL(diskon_pembelian,0)) as payment_total_after_vat')
                                ->where('supplier_id', '=', $ro->supplier_id)
                                ->whereRaw('approved_by IS NOT NULL')
                                ->where('is_draft', '=', 'N')
                                ->where('active', '=', 'Y')
                                ->value('payment_total_after_vat');
                                $sumPvTotalAllTmp = $sumPvTotalAll;

                                // less than 3 months ago
                                    $qRO_lastmore3month_amount = 0;
                                    $qRO_lastmore3month = \App\Models\Tx_receipt_order::selectRaw('CASE 
                                        WHEN supplier_type_id=10 THEN SUM(total_after_vat_rp) 
                                        WHEN supplier_type_id=11 THEN SUM(total_after_vat) 
                                        ELSE 0 END AS total_after_vat_ro')
                                    ->whereRaw('(receipt_date<=\''.date_format($last_more3month, "Y-m").'-01\')')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->groupBy('supplier_id')
                                    ->groupBy('supplier_type_id')
                                    ->first();
                                    if ($qRO_lastmore3month){
                                        $qRO_lastmore3month_amount = $qRO_lastmore3month->total_after_vat_ro;
                                        $qRO_lastmore3month_amountTmp = $qRO_lastmore3month->total_after_vat_ro;
                                    }

                                    $q_tx_pr_last_more3month = \App\Models\Tx_purchase_retur::whereRaw('purchase_retur_date<\''.date_format($last_more3month,"Y-m").'-01\'')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->sum('total_after_vat');

                                    $qRO_lastmore3month_amount = $qRO_lastmore3month_amount - $q_tx_pr_last_more3month - $sumPvTotalAll;
                                    $sumPvTotalAll = $qRO_lastmore3month_amount<0?$qRO_lastmore3month_amount*-1:0;
                                    $qRO_lastmore3month_amount = $qRO_lastmore3month_amount<0?0:$qRO_lastmore3month_amount;
                                    $totalMore3MonthAmount += $qRO_lastmore3month_amount;
                                // less than 3 months ago

                                // 3 months ago
                                    $qRO_last3month_amount = 0;
                                    $qRO_last3month = \App\Models\Tx_receipt_order::selectRaw('CASE 
                                        WHEN supplier_type_id=10 THEN SUM(IFNULL(total_after_vat_rp, 0)) 
                                        WHEN supplier_type_id=11 THEN SUM(IFNULL(total_after_vat, 0)) 
                                        ELSE 0 END AS total_after_vat_ro')
                                    ->whereRaw('(DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_3month, "Y-m").'\')')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->groupBy('supplier_id')
                                    ->groupBy('supplier_type_id')
                                    ->first();
                                    if ($qRO_last3month){
                                        $qRO_last3month_amount = $qRO_last3month->total_after_vat_ro==null?0:$qRO_last3month->total_after_vat_ro;
                                        $qRO_last3month_amountTmp = $qRO_last3month->total_after_vat_ro;
                                    }

                                    $q_tx_pr_last_3month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_3month, "Y-m").'\'')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->sum('total_after_vat');

                                    $qRO_last3month_amount = $qRO_last3month_amount - $q_tx_pr_last_3month - $sumPvTotalAll;
                                    $sumPvTotalAll = $qRO_last3month_amount<0?$qRO_last3month_amount*-1:0;
                                    $qRO_last3month_amount = $qRO_last3month_amount<0?0:$qRO_last3month_amount;
                                    $totalLast3MonthAmount += $qRO_last3month_amount;
                                // 3 months ago

                                // 2 months ago
                                    $qRO_last2month_amount = 0;
                                    $qRO_last2month = \App\Models\Tx_receipt_order::selectRaw('CASE 
                                        WHEN supplier_type_id=10 THEN SUM(IFNULL(total_after_vat_rp, 0)) 
                                        WHEN supplier_type_id=11 THEN SUM(IFNULL(total_after_vat, 0)) 
                                        ELSE 0 END AS total_after_vat_ro')
                                    ->whereRaw('(DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_2month, "Y-m").'\')')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->groupBy('supplier_id')
                                    ->groupBy('supplier_type_id')
                                    ->first();
                                    if ($qRO_last2month){
                                        $qRO_last2month_amount = $qRO_last2month->total_after_vat_ro==null?0:$qRO_last2month->total_after_vat_ro;
                                        $qRO_last2month_amountTmp = $qRO_last2month->total_after_vat_ro;
                                    }

                                    $q_tx_pr_last_2month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_2month, "Y-m").'\'')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->sum('total_after_vat');

                                    $qRO_last2month_amount = $qRO_last2month_amount - $q_tx_pr_last_2month - $sumPvTotalAll;
                                    $sumPvTotalAll = $qRO_last2month_amount<0?$qRO_last2month_amount*-1:0;
                                    $qRO_last2month_amount = $qRO_last2month_amount<0?0:$qRO_last2month_amount;
                                    $totalLast2MonthAmount += $qRO_last2month_amount;
                                // 2 months ago

                                // a month ago
                                    $qRO_lastmonth_amount = 0;
                                    $qRO_lastmonth = \App\Models\Tx_receipt_order::selectRaw('CASE 
                                        WHEN supplier_type_id=10 THEN SUM(IFNULL(total_after_vat_rp, 0)) 
                                        WHEN supplier_type_id=11 THEN SUM(IFNULL(total_after_vat, 0)) 
                                        ELSE 0 END AS total_after_vat_ro')
                                    ->whereRaw('(DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_month, "Y-m").'\')')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->groupBy('supplier_id')
                                    ->groupBy('supplier_type_id')
                                    ->first();
                                    if ($qRO_lastmonth){
                                        $qRO_lastmonth_amount = $qRO_lastmonth->total_after_vat_ro==null?0:$qRO_lastmonth->total_after_vat_ro;
                                        $qRO_lastmonth_amountTmp = $qRO_lastmonth->total_after_vat_ro;
                                    }

                                    $q_tx_pr_last_month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_month, "Y-m").'\'')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->sum('total_after_vat');

                                    $qRO_lastmonth_amount = $qRO_lastmonth_amount - $q_tx_pr_last_month - $sumPvTotalAll;
                                    $sumPvTotalAll = $qRO_lastmonth_amount<0?$qRO_lastmonth_amount*-1:0;
                                    $qRO_lastmonth_amount = $qRO_lastmonth_amount<0?0:$qRO_lastmonth_amount;
                                    $totalLastMonthAmount += $qRO_lastmonth_amount;
                                // a month ago

                                // this month
                                    $qRO_thismonth_amount = 0;
                                    $qRO_thismonth = \App\Models\Tx_receipt_order::selectRaw('CASE 
                                        WHEN supplier_type_id=10 THEN SUM(IFNULL(total_after_vat_rp, 0)) 
                                        WHEN supplier_type_id=11 THEN SUM(IFNULL(total_after_vat, 0)) 
                                        ELSE 0 END AS total_after_vat_ro')
                                    ->whereRaw('(DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($this_month, "Y-m").'\')')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->groupBy('supplier_id')
                                    ->groupBy('supplier_type_id')
                                    ->first();
                                    if ($qRO_thismonth){
                                        $qRO_thismonth_amount = $qRO_thismonth->total_after_vat_ro==null?0:$qRO_thismonth->total_after_vat_ro;
                                        $qRO_thismonth_amountTmp = $qRO_thismonth->total_after_vat_ro;
                                    }

                                    $q_tx_pr_this_month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($this_month, "Y-m").'\'')
                                    ->where('supplier_id', '=', $ro->supplier_id)
                                    ->whereRaw('approved_by IS NOT NULL')
                                    ->where('is_draft', '=', 'N')
                                    ->where('active', '=', 'Y')
                                    ->sum('total_after_vat');

                                    $qRO_thismonth_amount = $qRO_thismonth_amount - $q_tx_pr_this_month - $sumPvTotalAll;
                                    $sumPvTotalAll = $qRO_thismonth_amount<0?$qRO_thismonth_amount*-1:0;
                                    $qRO_thismonth_amount = $qRO_thismonth_amount<0?0:$qRO_thismonth_amount;
                                    $totalThisMonthAmount += $qRO_thismonth_amount;
                                // this month

                                $qRO_thisyear_amount = ($qRO_thismonth_amount
                                    +$qRO_lastmonth_amount
                                    +$qRO_last2month_amount
                                    +$qRO_last3month_amount
                                    +$qRO_lastmore3month_amount);
                            @endphp
                            @if ($qRO_thisyear_amount>0)
                                
                                <tr>
                                    <td style="border-left:1px solid black;">
                                        {{ $ro->supplier_code.' - '.
                                            ($ro->supplier->entity_type?$ro->supplier->entity_type->title_ind:'').' '.$ro->supplier_name }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($qRO_thismonth_amount,0,'.','') }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($qRO_lastmonth_amount,0,'.','') }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($qRO_last2month_amount,0,'.','') }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($qRO_last3month_amount,0,'.','') }}
                                    </td>
                                    <td style="text-align: right;">
                                        {{ number_format($qRO_lastmore3month_amount,0,'.','') }}
                                    </td>
                                    <td style="text-align: right;border-right:1px solid black;">
                                        {{ number_format($qRO_thisyear_amount,0,'.','') }}
                                    </td>
                                    {{-- <td style="text-align: right;border-right:1px solid black;">
                                        @php
                                            $sumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat
                                                +IFNULL(admin_bank,0)
                                                +IFNULL(biaya_kirim,0)
                                                +IFNULL(biaya_asuransi,0)
                                                -IFNULL(diskon_pembelian,0)) as payment_total_after_vat')
                                            ->where('supplier_id', '=', $ro->supplier_id)
                                            ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                                            ->whereRaw('approved_by IS NOT NULL')
                                            ->where('is_draft', '=', 'N')
                                            ->where('active', '=', 'Y')
                                            ->value('payment_total_after_vat');

                                            $totalPaymentAmount += $sumPV;
                                        @endphp
                                        {{ number_format($sumPV,0,'.','') }}
                                    </td> --}}
                                    {{-- <td style="text-align: right;border-right:1px solid black;">
                                        {{ number_format($sumPvTotalAllTmp,0,'.','') }}
                                    </td> --}}
                                </tr>
                            @endif
                            {{-- <tr>
                                <td>&nbsp;</td>
                                <td style="background-color: orange;">{{ $q_tx_pr_this_month }}</td>
                                <td style="background-color: orange;">{{ $q_tx_pr_last_month }}</td>
                                <td style="background-color: orange;">{{ $q_tx_pr_last_2month }}</td>
                                <td style="background-color: orange;">{{ $q_tx_pr_last_3month }}</td>
                                <td style="background-color: orange;">{{ $q_tx_pr_last_more3month }}</td>
                                <td style="background-color: orange;">&nbsp;</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td style="background-color: yellow;">{{ $qRO_thismonth_amountTmp }}</td>
                                <td style="background-color: yellow;">{{ $qRO_lastmonth_amountTmp }}</td>
                                <td style="background-color: yellow;">{{ $qRO_last2month_amountTmp }}</td>
                                <td style="background-color: yellow;">{{ $qRO_last3month_amountTmp }}</td>
                                <td style="background-color: yellow;">{{ $qRO_lastmore3month_amountTmp }}</td>
                                <td style="background-color: yellow;">&nbsp;</td>
                            </tr> --}}
                        @endforeach
                        <tr>
                            <td style="border-left:1px solid black;">&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    {{-- @endforeach --}}
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalThisMonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLastMonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLast2MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLast3MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalMore3MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
                            {{ number_format($totalThisMonthAmount+
                                $totalLastMonthAmount+
                                $totalLast2MonthAmount+
                                $totalLast3MonthAmount+
                                $totalMore3MonthAmount,0,'.','') }}
                        </td>
                        {{-- <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">{{ number_format($totalPaymentAmount,0,'.','') }}</td> --}}
                        {{-- <td style="text-align: right;font-weight:700;border-right:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalEndBalanceAmount,0,'.','') }}</td> --}}
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
