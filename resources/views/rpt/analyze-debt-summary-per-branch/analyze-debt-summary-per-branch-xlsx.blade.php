<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SummAnalisaHutangPerCb</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $totCols = 8;
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
                        <th colspan="{{ $totCols }}">SUMMARY ANALISA HUTANG PER CABANG</th>
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
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PAYMENT ({{ $qCurrency->string_val }})</th>
                        {{-- <th style="text-align: center;border:1px solid black;background-color:#daeef3;">END BALANCE ({{ $qCurrency->string_val }})</th> --}}
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
                            {{-- <td>&nbsp;</td> --}}
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $q_tx_ro = \App\Models\Tx_receipt_order::leftJoin('mst_suppliers as m_sp','tx_receipt_orders.supplier_id','=','m_sp.id')
                            ->select(
                                'm_sp.id as supplier_id',
                                'm_sp.name as supplier_name',
                                'm_sp.supplier_code as supplier_code',
                            )
                            ->whereRaw('tx_receipt_orders.receipt_no NOT LIKE \'%Draft%\'')
                            ->where([
                                'tx_receipt_orders.branch_id'=>$branch->id,
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
                            <tr>
                                <td style="border-left:1px solid black;">
                                    {{ $ro->supplier_code.' - '.
                                        ($ro->supplier->entity_type?$ro->supplier->entity_type->title_ind:'').' '.$ro->supplier_name }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $total_amount = 0;
                                        $qRO_thismonth_amount = 0;
                                        $qRO_thismonth = \App\Models\Tx_receipt_order::whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                                        ->where([
                                            'supplier_id'=>$ro->supplier_id,
                                            'branch_id'=>$branch->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        foreach ($qRO_thismonth as $ro_tm) {
                                            $total_amount = $ro_tm->supplier_type_id==10?$ro_tm->total_after_vat_rp:$ro_tm->total_after_vat;
                                            $qRO_thismonth_amount += $total_amount;
                                        }                                        

                                        $q_tx_pr_this_month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->where('branch_id', '=', $branch->id)
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->sum('total_after_vat');

                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        } 

                                        if (($qRO_thismonth_amount - $q_tx_pr_this_month)<=0){
                                            $qRO_thismonth_amount = $qRO_thismonth_amount - $q_tx_pr_this_month;
                                        }else{
                                            if (($qRO_thismonth_amount - $q_tx_pr_this_month - $sumPV)<=0){
                                                $qRO_thismonth_amount = 0;
                                            }else{
                                                $qRO_thismonth_amount = $qRO_thismonth_amount - $q_tx_pr_this_month - $sumPV;
                                            }
                                        }
                                        $totalThisMonthAmount+=$qRO_thismonth_amount;
                                    @endphp
                                    {{ number_format($qRO_thismonth_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $total_amount = 0;
                                        $qRO_lastmonth_amount = 0;
                                        $qRO_lastmonth = \App\Models\Tx_receipt_order::whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_month,"Y-m").'\'')
                                        ->where([
                                            'supplier_id'=>$ro->supplier_id,
                                            'branch_id'=>$branch->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        foreach ($qRO_lastmonth as $ro_tm) {
                                            $total_amount = $ro_tm->supplier_type_id==10?$ro_tm->total_after_vat_rp:$ro_tm->total_after_vat;
                                            $qRO_lastmonth_amount += $total_amount;
                                        }
                                        $q_tx_pr_last_month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_month,"Y-m").'\'')
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->where('branch_id', '=', $branch->id)
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->sum('total_after_vat');
                                        
                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        // ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($last_month,"Y-m").'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        }

                                        if (($qRO_lastmonth_amount - $q_tx_pr_last_month)<=0){
                                            $qRO_lastmonth_amount = $qRO_lastmonth_amount - $q_tx_pr_last_month;
                                        }else{
                                            if (($qRO_lastmonth_amount - $q_tx_pr_last_month - $sumPV)<=0){
                                                $qRO_lastmonth_amount = 0;
                                            }else{
                                                $qRO_lastmonth_amount = $qRO_lastmonth_amount - $q_tx_pr_last_month - $sumPV;
                                            }
                                        }
                                        // $qRO_lastmonth_amount = $qRO_lastmonth_amount - $q_tx_pr_last_month;
                                        $totalLastMonthAmount+=$qRO_lastmonth_amount;
                                    @endphp
                                    {{ number_format($qRO_lastmonth_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $total_amount = 0;
                                        $qRO_last2month_amount = 0;
                                        $qRO_last2month = \App\Models\Tx_receipt_order::whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_2month,"Y-m").'\'')
                                        ->where([
                                            'supplier_id'=>$ro->supplier_id,
                                            'branch_id'=>$branch->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        foreach ($qRO_last2month as $ro_tm) {
                                            $total_amount = $ro_tm->supplier_type_id==10?$ro_tm->total_after_vat_rp:$ro_tm->total_after_vat;
                                            $qRO_last2month_amount += $total_amount;
                                        }
                                        $q_tx_pr_last_2month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_2month,"Y-m").'\'')
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->where('branch_id', '=', $branch->id)
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->sum('total_after_vat');

                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        // ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($last_2month,"Y-m").'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        }

                                        if (($qRO_last2month_amount - $q_tx_pr_last_2month)<=0){
                                            $qRO_last2month_amount = $qRO_last2month_amount - $q_tx_pr_last_2month;
                                        }else{
                                            if (($qRO_last2month_amount - $q_tx_pr_last_2month - $sumPV)<=0){
                                                $qRO_last2month_amount = 0;
                                            }else{
                                                $qRO_last2month_amount = $qRO_last2month_amount - $q_tx_pr_last_2month - $sumPV;
                                            }
                                        }
                                        // $qRO_last2month_amount = $qRO_last2month_amount - $q_tx_pr_last_2month;
                                        $totalLast2MonthAmount+=$qRO_last2month_amount;
                                    @endphp
                                    {{ number_format($qRO_last2month_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $total_amount = 0;
                                        $qRO_last3month_amount = 0;
                                        $qRO_last3month = \App\Models\Tx_receipt_order::whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('DATE_FORMAT(receipt_date, "%Y-%m")=\''.date_format($last_3month,"Y-m").'\'')
                                        ->where([
                                            'supplier_id'=>$ro->supplier_id,
                                            'branch_id'=>$branch->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        foreach ($qRO_last3month as $ro_tm) {
                                            $total_amount = $ro_tm->supplier_type_id==10?$ro_tm->total_after_vat_rp:$ro_tm->total_after_vat;
                                            $qRO_last3month_amount += $total_amount;
                                        }
                                        $q_tx_pr_last_3month = \App\Models\Tx_purchase_retur::whereRaw('DATE_FORMAT(purchase_retur_date, "%Y-%m")=\''.date_format($last_3month,"Y-m").'\'')
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->where('branch_id', '=', $branch->id)
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->sum('total_after_vat');

                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        // ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($last_3month,"Y-m").'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        }

                                        if (($qRO_last3month_amount - $q_tx_pr_last_3month)<=0){
                                            $qRO_last3month_amount = $qRO_last3month_amount - $q_tx_pr_last_3month;
                                        }else{
                                            if (($qRO_last3month_amount - $q_tx_pr_last_3month - $sumPV)<=0){
                                                $qRO_last3month_amount = 0;
                                            }else{
                                                $qRO_last3month_amount = $qRO_last3month_amount - $q_tx_pr_last_3month - $sumPV;
                                            }
                                        }
                                        // $qRO_last3month_amount = $qRO_last3month_amount - $q_tx_pr_last_3month;
                                        $totalLast3MonthAmount+=$qRO_last3month_amount;
                                    @endphp
                                    {{ number_format($qRO_last3month_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    @php
                                        $total_amount = 0;
                                        $qRO_lastmore3month_amount = 0;
                                        $qRO_lastmore3month = \App\Models\Tx_receipt_order::whereRaw('receipt_no NOT LIKE \'%Draft%\'')
                                        ->whereRaw('(receipt_date BETWEEN \''.date_format($last_3month,"Y").'-01-01 0:0:0\' AND \''.date_format($last_3month,"Y-m").'-01 0:0:0\')')
                                        ->where([
                                            'supplier_id'=>$ro->supplier_id,
                                            'branch_id'=>$branch->id,
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                        foreach ($qRO_lastmore3month as $ro_tm) {
                                            $total_amount = $ro_tm->supplier_type_id==10?$ro_tm->total_after_vat_rp:$ro_tm->total_after_vat;
                                            $qRO_lastmore3month_amount += $total_amount;
                                        }

                                        $q_tx_pr_last_more3month = \App\Models\Tx_purchase_retur::whereRaw('purchase_retur_date<\''.date_format($last_3month,"Y-m").'-01\'')
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->where('branch_id', '=', $branch->id)
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->sum('total_after_vat');

                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        // ->whereRaw('payment_date<\''.date_format($last_3month,"Y-m").'-01\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        }

                                        if (($qRO_lastmore3month_amount - $q_tx_pr_last_more3month)<=0){
                                            $qRO_lastmore3month_amount = $qRO_lastmore3month_amount - $q_tx_pr_last_more3month;
                                        }else{
                                            if (($qRO_lastmore3month_amount - $q_tx_pr_last_more3month - $sumPV)<=0){
                                                $qRO_lastmore3month_amount = 0;
                                            }else{
                                                $qRO_lastmore3month_amount = $qRO_lastmore3month_amount - $q_tx_pr_last_more3month - $sumPV;
                                            }
                                        }
                                        // $qRO_lastmore3month_amount = $qRO_lastmore3month_amount - $q_tx_pr_last_more3month;
                                        $totalMore3MonthAmount+=$qRO_lastmore3month_amount;
                                    @endphp
                                    {{ number_format($qRO_lastmore3month_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;">
                                    {{-- total --}}
                                    @php
                                        $qRO_thisyear_amount = 0;
                                        $qRO_thisyear_amount+=($qRO_thismonth_amount+$qRO_lastmonth_amount+$qRO_last2month_amount+$qRO_last3month_amount+$qRO_lastmore3month_amount);
                                    @endphp
                                    {{ number_format($qRO_thisyear_amount,0,'.','') }}
                                </td>
                                <td style="text-align: right;border-right:1px solid black;">
                                    @php
                                        // payment
                                        $sumPV = 0;
                                        $qSumPV = \App\Models\Tx_payment_voucher::selectRaw('SUM(payment_total_after_vat+admin_bank+biaya_kirim+biaya_asuransi-diskon_pembelian) as payment_total_after_vat')
                                        ->whereIn('id', function($q) use($ro, $branch){
                                            $q->select('payment_voucher_id')
                                            ->from('tx_payment_voucher_invoices')
                                            ->whereIn('receipt_order_id', function ($q1) use($ro, $branch){
                                                $q1->select('id')
                                                ->from('tx_receipt_orders')
                                                ->where([
                                                    'supplier_id'=>$ro->supplier_id,
                                                    'branch_id'=>$branch->id,
                                                    'is_draft'=>'N',
                                                    'active'=>'Y',
                                                ]);
                                            })
                                            ->where('active', '=', 'Y');
                                        })
                                        ->where('supplier_id', '=', $ro->supplier_id)
                                        ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.date_format($this_month,"Y-m").'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('is_draft', '=', 'N')
                                        ->where('active', '=', 'Y')
                                        ->groupBy('supplier_id')
                                        ->first();
                                        if ($qSumPV){
                                            $sumPV = $qSumPV->payment_total_after_vat;
                                        }

                                        $totalPaymentAmount+=$sumPV;
                                    @endphp
                                    {{ number_format($sumPV,0,'.','') }}
                                </td>
                                {{-- <td style="text-align: right;border-right:1px solid black;">
                                    @php
                                        $totalEndBalanceAmount+=($qRO_thisyear_amount-$sumPV);
                                    @endphp
                                    {{ number_format(($qRO_thisyear_amount-$sumPV),0,'.','') }}
                                </td> --}}
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
                            {{-- <td>&nbsp;</td> --}}
                            <td style="border-right:1px solid black;">&nbsp;</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalThisMonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLastMonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLast2MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalLast3MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totalMore3MonthAmount,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">
                            {{ number_format($totalThisMonthAmount+
                                $totalLastMonthAmount+
                                $totalLast2MonthAmount+
                                $totalLast3MonthAmount+
                                $totalMore3MonthAmount,0,'.','') }}
                        </td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">{{ number_format($totalPaymentAmount,0,'.','') }}</td>
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
