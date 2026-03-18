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
                                            <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                id="branch_id" name="branch_id">
                                                <option value="">Choose...</option>
                                                @php
                                                    $p_Id = (old('branch_id')?old('branch_id'):(isset($reqs)?$reqs->branch_id:''));
                                                @endphp
                                                <option @if($p_Id==0){{ 'selected' }}@endif value="0">All</option>
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
                                                <option @if($supplier_id==0){{ 'selected' }}@endif value="0">All</option>
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
                                    {{-- <div class="row mb-3">
                                        <label for="year_id" class="col-sm-3 col-form-label">Lokal</label>
                                        <div class="col-sm-9">
                                            <input type="text" name="lokal_input" id="lokal_input" class="form-control @error('lokal_input') is-invalid @enderror" maxlength="1"
                                                value="@if(old('lokal_input')){{ old('lokal_input') }}@else{{ (isset($reqs)?$reqs->lokal_input:'') }}@endif">
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
                                    <th style="text-align: center;">TANGGAL</th>
                                    <th style="text-align: center;">INV NO</th>
                                    <th style="text-align: center;">RO NO</th>
                                    <th style="text-align: center;">NO PO/MO</th>
                                    <th style="text-align: center;">DPP ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">PPN ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: center;">EX INV NO</th>
                                    <th style="text-align: center;">PIC</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $dt_s = explode("-",$reqs->date_start);
                                    $dt_e = explode("-",$reqs->date_end);
                                    $totalDPP=0;
                                    $totalVAT=0;

                                    $branches = \App\Models\Mst_branch::when($reqs->branch_id!='0', function($q) use($reqs) {
                                        $q->where('id','=',$reqs->branch_id);
                                    })
                                    ->where('active','=','Y')
                                    ->orderBy('name','ASC')
                                    ->get();
                                @endphp
                                @foreach ($branches as $branch)
                                    <tr>
                                        <td style="font-weight:700;">Cabang</td>
                                        <td style="font-weight:700;">{{ $branch->name }}</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @php
                                        $supplier_name='';
                                        $suppliers = \App\Models\Mst_supplier::where('active','=','Y')
                                        ->orderBy('name','ASC')
                                        ->get();
                                    @endphp
                                    @foreach ($suppliers as $supplier)
                                        @php
                                            $receipt_orders = \App\Models\Tx_receipt_order::where('receipt_no','NOT LIKE','%Draft%')
                                            ->whereRaw('receipt_date>=\''.$dt_s[2].'-'.$dt_s[1].'-'.$dt_s[0].'\'')
                                            ->whereRaw('receipt_date<=\''.$dt_e[2].'-'.$dt_e[1].'-'.$dt_e[0].'\'')
                                            ->where([
                                                'supplier_id'=>$supplier->id,
                                                'branch_id'=>$branch->id,
                                                'active'=>'Y',
                                            ])
                                            ->orderBy('receipt_date','ASC')
                                            ->get();
                                        @endphp
                                        @foreach ($receipt_orders as $ro)
                                            <tr>
                                                <td>{{ ($supplier_name!=$supplier->name)?$supplier->name:'' }}</td>
                                                <td style="text-align:center;">{{ date_format(date_create($ro->receipt_date),"d/m/Y") }}</td>
                                                <td style="text-align:center;">{{ $ro->invoice_no}}</td>
                                                <td style="text-align:center;">{{ $ro->receipt_no}}</td>
                                                <td style="text-align:center;">{!! str_replace(",","<br/>",substr($ro->po_or_pm_no,1,strlen($ro->po_or_pm_no))) !!}</td>
                                                <td style="text-align:right;">{{ number_format($ro->total_before_vat,0,'.',',') }}</td>
                                                <td style="text-align:right;">{{ number_format(($ro->total_before_vat*$vat)/100,0,'.',',') }}</td>
                                                <td>&nbsp;</td>
                                                <td>{{ $supplier->pic1_name }}</td>
                                            </tr>
                                            @php
                                                $totalDPP+=$ro->total_before_vat;
                                                $totalVAT+=(($ro->total_before_vat*$vat)/100);
                                                $supplier_name=$supplier->name;
                                            @endphp
                                            @php
                                                $purchase_returs = \App\Models\Tx_purchase_retur::where('purchase_retur_no','NOT LIKE','%Draft%')
                                                ->whereRaw('approved_by IS NOT NULL')
                                                ->where([
                                                    'receipt_order_id'=>$ro->id,
                                                    'active'=>'Y',
                                                ])
                                                ->orderBy('purchase_retur_date','ASC')
                                                ->get();
                                            @endphp
                                            @foreach ($purchase_returs as $pr)
                                                <tr>
                                                    <td style="color:red;">{{ ($supplier_name!=$supplier->name)?$supplier->name:'' }}</td>
                                                    <td style="color:red;text-align:center;">{{ date_format(date_create($pr->purchase_retur_date),"d/m/Y") }}</td>
                                                    <td style="color:red;">&nbsp;</td>
                                                    <td style="color:red;text-align:center;">{{ $pr->purchase_retur_no}}</td>
                                                    <td style="color:red;text-align:center;">{!! str_replace(",","<br/>",substr($ro->po_or_pm_no,1,strlen($ro->po_or_pm_no))) !!}</td>
                                                    <td style="color:red;text-align:right;">-{{ number_format($pr->total_before_vat,0,'.',',') }}</td>
                                                    <td style="color:red;text-align:right;">-{{ number_format((($pr->total_before_vat*$vat)/100),0,'.',',') }}</td>
                                                    <td style="color:red;text-align:center;">{{ $ro->invoice_no}}</td>
                                                    <td style="color:red;">{{ $supplier->pic1_name }}</td>
                                                </tr>
                                                @php
                                                    $totalDPP=$totalDPP-$pr->total_before_vat;
                                                    $totalVAT=$totalVAT-(($pr->total_before_vat*$vat)/100);
                                                    $supplier_name=$supplier->name;
                                                @endphp
                                            @endforeach
                                        @endforeach
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
                                @endforeach
                                <tr>
                                    <td style="text-align: center;font-weight:700;">TOTAL</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td style="text-align:right;">{{ number_format($totalDPP,0,'.',',') }}</td>
                                    <td style="text-align:right;">{{ number_format($totalVAT,0,'.',',') }}</td>
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
