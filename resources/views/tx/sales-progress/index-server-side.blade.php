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
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
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
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <div class="card">
            <div class="card-body">
                <form name="form_search" id="form-search" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                    @csrf
                    <div class="row mb-3">
                        <label for="cust_code" class="col-sm-1 col-form-label">Customer</label>
                        <div class="col-sm-5">
                            <select class="form-select single-select @error('cust_code') is-invalid @enderror" id="cust_code" name="cust_code">
                                <option value="">Choose...</option>
                                @php
                                    $cust_code = (old('cust_code')?old('cust_code'):$req->c_d);
                                @endphp
                                @foreach ($qCustomers as $p)
                                    <option @if($cust_code==urlencode($p->slug)){{ 'selected' }}@endif
                                        value="{{ urlencode($p->slug) }}">{{ $p->customer_unique_code.' - '.$p->name }}</option>
                                @endforeach
                            </select>
                            @error('cust_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="start_date" class="col-sm-1 col-form-label">Start Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('start_date') is-invalid @enderror" maxlength="10"
                                id="start_date" name="start_date" placeholder="start date" value="@if(old('start_date')){{ old('start_date') }}@else{{ $req->s_d }}@endif">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="end_date" class="col-sm-1 col-form-label">End Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('end_date') is-invalid @enderror" maxlength="10"
                                id="end_date" name="end_date" placeholder="end date" value="@if(old('end_date')){{ old('end_date') }}@else{{ $req->e_d }}@endif">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="branch_code" class="col-sm-1 col-form-label">Branch</label>
                        <div class="col-sm-5">
                            <select class="form-select single-select @error('branch_code') is-invalid @enderror" id="branch_code" name="branch_code">
                                <option value="">Choose...</option>
                                @php
                                    $branch_code = (old('branch_code')?old('branch_code'):$req->b_c);
                                @endphp
                                @foreach ($qBranches as $p)
                                    <option @if($branch_code==urlencode($p->slug)){{ 'selected' }}@endif
                                        value="{{ urlencode($p->slug) }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="salesman_code" class="col-sm-1 col-form-label">Salesman</label>
                        <div class="col-sm-5">
                            <select class="form-select single-select @error('salesman_code') is-invalid @enderror" id="salesman_code" name="salesman_code">
                                <option value="">Choose...</option>
                                @php
                                    $salesman_code = (old('salesman_code')?old('salesman_code'):$req->s_c);
                                @endphp
                                @foreach ($qUsers as $p)
                                    <option @if($salesman_code==urlencode($p->slug)){{ 'selected' }}@endif
                                        value="{{ urlencode($p->slug) }}">{{ $p->initial }}</option>
                                @endforeach
                            </select>
                            @error('salesman_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- <div class="row mb-3">
                        <label for="lokal_opsi" class="col-sm-1 col-form-label">Lokal</label>
                        <div class="col-sm-2">
                            <input type="text" class="form-control @error('lokal_opsi') is-invalid @enderror" maxlength="1"
                                id="lokal_opsi" name="lokal_opsi" placeholder="lokal" value="@if(old('lokal_opsi')){{ old('lokal_opsi') }}@else{{ $req->l_o }}@endif">
                            @error('lokal_opsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="status_pos" class="col-sm-1 col-form-label">Status</label>
                        <div class="col-sm-5">
                            <select class="form-select single-select @error('status_pos') is-invalid @enderror" id="status_pos" name="status_pos">
                                <option value="">Choose...</option>
                                @php
                                    $status_pos = (old('status_pos')?old('status_pos'):$req->s_p);
                                @endphp
                                <option @if($status_pos==urlencode('so-sj')){{ 'selected' }}@endif value="{{ urlencode('so-sj') }}">SO & SJ</option>
                                <option @if($status_pos==urlencode('fk-np')){{ 'selected' }}@endif value="{{ urlencode('fk-np') }}">FK & NP</option>
                                <option @if($status_pos==urlencode('nr-re')){{ 'selected' }}@endif value="{{ urlencode('nr-re') }}">NR & RE</option>
                                <option @if($status_pos==urlencode('inv-kw')){{ 'selected' }}@endif value="{{ urlencode('inv-kw') }}">INV & KW</option>
                            </select>
                            @error('status_pos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div> --}}
                    <div class="row mb-3">
                        <div class="col-sm-1">&nbsp;</div>
                        <div class="col-sm-5">
                            <input type="submit" class="btn btn-primary px-5" value="Search">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                @if(session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                @if(session('status-error'))
                    <div class="alert alert-danger">
                        {{ session('status-error') }}
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="sales-progress" style="width:100%">
                        <thead>
                            <tr>
                                <th>SO/SJ Date</th>
                                <th>Customer</th>
                                <th>SO/SJ No</th>
                                <th style="text-align: left;">Total DPP({{ $qCurrency->string_val }})</th>
                                <th>FK/NP No</th>
                                <th>NR/RE No</th>
                                <th>IN/KW No</th>
                                <th>Branch</th>
                                <th>Sales</th>
                                <th style="text-align:left !important;">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
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
        $('#sales-progress').DataTable({
            processing: true,
            ordering: false,
            // scroller: true,
            // scrollY: 500,
            searching: true,
            serverSide: true,
            ajax: {
                url: '{!! url(request()->getRequestUri()) !!}',
                // url: '{!! url()->current() !!}',
            },
            columns: [{
                    data: 'surat_date',
                    name: 'surat_date',
                    searchable: true,
                },
                {
                    data: 'customer_complete_name',
                    name: 'customer_complete_name',
                    searchable: true,
                },
                {
                    data: 'surat_no',
                    name: 'v_so_sj.surat_no',
                    searchable: true,
                },
                {
                    data: 'total_dpp',
                    name: 'v_so_sj.total_dpp',
                    searchable: true,
                },
                {
                    data: 'fk_np',
                    name: 'v_so_sj.delivery_order_no',
                    searchable: true,
                },
                {
                    data: 'nr_re',
                    name: 'v_so_sj.nota_retur_no',
                    searchable: true,
                },
                {
                    data: 'in_kw',
                    name: 'v_so_sj.invoice_no',
                    searchable: true,
                },
                {
                    data: 'branch_initial',
                    name: 'mst_branches.initial',
                    searchable: true,
                },
                {
                    data: 'sales_initial',
                    name: 'usr_sales.initial'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                }
            ],
            columnDefs: [
                {
                    targets: [3],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [0,2,4,5,6,7,8,9],
                    className: 'text-center'
                }
            ],
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
            $('#start_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#end_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
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
