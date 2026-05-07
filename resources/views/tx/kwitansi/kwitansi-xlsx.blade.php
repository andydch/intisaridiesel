<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>ProsesTagihan</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
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
                        <th colspan="{{ $totCols }}">PROSES TAGIHAN</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $start_date.' s/d '.$end_date }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">KW No</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Create Date</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Plan Date</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NP Date</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Customer</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Sales</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Status</th>
                    </tr>
                    @php
                        $dt_s = explode("-",$start_date);
                        $dt_e = explode("-",$end_date);

                        $userLogin = \App\Models\Userdetail::where('user_id','=',Auth::user()->id)
                        ->first();

                        $qKwitansis = \App\Models\Tx_kwitansi::leftJoin('userdetails AS usr','tx_kwitansis.created_by','=','usr.user_id')
                        ->leftJoin('mst_customers','tx_kwitansis.customer_id','=','mst_customers.id')
                        ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
                        ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
                        ->select(
                            'tx_kwitansis.id as tx_id',
                            'tx_kwitansis.kwitansi_no',
                            DB::raw('DATE_FORMAT(tx_kwitansis.kwitansi_date, "%d/%m/%Y") as plan_date'),
                            'tx_kwitansis.np_total',
                            'tx_kwitansis.approved_by',
                            'tx_kwitansis.canceled_by',
                            'tx_kwitansis.active as kw_active',
                            'tx_kwitansis.created_by as createdby',
                            DB::raw('DATE_FORMAT(tx_kwitansis.created_at, "%d/%m/%Y") as create_date'),
                            'usr.initial',
                            'usr.is_director',
                            'usr.is_branch_head',
                            'mst_customers.name as cust_name',
                            'mst_customers.customer_unique_code',
                            'usr_sales.initial as sales_initial',
                            'ety_type.title_ind as ety_type_name',
                            DB::raw('(SELECT DATE_FORMAT(tx_delivery_order_non_taxes.delivery_order_date, "%d/%m/%Y") 
                                FROM tx_delivery_order_non_taxes 
                                WHERE tx_delivery_order_non_taxes.id IN (SELECT np_id FROM tx_kwitansi_details WHERE kwitansi_id = tx_kwitansis.id) 
                                LIMIT 1) AS np_date')
                        )
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
                        ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, 
                            function($q) use ($userLogin) {
                            $q->where('usr.branch_id','=', $userLogin->branch_id);
                        })
                        // ->whereRaw('DATE_ADD(tx_kwitansis.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR)>\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].' 0:0:0\' 
                        //     AND DATE_ADD(tx_kwitansis.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR)<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].' 23:59:59\'')
                        ->orderBy('tx_kwitansis.is_draft', 'DESC')
                        ->orderBy('tx_kwitansis.kwitansi_no', 'DESC')
                        ->get();
                    @endphp
                    @foreach ($qKwitansis as $qKwi)
                        <tr>
                            <td style="font-weight:bold;border-left:1px solid black;">{{ $qKwi->kwitansi_no }}</td>
                            <td style="text-align: center;">{{ $qKwi->create_date }}</td>
                            <td style="text-align: center;">{{ $qKwi->plan_date }}</td>
                            <td style="text-align: center;">{{ $qKwi->np_date }}</td>
                            <td style="text-align: left;">{{ $qKwi->customer_unique_code.' - '.$qKwi->ety_type_name.' '.$qKwi->cust_name }}</td>
                            @php
                                $totRetur = \App\Models\Tx_nota_retur_non_tax::whereRaw('approved_by IS NOT NULL')
                                ->where('active', '=', 'Y')
                                ->whereIn('id', function($q) use($qKwi){
                                    $q->select('nota_retur_id')
                                    ->from('tx_nota_retur_part_non_taxes')
                                    ->whereIn('surat_jalan_part_id', function($q1) use($qKwi){
                                        $q1->select('sales_order_part_id')
                                        ->from('tx_delivery_order_non_tax_parts')
                                        ->whereIn('delivery_order_id', function($q2) use($qKwi){
                                            $q2->select('np_id')
                                            ->from('tx_kwitansi_details')
                                            ->where([
                                                'kwitansi_id' => $qKwi->tx_id,
                                            ]);
                                        });
                                    });
                                })
                                ->sum('total_price');
                            @endphp 
                            <td style="text-align: right;">
                                {!! number_format($qKwi->np_total,0,".","").($totRetur>0?
                                    '<br/><span style="color:red;">('.number_format($totRetur,0,".","").')</span>':'') !!}
                            </td>
                            <td style="text-align: center;">{{ $qKwi->sales_initial }}</td>
                            @php
                                if ($qKwi->kw_active=='Y' && strpos($qKwi->kwitansi_no,'Draft')==0 && is_null($qKwi->approved_by) && is_null($qKwi->canceled_by)){
                                    $links = 'Created';

                                    // cek status di penerimaan customer
                                    $qPyReceipt = \App\Models\Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr','tx_payment_receipt_invoices.payment_receipt_id','=','tx_pr.id')
                                    ->select(
                                        'tx_payment_receipt_invoices.is_full_payment',
                                    )
                                    ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                                    ->where([
                                        'tx_payment_receipt_invoices.invoice_no'=>$qKwi->kwitansi_no,
                                        'tx_payment_receipt_invoices.active'=>'Y',
                                        'tx_pr.active'=>'Y',
                                    ])
                                    ->orderBy('tx_pr.id','DESC')
                                    ->first();
                                    if ($qPyReceipt){
                                        if ($qPyReceipt->is_full_payment=='Y'){
                                            $links = 'Paid';
                                        }else{
                                            if ($qPyReceipt->is_full_payment=='N'){
                                                $links = 'Partial';
                                            }
                                        }
                                    }
                                }
                                if ($qKwi->kw_active=='Y' && strpos($qKwi->kwitansi_no,'Draft')>0){
                                    $links = 'Draft';
                                }
                                if ($qKwi->kw_active=='N'){
                                    $links = 'Canceled';
                                }
                            @endphp
                            <td style="text-align: center;border-right:1px solid black;">{{ $links }}</td>
                        </tr>
                    @endforeach 
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;">&nbsp;</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
