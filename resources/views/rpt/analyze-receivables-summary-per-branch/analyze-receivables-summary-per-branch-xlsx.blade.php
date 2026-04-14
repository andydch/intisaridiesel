<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SummAnlsPiutangPerCabang</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    // keterangan
                    // secara hitungan harus disamakan dg report sales per customer per year
                    // notif dari pak sulian di meeting tgl 6 Apr 2026 jam 12 hingga 21

                    $dateNow = now();
                    $date = now();
                    $month = date_format($date,"m");
                    $year = date_format($date,"Y");
                    $totCols = 8;
                    $monthNm = '';

                    $thismonth = date_format($date,"m");
                    $thismonthYear = date_format($date,"Y");
                    date_add($date, date_interval_create_from_date_string("-1 months"));
                    $next1month = date_format($date,"m");
                    $next1monthYear = date_format($date,"Y");
                    date_add($date, date_interval_create_from_date_string("-1 months"));
                    $next2month = date_format($date,"m");
                    $next2monthYear = date_format($date,"Y");
                    date_add($date, date_interval_create_from_date_string("-1 months"));
                    $next3month = date_format($date,"m");
                    $next3monthYear = date_format($date,"Y");
                @endphp
                <thead>
                    <tr>
                        <th colspan="{{ $totCols }}">{{ $company->name }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}">Summary Analisa Piutang Per Cabang</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">{{ date_format($dateNow,"d/m/Y") }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NAMA CUSTOMER</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES - 1</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES - 2</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES - 3</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES > 3</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PAYMENT</th>
                        {{-- <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALDO</th> --}}
                    </tr>
                    @php
                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();

                        $totGrandThisMonth = 0;
                        $totGrandLastMonth = 0;
                        $totGrandLast2Month = 0;
                        $totGrandLast3Month = 0;
                        $totGrandLastThan3Month = 0;
                        $totGrandAll = 0;
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td colspan="{{ $totCols-1 }}" style="font-weight: bold;border-left:1px solid black;">{{ strtoupper($branch->name) }}</td>
                            <td style="border-left:1px solid black;border-right:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $qCusts = \App\Models\Mst_customer::leftJoin('userdetails as usr_d','mst_customers.salesman_id','=','usr_d.user_id')
                            ->leftJoin('mst_globals as ett_type','mst_customers.entity_type_id','=','ett_type.id')
                            ->select(
                                'mst_customers.id as cust_id',
                            )
                            ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mst_customers.customer_unique_code,\' - \',mst_customers.name),
                                CONCAT(mst_customers.customer_unique_code,\' - \',ett_type.title_ind,\' \',mst_customers.name)) as cust_name')
                            // ->whereIn('mst_customers.customer_unique_code', ['MIL02', 'CSH05'])
                            ->where([
                                'mst_customers.active'=>'Y',
                                'usr_d.branch_id'=>$branch->id,
                            ])
                            ->where(function($q){
                                $q->whereIn('mst_customers.id', function($q){
                                    $q->select('customer_id')
                                    ->from('tx_sales_orders')
                                    ->whereRaw('sales_order_no NOT LIKE \'%Draft%\'')
                                    ->where('active', 'Y');
                                })
                                ->orWhereIn('mst_customers.id', function($q){
                                    $q->select('customer_id')
                                    ->from('tx_surat_jalans')
                                    ->whereRaw('surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->where('active', 'Y');
                                });
                            })
                            ->orderBy('mst_customers.name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qCusts as $cust)
                            @php
                                // this month
                                $qSumFaktur = \App\Models\Tx_sales_order::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_dop')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(sales_order_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where([
                                    'active'=>'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total_after_vat');

                                $qSumNotaPenjualan = \App\Models\Tx_surat_jalan::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(surat_jalan_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where([
                                    'active' => 'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total');

                                $sumReturPPN = \App\Models\Tx_nota_retur::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_after_vat');

                                $sumReturNonPPN = \App\Models\Tx_nota_retur_non_tax::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_price');

                                $sumPenerimaanCustomer = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');

                                $totThisMonth = ($qSumFaktur-$sumReturPPN)+($qSumNotaPenjualan-$sumReturNonPPN)-$sumPenerimaanCustomer;
                                $totGrandThisMonth += ($totThisMonth>0?$totThisMonth:0);
                                // this month

                                // last month
                                $qSumFaktur = \App\Models\Tx_sales_order::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_dop')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(sales_order_date, "%Y-%m")=\''.$next1monthYear.'-'.$next1month.'\'')
                                ->where([
                                    'active'=>'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total_after_vat');

                                $qSumNotaPenjualan = \App\Models\Tx_surat_jalan::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(surat_jalan_date, "%Y-%m")=\''.$next1monthYear.'-'.$next1month.'\'')
                                ->where([
                                    'active' => 'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total');

                                $sumReturPPN = \App\Models\Tx_nota_retur::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next1monthYear.'-'.$next1month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_after_vat');

                                $sumReturNonPPN = \App\Models\Tx_nota_retur_non_tax::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next1monthYear.'-'.$next1month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_price');

                                $sumPenerimaanCustomer = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$next1monthYear.'-'.$next1month.'\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');

                                $totLastMonth = ($qSumFaktur-$sumReturPPN)+($qSumNotaPenjualan-$sumReturNonPPN)-$sumPenerimaanCustomer;
                                $totGrandLastMonth += ($totLastMonth>0?$totLastMonth:0);
                                // last month

                                // last 2 month
                                $qSumFaktur = \App\Models\Tx_sales_order::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_dop')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(sales_order_date, "%Y-%m")=\''.$next2monthYear.'-'.$next2month.'\'')
                                ->where([
                                    'active'=>'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total_after_vat');

                                $qSumNotaPenjualan = \App\Models\Tx_surat_jalan::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(surat_jalan_date, "%Y-%m")=\''.$next2monthYear.'-'.$next2month.'\'')
                                ->where([
                                    'active' => 'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total');

                                $sumReturPPN = \App\Models\Tx_nota_retur::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next2monthYear.'-'.$next2month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_after_vat');

                                $sumReturNonPPN = \App\Models\Tx_nota_retur_non_tax::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next2monthYear.'-'.$next2month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_price');

                                $sumPenerimaanCustomer = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$next2monthYear.'-'.$next2month.'\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');

                                $totLast2Months = ($qSumFaktur-$sumReturPPN)+($qSumNotaPenjualan-$sumReturNonPPN)-$sumPenerimaanCustomer;
                                $totGrandLast2Month += ($totLast2Months>0?$totLast2Months:0);
                                // last 2 month

                                // last 3 month
                                $qSumFaktur = \App\Models\Tx_sales_order::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_dop')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(sales_order_date, "%Y-%m")=\''.$next3monthYear.'-'.$next3month.'\'')
                                ->where([
                                    'active'=>'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total_after_vat');

                                $qSumNotaPenjualan = \App\Models\Tx_surat_jalan::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('DATE_FORMAT(surat_jalan_date, "%Y-%m")=\''.$next3monthYear.'-'.$next3month.'\'')
                                ->where([
                                    'active' => 'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total');

                                $sumReturPPN = \App\Models\Tx_nota_retur::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next3monthYear.'-'.$next3month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_after_vat');

                                $sumReturNonPPN = \App\Models\Tx_nota_retur_non_tax::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('DATE_FORMAT(nota_retur_date, "%Y-%m")=\''.$next3monthYear.'-'.$next3month.'\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_price');

                                $sumPenerimaanCustomer = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$next3monthYear.'-'.$next3month.'\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');

                                $totLast3Months = ($qSumFaktur-$sumReturPPN)+($qSumNotaPenjualan-$sumReturNonPPN)-$sumPenerimaanCustomer;
                                $totGrandLast3Month += ($totLast3Months>0?$totLast3Months:0);
                                // last 3 month

                                // last more than 3 month
                                $qSumFaktur = \App\Models\Tx_sales_order::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_parts as tx_dop')
                                    ->leftJoin('tx_delivery_orders as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('sales_order_date<\''.$next3monthYear.'-'.$next3month.'-01\'')
                                ->where([
                                    'active'=>'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total_after_vat');

                                $qSumNotaPenjualan = \App\Models\Tx_surat_jalan::whereIn('id', function($q) {
                                    $q->select('tx_dop.sales_order_id')
                                    ->from('tx_delivery_order_non_tax_parts as tx_dop')
                                    ->leftJoin('tx_delivery_order_non_taxes as tx_do','tx_dop.delivery_order_id','=','tx_do.id')
                                    ->whereRaw('tx_do.delivery_order_no NOT LIKE \'%Draft%\'')
                                    ->where([
                                        'tx_dop.active'=>'Y',
                                        'tx_do.active'=>'Y',
                                    ]);
                                })
                                ->whereRaw('surat_jalan_date<\''.$next3monthYear.'-'.$next3month.'-01\'')
                                ->where([
                                    'active' => 'Y',
                                    'customer_id'=>$cust->cust_id,
                                ])
                                ->sum('total');

                                $sumReturPPN = \App\Models\Tx_nota_retur::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('nota_retur_date<\''.$next3monthYear.'-'.$next3month.'-01\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_after_vat');

                                $sumReturNonPPN = \App\Models\Tx_nota_retur_non_tax::whereRaw('nota_retur_no NOT LIKE \'%Draft%\'')
                                ->whereRaw('nota_retur_date<\''.$next3monthYear.'-'.$next3month.'-01\'')
                                ->where('customer_id','=',$cust->cust_id)
                                // ->where('branch_id','=',$branch->id)
                                ->whereRaw('approved_by IS NOT null')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->sum('total_price');

                                $sumPenerimaanCustomer = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('payment_date<\''.$next3monthYear.'-'.$next3month.'-01\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');

                                $totLastThan3Months = ($qSumFaktur-$sumReturPPN)+($qSumNotaPenjualan-$sumReturNonPPN)-$sumPenerimaanCustomer;
                                $totGrandLastThan3Month += ($totLastThan3Months>0?$totLastThan3Months:0);
                                // last more than 3 month

                                // total
                                $totalAll = ($totThisMonth>0?$totThisMonth:0)+
                                    ($totLastMonth>0?$totLastMonth:0)+
                                    ($totLast2Months>0?$totLast2Months:0)+
                                    ($totLast3Months>0?$totLast3Months:0)+
                                    ($totLastThan3Months>0?$totLastThan3Months:0);
                                $totGrandAll += $totalAll;
                                // total

                                // payment this month
                                $totPayment = \App\Models\Tx_payment_receipt::selectRaw('SUM(payment_total_after_vat + biaya_kirim + penerimaan_lainnya - diskon_pembelian - admin_bank) as grand_total')
                                ->whereRaw('payment_receipt_no IS NOT null')
                                // ->whereIn('id', function($q1) use($branch){
                                //     $q1->select('payment_receipt_id')
                                //     ->from('tx_payment_receipt_invoices')
                                //     ->where(function($q2) use($branch){
                                //         $q2->whereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('invoice_no')
                                //             ->from('tx_invoices')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         })
                                //         ->orWhereIn('invoice_no', function($q3) use($branch){
                                //             $q3->select('kwitansi_no')
                                //             ->from('tx_kwitansis')
                                //             ->where([
                                //                 'branch_id'=>$branch->id,
                                //                 'active'=>'Y',
                                //             ]);
                                //         });
                                //     })
                                //     ->where('active','=','Y');
                                // })
                                ->where('customer_id','=',$cust->cust_id)
                                ->whereRaw('DATE_FORMAT(payment_date, "%Y-%m")=\''.$thismonthYear.'-'.$thismonth.'\'')
                                ->where('is_draft','=','N')
                                ->where('active','=','Y')
                                ->value('grand_total');
                                
                                // payment this month
                            @endphp
                            @if ($totalAll>0 || $totPayment>0)                                    
                                <tr>
                                    <td style="border-left:1px solid black;">{{ $cust->cust_name }}</td>
                                    <td>{{ $totThisMonth>0?number_format($totThisMonth,0,'.',''):0 }}</td>
                                    <td>{{ $totLastMonth>0?number_format($totLastMonth,0,'.',''):0 }}</td>
                                    <td>{{ $totLast2Months>0?number_format($totLast2Months,0,'.',''):0 }}</td>
                                    <td>{{ $totLast3Months>0?number_format($totLast3Months,0,'.',''):0 }}</td>
                                    <td>{{ $totLastThan3Months>0?number_format($totLastThan3Months,0,'.',''):0 }}</td>
                                    <td>{{ $totalAll>0?number_format($totalAll,0,'.',''):0 }}</td>
                                    <td style="border-left: 1px solid black;border-right: 1px solid black;">{{ number_format($totPayment,0,'.','') }}</td>
                                    {{-- <td style="border-left: 1px solid black;border-right: 1px solid black;">{{ number_format($totalAll-$totPayment,0,'.','') }}</td> --}}
                                </tr>
                            @endif
                        @endforeach
                        <tr>
                            <td style="border-left: 1px solid black;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td style="border-left: 1px solid black;border-right: 1px solid black;"></td>
                            {{-- <td></td> --}}
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;text-align: center;font-weight: 700;">TOTAL</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandThisMonth,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandLastMonth,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandLast2Month,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandLast3Month,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandLastThan3Month,0,'.','') }}</td>
                        <td style="border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;">{{ number_format($totGrandAll,0,'.','') }}</td>
                        <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;text-align: right;font-weight: 700;border-right: 1px solid black;"></td>
                        {{-- <td style="border-left: 1px solid black;border-top: 1px solid black;border-bottom: 1px solid black;border-right: 1px solid black;"></td> --}}
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
