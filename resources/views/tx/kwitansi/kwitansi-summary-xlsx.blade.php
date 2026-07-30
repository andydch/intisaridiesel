<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>Summary</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date, "m");
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
                        <th colspan="{{ $totCols }}">BILLING PROCESS</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE FK:&nbsp;{{ $start_date.' s/d '.$end_date }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Nama Customer</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Bank Account</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">KWI NO</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PLAN DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">CREATE DATE</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALES</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">STATUS</th>
                    </tr>
                    @php
                        $total = 0;
                        $dt_s = explode("-", $start_date);
                        $dt_e = explode("-", $end_date);

                        $userLogin = \App\Models\Userdetail::where('user_id', '=', Auth::user()->id)
                        ->first();

                        $qKwitansis = \App\Models\Tx_kwitansi::leftJoin('userdetails AS usr','tx_kwitansis.created_by','=','usr.user_id')
                        ->leftJoin('mst_customers','tx_kwitansis.customer_id','=','mst_customers.id')
                        ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
                        ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
                        ->leftJoin('mst_coas AS coa','tx_kwitansis.payment_to_id', '=', 'coa.id')
                        ->select(
                            'tx_kwitansis.id as tx_id',
                            'tx_kwitansis.kwitansi_no',
                            DB::raw('DATE_FORMAT(tx_kwitansis.kwitansi_date, "%d/%m/%Y") as plan_date'),
                            'tx_kwitansis.np_total',
                            'tx_kwitansis.approved_by',
                            'tx_kwitansis.canceled_by',
                            'tx_kwitansis.active as kwi_active',
                            'tx_kwitansis.created_by as createdby',
                            DB::raw('DATE_FORMAT(DATE_ADD(tx_kwitansis.created_at, INTERVAL '.env('WAKTU_ID').' HOUR), "%d/%m/%Y") as create_date'),
                            'usr.initial',
                            'usr.is_director',
                            'usr.is_branch_head',
                            'mst_customers.name as cust_name',
                            'mst_customers.customer_unique_code',
                            'usr_sales.initial as sales_initial',
                            'ety_type.title_ind as ety_type_name',
                            'coa.coa_name',
                        )
                        ->addSelect([
                            'totRetur' => \App\Models\Tx_nota_retur_non_tax::selectRaw('SUM(total_price)')
                                ->whereIn('id', function($q) {
                                    $q->select('nota_retur_id')
                                    ->from('tx_nota_retur_part_non_taxes')
                                    ->whereIn('surat_jalan_part_id', function($q1) {
                                        $q1->select('sales_order_part_id')
                                        ->from('tx_delivery_order_non_tax_parts')
                                        ->whereIn('delivery_order_id', function($q2) {
                                            $q2->select('kwitansi_id')
                                            ->from('tx_kwitansi_details')
                                            ->whereColumn('kwitansi_id', 'tx_kwitansis.id')
                                            ->where('active', 'Y');
                                        })
                                        ->where('active', 'Y');
                                    })
                                    ->where('active', 'Y');
                                })
                                ->whereRaw('approved_by IS NOT NULL')
                                ->where('active', 'Y')
                        ])
                        ->whereIn('tx_kwitansis.id', function($q) use($dt_s, $dt_e) {
                            $q->select('kwitansi_id')
                            ->from('tx_kwitansi_details')
                            ->whereIn('np_id', function($q1) use($dt_s, $dt_e) {
                                $q1->select('id')
                                ->from('tx_delivery_order_non_taxes')
                                ->whereRaw('delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' AND delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where('active', '=', 'Y');
                            })
                            ->where('active', '=', 'Y');
                        })
                        ->when($branch_id<>'', function($q) use($branch_id){
                            $q->where('tx_kwitansis.branch_id', $branch_id);
                        })
                        ->when($userLogin->is_director!='Y' 
                            && Auth::user()->email!='ekadessyarfianti@gmail.com' 
                            && Auth::user()->id!=1 
                            && Auth::user()->id!=16, 
                            function($q) use ($userLogin) {
                                $q->where('usr.branch_id','=', $userLogin->branch_id);
                            }
                        )
                        ->orderBy('mst_customers.name', 'asc')
                        ->orderBy('tx_kwitansis.is_draft', 'DESC')
                        ->orderBy('tx_kwitansis.kwitansi_no', 'DESC')
                        ->get();

                        $custNm = '';
                        $custNmTmp = '';
                    @endphp
                    @foreach ($qKwitansis as $qKwi)
                        @php
                            $custNm = $qKwi->customer_unique_code.' - '.$qKwi->ety_type_name.' '.$qKwi->cust_name;
                        @endphp
                        <tr>
                            <td style="text-align: left;">{{ $custNm<>$custNmTmp?$custNm:'' }}</td>
                            <td style="text-align: center;">{{ $qKwi->coa_name }}</td>
                            <td style="font-weight:bold;border-left:1px solid black;text-align: center;">{{ $qKwi->kwitansi_no }}</td>
                            <td style="text-align: center;">{{ $qKwi->plan_date }}</td>
                            @php
                                $total += ($qKwi->np_total-$qKwi->totRetur);
                            @endphp 
                            <td style="text-align: right;">{{ number_format($qKwi->np_total-$qKwi->totRetur,0,".","") }}</td>
                            <td style="text-align: center;">{{ $qKwi->create_date }}</td>
                            <td style="text-align: center;">{{ $qKwi->sales_initial }}</td>
                            @php
                                if ($qKwi->kwi_active=='Y' && !str_contains($qKwi->kwitansi_no, 'Draft') && is_null($qKwi->approved_by) && is_null($qKwi->canceled_by)){
                                    $status = 'Created';

                                    // cek status di penerimaan customer
                                    $qPyReceipt = \App\Models\Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr', 'tx_payment_receipt_invoices.payment_receipt_id', '=', 'tx_pr.id')
                                    ->selectRaw('(CASE 
                                        WHEN tx_payment_receipt_invoices.is_full_payment="Y" THEN "Paid"
                                        WHEN tx_payment_receipt_invoices.is_full_payment="N" THEN "Partial"
                                        ELSE "Created"
                                        END) AS payment_status')
                                    ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                                    ->where([
                                        'tx_payment_receipt_invoices.invoice_no' => $qKwi->kwitansi_no,
                                        'tx_payment_receipt_invoices.active' => 'Y',
                                        'tx_pr.active' => 'Y',
                                    ])
                                    ->orderBy('tx_pr.id','DESC')
                                    ->first();
                                    if ($qPyReceipt){
                                        $status = $qPyReceipt->payment_status;
                                    }
                                }
                                if ($qKwi->kwi_active=='Y' && str_contains($qKwi->kwitansi_no, 'Draft')){
                                    $status = 'Draft';
                                }
                                if ($qKwi->kwi_active=='N'){
                                    $status = 'Canceled';
                                }
                            @endphp
                            <td style="text-align: center;border-right:1px solid black;">{{ $status }}</td>
                        </tr>
                        @php
                            $custNmTmp = $qKwi->customer_unique_code.' - '.$qKwi->ety_type_name.' '.$qKwi->cust_name;
                        @endphp
                    @endforeach 
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;border-left:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;font-weight:blod;">TOTAL</td>
                        <td style="border-bottom:1px solid black;font-weight:blod;">{{ number_format($total,0,".","") }}</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
