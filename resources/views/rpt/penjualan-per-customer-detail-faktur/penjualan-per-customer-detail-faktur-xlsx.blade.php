<!doctype html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <title>SalesPerCustDetailFaktur</title>
    </head>
    <body>
        <div class="table-responsive">
            <table style="width:1024px;">
                @php
                    $date = now();
                    $month = date_format($date,"m");
                    $totCols = 13;
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
                        <th colspan="{{ $totCols }}">REPORT SALES PER CUSTOMER DETAIL FAKTUR</th>
                    </tr>
                    <tr>
                        <th colspan="{{ $totCols }}" style="text-align: right;">PERIODE:&nbsp;{{ $date_start.' s/d '.$date_end }}</th>
                    </tr>
                    <tr>
                        <th>&nbsp;</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TANGGAL</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FAKTUR</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">PARTS NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DESCRIPTION</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">QTY</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">TOTAL PPN ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">DOC NO</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO SO/SJ</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">NO FP</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">SALESMAN</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">AVG COST ({{ $qCurrency->string_val }})</th>
                        <th style="text-align: center;border:1px solid black;background-color:#daeef3;">GP %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cust_name='';
                        $dt_s = explode("-",$date_start);
                        $dt_e = explode("-",$date_end);
                        $dpp_total=0;
                        $totaldpp_total=0;

                        $customers = \App\Models\Mst_customer::leftJoin('userdetails','mst_customers.salesman_id','=','userdetails.user_id')
                        ->leftJoin('mst_globals as ett_type','mst_customers.entity_type_id','=','ett_type.id')
                        ->select(
                            'mst_customers.id as cust_id',
                            'mst_customers.customer_unique_code',
                            // 'mst_customers.name as cust_name',
                            'userdetails.initial as salesman_initial',
                        )
                        ->selectRaw('IF(ISNULL(ett_type.title_ind),
                            CONCAT(mst_customers.customer_unique_code,\' - \',mst_customers.name),
                            CONCAT(mst_customers.customer_unique_code,\' - \',ett_type.title_ind,\' \',mst_customers.name)) as cust_name')
                        ->when($customer_id>0, function($q) use($customer_id) {
                            $q->where('mst_customers.id','=', $customer_id);
                        })
                        ->where('mst_customers.active','=','Y')
                        ->orderBy('mst_customers.name','ASC')
                        ->get();
                    @endphp
                    @foreach ($customers as $c)
                        @php
                            // faktur
                            $faktur = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices as tx_inv','tx_delivery_orders.tax_invoice_id','=','tx_inv.id')
                            ->select(
                                'tx_delivery_orders.id as fk_id',
                                'tx_delivery_orders.delivery_order_no',
                                'tx_delivery_orders.delivery_order_date',
                                'tx_delivery_orders.total_before_vat',
                                'tx_delivery_orders.total_after_vat',
                                'tx_inv.fp_no',
                                'tx_inv.prefiks_code',
                            )
                            ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_delivery_orders.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_orders.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where([
                                'tx_delivery_orders.customer_id'=>$c->cust_id,
                                'tx_delivery_orders.active'=>'Y',
                            ])
                            ->orderBy('tx_delivery_orders.delivery_order_date','ASC');

                            // nota penjualan
                            $nota_jual = \App\Models\Tx_delivery_order_non_tax::select(
                                'tx_delivery_order_non_taxes.id as np_id',
                                'tx_delivery_order_non_taxes.delivery_order_no',
                                'tx_delivery_order_non_taxes.delivery_order_date',
                            )
                            ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->where([
                                'tx_delivery_order_non_taxes.customer_id'=>$c->cust_id,
                                'tx_delivery_order_non_taxes.active'=>'Y',
                            ])
                            ->orderBy('tx_delivery_order_non_taxes.delivery_order_date','ASC');
                        @endphp
                        @if ($faktur->get())
                            @if ($faktur->count()>0 || $nota_jual->count()>0)
                                <tr>
                                    <td style="font-weight: 700;border-left:1px solid black">{{ $cust_name!=$c->cust_name?$c->cust_name:'' }}</td>
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
                                    <td style=";border-right:1px solid black;">&nbsp;</td>
                                </tr>
                                @php
                                    $cust_name=$c->cust_name;
                                @endphp
                            @endif
                            @foreach ($faktur->get() as $fk)
                                @php
                                    $faktur_no_tmp='';
                                    $faktur_part = \App\Models\Tx_delivery_order_part::leftJoin('mst_parts as msp','tx_delivery_order_parts.part_id','=','msp.id')
                                    ->leftJoin('tx_sales_orders as tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                    ->leftJoin('tx_sales_order_parts as tx_sop','tx_delivery_order_parts.sales_order_part_id','=','tx_sop.id')
                                    ->select(
                                        'tx_delivery_order_parts.part_id',
                                        'tx_delivery_order_parts.qty',
                                        'tx_delivery_order_parts.final_price',
                                        'tx_delivery_order_parts.total_price',
                                        'tx_delivery_order_parts.updated_at as updatedat',
                                        'msp.part_number',
                                        'msp.part_name',
                                        'tx_so.sales_order_no',
                                        'tx_so.customer_doc_no',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_delivery_order_parts.delivery_order_id'=>$fk->fk_id,
                                        'tx_delivery_order_parts.active'=>'Y',
                                        'tx_so.active'=>'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($faktur_part as $fk_p)
                                    @php
                                        $dpp_total+=$fk_p->final_price;
                                        $totaldpp_total+=$fk_p->total_price;
                                    @endphp
                                    <tr>
                                        <td style="border-left:1px solid black;">{{ ($faktur_no_tmp!=$fk->delivery_order_no)?date_format(date_create($fk->delivery_order_date),"d/m/Y"):'' }}</td>
                                        <td>{{ ($faktur_no_tmp!=$fk->delivery_order_no)?$fk->delivery_order_no:'' }}</td>
                                        <td>
                                            @php
                                                $partNumber = $fk_p->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            {{ $partNumber }}
                                        </td>
                                        <td>{{ $fk_p->part_name }}</td>
                                        <td style="text-align: right;">{{ $fk_p->qty }}</td>
                                        <td style="text-align: right;">{{ number_format($fk_p->final_price,0,'.','') }}</td>
                                        <td style="text-align: right;">{{ number_format($fk_p->total_price,0,'.','') }}</td>
                                        <td style="text-align: right;">
                                            @php
                                                $vatNum = ($fk_p->total_price/$fk->total_before_vat)*($fk->total_after_vat-$fk->total_before_vat);
                                            @endphp
                                            {{ number_format($vatNum,0,'.','') }}
                                        </td>
                                        <td style="text-align: center;">{{ $fk_p->customer_doc_no }}</td>
                                        <td style="text-align: center;">{{ $fk_p->sales_order_no }}</td>
                                        <td style="text-align: center;">{{ $fk->prefiks_code.$fk->fp_no }}</td>
                                        <td style="text-align: center;">{{ $c->salesman_initial }}</td>
                                        <td style="text-align: right;">
                                            {{ number_format($fk_p->last_avg_cost,0,'.','') }}
                                            @php
                                                $gp = ($fk_p->final_price-$fk_p->last_avg_cost)/$fk_p->final_price;
                                            @endphp
                                        </td>
                                        <td style="text-align: right;border-right:1px solid black;">{{ number_format($gp*100,0,'.','') }}%</td>
                                    </tr>
                                    @if ($faktur_no_tmp!=$fk->delivery_order_no)
                                        @php
                                            $faktur_no_tmp=$fk->delivery_order_no;
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
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="border-right:1px solid black;">&nbsp;</td>
                                </tr>
                                @php
                                    $nota_retur_no='';

                                    // nota retur
                                    $nota_returs = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('mst_parts as msp','tx_nota_retur_parts.part_id','=','msp.id')
                                    ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                    ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                    ->select(
                                        'tx_nota_retur_parts.part_id',
                                        'tx_nota_retur_parts.qty_retur',
                                        'tx_nota_retur_parts.final_price',
                                        'tx_nota_retur_parts.total_price',
                                        'tx_nota_retur_parts.updated_at as updatedat',
                                        'tx_nr.nota_retur_no',
                                        'tx_nr.nota_retur_date',
                                        'tx_nr.total_before_vat',
                                        'tx_nr.total_after_vat',
                                        'msp.part_number',
                                        'msp.part_name',
                                        'tx_so.sales_order_no',
                                        'tx_so.customer_doc_no',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->where('tx_nr.nota_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_nota_retur_parts.active'=>'Y',
                                        'tx_nr.delivery_order_id'=>$fk->fk_id,
                                        'tx_nr.customer_id'=>$c->cust_id,
                                        'tx_nr.active'=>'Y',
                                    ])
                                    ->orderBy('tx_nr.nota_retur_date','ASC')
                                    ->get();
                                @endphp
                                @if ($nota_returs)
                                    @foreach ($nota_returs as $nr)
                                        <tr>
                                            <td style="color: red;border-left:1px solid black;">{{ ($nota_retur_no!=$nr->nota_retur_no)?date_format(date_create($nr->nota_retur_date),"d/m/Y"):'' }}</td>
                                            <td style="color: red;">{{ ($nota_retur_no!=$nr->nota_retur_no)?$nr->nota_retur_no:'' }}</td>
                                            <td style="color: red;">
                                                @php
                                                    $partNumber = $nr->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                {{ $partNumber }}
                                            </td>
                                            <td style="color: red;">{{ $nr->part_name }}</td>
                                            <td style="text-align: right;color: red;">-{{ $nr->qty_retur }}</td>
                                            <td style="text-align: right;color: red;">{{ number_format($nr->final_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($nr->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">
                                                @php
                                                    $vatNumNR = ($nr->total_price/$nr->total_before_vat)*($nr->total_after_vat-$nr->total_before_vat);
                                                @endphp
                                                -{{ number_format($vatNumNR,0,'.','') }}
                                            </td>
                                            <td style="text-align: center;color: red;">{{ $nr->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $nr->sales_order_no }}</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: center;color: red;">{{ $c->salesman_initial }}</td>
                                            <td style="text-align: right;color: red;">
                                                -{{ number_format($nr->last_avg_cost,0,'.','') }}
                                                @php
                                                    $gp = ($nr->final_price-$nr->last_avg_cost)/$nr->final_price;
                                                @endphp
                                            </td>
                                            <td style="text-align: right;color: red;border-right:1px solid black;">-{{ number_format($gp*100,0,'.','') }}%</td>
                                        </tr>
                                        @php
                                            $nota_retur_no=$nr->nota_retur_no;
                                            $totaldpp_total=$totaldpp_total-$nr->total_price;
                                        @endphp
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
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                        @if ($nota_jual->get())
                            @foreach ($nota_jual->get() as $np)
                                @php
                                    $nota_jual_no_tmp='';
                                    $nota_jual_part = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('mst_parts as msp','tx_delivery_order_non_tax_parts.part_id','=','msp.id')
                                    ->leftJoin('tx_surat_jalans as tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                    ->leftJoin('tx_surat_jalan_parts as tx_sjp','tx_delivery_order_non_tax_parts.sales_order_part_id','=','tx_sjp.id')
                                    ->select(
                                        'tx_delivery_order_non_tax_parts.part_id',
                                        'tx_delivery_order_non_tax_parts.qty',
                                        'tx_delivery_order_non_tax_parts.final_price',
                                        'tx_delivery_order_non_tax_parts.total_price',
                                        'tx_delivery_order_non_tax_parts.updated_at as updatedat',
                                        'msp.part_number',
                                        'msp.part_name',
                                        'tx_sj.surat_jalan_no',
                                        'tx_sj.customer_doc_no',
                                        'tx_sjp.last_avg_cost',
                                    )
                                    ->where([
                                        'tx_delivery_order_non_tax_parts.delivery_order_id'=>$np->np_id,
                                        'tx_delivery_order_non_tax_parts.active'=>'Y',
                                        'tx_sj.active'=>'Y',
                                    ])
                                    ->orderBy('msp.part_number','ASC')
                                    ->get();
                                @endphp
                                @foreach ($nota_jual_part as $np_p)
                                    @php
                                        $dpp_total+=$np_p->final_price;
                                        $totaldpp_total+=$np_p->total_price;
                                    @endphp
                                    <tr>
                                        <td style="border-left:1px solid black;border-left:1px solid black;">{{ ($nota_jual_no_tmp!=$np->delivery_order_no)?date_format(date_create($np->delivery_order_date),"d/m/Y"):'' }}</td>
                                        <td>{{ ($nota_jual_no_tmp!=$np->delivery_order_no)?$np->delivery_order_no:'' }}</td>
                                        <td>
                                            @php
                                                $partNumber = $np_p->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            {{ $partNumber }}
                                        </td>
                                        <td>{{ $np_p->part_name }}</td>
                                        <td style="text-align: right;">{{ $np_p->qty }}</td>
                                        <td style="text-align: right;">{{ number_format($np_p->final_price,0,'.','') }}</td>
                                        <td style="text-align: right;">{{ number_format($np_p->total_price,0,'.','') }}</td>
                                        <td style="text-align: right;">&nbsp;</td>
                                        <td style="text-align: center;">{{ $np_p->customer_doc_no }}</td>
                                        <td style="text-align: center;">{{ $np_p->surat_jalan_no }}</td>
                                        <td style="text-align: center;">&nbsp;</td>
                                        <td style="text-align: center;">{{ $c->salesman_initial }}</td>
                                        <td style="text-align: right;">
                                            {{ number_format($np_p->last_avg_cost,0,'.','') }}
                                            @php
                                                $gp = ($np_p->final_price-$np_p->last_avg_cost)/$np_p->final_price;
                                            @endphp
                                        </td>
                                        <td style="text-align: right;border-right:1px solid black;border-right:1px solid black;">{{ number_format($gp*100,0,'.','') }}%</td>
                                    </tr>
                                    @if ($nota_jual_no_tmp!=$np->delivery_order_no)
                                        @php
                                            $nota_jual_no_tmp=$np->delivery_order_no;
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
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="border-right:1px solid black;">&nbsp;</td>
                                </tr>
                                @php
                                    $retur_no='';

                                    // nota retur
                                    $returs = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_nota_retur_non_taxes as tx_nr','tx_nota_retur_part_non_taxes.nota_retur_id','=','tx_nr.id')
                                    ->leftJoin('mst_parts as msp','tx_nota_retur_part_non_taxes.part_id','=','msp.id')
                                    ->leftJoin('tx_surat_jalan_parts as tx_sop','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sop.id')
                                    ->leftJoin('tx_surat_jalans as tx_so','tx_sop.surat_jalan_id','=','tx_so.id')
                                    ->select(
                                        'tx_nota_retur_part_non_taxes.part_id',
                                        'tx_nota_retur_part_non_taxes.qty_retur',
                                        'tx_nota_retur_part_non_taxes.final_price',
                                        'tx_nota_retur_part_non_taxes.total_price',
                                        'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                        'tx_nr.nota_retur_no',
                                        'tx_nr.nota_retur_date',
                                        'msp.part_number',
                                        'msp.part_name',
                                        'tx_so.surat_jalan_no',
                                        'tx_so.customer_doc_no',
                                        'tx_sop.last_avg_cost',
                                    )
                                    ->where('tx_nr.nota_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('tx_nr.approved_by IS NOT NULL')
                                    ->where([
                                        'tx_nota_retur_part_non_taxes.active'=>'Y',
                                        'tx_nr.delivery_order_id'=>$np->np_id,
                                        'tx_nr.customer_id'=>$c->cust_id,
                                        'tx_nr.active'=>'Y',
                                    ])
                                    ->orderBy('tx_nr.nota_retur_date','ASC')
                                    ->get();
                                @endphp
                                @if ($returs)
                                    @foreach ($returs as $nr)
                                        <tr>
                                            <td style="color: red;border-left:1px solid black;">{{ ($retur_no!=$nr->nota_retur_no)?date_format(date_create($nr->nota_retur_date),"d/m/Y"):'' }}</td>
                                            <td style="color: red;">{{ ($retur_no!=$nr->nota_retur_no)?$nr->nota_retur_no:'' }}</td>
                                            <td style="color: red;">
                                                @php
                                                    $partNumber = $nr->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                {{ $partNumber }}
                                            </td>
                                            <td style="color: red;">{{ $nr->part_name }}</td>
                                            <td style="text-align: right;color: red;">-{{ $nr->qty_retur }}</td>
                                            <td style="text-align: right;color: red;">{{ number_format($nr->final_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">-{{ number_format($nr->total_price,0,'.','') }}</td>
                                            <td style="text-align: right;color: red;">&nbsp;</td>
                                            <td style="text-align: center;color: red;">{{ $nr->customer_doc_no }}</td>
                                            <td style="text-align: center;color: red;">{{ $nr->surat_jalan_no }}</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: center;color: red;">{{ $c->salesman_initial }}</td>
                                            <td style="text-align: right;color: red;">
                                                -{{ number_format($nr->last_avg_cost,0,'.','') }}
                                                @php
                                                    $gp = ($nr->final_price-$nr->last_avg_cost)/$nr->final_price;
                                                @endphp
                                            </td>
                                            <td style="text-align: right;color: red;border-right:1px solid black;">-{{ number_format($gp*100,0,'.','') }}%</td>
                                        </tr>
                                        @php
                                            $retur_no=$nr->nota_retur_no;
                                            $totaldpp_total=$totaldpp_total-$nr->total_price;
                                        @endphp
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
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="border-right:1px solid black;">&nbsp;</td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif
                        @php
                            // nota retur dg SO sebelum range tanggal yang dipilih
                            $qNRoth = \App\Models\Tx_nota_retur::whereIn('id', function($q) use ($c, $dt_s) {
                                $q->select('nota_retur_id')
                                ->from('tx_nota_retur_parts')
                                ->whereIn('sales_order_part_id', function($q1) use ($c, $dt_s) {
                                    $q1->select('tx_sop.id')
                                    ->from('tx_sales_order_parts as tx_sop')
                                    ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                    ->where('tx_so.sales_order_no','NOT LIKE','%Draft%')
                                    ->where('tx_so.customer_id','=',$c->cust_id)
                                    ->whereRaw('tx_so.sales_order_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->where('tx_sop.active','=','Y')
                                    ->where('tx_so.need_approval','=','N')
                                    ->where('tx_so.active','=','Y');
                                })
                                ->where([
                                    'active'=>'Y',
                                ]);
                            })
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->whereRaw('approved_by IS NOT null')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('nota_retur_date','DESC');
                        @endphp
                        @if ($qNRoth->count()>0)
                            <tr>
                                <td style="font-weight: 700;border-left:1px solid black">{{ $cust_name!=$c->cust_name?$c->cust_name:'' }}</td>
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
                                <td style=";border-right:1px solid black;">&nbsp;</td>
                            </tr>
                            @php
                                $cust_name=$c->cust_name;
                            @endphp
                        @endif
                        @foreach ($qNRoth->get() as $qNR)
                            @php
                                $nr_no = '';
                                $qNRpart_oth = \App\Models\Tx_nota_retur_part::leftJoin('tx_nota_returs as tx_nr','tx_nota_retur_parts.nota_retur_id','=','tx_nr.id')
                                ->leftJoin('mst_parts as msp','tx_nota_retur_parts.part_id','=','msp.id')
                                ->leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                ->select(
                                    'tx_nota_retur_parts.qty_retur',
                                    'tx_nota_retur_parts.final_price',
                                    'tx_nota_retur_parts.total_price',
                                    'tx_nr.total_before_vat',
                                    'tx_nr.total_after_vat',
                                    'msp.part_number',
                                    'msp.part_name',
                                    'tx_sop.last_avg_cost',
                                    'tx_so.id as so_id',
                                    'tx_so.sales_order_no',
                                    'tx_so.customer_doc_no',
                                )
                                ->where([
                                    'tx_nota_retur_parts.nota_retur_id'=>$qNR->id,
                                    'tx_nota_retur_parts.active'=>'Y',
                                ])
                                ->get();
                            @endphp
                            @foreach ($qNRpart_oth as $qNRp)
                                <tr>
                                    <td style="border-left:1px solid black;color:red;">{{ $nr_no!=$qNR->nota_retur_no?date_format(date_create($qNR->nota_retur_date),"d/m/Y"):'' }}</td>
                                    <td style="color:red;">{{ $nr_no!=$qNR->nota_retur_no?$qNR->nota_retur_no:'' }}</td>
                                    <td style="color:red;">
                                        @php
                                            $partNumber = $qNRp->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ $partNumber }}
                                    </td>
                                    <td style="color:red;">{{ $qNRp->part_name }}</td>
                                    <td style="color:red;">-{{ $qNRp->qty_retur }}</td>
                                    <td style="color:red;">{{ number_format($qNRp->final_price,0,'.','') }}</td>
                                    <td style="color:red;">-{{ number_format($qNRp->total_price,0,'.','') }}</td>
                                    <td style="color:red;">
                                        @php
                                            $vatNumNRoth = ($qNRp->total_price/$qNRp->total_before_vat)*($qNRp->total_after_vat-$qNRp->total_before_vat);
                                        @endphp
                                        -{{ number_format($vatNumNRoth,0,'.','') }}
                                    </td>
                                    <td style="color:red;text-align:center;">{{ $qNRp->customer_doc_no }}</td>
                                    <td style="color:red;text-align:center;">{{ $qNRp->sales_order_no }}</td>
                                    <td style="color:red;">
                                        @php
                                            $qFKpart = \App\Models\Tx_delivery_order_part::leftJoin('tx_delivery_orders as fk','tx_delivery_order_parts.delivery_order_id','=','fk.id')
                                            ->leftJoin('tx_tax_invoices as fp','fk.tax_invoice_id','=','fp.id')
                                            ->select(
                                                'fp.fp_no',
                                                'fp.prefiks_code',
                                                )
                                            ->where([
                                                'tx_delivery_order_parts.sales_order_id'=>$qNRp->so_id,
                                                'tx_delivery_order_parts.active'=>'Y',
                                                'fk.active'=>'Y',
                                            ])
                                            ->first();
                                        @endphp
                                        {{ $qFKpart?$qFKpart->prefiks_code.$qFKpart->fp_no:'' }}
                                    </td>
                                    <td style="color:red;text-align:center;">{{ $c->salesman_initial }}</td>
                                    <td style="color:red;">-{{ number_format($qNRp->last_avg_cost,0,'.','') }}</td>
                                    <td style="border-right:1px solid black;color:red;text-align:right;">{{ number_format((($qNRp->final_price-$qNRp->last_avg_cost)/$qNRp->final_price)*100,0,'.','') }}%</td>
                                </tr>
                                @php
                                    $nr_no = $qNR->nota_retur_no;
                                    $totaldpp_total=$totaldpp_total-$qNRp->total_price;
                                @endphp
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
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-right:1px solid black;">&nbsp;</td>
                            </tr>
                        @endforeach
                        @php
                            // retur dg SJ sebelum range tanggal yang dipilih
                            $qREoth = \App\Models\Tx_nota_retur_non_tax::whereIn('id', function($q) use ($c, $dt_s) {
                                $q->select('nota_retur_id')
                                ->from('tx_nota_retur_part_non_taxes')
                                ->whereIn('surat_jalan_part_id', function($q1) use ($c, $dt_s) {
                                    $q1->select('tx_sop.id')
                                    ->from('tx_surat_jalan_parts as tx_sop')
                                    ->leftJoin('tx_surat_jalans as tx_so','tx_sop.surat_jalan_id','=','tx_so.id')
                                    ->where('tx_so.surat_jalan_no','NOT LIKE','%Draft%')
                                    ->where('tx_so.customer_id','=',$c->cust_id)
                                    ->whereRaw('tx_so.surat_jalan_date<\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->where('tx_sop.active','=','Y')
                                    ->where('tx_so.need_approval','=','N')
                                    ->where('tx_so.active','=','Y');
                                })
                                ->where([
                                    'active'=>'Y',
                                ]);
                            })
                            ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                            ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                            ->whereRaw('approved_by IS NOT null')
                            ->where([
                                'active'=>'Y',
                            ])
                            ->orderBy('nota_retur_date','DESC');
                        @endphp
                        @if ($qREoth->count()>0)
                            <tr>
                                <td style="font-weight: 700;border-left:1px solid black">{{ $cust_name!=$c->cust_name?$c->cust_name:'' }}</td>
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
                                <td style=";border-right:1px solid black;">&nbsp;</td>
                            </tr>
                            @php
                                $cust_name=$c->cust_name;
                            @endphp
                        @endif
                        @foreach ($qREoth->get() as $qRE)
                            @php
                                $nr_no = '';
                                $qREpart_oth = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('mst_parts as msp','tx_nota_retur_part_non_taxes.part_id','=','msp.id')
                                ->leftJoin('tx_surat_jalan_parts as tx_sop','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sop.id')
                                ->leftJoin('tx_surat_jalans as tx_so','tx_sop.surat_jalan_id','=','tx_so.id')
                                ->select(
                                    'tx_nota_retur_part_non_taxes.qty_retur',
                                    'tx_nota_retur_part_non_taxes.final_price',
                                    'tx_nota_retur_part_non_taxes.total_price',
                                    'msp.part_number',
                                    'msp.part_name',
                                    'tx_sop.last_avg_cost',
                                    'tx_so.id as so_id',
                                    'tx_so.surat_jalan_no',
                                    'tx_so.customer_doc_no',
                                )
                                ->where([
                                    'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qRE->id,
                                    'tx_nota_retur_part_non_taxes.active'=>'Y',
                                ])
                                ->get();
                            @endphp
                            @foreach ($qREpart_oth as $qREp)
                                <tr>
                                    <td style="border-left:1px solid black;color:red;">{{ $nr_no!=$qRE->nota_retur_no?date_format(date_create($qRE->nota_retur_date),"d/m/Y"):'' }}</td>
                                    <td style="color:red;">{{ $nr_no!=$qRE->nota_retur_no?$qRE->nota_retur_no:'' }}</td>
                                    <td style="color:red;">
                                        @php
                                            $partNumber = $qREp->part_number;
                                            if(strlen($partNumber)<11){
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                            }else{
                                                $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                            }
                                        @endphp
                                        {{ $partNumber }}
                                    </td>
                                    <td style="color:red;">{{ $qREp->part_name }}</td>
                                    <td style="color:red;">-{{ $qREp->qty_retur }}</td>
                                    <td style="color:red;">{{ number_format($qREp->final_price,0,'.','') }}</td>
                                    <td style="color:red;">-{{ number_format($qREp->total_price,0,'.','') }}</td>
                                    <td style="color:red;">&nbsp;</td>
                                    <td style="color:red;text-align:center;">{{ $qREp->customer_doc_no }}</td>
                                    <td style="color:red;text-align:center;">{{ $qREp->surat_jalan_no }}</td>
                                    <td style="color:red;">&nbsp;</td>
                                    <td style="color:red;text-align:center;">{{ $c->salesman_initial }}</td>
                                    <td style="color:red;">-{{ number_format($qREp->last_avg_cost,0,'.','') }}</td>
                                    <td style="border-right:1px solid black;color:red;text-align:right;">{{ number_format((($qREp->final_price-$qREp->last_avg_cost)/$qREp->final_price)*100,0,'.','') }}%</td>
                                </tr>
                                @php
                                    $nr_no = $qREp->nota_retur_no;
                                    $totaldpp_total=$totaldpp_total-$qREp->total_price;
                                @endphp
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
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td style="border-right:1px solid black;">&nbsp;</td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr>
                        <td style="text-align: center;font-weight:700;border-left:1px solid black;border-top:1px solid black;border-bottom:1px solid black;">TOTAL</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">{{ number_format($totaldpp_total,0,'.','') }}</td>
                        <td style="text-align: right;font-weight:700;border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;">&nbsp;</td>
                        <td style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Option 1: Bootstrap Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>
</html>
