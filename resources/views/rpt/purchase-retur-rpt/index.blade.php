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
                        <table class="table table-striped table-bordered" id="retur-penjualan-detail" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">TANGGAL</th>
                                    <th style="text-align: center;">NAMA SUPPLIER</th>
                                    <th style="text-align: center;">PR NO</th>
                                    <th style="text-align: center;">DPP RETUR ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">PPN RETUR ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">INV NO</th>
                                    <th style="text-align: center;">RO NO</th>
                                    <th style="text-align: center;">BRANCH</th>
                                    <th style="text-align: center;">PIC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $totalDPP=0;
                                    $totalDPPppn=0;

                                    $purchase_retur = \App\Models\Tx_purchase_retur::leftJoin('mst_suppliers as msspp','tx_purchase_returs.supplier_id','=','msspp.id')
                                    ->leftJoin('tx_receipt_orders as tx_ro','tx_purchase_returs.receipt_order_id','=','tx_ro.id')
                                    ->leftJoin('mst_branches as msb','tx_ro.branch_id','=','msb.id')
                                    ->select(
                                        'tx_purchase_returs.purchase_retur_no',
                                        'tx_purchase_returs.purchase_retur_date',
                                        'tx_purchase_returs.total_before_vat',
                                        'msspp.name as supplier_name',
                                        'msspp.pic1_name',
                                        'tx_ro.receipt_no',
                                        'tx_ro.invoice_no',
                                        'msb.name as branch_name',
                                    )
                                    ->where('tx_purchase_returs.purchase_retur_no','NOT LIKE','%Draft%')
                                    ->whereRaw('tx_purchase_returs.purchase_retur_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                    ->whereRaw('tx_purchase_returs.purchase_retur_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                    ->whereRaw('tx_purchase_returs.approved_by is not null')
                                    ->where([
                                        'tx_purchase_returs.active'=>'Y',
                                    ])
                                    ->orderBy('tx_purchase_returs.purchase_retur_date','ASC')
                                    ->get();
                                @endphp
                                @foreach ($purchase_retur as $pr)
                                    <tr>
                                        <td style="text-align: center;">{{ date_format(date_create($pr->purchase_retur_date),"d/m/Y") }}</td>
                                        <td>{{ $pr->supplier_name }}</td>
                                        <td style="text-align: center;">{{ $pr->purchase_retur_no }}</td>
                                        <td style="text-align: right;">{{ number_format($pr->total_before_vat,0,'.',',') }}</td>
                                        <td style="text-align: right;">{{ number_format(($pr->total_before_vat*$vat)/100,0,'.',',') }}</td>
                                        <td style="text-align: center;">{{ $pr->invoice_no }}</td>
                                        <td style="text-align: center;">{{ $pr->receipt_no }}</td>
                                        <td style="text-align: center;">{{ $pr->branch_name }}</td>
                                        <td style="text-align: center;">{{ $pr->pic1_name }}</td>
                                    </tr>
                                    @php
                                        $totalDPP+=$pr->total_before_vat;
                                        $totalDPPppn+=(($pr->total_before_vat*$vat)/100);
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
                                </tr>
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalDPP,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalDPPppn,0,'.',',') }}</td>
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
