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
                                        <label for="branch_id" class="col-sm-3 col-form-label">Branch</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:''));
                                                @endphp
                                                <option @if ($p_Id==0){{ 'selected=""' }}@endif value="0">All</option>
                                                @foreach ($branches as $branch)
                                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif
                                                        value="{{ $branch->id }}">{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('branch_id')
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
                                    <th style="text-align: center;">SUPPLIER NAME</th>
                                    <th style="text-align: center;">SUPPLIER CODE</th>
                                    <th style="text-align: center;">TOTAL DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL PPN ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL RETUR ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">TOTAL AMOUNT ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">DUE DATE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $totalAllDpp=0;
                                    $totalAllVat=0;
                                    $totalAllRetur=0;
                                    $totalAllAmount=0;

                                    $branches = \App\Models\Mst_branch::when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight:700;">{{ $branch->name }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $suppliers = \App\Models\Mst_supplier::where('active','=','Y')
                                        ->orderBy('name','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($suppliers as $supplier)
                                        @php
                                            $sumTotDpp = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                            ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'supplier_id'=>$supplier->id,
                                                'branch_id'=>$branch->id,
                                                'active'=>'Y',
                                            ])
                                            ->sum('total_before_vat');
                                        @endphp
                                        @if ($sumTotDpp>0)
                                            <tr>
                                                <td>{{ $supplier->name }}</td>
                                                <td>{{ $supplier->supplier_code }}</td>
                                                <td style="text-align: right;">{{ number_format($sumTotDpp,0,'.',',') }}</td>
                                                <td style="text-align: right;">{{ number_format((($sumTotDpp*$vat)/100),0,'.',',') }}</td>
                                                <td style="text-align: right;color:red;">
                                                    @php
                                                        $p_returs = \App\Models\Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
                                                        ->whereIn('receipt_order_id',function($query) use($dt_s,$dt_e,$supplier,$branch){
                                                            $query->select('tx_ro.id')
                                                            ->from('tx_receipt_orders as tx_ro')
                                                            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                            ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                            ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                            ->where([
                                                                'tx_ro.supplier_id'=>$supplier->id,
                                                                'tx_ro.branch_id'=>$branch->id,
                                                                'tx_ro.active'=>'Y',
                                                            ]);
                                                        })
                                                        ->where([
                                                            'active'=>'Y',
                                                        ])
                                                        ->whereRaw('approved_by IS NOT NULL')
                                                        ->sum('total_before_vat');
                                                    @endphp
                                                    {{ number_format($p_returs,0,'.',',') }}
                                                </td>
                                                <td style="text-align: right;">{{ number_format(($sumTotDpp+(($sumTotDpp*$vat)/100)-$p_returs),0,'.',',') }}</td>
                                                <td style="text-align: center;">
                                                    {{-- @php
                                                        $dateDue = \App\Models\Tx_purchase_order::where('purchase_no','NOT LIKE','%Draft%')
                                                        ->whereIn('id',function($query) use($dt_s,$dt_e,$supplier,$branch){
                                                            $query->select('tx_rop.po_mo_id')
                                                            ->from('tx_receipt_order_parts as tx_rop')
                                                            ->leftJoin('tx_receipt_orders as tx_ro','tx_rop.receipt_order_id','=','tx_ro.id')
                                                            ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                            ->whereRaw('tx_ro.receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                            ->whereRaw('tx_ro.receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                            ->where([
                                                                'tx_ro.supplier_id'=>$supplier->id,
                                                                'tx_ro.branch_id'=>$branch->id,
                                                                'tx_ro.active'=>'Y',
                                                            ])
                                                            ->where('tx_rop.po_mo_no','LIKE','PO%')
                                                            ->where('tx_rop.active','=','Y');
                                                        })
                                                        ->where([
                                                            'active'=>'Y',
                                                        ])
                                                        ->orderBy('purchase_date','ASC')
                                                        ->first();
                                                    @endphp
                                                    @if ($dateDue)
                                                        {{ date_format(date_create($dateDue->est_supply_date),"d/m/Y") }}
                                                    @endif --}}

                                                    @php
                                                        $lastEstDate = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                                        ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                                        ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                                        ->where([
                                                            'supplier_id'=>$supplier->id,
                                                            'branch_id'=>$branch->id,
                                                            'active'=>'Y',
                                                        ])
                                                        ->orderBy('receipt_date','DESC')
                                                        ->first();
                                                    @endphp
                                                    @if ($lastEstDate)
                                                        @php
                                                            $date = date_create($lastEstDate->receipt_date);
                                                            date_add($date, date_interval_create_from_date_string($supplier->top." days"));
                                                            echo date_format($date, "d/m/Y");
                                                        @endphp
                                                    @endif
                                                </td>
                                            </tr>
                                            @php
                                                $totalAllDpp+=$sumTotDpp;
                                                $totalAllVat+=(($sumTotDpp*$vat)/100);
                                                $totalAllRetur+=$p_returs;
                                                $totalAllAmount+=($sumTotDpp+(($sumTotDpp*$vat)/100)-$p_returs);
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
                                    </tr>
                                @endforeach
                                <tr>
                                    <td>&nbsp;</td>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAllDpp,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAllVat,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;color:red;">{{ number_format($totalAllRetur,0,'.',',') }}</td>
                                    <td style="text-align: right;font-weight:700;">{{ number_format($totalAllAmount,0,'.',',') }}</td>
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
