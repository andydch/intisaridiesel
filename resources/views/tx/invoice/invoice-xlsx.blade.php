<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>BillingProcess</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 7;
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
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $start_date.' s/d '.$end_date }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">INV No</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Create Date</th>
                        <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">Plan Date</th>
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

                        $qInvoices = \App\Models\Tx_invoice::leftJoin('userdetails AS usr','tx_invoices.created_by','=','usr.user_id')
                        ->leftJoin('mst_customers','tx_invoices.customer_id','=','mst_customers.id')
                        ->leftJoin('userdetails AS usr_sales','mst_customers.salesman_id','=','usr_sales.user_id')
                        ->leftJoin('mst_globals AS ety_type','mst_customers.entity_type_id','=','ety_type.id')
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
                            DB::raw('DATE_FORMAT(tx_invoices.created_at, "%d/%m/%Y") as create_date'),
                            'usr.initial',
                            'usr.is_director',
                            'usr.is_branch_head',
                            'mst_customers.name as cust_name',
                            'mst_customers.customer_unique_code',
                            'usr_sales.initial as sales_initial',
                            'ety_type.title_ind as ety_type_name',
                        )
                        ->when($userLogin->is_director!='Y' && Auth::user()->id!=1, 
                            function($q) use ($userLogin) {
                                $q->where('usr.branch_id','=', $userLogin->branch_id);
                            }
                        )
                        ->whereRaw('DATE_ADD(tx_invoices.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR)>\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].' 0:0:0\' 
                            AND DATE_ADD(tx_invoices.created_at, INTERVAL '.ENV("WAKTU_ID").' HOUR)<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].' 23:59:59\'')
                        ->orderBy('tx_invoices.is_draft', 'DESC')
                        ->orderBy('tx_invoices.invoice_no', 'DESC')
                        ->get();
                    @endphp
                    @foreach ($qInvoices as $qInv)
                        <tr>
                            <td style="font-weight:bold;border-left:1px solid black;">{{ $qInv->invoice_no }}</td>
                            <td style="text-align: center;">{{ $qInv->create_date }}</td>
                            <td style="text-align: center;">{{ $qInv->plan_date }}</td>
                            <td style="text-align: left;">{{ $qInv->customer_unique_code.' - '.$qInv->ety_type_name.' '.$qInv->cust_name }}</td>
                            @php
                                $totRetur = \App\Models\Tx_nota_retur::whereRaw('approved_by IS NOT NULL')
                                ->where('active', '=', 'Y')
                                ->whereIn('id', function($q) use($qInv){
                                    $q->select('nota_retur_id')
                                    ->from('tx_nota_retur_parts')
                                    ->whereIn('sales_order_part_id', function($q1) use($qInv){
                                        $q1->select('sales_order_part_id')
                                        ->from('tx_delivery_order_parts')
                                        ->whereIn('delivery_order_id', function($q2) use($qInv){
                                            $q2->select('fk_id')
                                            ->from('tx_invoice_details')
                                            ->where([
                                                'invoice_id' => $qInv->tx_id,
                                            ]);
                                        });
                                    });
                                })
                                ->sum('total_after_vat');
                            @endphp 
                            <td style="text-align: right;">
                                {!! number_format($qInv->do_grandtotal_vat,0,".",",").($totRetur>0?
                                    '<br/><span style="color:red;">('.number_format($totRetur,0,".",",").')</span>':'') !!}
                            </td>
                            <td style="text-align: center;">{{ $qInv->sales_initial }}</td>
                            @php
                                if ($qInv->inv_active=='Y' && strpos($qInv->invoice_no,'Draft')==0 && is_null($qInv->approved_by) && is_null($qInv->canceled_by)){
                                    $links = 'Created';

                                    // cek status di penerimaan customer
                                    $qPyReceipt = \App\Models\Tx_payment_receipt_invoice::leftJoin('tx_payment_receipts as tx_pr','tx_payment_receipt_invoices.payment_receipt_id','=','tx_pr.id')
                                    ->select(
                                        'tx_payment_receipt_invoices.is_full_payment',
                                    )
                                    ->whereRaw('tx_pr.payment_receipt_no IS NOT null')
                                    ->where([
                                        'tx_payment_receipt_invoices.invoice_no'=>$qInv->invoice_no,
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
                                if ($qInv->inv_active=='Y' && strpos($qInv->invoice_no,'Draft')>0){
                                    $links = 'Draft';
                                }
                                if ($qInv->inv_active=='N'){
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
                    </tr>
                </tfoot>
                
                
                
                {{-- <tbody>
                    @php
                        $grandtotalDPP = 0;
                        $grandtotalPPN = 0;
                        $grandtotalDPPplusPPN = 0;
                        $grandtotalAVG = 0;
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);

                        $branches = \App\Models\Mst_branch::where('active','=','Y')
                        ->when($branch_id!='0', function($q) use($branch_id) {
                            $q->where('id','=',$branch_id);
                        })
                        ->orderBy('name','ASC')
                        ->get();
                    @endphp
                    @foreach ($branches as $branch)
                        <tr>
                            <td style="font-weight: bold;">{{ $branch->name }}</td>
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
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">CUSTOMER</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">DATE</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO SO</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO FK/NR</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">DPP ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">PPN ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">TOTAL ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">AVG ({{ $qCurrency->string_val }})</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">GP (%)</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">NO DOC</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">EX FAKTUR</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                            <th style="text-align: center;font-weight:bold;border:1px solid black;background-color:#daeef3;">CREATED BY</th>
                        </tr>
                        @php
                            $totalDPP = 0;
                            $totalPPN = 0;
                            $totalDPPplusPPN = 0;
                            $totalAVG = 0;
                            $cust_name = '';

                            $qCust = \App\Models\Mst_customer::leftJoin('mst_globals as ett_type','mst_customers.entity_type_id','=','ett_type.id')
                            ->select(
                                'mst_customers.id as cust_id',
                                'mst_customers.name as name',
                            )
                            ->selectRaw('IF(ISNULL(ett_type.title_ind),
                                CONCAT(mst_customers.customer_unique_code,\' - \',mst_customers.name),
                                CONCAT(mst_customers.customer_unique_code,\' - \',ett_type.title_ind,\' \',mst_customers.name)) as cust_name')
                            ->when($customer_id!='0', function($q) use($customer_id) {
                                $q->where('mst_customers.id','=',$customer_id);
                            })
                            ->where([
                                'mst_customers.active'=>'Y',
                            ])
                            ->orderBy('mst_customers.name','ASC')
                            ->get();
                        @endphp
                        @foreach ($qCust as $qC)
                            @php
                                $isCustExist = false;
                                $totalDPPperCust = 0;
                                $totalPPNperCust = 0;
                                $totalDPPplusPPNperCust = 0;
                                $totalAVGperCust = 0;
                            @endphp

                            // with tax
                            @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='P' || $lokal_input=='' || $lokal_input=='x')
                                @php
                                    // sales order
                                    $qSO = \App\Models\Tx_sales_order::leftJoin('mst_customers as m_cust','tx_sales_orders.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr','m_cust.salesman_id','=','usr.user_id')
                                    ->select(
                                        'tx_sales_orders.id AS sales_order_id',
                                        'tx_sales_orders.sales_order_no',
                                        'tx_sales_orders.sales_order_date',
                                        'tx_sales_orders.total_before_vat',
                                        'tx_sales_orders.total_after_vat',
                                        'tx_sales_orders.customer_doc_no',
                                        'tx_sales_orders.vat_val',
                                        'tx_sales_orders.created_by',
                                        'tx_sales_orders.customer_id',
                                        'tx_sales_orders.updated_by as so_updated_by',
                                    )
                                    ->whereRaw('tx_sales_orders.sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'tx_sales_orders.customer_id'=>$qC->cust_id,
                                        'tx_sales_orders.need_approval'=>'N',
                                        'usr.branch_id'=>$branch->id,
                                        'tx_sales_orders.active'=>'Y',
                                    ])
                                    ->orderBy('tx_sales_orders.sales_order_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qSO as $q)
                                    @php
                                        $faktur_no = '';
                                        $faktur = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders AS txdo','tx_delivery_order_parts.delivery_order_id','=','txdo.id')
                                        ->select(
                                            'txdo.delivery_order_no',
                                        )
                                        ->where([
                                            'tx_delivery_order_parts.sales_order_id'=>$q->sales_order_id,
                                            'tx_delivery_order_parts.active'=>'Y',
                                            'txdo.active'=>'Y',
                                        ])
                                        ->first();
                                        if($faktur){
                                            $faktur_no = $faktur->delivery_order_no;
                                        }

                                        $totalDPP += $q->total_before_vat;
                                        $totalPPN += (($q->total_before_vat*$q->vat_val)/100);
                                        $totalDPPplusPPN += ($q->total_before_vat+(($q->total_before_vat*$q->vat_val)/100));

                                        $totalDPPperCust += $q->total_before_vat;
                                        $totalPPNperCust += ($q->total_after_vat-$q->total_before_vat);
                                        $totalDPPplusPPNperCust += $q->total_after_vat;
                                        $isCustExist = true;
                                    @endphp
                                    <tr>
                                        <td style="font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                        <td style="text-align: center;">{{ date_format(date_create($q->sales_order_date),"d/m/Y") }}</td>
                                        <td style="text-align: center;">{{ $q->sales_order_no }}</td>
                                        <td style="text-align: center;">{{ strpos($faktur_no,"Draft")>0?'':$faktur_no }}</td>
                                        <td style="text-align: right;">{{ number_format($q->total_before_vat,0,'.','') }}</td>
                                        <td style="text-align: right;">{{ number_format(($q->total_after_vat-$q->total_before_vat),0,'.','') }}</td>
                                        <td style="text-align: right;">{{ number_format($q->total_after_vat,0,'.','') }}</td>
                                        <td style="text-align: right;">
                                            @php
                                                $totAVG = 0;
                                                $qAVGlogdbg = '';
                                                $qFkPart = \App\Models\Tx_sales_order_part::where([
                                                    'order_id' => $q->sales_order_id,
                                                    'active' => 'Y',
                                                ])
                                                ->get();
                                            @endphp
                                            @foreach ($qFkPart as $qP)
                                                @php
                                                    $totAVG += ($qP->qty*$qP->last_avg_cost);
                                                @endphp
                                            @endforeach
                                            {{ number_format($totAVG,0,'.','') }}
                                            @php
                                                $totalAVGperCust += $totAVG;
                                                $totalAVG += $totAVG;
                                            @endphp
                                        </td>
                                        <td style="text-align: right;">{{ number_format((($q->total_before_vat!=0)?((($q->total_before_vat-$totAVG)/$q->total_before_vat)*100):0),0,'.','') }}%</td>
                                        <td style="text-align: center;">{{ $q->customer_doc_no }}</td>
                                        <td style="text-align: center;">&nbsp;</td>
                                        <td style="text-align: center;">{{ $q->customer->salesman01->initial }}</td>
                                        <td style="text-align: center;border-right:1px solid black;">{{ $q->createdBy->userDetail->initial }}</td>
                                    </tr>
                                    @php
                                        $cust_name = $qC->cust_name;

                                        // nota retur
                                        $qNR = \App\Models\Tx_nota_retur::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'nota_retur_date',
                                            'total_before_vat',
                                            'total_after_vat',
                                            'delivery_order_id',
                                            'customer_id',
                                            'created_by',
                                        )
                                        ->whereIn('id', function($qInv) use($q) {
                                            $qInv->select('tx_nr.nota_retur_id')
                                            ->from('tx_nota_retur_parts as tx_nr')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_nr.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                            ->where([
                                                'tx_sop.active'=>'Y',
                                                'tx_so.id'=>$q->sales_order_id,
                                                'tx_so.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\' AND approved_by IS NOT null')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'active'=>'Y',
                                        ])
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qNR as $qNRd)
                                        <tr>
                                            <td style="color: red;font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                            <td style="text-align: center;color: red;">{{ date_format(date_create($qNRd->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color: red;">{{ $q->sales_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ strpos($qNRd->nota_retur_no,"Draft")>0?'':$qNRd->nota_retur_no }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qNRd->total_before_vat,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format(($qNRd->total_after_vat-$qNRd->total_before_vat),0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qNRd->total_after_vat,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">
                                                @php
                                                    $qNRavg = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                                    ->select(
                                                        'tx_nota_retur_parts.qty_retur',
                                                        'tx_sop.last_avg_cost',
                                                    )
                                                    ->where([
                                                        'tx_nota_retur_parts.nota_retur_id'=>$qNRd->nr_id,
                                                        'tx_nota_retur_parts.active'=>'Y',
                                                    ])
                                                    ->get();
                                                    $totAVG = 0;
                                                @endphp
                                                @foreach ($qNRavg as $qAvg)
                                                    @php
                                                        $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                    @endphp
                                                @endforeach
                                                -{{ number_format($totAVG,0,'.','') }}
                                            </td>
                                            <td style="text-align: right;color: red;">{{ number_format(($qNRd->total_before_vat!=0)?((($qNRd->total_before_vat-$totAVG)/$qNRd->total_before_vat)*100):0,0,'.','') }}%</td>
                                            <td style="text-align: center;color: red;">{{ $q->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qNRd->delivery_order->delivery_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $q->customer->salesman01->initial }}</td>
                                            <td style="text-align: center;color: red;border-right:1px solid black;">{{ $q->createdBy->userDetail->initial }}</td>
                                        </tr>
                                        @php
                                            $totalDPP = $totalDPP-$qNRd->total_before_vat;
                                            $totalPPN = $totalPPN-($qNRd->total_after_vat-$qNRd->total_before_vat);
                                            $totalDPPplusPPN = $totalDPPplusPPN-$qNRd->total_after_vat;
                                            $totalAVG = $totalAVG-$totAVG;

                                            $totalDPPperCust = $totalDPPperCust-$qNRd->total_before_vat;
                                            $totalPPNperCust = $totalPPNperCust-($qNRd->total_after_vat-$qNRd->total_before_vat);
                                            $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-$qNRd->total_after_vat;
                                            $totalAVGperCust = $totalAVGperCust-$totAVG;
                                            $isCustExist = true;
                                        @endphp
                                    @endforeach

                                    @php
                                        $cust_name = $qC->cust_name;
                                    @endphp
                                @endforeach

                                @php
                                    // SO sebelum tanggal mulai yang dipilih tapi memiliki retur di antara tanggal yang dipilih
                                    $qSOoth = \App\Models\Tx_sales_order::leftJoin('mst_customers as m_cust','tx_sales_orders.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr','m_cust.salesman_id','=','usr.user_id')
                                    ->select(
                                        'tx_sales_orders.id AS sales_order_id',
                                        'tx_sales_orders.sales_order_no',
                                        'tx_sales_orders.sales_order_date',
                                        'tx_sales_orders.total_before_vat',
                                        'tx_sales_orders.total_after_vat',
                                        'tx_sales_orders.customer_doc_no',
                                        'tx_sales_orders.created_by',
                                        'tx_sales_orders.customer_id',
                                        'tx_sales_orders.updated_by as so_updated_by',
                                    )
                                    ->whereIn('tx_sales_orders.id', function($q) use($qC,$dt_s,$dt_e) {
                                        $q->select('tx_sop.order_id')
                                        ->from('tx_sales_order_parts as tx_sop')
                                        ->whereIn('tx_sop.id', function($q1) use($qC,$dt_s,$dt_e) {
                                            $q1->select('tx_nrp.sales_order_part_id')
                                            ->from('tx_nota_retur_parts as tx_nrp')
                                            ->leftJoin('tx_nota_returs as tx_nr','tx_nrp.nota_retur_id','=','tx_nr.id')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT null')
                                            ->where([
                                                'tx_nrp.active'=>'Y',
                                                'tx_nr.customer_id'=>$qC->cust_id,
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->where([
                                            'tx_sop.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_sales_orders.sales_order_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_sales_orders.sales_order_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->where([
                                        'tx_sales_orders.customer_id'=>$qC->cust_id,
                                        'tx_sales_orders.need_approval'=>'N',
                                        'usr.branch_id'=>$branch->id,
                                        'tx_sales_orders.active'=>'Y',
                                    ])
                                    ->orderBy('tx_sales_orders.sales_order_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qSOoth as $qSo)
                                    @php
                                        // nota retur
                                        $qNRoth = \App\Models\Tx_nota_retur::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'nota_retur_date',
                                            'total_before_vat',
                                            'total_after_vat',
                                            'delivery_order_id',
                                            'customer_id',
                                            'created_by',
                                        )
                                        ->whereIn('id', function($qInv) use($qSo) {
                                            $qInv->select('tx_nr.nota_retur_id')
                                            ->from('tx_nota_retur_parts as tx_nr')
                                            ->leftJoin('tx_sales_order_parts as tx_sop','tx_nr.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                            ->where([
                                                'tx_sop.active'=>'Y',
                                                'tx_so.id'=>$qSo->sales_order_id,
                                                'tx_so.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\' AND approved_by IS NOT null')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'active'=>'Y',
                                        ])
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qNRoth as $qN)
                                        <tr>
                                            <td style="font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                            <td style="text-align: center;color: red;">{{ date_format(date_create($qN->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color: red;">{{ $qSo->sales_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ strpos($qN->nota_retur_no,"Draft")>0?'':$qN->nota_retur_no }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qN->total_before_vat,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format(($qN->total_after_vat-$qN->total_before_vat),0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qN->total_after_vat,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">
                                                @php
                                                    $qNRavg = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                                    ->select(
                                                        'tx_nota_retur_parts.qty_retur',
                                                        'tx_sop.last_avg_cost',
                                                    )
                                                    ->where([
                                                        'tx_nota_retur_parts.nota_retur_id'=>$qN->nr_id,
                                                        'tx_nota_retur_parts.active'=>'Y',
                                                    ])
                                                    ->get();
                                                    $totAVG = 0;
                                                @endphp
                                                @foreach ($qNRavg as $qAvg)
                                                    @php
                                                        $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                    @endphp
                                                @endforeach
                                                -{{ number_format($totAVG,0,'.','') }}
                                            </td>
                                            <td style="text-align: right;color: red;">{{ number_format(($qN->total_before_vat!=0)?((($qN->total_before_vat-$totAVG)/$qN->total_before_vat)*100):0,0,'.','') }}%</td>
                                            <td style="text-align: center;color: red;">{{ $qSo->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qN->delivery_order->delivery_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qSo->customer->salesman01->initial }}</td>
                                            <td style="text-align: center;color: red;border-right:1px solid black;">{{ $qSo->createdBy->userDetail->initial }}</td>
                                        </tr>

                                        @php
                                            $totalDPP = $totalDPP-$qN->total_before_vat;
                                            $totalPPN = $totalPPN-($qN->total_after_vat-$qN->total_before_vat);
                                            $totalDPPplusPPN = $totalDPPplusPPN-$qN->total_after_vat;
                                            $totalAVG = $totalAVG-$totAVG;

                                            $totalDPPperCust = $totalDPPperCust-$qN->total_before_vat;
                                            $totalPPNperCust = $totalPPNperCust-($qN->total_after_vat-$qN->total_before_vat);
                                            $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-$qN->total_after_vat;
                                            $totalAVGperCust = $totalAVGperCust-$totAVG;
                                            $isCustExist = true;
                                        @endphp
                                    @endforeach

                                    @php
                                        $cust_name = $qC->cust_name;
                                    @endphp
                                @endforeach
                            @endif
                            // with tax

                            // non tax
                            @if (strtoupper($lokal_input)=='A' || strtoupper($lokal_input)=='N')
                                @php
                                    // surat jalan
                                    $qSJ = \App\Models\Tx_surat_jalan::leftJoin('mst_customers as m_cust','tx_surat_jalans.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr','m_cust.salesman_id','=','usr.user_id')
                                    ->select(
                                        'tx_surat_jalans.id AS surat_jalan_id',
                                        'tx_surat_jalans.surat_jalan_no',
                                        'tx_surat_jalans.surat_jalan_date',
                                        'tx_surat_jalans.total',
                                        'tx_surat_jalans.customer_doc_no',
                                        'tx_surat_jalans.customer_id',
                                        'tx_surat_jalans.created_by',
                                        'tx_surat_jalans.updated_by as sj_updatedby',
                                    )
                                    ->whereRaw('tx_surat_jalans.surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_surat_jalans.surat_jalan_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_surat_jalans.surat_jalan_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->where([
                                        'tx_surat_jalans.customer_id'=>$qC->cust_id,
                                        'tx_surat_jalans.need_approval'=>'N',
                                        'tx_surat_jalans.active'=>'Y',
                                        'usr.branch_id'=>$branch->id,
                                    ])
                                    ->orderBy('tx_surat_jalans.surat_jalan_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qSJ as $q)
                                    @php
                                        $totalDPP += $q->total;
                                        $totalDPPplusPPN += $q->total;
                                        $totalDPPperCust += $q->total;
                                        $totalDPPplusPPNperCust += $q->total;

                                        $np_no = '';
                                        $np = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_delivery_order_non_taxes AS txdo','tx_delivery_order_non_tax_parts.delivery_order_id','=','txdo.id')
                                        ->select('txdo.delivery_order_no')
                                        ->where([
                                            'tx_delivery_order_non_tax_parts.sales_order_id'=>$q->surat_jalan_id,
                                            'tx_delivery_order_non_tax_parts.active'=>'Y',
                                            'txdo.active'=>'Y',
                                        ])
                                        ->first();
                                        if($np){
                                            $np_no = $np->delivery_order_no;
                                        }
                                    @endphp
                                    <tr>
                                        <td style="font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                        <td style="text-align: center;">{{ date_format(date_create($q->surat_jalan_date),"d/m/Y") }}</td>
                                        <td style="text-align: center;">{{ $q->surat_jalan_no }}</td>
                                        <td style="text-align: center;">{{ strpos($np_no,"Draft")>0?'':$np_no }}</td>
                                        <td style="text-align: right;">{{ number_format($q->total,0,'.','') }}</td>
                                        <td style="text-align: right;">&nbsp;</td>
                                        <td style="text-align: right;">{{ number_format($q->total,0,'.','') }}</td>
                                        <td style="text-align: right;">
                                            @php
                                                $totAVG = 0;
                                                $qAVGlogdbg = '';
                                                $qFkPart = \App\Models\Tx_surat_jalan_part::where([
                                                    'surat_jalan_id' => $q->surat_jalan_id,
                                                    'active' => 'Y',
                                                ])
                                                ->get();
                                            @endphp
                                            @foreach ($qFkPart as $qP)
                                                @php
                                                    $totAVG += ($qP->qty*$qP->last_avg_cost);
                                                @endphp
                                            @endforeach
                                            {{ number_format($totAVG,0,'.','') }}
                                        </td>
                                        <td style="text-align: right;">{{ number_format(($q->total>0)?((($q->total-$totAVG)/$q->total)*100):0,0,'.','') }}%</td>
                                        <td style="text-align: center;">{{ $q->customer_doc_no }}</td>
                                        <td style="text-align: center;">&nbsp;</td>
                                        <td style="text-align: center;">{{ $q->customer->salesman01->initial }}</td>
                                        <td style="text-align: center;border-right:1px solid black;">{{ $q->createdBy->userDetail->initial }}</td>
                                    </tr>
                                    @php
                                        $totalAVGperCust += $totAVG;
                                        $totalAVG += $totAVG;
                                        $cust_name = $qC->cust_name;

                                        // nota retur non tax
                                        $qNR = \App\Models\Tx_nota_retur_non_tax::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'total_price',
                                            'delivery_order_id',
                                            'nota_retur_date',
                                        )
                                        ->whereIn('id', function($qInv) use($q) {
                                            $qInv->select('tx_nr.nota_retur_id')
                                            ->from('tx_nota_retur_part_non_taxes as tx_nr')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sop','tx_nr.surat_jalan_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_surat_jalans as tx_sj','tx_sop.surat_jalan_id','=','tx_sj.id')
                                            ->where([
                                                'tx_sop.active'=>'Y',
                                                'tx_sj.id'=>$q->surat_jalan_id,
                                                'tx_sj.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\' AND approved_by IS NOT null')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'active'=>'Y',
                                        ])
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qNR as $qNRd)
                                        <tr>
                                            <td style="color: red;font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                            <td style="text-align: center;color: red;">{{ date_format(date_create($qNRd->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color: red;">{{ $q->surat_jalan_no }}</td>
                                            <td style="text-align: center;color: red;">{{ strpos($qNRd->nota_retur_no,"Draft")>0?'':$qNRd->nota_retur_no }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qNRd->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">&nbsp;</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qNRd->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">
                                                @php
                                                    $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                                    ->select(
                                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                                        'tx_sjp.last_avg_cost',
                                                    )
                                                    ->where([
                                                        'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qNRd->nr_id,
                                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                    ])
                                                    ->get();
                                                    $totAVG = 0;
                                                @endphp
                                                @foreach ($qNRavg as $qAvg)
                                                    @php
                                                        $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                    @endphp
                                                @endforeach
                                                -{{ number_format($totAVG,0,'.','') }}
                                            </td>
                                            <td style="text-align: right;color: red;">{{ number_format(($qNRd->total_price!=0)?((($qNRd->total_price-$totAVG)/$qNRd->total_price)*100):0,0,'.','') }}%</td>
                                            <td style="text-align: center;color: red;">{{ $q->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qNRd->delivery_order->delivery_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $q->customer->salesman01->initial }}</td>
                                            <td style="text-align: center;color: red;border-right:1px solid black;">{{ $q->createdBy->userDetail->initial }}</td>
                                        </tr>
                                        @php
                                            $totalDPP = $totalDPP-$qNRd->total_price;
                                            $totalDPPplusPPN = $totalDPPplusPPN-$qNRd->total_price;
                                            $totalAVG = $totalAVG-$totAVG;

                                            $totalDPPperCust = $totalDPPperCust-$qNRd->total_price;
                                            $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-$qNRd->total_price;
                                            $totalAVGperCust = $totalAVGperCust-$totAVG;
                                            $isCustExist = true;
                                        @endphp
                                    @endforeach

                                    @php
                                        $cust_name = $qC->cust_name;
                                    @endphp
                                @endforeach

                                @php
                                    // SJ sebelum tanggal mulai yang dipilih tapi memiliki retur di antara tanggal yang dipilih
                                    $qSJoth = \App\Models\Tx_surat_jalan::leftJoin('mst_customers as m_cust','tx_surat_jalans.customer_id','=','m_cust.id')
                                    ->leftJoin('userdetails AS usr','m_cust.salesman_id','=','usr.user_id')
                                    ->select(
                                        'tx_surat_jalans.id AS surat_jalan_id',
                                        'tx_surat_jalans.surat_jalan_no',
                                        'tx_surat_jalans.surat_jalan_date',
                                        'tx_surat_jalans.total',
                                        'tx_surat_jalans.customer_doc_no',
                                        'tx_surat_jalans.customer_id',
                                        'tx_surat_jalans.created_by',
                                        'tx_surat_jalans.updated_by as sj_updatedby',
                                    )
                                    ->whereIn('tx_surat_jalans.id', function($q) use($qC,$dt_s,$dt_e) {
                                        $q->select('tx_sop.surat_jalan_id')
                                        ->from('tx_surat_jalan_parts as tx_sop')
                                        ->whereIn('tx_sop.id', function($q1) use($qC,$dt_s,$dt_e) {
                                            $q1->select('tx_nrp.surat_jalan_part_id')
                                            ->from('tx_nota_retur_part_non_taxes as tx_nrp')
                                            ->leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nrp.nota_retur_id','=','tx_nr.id')
                                            ->whereRaw('tx_nr.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nr.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->whereRaw('tx_nr.approved_by IS NOT null')
                                            ->where([
                                                'tx_nrp.active'=>'Y',
                                                'tx_nr.customer_id'=>$qC->cust_id,
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->where([
                                            'tx_sop.active'=>'Y',
                                        ]);
                                    })
                                    ->whereRaw('tx_surat_jalans.surat_jalan_no NOT LIKE \'%Draft%\'')
                                    ->whereRaw('tx_surat_jalans.surat_jalan_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->where([
                                        'tx_surat_jalans.customer_id'=>$qC->cust_id,
                                        'tx_surat_jalans.need_approval'=>'N',
                                        'tx_surat_jalans.active'=>'Y',
                                        'usr.branch_id'=>$branch->id,
                                    ])
                                    ->orderBy('tx_surat_jalans.surat_jalan_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($qSJoth as $qSJo)
                                    @php
                                        // nota retur non tax
                                        $qREoth = \App\Models\Tx_nota_retur_non_tax::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'total_price',
                                            'delivery_order_id',
                                            'nota_retur_date',
                                        )
                                        ->whereIn('id', function($qInv) use($qSJo) {
                                            $qInv->select('tx_nr.nota_retur_id')
                                            ->from('tx_nota_retur_part_non_taxes as tx_nr')
                                            ->leftJoin('tx_surat_jalan_parts as tx_sop','tx_nr.surat_jalan_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_surat_jalans as tx_sj','tx_sop.surat_jalan_id','=','tx_sj.id')
                                            ->where([
                                                'tx_sop.active'=>'Y',
                                                'tx_sj.id'=>$qSJo->surat_jalan_id,
                                                'tx_sj.active'=>'Y',
                                                'tx_nr.active'=>'Y',
                                            ]);
                                        })
                                        ->whereRaw('nota_retur_no NOT LIKE \'%Draft%\' AND approved_by IS NOT null')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'active'=>'Y',
                                        ])
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qREoth as $qRo)
                                        <tr>
                                            <td style="font-weight:bold;border-left:1px solid black;">@if ($cust_name!=$qC->cust_name){{ $qC->cust_name }}@endif</td>
                                            <td style="text-align: center;color: red;">{{ date_format(date_create($qRo->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color: red;">{{ $qSJo->surat_jalan_no }}</td>
                                            <td style="text-align: center;color: red;">{{ strpos($qRo->nota_retur_no,"Draft")>0?'':$qRo->nota_retur_no }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qRo->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">&nbsp;</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($qRo->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">
                                                @php
                                                    $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                                    ->select(
                                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                                        'tx_sjp.last_avg_cost',
                                                    )
                                                    ->where([
                                                        'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qRo->nr_id,
                                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                    ])
                                                    ->get();
                                                    $totAVG = 0;
                                                @endphp
                                                @foreach ($qNRavg as $qAvg)
                                                    @php
                                                        $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                    @endphp
                                                @endforeach
                                                -{{ number_format($totAVG,0,'.','') }}
                                            </td>
                                            <td style="text-align: right;color: red;">{{ number_format(($qRo->total_price!=0)?((($qRo->total_price-$totAVG)/$qRo->total_price)*100):0,0,'.','') }}%</td>
                                            <td style="text-align: center;color: red;">{{ $qSJo->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qRo->delivery_order->delivery_order_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $qSJo->customer->salesman01->initial }}</td>
                                            <td style="text-align: center;color: red;border-right:1px solid black;">{{ $qSJo->createdBy->userDetail->initial }}</td>
                                        </tr>

                                        @php
                                            $totalDPP = $totalDPP-$qRo->total_price;
                                            $totalDPPplusPPN = $totalDPPplusPPN-$qRo->total_price;
                                            $totalAVG = $totalAVG-$totAVG;

                                            $totalDPPperCust = $totalDPPperCust-$qRo->total_price;
                                            $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-$qRo->total_price;
                                            $totalAVGperCust = $totalAVGperCust-$totAVG;
                                            $isCustExist = true;
                                        @endphp
                                    @endforeach

                                    @php
                                        $cust_name = $qC->cust_name;
                                    @endphp
                                @endforeach
                            @endif
                            // non tax

                            @if (($isCustExist && $totalDPPperCust==0) || $totalDPPperCust!=0)
                                <tr>
                                    <td style="border-left:1px solid black;">&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPperCust,0,'.','') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ $totalPPNperCust>0?number_format($totalPPNperCust,0,'.',''):'' }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPplusPPNperCust,0,'.','') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAVGperCust,0,'.','') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ $totalDPPperCust>0?number_format(((($totalDPPperCust-$totalAVGperCust)/$totalDPPperCust)*100),0,'.',''):0 }}%</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="border-right:1px solid black;">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-bottom:1px solid black;">&nbsp;</td>
                                    <td style="border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                                </tr>
                            @endif
                        @endforeach
                        @if ($totalDPP>0)
                            <tr>
                                <td style="text-align: rightborder-left:1px solid black;font-weight:700;">Total</td>
                                <td style="text-align: left;font-weight:700;">{{ $branch->name }}</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalDPP,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalPPN,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPplusPPN,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format($totalAVG,0,'.','') }}</td>
                                <td style="text-align: right;font-weight:700;">{{ number_format(($totalDPP!=0)?((($totalDPP-$totalAVG)/$totalDPP)*100):0,0,'.','') }}%</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-right:1px solid black;">&nbsp;</td>
                            </tr>
                        @endif
                        <tr>
                            <td style="border-left:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-bottom:1px solid black;">&nbsp;</td>
                            <td style="border-right:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        </tr>
                        @php
                            $grandtotalDPP += $totalDPP;
                            $grandtotalPPN += $totalPPN;
                            $grandtotalDPPplusPPN += ($totalDPP+$totalPPN);
                            $grandtotalAVG += $totalAVG;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="4" style="text-align: center;font-weight:bold;border:1px solid black;">Grand Total</td>
                        <td style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandtotalDPP,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandtotalPPN,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandtotalDPPplusPPN,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format($grandtotalAVG,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:bold;border:1px solid black;">{{ number_format(($grandtotalDPP!=0)?((($grandtotalDPP-$grandtotalAVG)/$grandtotalDPP)*100):0,0,'.','') }}%</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                        <td style="border:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody> --}}
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
