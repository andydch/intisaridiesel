@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}
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
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if(session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                <option @if(old('branch_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
                                                @endphp
                                                @foreach ($branches as $branch)
                                                    <option @if($p_Id==$branch->id){{ 'selected' }}@endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id">
                                                <option value="#">Choose...</option>
                                                <option @if(old('customer_id')==0){{ 'selected' }}@endif value="0">All</option>
                                                @php
                                                    $customer_id = (old('customer_id')?old('customer_id'):(isset($reqs)?$reqs->customer_id:0));
                                                @endphp
                                                @foreach ($customers as $customer)
                                                    <option @if($customer_id==$customer->id){{ 'selected' }}@endif 
                                                        value="{{ $customer->id }}">{{ $customer->customer_unique_code.' - '.($customer->entity_type->title_ind??'').' '.$customer->name }}</option>
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
                                                value="@if(old('date_start')){{ old('date_start') }}@else{{ (isset($reqs)?$reqs->date_start:'') }}@endif">
                                            @error('date_start')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-sm-3">
                                            <input readonly type="text" class="form-control @error('date_end') is-invalid @enderror" maxlength="10"
                                                id="date_end" name="date_end" placeholder="End Date"
                                                value="@if(old('date_end')){{ old('date_end') }}@else{{ (isset($reqs)?$reqs->date_end:'') }}@endif">
                                            @error('date_end')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if(old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
                                            @error('lokal_input')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
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
                        <table class="table table-striped table-bordered" id="sales-per-branch-per-cust" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">CUSTOMER.</th>
                                    <th style="text-align: center;">DATE</th>
                                    <th style="text-align: center;">NO SO</th>
                                    <th style="text-align: center;">NO FK/NR</th>
                                    <th style="text-align: center;">DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">PPN ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">AVG ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">GP (%)</th>
                                    <th style="text-align: center;">NO DOC</th>
                                    <th style="text-align: center;">EX FAKTUR</th>
                                    <th style="text-align: center;">SALESMAN</th>
                                    <th style="text-align: center;">CREATED BY</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandtotalDPP = 0;
                                    $grandtotalPPN = 0;
                                    $grandtotalDPPplusPPN = 0;
                                    $grandtotalAVG = 0;
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);

                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->when($reqs->branch_id!='0', function($q) use($reqs){
                                        $q->where('id','=',$reqs->branch_id);
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
                                    @php
                                        $totalDPP = 0;
                                        $totalPPN = 0;
                                        $totalDPPplusPPN = 0;
                                        $totalAVG = 0;
                                        $cust_name = '';

                                        $qCust = \App\Models\Mst_customer::where('active','=','Y')
                                        ->orderBy('name','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qCust as $qC)
                                        @php
                                            $totalDPPperCust = 0;
                                            $totalPPNperCust = 0;
                                            $totalDPPplusPPNperCust = 0;
                                            $totalAVGperCust = 0;
                                        @endphp

                                        {{-- with tax --}}
                                        @if(strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='P'  || strtoupper($reqs->lokal_input)=='')

                                            @php
                                                // sales order
                                                $qSO = \App\Models\Tx_sales_order::leftJoin('userdetails AS usr','tx_sales_orders.created_by','=','usr.user_id')
                                                ->select(
                                                    'tx_sales_orders.id AS sales_order_id',
                                                    'tx_sales_orders.sales_order_no',
                                                    'tx_sales_orders.sales_order_date',
                                                    'tx_sales_orders.total_before_vat',
                                                    'tx_sales_orders.customer_doc_no',
                                                    'tx_sales_orders.created_by',
                                                    'tx_sales_orders.customer_id',
                                                    'tx_sales_orders.updated_by as so_updated_by',
                                                )
                                                ->where('tx_sales_orders.sales_order_no','NOT LIKE','%Draft%')
                                                ->whereRaw('tx_sales_orders.sales_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                ->whereRaw('tx_sales_orders.sales_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                ->where('tx_sales_orders.customer_id','=',$qC->id)
                                                ->where('tx_sales_orders.need_approval','=','N')
                                                ->where('tx_sales_orders.branch_id','=',$branch->id)
                                                ->where('tx_sales_orders.active','=','Y')
                                                // ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_sales_orders.branch_id IS null) OR tx_sales_orders.branch_id='.$branch->id.')')
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
                                                    ->where('tx_delivery_order_parts.sales_order_id','=',$q->sales_order_id)
                                                    ->where('tx_delivery_order_parts.active','=','Y')
                                                    ->where('txdo.active','=','Y')
                                                    ->first();
                                                    if($faktur){
                                                        $faktur_no = $faktur->delivery_order_no;
                                                    }

                                                    $totalDPP += $q->total_before_vat;
                                                    $totalPPN += (($q->total_before_vat*$vat)/100);
                                                    $totalDPPplusPPN += ($q->total_before_vat+(($q->total_before_vat*$vat)/100));

                                                    $totalDPPperCust += $q->total_before_vat;
                                                    $totalPPNperCust += (($q->total_before_vat*$vat)/100);
                                                    $totalDPPplusPPNperCust += ($q->total_before_vat+(($q->total_before_vat*$vat)/100));
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold;">@if($cust_name!=$qC->name){{ $qC->name }}@endif</td>
                                                    <td style="text-align: center;">{{ date_format(date_create($q->sales_order_date),"d/m/Y") }}</td>
                                                    <td style="text-align: center;">{{ $q->sales_order_no }}</td>
                                                    <td style="text-align: center;">{{ $faktur_no }}</td>
                                                    <td style="text-align: right;">{{ number_format($q->total_before_vat,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format(($q->total_before_vat*$vat)/100,0,'.',',') }}</td>
                                                    <td style="text-align: right;">{{ number_format($q->total_before_vat+(($q->total_before_vat*$vat)/100),0,'.',',') }}</td>
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
                                                        {{ number_format($totAVG,0,'.',',') }}
                                                        @php
                                                            $totalAVGperCust += $totAVG;
                                                            $totalAVG += $totAVG;
                                                        @endphp
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format(($q->total_before_vat>0)?((($q->total_before_vat-$totAVG)/$q->total_before_vat)*100):0,0,'.',',') }}%</td>
                                                    <td style="text-align: center;">{{ $q->customer_doc_no }}</td>
                                                    <td style="text-align: center;">&nbsp;</td>
                                                    <td style="text-align: center;">{{ $q->customer->salesman01->initial }}</td>
                                                    <td style="text-align: center;">{{ $q->createdBy->userDetail->initial }}</td>
                                                </tr>
                                                @php
                                                    $cust_name = $qC->name;

                                                    // nota retur
                                                    $qNR = \App\Models\Tx_nota_retur::select(
                                                        'id as nr_id',
                                                        'nota_retur_no',
                                                        'nota_retur_date',
                                                        'total_before_vat',
                                                        'delivery_order_id',
                                                        'customer_id',
                                                        'created_by',
                                                    )
                                                    ->whereIn('id', function($query) use($q){
                                                        $query->select('tx_nr.nota_retur_id')
                                                        ->from('tx_nota_retur_parts as tx_nr')
                                                        ->leftJoin('tx_sales_order_parts as tx_sop','tx_nr.sales_order_part_id','=','tx_sop.id')
                                                        ->leftJoin('tx_sales_orders as tx_so','tx_sop.order_id','=','tx_so.id')
                                                        ->where('tx_sop.active','=','Y')
                                                        ->where('tx_so.id','=',$q->sales_order_id)
                                                        ->where('tx_so.active','=','Y')
                                                        ->where('tx_nr.active','=','Y');
                                                    })
                                                    ->where('nota_retur_no','NOT LIKE','%Draft%')
                                                    ->where('approved_by','<>',null)
                                                    ->where('active','=','Y')
                                                    ->orderBy('nota_retur_date','ASC')
                                                    ->get();
                                                @endphp
                                                @foreach ($qNR as $qNRd)
                                                    <tr>
                                                        <td style="color: red;font-weight:bold;">@if($cust_name!=$qC->name){{ $qC->name }}@endif</td>
                                                        <td style="text-align: center;color: red;">{{ date_format(date_create($qNRd->nota_retur_date),"d/m/Y") }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->sales_order_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $qNRd->nota_retur_no }}</td>
                                                        <td style="text-align: right;color: red;">{{ number_format($qNRd->total_before_vat,0,'.',',') }}</td>
                                                        <td style="text-align: right;color: red;">{{ number_format(($qNRd->total_before_vat*$vat)/100,0,'.',',') }}</td>
                                                        <td style="text-align: right;color: red;">{{ number_format($qNRd->total_before_vat+(($qNRd->total_before_vat*$vat)/100),0,'.',',') }}</td>
                                                        <td style="text-align: right;color: red;">
                                                            @php
                                                                $qNRavg = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                                                ->select(
                                                                    'tx_nota_retur_parts.qty_retur',
                                                                    'tx_sop.last_avg_cost',
                                                                )
                                                                ->where('tx_nota_retur_parts.nota_retur_id','=',$qNRd->nr_id)
                                                                ->where('tx_nota_retur_parts.active','=','Y')
                                                                ->get();
                                                                $totAVG = 0;
                                                            @endphp
                                                            @foreach ($qNRavg as $qAvg)
                                                                @php
                                                                    $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                                @endphp
                                                            @endforeach
                                                            {{ number_format($totAVG,0,'.',',') }}
                                                        </td>
                                                        <td style="text-align: right;color: red;">{{ number_format(($qNRd->total_before_vat>0)?((($qNRd->total_before_vat-$totAVG)/$qNRd->total_before_vat)*100):0,0,'.',',') }}%</td>
                                                        <td style="text-align: center;color: red;">{{ $q->customer_doc_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $qNRd->delivery_order->delivery_order_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->customer->salesman01->initial }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->createdBy->userDetail->initial }}</td>
                                                    </tr>
                                                    @php
                                                        $totalDPP = $totalDPP-$qNRd->total_before_vat;
                                                        $totalPPN = $totalPPN-(($qNRd->total_before_vat*$vat)/100);
                                                        $totalDPPplusPPN = $totalDPPplusPPN-($qNRd->total_before_vat+(($qNRd->total_before_vat*$vat)/100));
                                                        $totalAVG = $totalAVG-$totAVG;

                                                        $totalDPPperCust = $totalDPPperCust-$qNRd->total_before_vat;
                                                        $totalPPNperCust = $totalPPNperCust-(($qNRd->total_before_vat*$vat)/100);
                                                        $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-($qNRd->total_before_vat+(($qNRd->total_before_vat*$vat)/100));
                                                        $totalAVGperCust = $totalAVGperCust-$totAVG;
                                                    @endphp
                                                @endforeach
                                            @endforeach

                                        @endif
                                        {{-- with tax --}}

                                        {{-- non tax --}}
                                        @if(strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='N')
                                            @php
                                                // surat jalan
                                                $qSJ = \App\Models\Tx_surat_jalan::leftJoin('userdetails AS usr','tx_surat_jalans.created_by','=','usr.user_id')
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
                                                ->where('tx_surat_jalans.surat_jalan_no','NOT LIKE','%Draft%')
                                                ->whereRaw('tx_surat_jalans.surat_jalan_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                ->whereRaw('tx_surat_jalans.surat_jalan_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                ->where('tx_surat_jalans.customer_id','=',$qC->id)
                                                ->where('tx_surat_jalans.need_approval','=','N')
                                                ->where('tx_surat_jalans.active','=','Y')
                                                ->whereRaw('((usr.branch_id='.$branch->id.' AND tx_surat_jalans.branch_id IS null) OR tx_surat_jalans.branch_id='.$branch->id.')')
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
                                                    ->where('tx_delivery_order_non_tax_parts.sales_order_id','=',$q->surat_jalan_id)
                                                    ->where('tx_delivery_order_non_tax_parts.active','=','Y')
                                                    ->where('txdo.active','=','Y')
                                                    ->first();
                                                    if($np){
                                                        $np_no = $np->delivery_order_no;
                                                    }
                                                @endphp
                                                <tr>
                                                    <td style="font-weight:bold;">@if($cust_name!=$qC->name){{ $qC->name }}@endif</td>
                                                    <td style="text-align: center;">{{ date_format(date_create($q->surat_jalan_date),"d/m/Y") }}</td>
                                                    <td style="text-align: center;">{{ $q->surat_jalan_no }}</td>
                                                    <td style="text-align: center;">{{ $np_no }}</td>
                                                    <td style="text-align: right;">{{ number_format($q->total,0,'.',',') }}</td>
                                                    <td style="text-align: right;">0</td>
                                                    <td style="text-align: right;">{{ number_format($q->total,0,'.',',') }}</td>
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
                                                        {{ number_format($totAVG,0,'.',',') }}
                                                    </td>
                                                    <td style="text-align: right;">{{ number_format(($q->total>0)?((($q->total-$totAVG)/$q->total)*100):0,0,'.',',') }}%</td>
                                                    <td style="text-align: center;">{{ $q->customer_doc_no }}</td>
                                                    <td style="text-align: center;">&nbsp;</td>
                                                    <td style="text-align: center;">{{ $q->customer->salesman01->initial }}</td>
                                                    <td style="text-align: center;">{{ $q->createdBy->userDetail->initial }}</td>
                                                </tr>
                                                @php
                                                    $totalAVGperCust += $totAVG;
                                                    $totalAVG += $totAVG;
                                                    $cust_name = $qC->name;

                                                    // nota retur non tax
                                                    $qNR = \App\Models\Tx_nota_retur_non_tax::select(
                                                        'id as nr_id',
                                                        'nota_retur_no',
                                                        'total_price',
                                                        'delivery_order_id'
                                                    )
                                                    ->whereIn('id', function($query) use($q){
                                                        $query->select('tx_nr.nota_retur_id')
                                                        ->from('tx_nota_retur_part_non_taxes as tx_nr')
                                                        ->leftJoin('tx_surat_jalan_parts as tx_sop','tx_nr.surat_jalan_part_id','=','tx_sop.id')
                                                        ->leftJoin('tx_surat_jalans as tx_sj','tx_sop.surat_jalan_id','=','tx_sj.id')
                                                        ->where('tx_sop.active','=','Y')
                                                        ->where('tx_sj.id','=',$q->surat_jalan_id)
                                                        ->where('tx_sj.active','=','Y')
                                                        ->where('tx_nr.active','=','Y');
                                                    })
                                                    ->where('nota_retur_no','NOT LIKE','%Draft%')
                                                    ->where('approved_by','<>',null)
                                                    ->where('active','=','Y')
                                                    ->orderBy('nota_retur_date','ASC')
                                                    ->get();
                                                @endphp
                                                @foreach ($qNR as $qNRd)
                                                    <tr>
                                                        <td style="color: red;font-weight:bold;">@if($cust_name!=$qC->name){{ $qC->name }}@endif</td>
                                                        <td style="text-align: center;color: red;">{{ date_format(date_create($qNRd->nota_retur_date),"d/m/Y") }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->surat_jalan_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $qNRd->nota_retur_no }}</td>
                                                        <td style="text-align: right;color: red;">{{ number_format($qNRd->total_price,0,'.',',') }}</td>
                                                        <td style="text-align: right;color: red;">&nbsp;</td>
                                                        <td style="text-align: right;color: red;">{{ number_format($qNRd->total_price,0,'.',',') }}</td>
                                                        <td style="text-align: right;color: red;">
                                                            @php
                                                                $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.last_avg_cost')
                                                                ->select(
                                                                    'tx_nota_retur_part_non_taxes.qty_retur',
                                                                    'tx_sjp.last_avg_cost',
                                                                )
                                                                ->where('tx_nota_retur_part_non_taxes.nota_retur_id','=',$qNRd->nr_id)
                                                                ->where('tx_nota_retur_part_non_taxes.active','=','Y')
                                                                ->get();
                                                                $totAVG = 0;
                                                            @endphp
                                                            @foreach ($qNRavg as $qAvg)
                                                                @php
                                                                    $totAVG += ($qAvg->qty_retur*$qAvg->last_avg_cost);
                                                                @endphp
                                                            @endforeach
                                                            {{ number_format($totAVG,0,'.',',') }}
                                                        </td>
                                                        <td style="text-align: right;color: red;">{{ number_format(($qNRd->total_price>0)?((($qNRd->total_price-$totAVG)/$qNRd->total_price)*100):0,0,'.',',') }}%</td>
                                                        <td style="text-align: center;color: red;">{{ $q->customer_doc_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $qNRd->delivery_order->delivery_order_no }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->customer->salesman01->initial }}</td>
                                                        <td style="text-align: center;color: red;">{{ $q->createdBy->userDetail->initial }}</td>
                                                    </tr>
                                                    @php
                                                        $totalDPP = $totalDPP-$qNRd->total_price;
                                                        $totalDPPplusPPN = $totalDPPplusPPN-$qNRd->total_price;
                                                        $totalAVG = $totalAVG-$totAVG;

                                                        $totalDPPperCust = $totalDPPperCust-$qNRd->total_price;
                                                        $totalAVGperCust = $totalAVGperCust-$totAVG;
                                                        $totalDPPplusPPNperCust = $totalDPPplusPPNperCust-$qNRd->total_price;
                                                    @endphp
                                                @endforeach
                                            @endforeach
                                        @endif
                                        {{-- non tax --}}

                                        @if($totalDPPperCust>0)
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align: right;">{{ number_format($totalDPPperCust,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ number_format($totalPPNperCust,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ number_format($totalDPPplusPPNperCust,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ number_format($totalAVGperCust,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ number_format(($totalDPPperCust>0)?((($totalDPPperCust-$totalAVGperCust)/$totalDPPperCust)*100):0,0,'.',',') }}%</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>
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
                                        @endif
                                    @endforeach
                                    @if($totalDPP>0)
                                        <tr>
                                            <td style="text-align: right">Total</td>
                                            <td style="text-align: left">{{ $branch->name }}</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: right;">{{ number_format($totalDPP,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($totalPPN,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($totalDPPplusPPN,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($totalAVG,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format(($totalDPP>0)?((($totalDPP-$totalAVG)/$totalDPP)*100):0,0,'.',',') }}%</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endif
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
                                        $grandtotalDPP += $totalDPP;
                                        $grandtotalPPN += $totalPPN;
                                        $grandtotalDPPplusPPN += ($totalDPP+$totalPPN);
                                        $grandtotalAVG += $totalAVG;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td style="text-align: right">Grand Total</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;">{{ number_format($grandtotalDPP,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format($grandtotalPPN,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format($grandtotalDPPplusPPN,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format($grandtotalAVG,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format(($grandtotalDPP>0)?((($grandtotalDPP-$grandtotalAVG)/$grandtotalDPP)*100):0,0,'.',',') }}%</td>
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
    $(document).ready(function(){
        $("#sales-per-branch-per-cust").DataTable({
            'ordering': false,
        });

        $("#generate-report").click(function(){
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function(){
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function(){
            history.back();
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function(){
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
