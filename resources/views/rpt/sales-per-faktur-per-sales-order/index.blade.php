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
                                    <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if (old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
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
                        <table class="table table-striped table-bordered" id="sales-per-faktur-per-sales-order" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">TANGGAL</th>
                                    <th style="text-align: center;">NO FK/NR</th>
                                    <th style="text-align: center;">NO SO</th>
                                    <th style="text-align: center;">NAMA CUST</th>
                                    <th style="text-align: center;">TOTAL DPP+PPN ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL AVG ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">GP %</th>
                                    <th style="text-align: center;">NO FP</th>
                                    <th style="text-align: center;">SALES</th>
                                    <th style="text-align: center;">EX FAKTUR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                @endphp
                                @if (strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='P' || $reqs->lokal_input=='')
                                    @php
                                        $totalDppPajak = 0;
                                        $totalDppPlusPpnPajak = 0;
                                        $totalAvgPajak = 0;

                                        // faktur
                                        $qFaktur = \App\Models\Tx_delivery_order::leftJoin('mst_customers as ms_cust','tx_delivery_orders.customer_id','=','ms_cust.id')
                                        ->select(
                                            'tx_delivery_orders.id AS faktur_id',
                                            'tx_delivery_orders.delivery_order_no',
                                            'tx_delivery_orders.delivery_order_date',
                                            'tx_delivery_orders.total_before_vat',
                                            'tx_delivery_orders.total_after_vat',
                                            'tx_delivery_orders.customer_id',
                                            'tx_delivery_orders.tax_invoice_id',
                                            'tx_delivery_orders.created_by',
                                            'tx_delivery_orders.updated_by as updatedby',
                                            'ms_cust.name AS cust_name',
                                        )
                                        ->where('tx_delivery_orders.delivery_order_no','NOT LIKE','%Draft%')
                                        ->whereRaw('tx_delivery_orders.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_delivery_orders.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where('tx_delivery_orders.active','=','Y')
                                        ->orderBy('tx_delivery_orders.delivery_order_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qFaktur as $qFk)
                                        @php
                                            $qFakturParts = \App\Models\Tx_delivery_order_part::leftJoin('tx_sales_orders AS tx_so','tx_delivery_order_parts.sales_order_id','=','tx_so.id')
                                            ->select(
                                                'tx_delivery_order_parts.updated_at AS tx_do_updated_at',
                                                'tx_delivery_order_parts.sales_order_part_id',
                                                'tx_so.id AS sales_order_id',
                                                'tx_so.sales_order_no',
                                                'tx_so.total_before_vat',
                                                'tx_so.updated_by as so_updated_by',
                                            )
                                            ->where('tx_delivery_order_parts.delivery_order_id','=',$qFk->faktur_id)
                                            ->where('tx_delivery_order_parts.active','=','Y');

                                            $sales_order_no = '';
                                            $avg = 0;

                                            $qFkSo = $qFakturParts->first();
                                            if($qFkSo){
                                                $totalDppPajak += $qFk->total_before_vat;
                                                $totalDppPlusPpnPajak += $qFk->total_after_vat;
                                                $sales_order_no = $qFkSo->sales_order_no;

                                                $qSoPart = \App\Models\Tx_sales_order_part::where([
                                                    'id' => $qFkSo->sales_order_part_id,
                                                    // 'order_id' => $qFkSo->sales_order_id,
                                                    'active' => 'Y',
                                                ])
                                                ->get();
                                                foreach ($qSoPart as $qSoP) {
                                                    $avg += ($qSoP->last_avg_cost*$qSoP->qty);
                                                }
                                            }
                                            $totalAvgPajak += $avg;
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;">{{ $qFk->delivery_order_no }}</td>
                                            <td style="text-align: center;">{{ $sales_order_no }}</td>
                                            <td style="text-align: center;">{{ $qFk->cust_name }}</td>
                                            <td style="text-align: right;">{{ number_format($qFk->total_after_vat,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($qFk->total_before_vat,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($avg,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ ($qFkSo->total_before_vat>0?number_format((($qFkSo->total_before_vat-$avg)/$qFkSo->total_before_vat)*100,0,'.',','):0) }}%</td>
                                            <td style="text-align: center;">{{ (!is_null($qFk->tax_invoice)?$qFk->tax_invoice->prefiks_code.$qFk->tax_invoice->fp_no:'') }}</td>
                                            <td style="text-align: center;">{{ $qFk->customer->salesman01->initial }}</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endforeach
                                    @php
                                        // nota retur
                                        $qNotaRetur = \App\Models\Tx_nota_retur::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'nota_retur_date',
                                            'total_before_vat',
                                            'total_after_vat',
                                            'delivery_order_id',
                                            'customer_id',
                                            'created_by',
                                            'updated_by',
                                        )
                                        ->where('nota_retur_no','NOT LIKE','%Draft%')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('active','=','Y')
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qNotaRetur as $qNR)
                                        @php
                                            $totalDppPajak = $totalDppPajak-$qNR->total_before_vat;
                                            $totalDppPlusPpnPajak = $totalDppPlusPpnPajak-$qNR->total_after_vat;

                                            $sales_order_no = '';
                                            $so_updated_by = null;
                                            $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts AS tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                            ->leftJoin('tx_sales_orders AS tx_so','tx_sop.order_id','=','tx_so.id')
                                            ->leftJoin('tx_delivery_order_parts as tx_do_part','tx_sop.id','=','tx_do_part.sales_order_part_id')
                                            ->select(
                                                'tx_do_part.updated_at AS tx_do_updated_at',
                                                'tx_so.id AS sales_order_id',
                                                'tx_so.sales_order_no',
                                                'tx_so.total_before_vat',
                                                'tx_so.updated_by as so_updated_by',
                                                )
                                            ->where('tx_nota_retur_parts.nota_retur_id','=',$qNR->nr_id)
                                            ->first();
                                            if ($qNotaReturPart){
                                                $sales_order_no = $qNotaReturPart->sales_order_no;
                                                $so_updated_by = $qNotaReturPart->so_updated_by;
                                            }
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;color:red;">{{ date_format(date_create($qNR->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color:red;">{{ $qNR->nota_retur_no }}</td>
                                            <td style="text-align: center;color:red;">{{ $sales_order_no }}</td>
                                            <td style="text-align: center;">{{ $qNR->customer->name }}</td>
                                            <td style="text-align: right;color:red;">-{{ number_format($qNR->total_after_vat,0,'.',',') }}</td>
                                            <td style="text-align: right;color:red;">-{{ number_format($qNR->total_before_vat,0,'.',',') }}</td>
                                            <td style="text-align: right;color:red;">
                                            @php
                                                $avg = 0;
                                                $qNRavg = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                                ->select(
                                                    'tx_nota_retur_parts.qty_retur',
                                                    'tx_sop.last_avg_cost',
                                                )
                                                ->where([
                                                    'tx_nota_retur_parts.nota_retur_id'=>$qNR->nr_id,
                                                    'tx_nota_retur_parts.active'=>'Y',
                                                ])
                                                ->get();
                                                foreach ($qNRavg as $qAvg) {
                                                    $avg += ($qAvg->last_avg_cost*$qAvg->qty_retur);
                                                }
                                                $totalAvgPajak = $totalAvgPajak-$avg;
                                            @endphp
                                            -{{ number_format($avg,0,'.',',') }}
                                            </td>
                                            <td style="text-align: right;color:red;">{{ ($qNR->total_before_vat>0?number_format((($qNR->total_before_vat-$avg)/$qNR->total_before_vat)*100,0,'.',','):'') }}%</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: center;">{{ $qNR->customer->salesman01->initial }}</td>
                                            <td style="text-align: center;color:red;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: center;">TOTAL</td>
                                        <td style="text-align: right;">{{ number_format($totalDppPlusPpnPajak,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($totalDppPajak,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($totalAvgPajak,0,'.',',') }}</td>
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
                                    </tr>
                                @endif

                                @if (strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='N')
                                    @php
                                        $totalDppNonPajak = 0;
                                        $totalAvgNonPajak = 0;

                                        // faktur non pajak
                                        $qFakturNonPajak = \App\Models\Tx_delivery_order_non_tax::leftJoin('mst_customers as ms_cust','tx_delivery_order_non_taxes.customer_id','=','ms_cust.id')
                                        ->select(
                                            'tx_delivery_order_non_taxes.id AS faktur_id',
                                            'tx_delivery_order_non_taxes.delivery_order_no',
                                            'tx_delivery_order_non_taxes.delivery_order_date',
                                            'tx_delivery_order_non_taxes.total_price',
                                            'tx_delivery_order_non_taxes.customer_id',
                                            'tx_delivery_order_non_taxes.created_by',
                                            'tx_delivery_order_non_taxes.updated_by',
                                            'ms_cust.name AS cust_name',
                                        )
                                        ->where('tx_delivery_order_non_taxes.delivery_order_no','NOT LIKE','%Draft%')
                                        ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_delivery_order_non_taxes.delivery_order_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where('tx_delivery_order_non_taxes.active','=','Y')
                                        ->orderBy('tx_delivery_order_non_taxes.delivery_order_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qFakturNonPajak as $qFk)
                                        @php
                                            $qFakturNonPajakParts = \App\Models\Tx_delivery_order_non_tax_part::leftJoin('tx_surat_jalans AS tx_sj','tx_delivery_order_non_tax_parts.sales_order_id','=','tx_sj.id')
                                            ->select(
                                                'tx_delivery_order_non_tax_parts.sales_order_part_id',
                                                'tx_delivery_order_non_tax_parts.updated_at AS tx_do_updated_at',
                                                'tx_sj.id AS surat_jalan_id',
                                                'tx_sj.surat_jalan_no',
                                                'tx_sj.total',
                                                'tx_sj.updated_by as so_updated_by',
                                                )
                                            ->where('tx_delivery_order_non_tax_parts.delivery_order_id','=',$qFk->faktur_id)
                                            ->where('tx_delivery_order_non_tax_parts.active','=','Y');

                                            $avg = 0;
                                            $surat_jalan_no = '';
                                            $qNpSj = $qFakturNonPajakParts->first();
                                            if ($qNpSj){
                                                $totalDppNonPajak += $qFk->total_price;
                                                $surat_jalan_no = $qNpSj->surat_jalan_no;

                                                $qFkPart = \App\Models\Tx_surat_jalan_part::where([
                                                    'id' => $qNpSj->sales_order_part_id,
                                                    // 'surat_jalan_id' => $qNpSj->surat_jalan_id,
                                                    'active' => 'Y',
                                                ])
                                                ->get();
                                                foreach ($qFkPart as $qP) {
                                                    $avg += ($qP->last_avg_cost*$qP->qty);
                                                }
                                                $totalAvgNonPajak += $avg;
                                            }
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;">{{ date_format(date_create($qFk->delivery_order_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;">{{ $qFk->delivery_order_no }}</td>
                                            <td style="text-align: center;">{{ $surat_jalan_no }}</td>
                                            <td style="text-align: center;">{{ $qFk->cust_name }}</td>
                                            <td style="text-align: right;">{{ number_format(0,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($qFk->total_price,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ number_format($avg,0,'.',',') }}</td>
                                            <td style="text-align: right;">{{ ($qFk->total_price>0?number_format((($qFk->total_price-$avg)/$qFk->total_price)*100,0,'.',','):0) }}%</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: center;">{{ $qFk->customer->salesman01->initial }}</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                    @endforeach
                                    @php
                                        // retur
                                        $qRetur = \App\Models\Tx_nota_retur_non_tax::select(
                                            'id as nr_id',
                                            'nota_retur_no',
                                            'nota_retur_date',
                                            'total_price',
                                            'delivery_order_id',
                                            'customer_id',
                                            'created_by',
                                            'updated_by',
                                        )
                                        ->where('nota_retur_no','NOT LIKE','%Draft%')
                                        ->whereRaw('nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->whereRaw('approved_by IS NOT NULL')
                                        ->where('active','=','Y')
                                        ->orderBy('nota_retur_date','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($qRetur as $qNR)
                                        @php
                                            $totalDppNonPajak = $totalDppNonPajak-$qNR->total_price;
                                        @endphp
                                        <tr>
                                            <td style="text-align: center;color:red;">{{ date_format(date_create($qNR->nota_retur_date),"d/m/Y") }}</td>
                                            <td style="text-align: center;color:red;">{{ $qNR->nota_retur_no }}</td>
                                            <td style="text-align: center;color:red;">
                                            @php
                                                $qNotaReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts AS tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                                ->leftJoin('tx_surat_jalans AS tx_sj','tx_sjp.surat_jalan_id','=','tx_sj.id')
                                                ->leftJoin('tx_delivery_order_non_tax_parts as tx_do_part','tx_sjp.id','=','tx_do_part.sales_order_part_id')
                                                ->select(
                                                    'tx_do_part.updated_at AS tx_do_updated_at',
                                                    'tx_sj.surat_jalan_no',
                                                    'tx_sj.total',
                                                    'tx_sj.updated_by as so_updated_by',
                                                )
                                                ->where('tx_nota_retur_part_non_taxes.nota_retur_id','=',$qNR->nr_id)
                                                ->first();
                                            @endphp
                                            {{ (!is_null($qNotaReturPart)?$qNotaReturPart->surat_jalan_no:'') }}
                                            </td>
                                            <td style="text-align: center;">{{ $qNR->customer->name }}</td>
                                            <td style="text-align: right;color:red;">{{ number_format(0,0,'.',',') }}</td>
                                            <td style="text-align: right;color:red;">-{{ number_format($qNR->total_price,0,'.',',') }}</td>
                                            <td style="text-align: right;color:red;">
                                            @php
                                                $avg = 0;
                                                $qNRavg = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                                ->select(
                                                    'tx_nota_retur_part_non_taxes.qty_retur',
                                                    'tx_sjp.last_avg_cost',
                                                )
                                                ->where([
                                                    'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qNR->nr_id,
                                                    'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                ])
                                                ->get();
                                                foreach ($qNRavg as $qAvg) {
                                                    $avg += ($qAvg->last_avg_cost*$qAvg->qty_retur);
                                                }
                                                $totalAvgNonPajak = $totalAvgNonPajak-$avg;
                                            @endphp
                                            -{{ number_format($avg,0,'.',',') }}
                                            </td>
                                            <td style="text-align: right;color:red;">{{ ($qNR->total_price>0?number_format((($qNR->total_price-$avg)/$qNR->total_price)*100,0,'.',','):0) }}%</td>
                                            <td>&nbsp;</td>
                                            <td style="text-align: center;">{{ $qNR->createdBy->userDetail->initial }}</td>
                                            <td style="text-align: center;color:red;">{{ $qNR->delivery_order->delivery_order_no }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: center;">TOTAL</td>
                                        <td style="text-align: right;">{{ number_format(0,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($totalDppNonPajak,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($totalAvgNonPajak,0,'.',',') }}</td>
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
                                    </tr>
                                @endif
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
        $("#sales-per-faktur-per-sales-order").DataTable({
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
