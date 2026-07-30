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
                        <th colspan="{{ $totCols }}">SUMMARY BILLING PROCESS</th>
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
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">INV NO</th>
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

                        $qInvoices = \App\Models\Tx_invoice::leftJoin('userdetails AS usr','tx_invoices.created_by','=','usr.user_id')
                        ->leftJoin('mst_customers','tx_invoices.customer_id','=','mst_customers.id')
                        ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
                        ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
                        ->leftJoin('mst_coas AS coa','tx_invoices.payment_to_id', '=', 'coa.id')
                        ->select(
                            'tx_invoices.id as tx_id',
                            'tx_invoices.invoice_no',
                            'tx_invoices.tax_invoice_no',
                            DB::raw('DATE_FORMAT(tx_invoices.invoice_date, "%d/%m/%Y") as plan_date'),
                            'tx_invoices.do_total',
                            'tx_invoices.do_grandtotal_vat',
                            'tx_invoices.approved_by',
                            'tx_invoices.canceled_by',
                            'tx_invoices.active as inv_active',
                            'tx_invoices.created_by as createdby',
                            DB::raw('DATE_FORMAT(DATE_ADD(tx_invoices.created_at, INTERVAL '.env('WAKTU_ID').' HOUR), "%d/%m/%Y") as create_date'),
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
                            'totRetur' => \App\Models\Tx_nota_retur::selectRaw('SUM(total_after_vat)')
                                ->whereIn('id', function($q) {
                                    $q->select('nota_retur_id')
                                    ->from('tx_nota_retur_parts')
                                    ->whereIn('sales_order_part_id', function($q1) {
                                        $q1->select('sales_order_part_id')
                                        ->from('tx_delivery_order_parts')
                                        ->whereIn('delivery_order_id', function($q2) {
                                            $q2->select('fk_id')
                                            ->from('tx_invoice_details')
                                            ->whereColumn('invoice_id', 'tx_invoices.id')
                                            ->where('active', 'Y');
                                        })
                                        ->where('active', 'Y');
                                    })
                                    ->where('active', 'Y');
                                })
                                ->whereRaw('approved_by IS NOT NULL')
                                ->where('active', 'Y')
                        ])
                        ->whereIn('tx_invoices.id', function($q) use($dt_s, $dt_e) {
                            $q->select('invoice_id')
                            ->from('tx_invoice_details')
                            ->whereIn('fk_id', function($q1) use($dt_s, $dt_e) {
                                $q1->select('id')
                                ->from('tx_delivery_orders')
                                ->whereRaw('delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\' AND delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                ->where('active', '=', 'Y');
                            })
                            ->where('active', '=', 'Y');
                        })
                        ->when($branch_id<>'', function($q) use($branch_id){
                            $q->where('tx_invoices.branch_id', $branch_id);
                        })
                        ->when($userLogin->is_director!='Y' 
                            && Auth::user()->email!='ekadessyarfianti@gmail.com' 
                            && Auth::user()->id!=1 
                            && Auth::user()->id!=24, 
                            function($q) use ($userLogin) {
                                $q->where('usr.branch_id','=', $userLogin->branch_id);
                            }
                        )
                        ->orderBy('tx_invoices.is_draft', 'DESC')
                        ->orderBy('tx_invoices.invoice_no', 'DESC')
                        ->get();
                    @endphp
                    @foreach ($qInvoices as $qInv)
                        <tr>
                            <td style="text-align: left;">{{ $qInv->customer_unique_code.' - '.$qInv->ety_type_name.' '.$qInv->cust_name }}</td>
                            <td style="text-align: center;">{{ $qInv->coa_name }}</td>
                            <td style="font-weight:bold;border-left:1px solid black;text-align: center;">{{ $qInv->invoice_no }}</td>
                            <td style="text-align: center;">{{ $qInv->plan_date }}</td>
                            @php
                                $total += ($qInv->do_grandtotal_vat-$qInv->totRetur);
                            @endphp 
                            <td style="text-align: right;">{{ number_format($qInv->do_grandtotal_vat-$qInv->totRetur,0,".","") }}</td>
                            <td style="text-align: center;">{{ $qInv->create_date }}</td>
                            <td style="text-align: center;">{{ $qInv->sales_initial }}</td>
                            @php
                                if ($qInv->inv_active=='Y' && !str_contains($qInv->invoice_no, 'Draft') && is_null($qInv->approved_by) && is_null($qInv->canceled_by)){
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
                                        'tx_payment_receipt_invoices.invoice_no' => $qInv->invoice_no,
                                        'tx_payment_receipt_invoices.active' => 'Y',
                                        'tx_pr.active' => 'Y',
                                    ])
                                    ->orderBy('tx_pr.id','DESC')
                                    ->first();
                                    if ($qPyReceipt){
                                        $status = $qPyReceipt->payment_status;
                                    }
                                }
                                if ($qInv->inv_active=='Y' && str_contains($qInv->invoice_no, 'Draft')){
                                    $status = 'Draft';
                                }
                                if ($qInv->inv_active=='N'){
                                    $status = 'Canceled';
                                }
                            @endphp
                            <td style="text-align: center;border-right:1px solid black;">{{ $status }}</td>
                        </tr>
                    @endforeach 
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-bottom:1px solid black;font-weight:700;">TOTAL</td>
                        <td style="border-bottom:1px solid black;font-weight:700;">{{ number_format($total,0,".","") }}</td>
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
