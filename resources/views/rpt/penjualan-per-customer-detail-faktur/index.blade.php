@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include(ENV('REPORT_FOLDER_NAME').'.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                                <option value="#">Choose...</option>
                                                <option @if (old('customer_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('customer_id')?old('customer_id'):(isset($reqs)?$reqs->customer_id:0));
                                                @endphp
                                                @foreach ($customers as $customer)
                                                    <option @if ($p_Id==$customer->id) {{ 'selected' }} @endif value="{{ $customer->id }}">{{ $customer->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('customer_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Period</label>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_start') is-invalid @enderror" maxlength="10"
                                                id="date_start" name="date_start" placeholder="Start Date"
                                                value="@if (old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_end') is-invalid @enderror" maxlength="10"
                                                id="date_end" name="date_end" placeholder="End Date"
                                                value="@if (old('date_end')){{ old('date_end') }}@else{{ (isset($reqs)?$reqs->date_end:'') }}@endif">
                                            @error('date_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    {{-- <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if (old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
                                            @error('lokal_input')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            {{-- <input type="button" id="generate-report" class="btn btn-primary px-5" value="Generate"> --}}
                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card" style="display: none;">
                <div class="card-body">
                    <div class="table-responsive">
                        @isset($reqs)
                        <table class="table table-striped table-bordered" id="penjualan-per-customer-detail-faktur" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">TANGGAL</th>
                                    <th style="text-align: center;">NO FAKTUR</th>
                                    <th style="text-align: center;">PARTS NO</th>
                                    <th style="text-align: center;">DESCRIPTION</th>
                                    <th style="text-align: center;">QTY</th>
                                    <th style="text-align: center;">DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">DOC NO</th>
                                    <th style="text-align: center;">NO SO/SJ</th>
                                    <th style="text-align: center;">NO FP</th>
                                    <th style="text-align: center;">SALESMAN</th>
                                    <th style="text-align: center;">AVG COST ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">GP %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $dpp_total=0;
                                    $totaldpp_total=0;

                                    $customers = \App\Models\Mst_customer::leftJoin('userdetails','mst_customers.salesman_id','=','userdetails.user_id')
                                    ->select(
                                        'mst_customers.id as cust_id',
                                        'mst_customers.name as cust_name',
                                        'userdetails.initial as salesman_initial',
                                    )
                                    ->when($reqs->customer_id>0, function($q) use($reqs) {
                                        $q->where('mst_customers.id','=', $reqs->customer_id);
                                    })
                                    ->where('mst_customers.active','=','Y')
                                    ->orderBy('mst_customers.name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($customers as $c)
                                    @php
                                        $cust_name='';

                                        // faktur
                                        $faktur = \App\Models\Tx_delivery_order::leftJoin('tx_tax_invoices as tx_inv','tx_delivery_orders.tax_invoice_id','=','tx_inv.id')
                                        ->select(
                                            'tx_delivery_orders.id as fk_id',
                                            'tx_delivery_orders.delivery_order_no',
                                            'tx_delivery_orders.delivery_order_date',
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
                                    @if ($faktur->count()>0 || $nota_jual->count()>0)
                                        <tr>
                                            <td style="font-weight: 700;">{{ $c->cust_name }}</td>
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
                                    @endif
                                    @if ($faktur->get())
                                        @php
                                            $cust_name=$c->cust_name;
                                        @endphp
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
                                                    <td>{{ ($faktur_no_tmp!=$fk->delivery_order_no)?date_format(date_create($fk->delivery_order_date),"d/m/Y"):'' }}</td>
                                                    <td>{{ ($faktur_no_tmp!=$fk->delivery_order_no)?$fk->delivery_order_no:'' }}</td>
                                                    <td>{{ $fk_p->part_number }}</td>
                                                    <td>{{ $fk_p->part_name }}</td>
                                                    <td style="text-align: right;">{{ $fk_p->qty }}</td>
                                                    <td style="text-align: right;">{{ number_format($fk_p->final_price,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format($fk_p->total_price,0,'.',',') }}</td>
                                                    <td style="text-align: center;">{{ $fk_p->customer_doc_no }}</td>
                                                    <td style="text-align: center;">{{ $fk_p->sales_order_no }}</td>
                                                    <td style="text-align: center;">{{ $fk->prefiks_code.$fk->fp_no }}</td>
                                                    <td style="text-align: center;">{{ $c->salesman_initial }}</td>
                                                    <td style="text-align: right;">
                                                        {{ number_format($fk_p->last_avg_cost,0,'.',',') }}
                                                        @php
                                                            $gp = ($fk_p->final_price-$fk_p->last_avg_cost)/$fk_p->final_price;
                                                        @endphp

                                                        {{-- @php
                                                            $gp=0;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$fk_p->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$fk_p->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            {{ number_format($avg->avg_cost,0,'.',',') }}
                                                            @php
                                                                $gp = ($fk_p->final_price-$avg->avg_cost)/$fk_p->final_price;
                                                            @endphp
                                                        @else
                                                            {{ 0 }}
                                                        @endif --}}
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format($gp*100,0,'.',',') }}%</td>
                                                </tr>
                                                @if ($faktur_no_tmp!=$fk->delivery_order_no)
                                                    @php
                                                        $faktur_no_tmp=$fk->delivery_order_no;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            <tr>
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
                                                <td>&nbsp;</td>
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
                                            @foreach ($nota_returs as $nr)
                                                <tr>
                                                    <td style="color: red;">{{ ($nota_retur_no!=$nr->nota_retur_no)?date_format(date_create($nr->nota_retur_date),"d/m/Y"):'' }}</td>
                                                    <td style="color: red;">{{ ($nota_retur_no!=$nr->nota_retur_no)?$nr->nota_retur_no:'' }}</td>
                                                    <td style="color: red;">{{ $nr->part_number }}</td>
                                                    <td style="color: red;">{{ $nr->part_name }}</td>
                                                    <td style="text-align: right;color: red;">{{ $nr->qty_retur }}</td>
                                                    <td style="text-align: right;color: red;">{{ number_format($nr->final_price,0,'.',',') }}</td>
                                                    <td style="text-align: right;color: red;">{{ number_format($nr->total_price,0,'.',',') }}</td>
                                                    <td style="text-align: center;color: red;">{{ $nr->customer_doc_no }}</td>
                                                    <td style="text-align: center;color: red;">{{ $nr->sales_order_no }}</td>
                                                    <td>&nbsp;</td>
                                                    <td style="text-align: center;color: red;">{{ $c->salesman_initial }}</td>
                                                    <td style="text-align: right;color: red;">
                                                        {{ number_format($nr->last_avg_cost,0,'.',',') }}
                                                        @php
                                                            $gp = ($nr->final_price-$nr->last_avg_cost)/$nr->final_price;
                                                        @endphp
                                                        {{-- @php
                                                            $gp=0;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$nr->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$nr->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            {{ number_format($avg->avg_cost,0,'.',',') }}
                                                            @php
                                                                $gp = ($nr->final_price-$avg->avg_cost)/$nr->final_price;
                                                            @endphp
                                                        @else
                                                            {{ 0 }}
                                                        @endif --}}
                                                    </td>
                                                    <td style="text-align: right;color: red;">{{ number_format($gp*100,0,'.',',') }}%</td>
                                                </tr>
                                                @php
                                                    $nota_retur_no=$nr->nota_retur_no;
                                                    $totaldpp_total=$totaldpp_total-$nr->total_price;
                                                @endphp
                                            @endforeach
                                            <tr>
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
                                                <td>&nbsp;</td>
                                            </tr>
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
                                                    <td>{{ ($nota_jual_no_tmp!=$np->delivery_order_no)?date_format(date_create($np->delivery_order_date),"d/m/Y"):'' }}</td>
                                                    <td>{{ ($nota_jual_no_tmp!=$np->delivery_order_no)?$np->delivery_order_no:'' }}</td>
                                                    <td>{{ $np_p->part_number }}</td>
                                                    <td>{{ $np_p->part_name }}</td>
                                                    <td style="text-align: right;">{{ $np_p->qty }}</td>
                                                    <td style="text-align: right;">{{ number_format($np_p->final_price,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format($np_p->total_price,0,'.',',') }}</td>
                                                    <td style="text-align: center;">{{ $np_p->customer_doc_no }}</td>
                                                    <td style="text-align: center;">{{ $np_p->surat_jalan_no }}</td>
                                                    <td style="text-align: center;">&nbsp;</td>
                                                    <td style="text-align: center;">{{ $c->salesman_initial }}</td>
                                                    <td style="text-align: right;">
                                                        {{ number_format($np_p->last_avg_cost,0,'.',',') }}
                                                        @php
                                                            $gp = ($np_p->final_price-$np_p->last_avg_cost)/$np_p->final_price;
                                                        @endphp

                                                        {{-- @php
                                                            $gp=0;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$np_p->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$np_p->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            {{ number_format($avg->avg_cost,0,'.',',') }}
                                                            @php
                                                                $gp = ($np_p->final_price-$avg->avg_cost)/$np_p->final_price;
                                                            @endphp
                                                        @else
                                                            {{ 0 }}
                                                        @endif --}}
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format($gp*100,0,'.',',') }}%</td>
                                                </tr>
                                                @if ($nota_jual_no_tmp!=$np->delivery_order_no)
                                                    @php
                                                        $nota_jual_no_tmp=$np->delivery_order_no;
                                                    @endphp
                                                @endif
                                            @endforeach
                                            <tr>
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
                                                <td>&nbsp;</td>
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
                                            @foreach ($returs as $nr)
                                                <tr>
                                                    <td style="color: red;">{{ ($retur_no!=$nr->nota_retur_no)?date_format(date_create($nr->nota_retur_date),"d/m/Y"):'' }}</td>
                                                    <td style="color: red;">{{ ($retur_no!=$nr->nota_retur_no)?$nr->nota_retur_no:'' }}</td>
                                                    <td style="color: red;">{{ $nr->part_number }}</td>
                                                    <td style="color: red;">{{ $nr->part_name }}</td>
                                                    <td style="text-align: right;color: red;">{{ $nr->qty_retur }}</td>
                                                    <td style="text-align: right;color: red;">{{ number_format($nr->final_price,0,'.',',') }}</td>
                                                    <td style="text-align: right;color: red;">{{ number_format($nr->total_price,0,'.',',') }}</td>
                                                    <td style="text-align: center;color: red;">{{ $nr->customer_doc_no }}</td>
                                                    <td style="text-align: center;color: red;">{{ $nr->surat_jalan_no }}</td>
                                                    <td>&nbsp;</td>
                                                    <td style="text-align: center;color: red;">{{ $c->salesman_initial }}</td>
                                                    <td style="text-align: right;color: red;">
                                                        {{ number_format($nr->last_avg_cost,0,'.',',') }}
                                                        @php
                                                            $gp = ($nr->final_price-$nr->last_avg_cost)/$nr->final_price;
                                                        @endphp

                                                        {{-- @php
                                                            $gp=0;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$nr->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$nr->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            {{ number_format($avg->avg_cost,0,'.',',') }}
                                                            @php
                                                                $gp = ($nr->final_price-$avg->avg_cost)/$nr->final_price;
                                                            @endphp
                                                        @else
                                                            {{ 0 }}
                                                        @endif --}}
                                                    </td>
                                                    <td style="text-align: right;color: red;">{{ number_format($gp*100,0,'.',',') }}%</td>
                                                </tr>
                                                @php
                                                    $retur_no=$nr->nota_retur_no;
                                                    $totaldpp_total=$totaldpp_total-$nr->total_price;
                                                @endphp
                                            @endforeach
                                            <tr>
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
                                                <td>&nbsp;</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totaldpp_total,0,'.',',') }}</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            </tbody>
                        </table>
                        @endisset
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#penjualan-per-customer-detail-faktur").DataTable({
            'ordering': false,
        });

        $("#generate-report").click(function() {
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function() {
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            // $('#date-time').bootstrapMaterialDatePicker({
            //     format: 'YYYY-MM-DD HH:mm'
            // });
            $('#date_start').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            $('#date_end').bootstrapMaterialDatePicker({
                time: false,
                format: 'DD-MM-YYYY'
            });
            // $('#time').bootstrapMaterialDatePicker({
            //     date: false,
            //     format: 'HH:mm'
            // });
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
