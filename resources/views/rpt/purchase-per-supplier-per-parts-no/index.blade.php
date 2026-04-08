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
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:''));
                                                @endphp
                                                <option @if($p_Id==0){{ 'selected' }}@endif value="9999">All</option>
                                                @foreach ($branches as $branch)
                                                    <option @if($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="supplier_id" class="col-sm-3 col-form-label">Supplier</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                                id="supplier_id" name="supplier_id">
                                                <option value="">Choose...</option>
                                                @php
                                                    $supplier_id = (old('supplier_id')?old('supplier_id'):(isset($reqs)?$reqs->supplier_id:''));
                                                @endphp
                                                <option @if($supplier_id==0){{ 'selected' }}@endif value="9999">All</option>
                                                @foreach ($suppliers as $supplier)
                                                    <option @if($supplier_id==$supplier->id) {{ 'selected' }} @endif
                                                        value="{{ $supplier->id }}">{{ $supplier->supplier_code.' - '.
                                                        ($supplier->entity_type?$supplier->entity_type->title_ind:'').' '.$supplier->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
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
                        <table class="table table-striped table-bordered" id="retur-penjualan-detail" style="width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">NAMA SUPPLIER</th>
                                    <th style="text-align: center;">PARTS NO</th>
                                    <th style="text-align: center;">PARTS NAME</th>
                                    <th style="text-align: center;">QTY</th>
                                    <th style="text-align: center;">TOTAL HARGA ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">BRANCH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $totalDPP=0;
                                    $supplier_name='';
                                    $part_no='';
                                    $totHargaAll=0;

                                    $suppliers = \App\Models\Mst_supplier::where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($suppliers as $supplier)
                                    @php
                                        $totHargaPerSupplier=0;
                                        $receipt_order_parts = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders as tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                        ->leftJoin('mst_parts as msp','tx_receipt_order_parts.part_id','=','msp.id')
                                        ->leftJoin('mst_suppliers as mssp','tx_ro.supplier_id','=','mssp.id')
                                        ->leftJoin('mst_branches as msb','tx_ro.branch_id','=','msb.id')
                                        ->select(
                                            'tx_receipt_order_parts.qty as part_qty',
                                            'tx_receipt_order_parts.final_cost',
                                            'mssp.name as supplier_name',
                                            'msp.part_number',
                                            'msp.part_name',
                                            'msb.initial as branch_initial',
                                        )
                                        ->where([
                                            'tx_receipt_order_parts.active'=>'Y',
                                        ])
                                        ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                        ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                        ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                        ->where([
                                            'tx_ro.supplier_id'=>$supplier->id,
                                            'tx_ro.active'=>'Y',
                                        ])
                                        ->orderBy('msp.part_number','ASC')
                                        ->get();
                                    @endphp
                                    @if ($receipt_order_parts)
                                        @foreach ($receipt_order_parts as $rop)
                                            <tr>
                                                <td>{{ ($supplier_name!=$rop->supplier_name)?$rop->supplier_name:'' }}</td>
                                                <td>{{ ($part_no!=$rop->part_number)?$rop->part_number:'' }}</td>
                                                <td>{{ ($part_no!=$rop->part_number)?$rop->part_name:'' }}</td>
                                                <td style="text-align: right;">{{ $rop->part_qty }}</td>
                                                <td style="text-align: right;">{{ number_format(($rop->part_qty*$rop->final_cost),0,'.',',') }}</td>
                                                <td style="text-align: center;">{{ $rop->branch_initial }}</td>
                                            </tr>
                                            @php
                                                $totHargaPerSupplier+=($rop->part_qty*$rop->final_cost);
                                                $supplier_name=$rop->supplier_name;
                                                $part_no=$rop->part_number;
                                            @endphp
                                        @endforeach
                                        @if ($totHargaPerSupplier>0)
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td style="text-align: right;font-weight:700;">{{ number_format($totHargaPerSupplier,0,'.',',') }}</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                                <td>&nbsp;</td>
                                            </tr>
                                        @endif
                                    @endif
                                    @php
                                        $totHargaAll+=$totHargaPerSupplier;
                                    @endphp
                                @endforeach
                                @if ($totHargaAll>0)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td style="text-align: center;font-weight:700;">TOTAL</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td style="text-align: right;font-weight:700;">{{ number_format($totHargaAll,0,'.',',') }}</td>
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
