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
                                        <label for="journal_date" class="col-sm-3 col-form-label">Date</label>
                                        <div class="col-sm-9">
                                            <input readonly type="text" class="form-control @error('journal_date') is-invalid @enderror" maxlength="10"
                                                id="journal_date" name="journal_date" placeholder="Enter General Journal Date"
                                                value="@if (old('journal_date')){{ old('journal_date') }}@else{{ (isset($reqs)?$reqs->journal_date:'') }}@endif">
                                            @error('journal_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="part_no" class="col-sm-3 col-form-label">Part Number</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('part_no') is-invalid @enderror"
                                                id="part_no" name="part_no">
                                                <option value="0">Choose...</option>
                                                @php
                                                    $p_Id = (old('part_no')?old('part_no'):(isset($reqs)?$reqs->part_no:0));
                                                @endphp
                                                @foreach ($parts as $part)
                                                    <option @if ($p_Id==$part->id) {{ 'selected' }} @endif
                                                        value="{{ $part->id }}">{{ $part->part_number }}</option>
                                                @endforeach
                                            </select>
                                            @error('part_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="part_name" class="col-sm-3 col-form-label">Part Name</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('part_name') is-invalid @enderror"
                                                id="part_name" name="part_name">
                                                <option value="0">Choose...</option>
                                                @php
                                                    $p_Id = (old('part_name')?old('part_name'):(isset($reqs)?$reqs->part_name:0));
                                                @endphp
                                                @foreach ($parts as $part)
                                                    <option @if ($p_Id==$part->id) {{ 'selected' }} @endif
                                                        value="{{ $part->id }}">{{ $part->part_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('part_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="brand_id" class="col-sm-3 col-form-label">Brand</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('brand_id') is-invalid @enderror"
                                                id="brand_id" name="brand_id">
                                                <option value="0">Choose...</option>
                                                @php
                                                    $p_Id = (old('brand_id')?old('brand_id'):(isset($reqs)?$reqs->brand_id:0));
                                                @endphp
                                                @foreach ($brands as $brand)
                                                    <option @if ($p_Id==$brand->id) {{ 'selected' }} @endif
                                                        value="{{ $brand->id }}">{{ $brand->title_ind }}</option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
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
                            <input type="button" id="generate-report" class="btn btn-light px-5" value="Generate">
                            <input type="button" id="download-report" class="btn btn-light px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-light px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="inventory-per-merk-per-part-no" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>DESCRIPTION</th>
                                    <th>BRAND</th>
                                    <th>HARGA JUAL</th>
                                    <th>COST RATA2</th>
                                    <th>QTY OH</th>
                                    <th>QTY OO</th>
                                    <th>QTY MIN</th>
                                    <th>QTY MAX</th>
                                    <th>SATUAN</th>
                                    <th>GUDANG</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($query as $q)
                                    <tr>
                                        <td>{{ $q->part_number }}</td>
                                        <td>{{ $q->part_name }}</td>
                                        <td>{{ $q->brand_name }}</td>
                                        <td style="text-align: right;">{{ number_format($q->selling_price,2,',','.') }}</td>
                                        <td style="text-align: right;">{{ number_format($q->avg_selling_price,2,',','.') }}</td>
                                        <td style="text-align: right;">{{ number_format((!is_null($q->last_qty_on_hand_per_date)?$q->last_qty_on_hand_per_date:0),2,',','.') }}</td>
                                        <td style="text-align: right;">{{ $q->on_o }}</td>
                                        <td style="text-align: right;">{{ $q->safety_stock }}</td>
                                        <td style="text-align: right;">{{ $q->max_stock }}</td>
                                        <td>{{ $q->unit_name }}</td>
                                        <td>{{ $q->branch_name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>PARTS NO</th>
                                    <th>DESCRIPTION</th>
                                    <th>BRAND</th>
                                    <th>HARGA JUAL</th>
                                    <th>COST RATA2</th>
                                    <th>QTY OH</th>
                                    <th>QTY OO</th>
                                    <th>QTY MIN</th>
                                    <th>QTY MAX</th>
                                    <th>SATUAN</th>
                                    <th>GUDANG</th>
                                </tr>
                            </tfoot>
                        </table>
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
        $("#inventory-per-merk-per-part-no").DataTable();

        $("#generate-report").click(function() {
            if(!confirm("Data for Report will be generated.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('V');
                $("#submit-form").submit();
            }
        });
        $("#download-report").click(function() {
            // $("#submit-form").removeAttr("action");
            // let qString = "/"+$("#part_no").val()+"/"+$("#part_name").val()+"/"+$("#brand_id").val()+"/"+$("#branch_id").val();
            // $("#submit-form").attr("action", encodeURI("{{ url('/'.ENV('REPORT_FOLDER_NAME').'/'.$folder.'-xlsx') }}"+qString));
            // $("#submit-form").attr("method", "GET");
            // $("#submit-form").submit();

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
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#journal_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
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
