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
                        <table class="table table-striped table-bordered" id="retur-penjualan-detail" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">TANGGAL</th>
                                    <th style="text-align: center;">NO NOTA RETUR</th>
                                    <th style="text-align: center;">NAMA CUSTOMER</th>
                                    <th style="text-align: center;">DPP AMOUNT ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">PPN ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">COST AVG ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">EX FAKTUR</th>
                                    <th style="text-align: center;">SALESMAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $nota_retur_no = '';
                                    $nota_retur_date = '';
                                    $cust_name = '';
                                    $totalDPP = 0;
                                    $totalPPN = 0;
                                    $totalAVG = 0;

                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="text-align: center;font-weight: 700;">Cabang</td>
                                        <td style="text-align: center;font-weight: 700;">{{ $branch->name }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    {{-- with tax --}}
                                    @if (strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='P' || $reqs->lokal_input=='')
                                        @php
                                            $qNotaRetur = \App\Models\Tx_nota_retur::leftJoin('mst_customers as msc','tx_nota_returs.customer_id','=','msc.id')
                                            ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                            ->leftJoin('tx_delivery_orders as fk','tx_nota_returs.delivery_order_id','=','fk.id')
                                            ->select(
                                                'tx_nota_returs.id as nr_id',
                                                'tx_nota_returs.nota_retur_no',
                                                'tx_nota_returs.nota_retur_date',
                                                'tx_nota_returs.total_before_vat',
                                                'msc.name as cust_name',
                                                'userdetails.initial',
                                                'fk.delivery_order_no as faktur_no',
                                            )
                                            ->where('tx_nota_returs.nota_retur_no','NOT LIKE','%Draft%')
                                            ->whereRaw('tx_nota_returs.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nota_returs.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'tx_nota_returs.branch_id'=>$branch->id,
                                                'tx_nota_returs.active'=>'Y',
                                            ])
                                            ->orderBy('tx_nota_returs.nota_retur_date','ASC')
                                            ->get();
                                        @endphp
                                        @foreach ($qNotaRetur as $qNR)
                                            @php
                                                $totalDPP+= $qNR->total_before_vat;
                                                $totalPPN+= (($qNR->total_before_vat*$vat)/100);
                                            @endphp
                                            <tr>
                                                <td style="text-align: center;">{{ ($nota_retur_date!=$qNR->nota_retur_date)?date_format(date_create($qNR->nota_retur_date),"d/m/Y"):'' }}</td>
                                                <td style="text-align: center;">{{ ($nota_retur_no!=$qNR->nota_retur_no)?$qNR->nota_retur_no:'' }}</td>
                                                <td>{{ ($cust_name!=$qNR->cust_name)?$qNR->cust_name:'' }}</td>
                                                <td style="text-align: right;">{{ number_format($qNR->total_before_vat,0,'.',',') }}</td>
                                                <td style="text-align: right;color:red;">{{ number_format(($qNR->total_before_vat*$vat)/100,0,'.',',') }}</td>
                                                <td style="text-align: right;">
                                                    @php
                                                        $totAVG=0;
                                                        $customer_doc_no='';
                                                        $qNotaReturPart = \App\Models\Tx_nota_retur_part::leftJoin('tx_sales_order_parts as tx_sop','tx_nota_retur_parts.sales_order_part_id','=','tx_sop.id')
                                                        ->leftJoin('tx_sales_orders as tx_sp','tx_sop.order_id','=','tx_sp.id')
                                                        ->select(
                                                            'tx_nota_retur_parts.part_id',
                                                            'tx_nota_retur_parts.qty_retur',
                                                            'tx_nota_retur_parts.updated_at as updatedat',
                                                            'tx_sp.customer_doc_no',
                                                            'tx_sop.last_avg_cost',
                                                        )
                                                        ->where([
                                                            'tx_nota_retur_parts.nota_retur_id'=>$qNR->nr_id,
                                                            'tx_nota_retur_parts.active'=>'Y',
                                                        ])
                                                        ->get()
                                                    @endphp
                                                    @foreach ($qNotaReturPart as $qNRp)
                                                        @php
                                                            $totAVG+=($qNRp->last_avg_cost*$qNRp->qty_retur);
                                                        @endphp
                                                        {{-- @php
                                                            $customer_doc_no=$qNRp->customer_doc_no;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$qNRp->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$qNRp->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            @php
                                                                $totAVG+=($avg->avg_cost*$qNRp->qty_retur);
                                                            @endphp
                                                        @endif --}}
                                                    @endforeach
                                                    {{ number_format($totAVG,0,'.',',') }}
                                                    @php
                                                        $totalAVG+= $totAVG;
                                                    @endphp
                                                </td>
                                                <td>{{ $qNR->faktur_no }}</td>
                                                <td style="text-align: center;">{{ $qNR->initial }}</td>
                                            </tr>
                                            @php
                                                $nota_retur_no = $qNR->nota_retur_no;
                                                $nota_retur_date = $qNR->nota_retur_date;
                                                $cust_name = $qNR->cust_name;
                                            @endphp
                                        @endforeach
                                    @endif

                                    {{-- non tax --}}
                                    @if (strtoupper($reqs->lokal_input)=='A' || strtoupper($reqs->lokal_input)=='N')
                                        @php
                                            $qRetur = \App\Models\Tx_nota_retur_non_tax::leftJoin('mst_customers as msc','tx_nota_retur_non_taxes.customer_id','=','msc.id')
                                            ->leftJoin('userdetails','msc.salesman_id','=','userdetails.user_id')
                                            ->leftJoin('tx_delivery_order_non_taxes as np','tx_nota_retur_non_taxes.delivery_order_id','=','np.id')
                                            ->select(
                                                'tx_nota_retur_non_taxes.id as nr_id',
                                                'tx_nota_retur_non_taxes.nota_retur_no',
                                                'tx_nota_retur_non_taxes.nota_retur_date',
                                                'tx_nota_retur_non_taxes.total_price',
                                                'msc.name as cust_name',
                                                'userdetails.initial',
                                                'np.delivery_order_no as faktur_no',
                                            )
                                            ->where('tx_nota_retur_non_taxes.nota_retur_no','NOT LIKE','%Draft%')
                                            ->whereRaw('tx_nota_retur_non_taxes.nota_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('tx_nota_retur_non_taxes.nota_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'tx_nota_retur_non_taxes.branch_id'=>$branch->id,
                                                'tx_nota_retur_non_taxes.active'=>'Y',
                                            ])
                                            ->orderBy('tx_nota_retur_non_taxes.nota_retur_date','ASC')
                                            ->get();
                                        @endphp
                                        @foreach ($qRetur as $qRE)
                                            @php
                                                $totalDPP+= $qRE->total_before_vat;
                                                $totalPPN+= (($qRE->total_before_vat*$vat)/100);
                                            @endphp
                                            <tr>
                                                <td style="text-align: center;">{{ ($nota_retur_date!=$qRE->nota_retur_date)?date_format(date_create($qRE->nota_retur_date),"d/m/Y"):'' }}</td>
                                                <td style="text-align: center;">{{ ($nota_retur_no!=$qRE->nota_retur_no)?$qRE->nota_retur_no:'' }}</td>
                                                <td>{{ ($cust_name!=$qRE->cust_name)?$qRE->cust_name:'' }}</td>
                                                <td style="text-align: right;">{{ number_format($qRE->total_price,0,'.',',') }}</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align: right;">
                                                    @php
                                                        $totAVG=0;
                                                        $customer_doc_no='';
                                                        $qReturPart = \App\Models\Tx_nota_retur_part_non_tax::leftJoin('tx_surat_jalan_parts as tx_sjp','tx_nota_retur_part_non_taxes.surat_jalan_part_id','=','tx_sjp.id')
                                                        ->leftJoin('tx_surat_jalans as tx_sj','tx_sjp.surat_jalan_id','=','tx_sj.id')
                                                        ->select(
                                                            'tx_nota_retur_part_non_taxes.part_id',
                                                            'tx_nota_retur_part_non_taxes.qty_retur',
                                                            'tx_nota_retur_part_non_taxes.updated_at as updatedat',
                                                            'tx_sj.customer_doc_no',
                                                            'tx_sjp.last_avg_cost',
                                                        )
                                                        ->where([
                                                            'tx_nota_retur_part_non_taxes.nota_retur_id'=>$qRE->nr_id,
                                                            'tx_nota_retur_part_non_taxes.active'=>'Y',
                                                        ])
                                                        ->get();
                                                    @endphp
                                                    @foreach ($qReturPart as $qREp)
                                                        @php
                                                            $totAVG+=($qREp->last_avg_cost*$qREp->qty_retur);
                                                        @endphp
                                                        {{-- @php
                                                            $customer_doc_no=$qREp->customer_doc_no;
                                                            $avg = \App\Models\V_log_avg_cost::where([
                                                                'part_id'=>$qREp->part_id,
                                                            ])
                                                            ->whereRaw('updated_at<=\''.$qREp->updatedat.'\'')
                                                            ->orderBy('updated_at','DESC')
                                                            ->first();
                                                        @endphp
                                                        @if ($avg)
                                                            @php
                                                                $totAVG+=($avg->avg_cost*$qREp->qty_retur);
                                                            @endphp
                                                        @endif --}}
                                                    @endforeach
                                                    {{ number_format($totAVG,0,'.',',') }}
                                                    @php
                                                        $totalAVG+= $totAVG;
                                                    @endphp
                                                </td>
                                                <td>{{ $qRE->faktur_no }}</td>
                                                <td style="text-align: center;">{{ $qRE->initial }}</td>
                                            </tr>
                                            @php
                                                $nota_retur_no = $qRE->nota_retur_no;
                                                $nota_retur_date = $qRE->nota_retur_date;
                                                $cust_name = $qRE->cust_name;
                                            @endphp
                                        @endforeach
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
                                    </tr>
                                @endforeach
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalDPP,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalPPN,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAVG,0,'.',',') }}</td>
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
        $("#retur-penjualan-detail").DataTable({
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
