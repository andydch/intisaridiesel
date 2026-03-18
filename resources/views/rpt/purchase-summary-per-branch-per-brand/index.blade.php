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
                                    {{-- <div class="row mb-3">
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="0">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:0));
                                                @endphp
                                                @foreach ($branches as $branch)
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div> --}}
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
                        <table class="table table-striped table-bordered" id="purchase-summary-per-branch-per-brand" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">BRANCH</th>
                                    <th style="text-align: center;">BRAND</th>
                                    <th style="text-align: center;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL RETUR DPP({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL NET DPP ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $branchNm='';
                                    $TotDpp=0;
                                    $TotRetur=0;
                                    $TotNetDpp=0;

                                    $branches = \App\Models\Mst_branch::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    @php
                                        $subTotDpp=0;
                                        $subTotRetur=0;
                                        $subTotNetDpp=0;
                                        $brands = \App\Models\Mst_global::where([
                                            'data_cat'=>'brand',
                                            'active'=>'Y',
                                        ])
                                        ->get();
                                    @endphp
                                    @foreach ($brands as $brand)
                                        <tr>
                                            <td style="text-align: center;">{{ ($branchNm!=$branch->name)?$branch->name:'' }}</td>
                                            <td>{{ $brand->title_ind }}</td>
                                            <td style="text-align:right;">
                                                @php
                                                    $sumTotDpp = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                    ->leftJoin('mst_parts as msp','tx_receipt_order_parts.part_id','=','msp.id')
                                                    ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                    ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                    ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                    ->where([
                                                        'tx_receipt_order_parts.active'=>'Y',
                                                        'tx_ro.branch_id'=>$branch->id,
                                                        'tx_ro.active'=>'Y',
                                                        'msp.brand_id'=>$brand->id,
                                                    ])
                                                    ->sum('total_price');
                                                @endphp
                                                {{ number_format($sumTotDpp,0,'.',',') }}
                                            </td>
                                            <td style="text-align:right;color:red;">
                                                @php
                                                    $sumTotRetur = \App\Models\Tx_purchase_retur_part::leftJoin('tx_purchase_returs as tx_pr','tx_purchase_retur_parts.purchase_retur_id','=','tx_pr.id')
                                                    ->leftJoin('mst_parts as msp','tx_purchase_retur_parts.part_id','=','msp.id')
                                                    ->whereIn('tx_pr.receipt_order_id',function($query) use($dt_s,$dt_e,$branch){
                                                        $query->select('tx_ro.id')
                                                        ->from('tx_receipt_orders as tx_ro')
                                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                        ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                        ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                        ->where([
                                                            'tx_ro.branch_id'=>$branch->id,
                                                            'tx_ro.active'=>'Y',
                                                        ]);
                                                    })
                                                    ->where('tx_pr.purchase_retur_no','NOT LIKE','%Draft%')
                                                    ->where([
                                                        'tx_purchase_retur_parts.active'=>'Y',
                                                        'tx_pr.active'=>'Y',
                                                        'msp.brand_id'=>$brand->id,
                                                    ])
                                                    ->sum('tx_purchase_retur_parts.total_price');
                                                @endphp
                                                {{ number_format($sumTotRetur,0,'.',',') }}
                                            </td>
                                            <td style="text-align:right;">{{ number_format(($sumTotDpp-$sumTotRetur),0,'.',',') }}</td>
                                        </tr>
                                        @php
                                            $branchNm=$branch->name;
                                            $subTotDpp+=$sumTotDpp;
                                            $subTotRetur+=$sumTotRetur;
                                            $subTotNetDpp+=($sumTotDpp-$sumTotRetur);
                                        @endphp
                                    @endforeach
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td style="text-align: center;font-weight:700;">SUB TOTAL</td>
                                        <td style="text-align: right;">{{ number_format($subTotDpp,0,'.',',') }}</td>
                                        <td style="text-align: right;color:red;">{{ number_format($subTotRetur,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format($subTotNetDpp,0,'.',',') }}</td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $TotDpp+=$subTotDpp;
                                        $TotRetur+=$subTotRetur;
                                        $TotNetDpp+=$subTotNetDpp;
                                        $branchNm=$branch->name;
                                    @endphp
                                @endforeach
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td style="text-align: right;">{{ number_format($TotDpp,0,'.',',') }}</td>
                                    <td style="text-align: right;color:red;">{{ number_format($TotRetur,0,'.',',') }}</td>
                                    <td style="text-align: right;">{{ number_format($TotNetDpp,0,'.',',') }}</td>
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
        $("#purchase-summary-per-branch-per-brand").DataTable({
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
