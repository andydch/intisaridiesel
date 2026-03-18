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
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
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
        <hr />
        <form name="form_search" id="form-search" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="GET" enctype="application/x-www-form-urlencoded">
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="date_begin" class="col-sm-3 col-form-label">Date Begin</label>
                            <div class="col-sm-9">
                                <input readonly type="text" class="form-control @error('date_begin') is-invalid @enderror" maxlength="10"
                                    id="date_begin" name="date_begin" placeholder="Enter Date Begin" value="{{ $requestAll->date_begin }}">
                                @error('date_begin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="date_ending" class="col-sm-3 col-form-label">Date Ending</label>
                            <div class="col-sm-9">
                                <input readonly type="text" class="form-control @error('date_ending') is-invalid @enderror" maxlength="10"
                                    id="date_ending" name="date_ending" placeholder="Enter Date Ending" value="{{ $requestAll->date_ending }}">
                                @error('date_ending')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label for="branch_id" class="col-sm-3 col-form-label">Branch {{ $requestAll->branch_id }}</label>
                            <div class="col-sm-9">
                                <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                    <option value="#">Choose...</option>
                                    @php
                                        $branchId = $requestAll->branch_id;
                                    @endphp
                                    @foreach ($branches as $p)
                                        <option @if ($branchId==$p->id) {{ 'selected' }} @endif
                                            value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-3">&nbsp;</div>
                            <div class="col-sm-9">
                                <input type="submit" id="search-btn" class="btn btn-primary px-5" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form name="form_del" id="form-del" action="{{ url('/del_general_journal') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
            </div>
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="gj-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>GJ No</th>
                                    <th style="text-align: left !important;">Transaction Date</th>
                                    <th style="text-align: left !important;">Total Debet ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: left !important;">Total Credit ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: left !important;">Created by</th>
                                    <th style="text-align: left !important;">Action</th>
                                    <th style="text-align: left !important;">Status</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
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
        $('#gj-list').DataTable({
            processing: true,
            ordering: false,
            // scroller: true,
            // scrollY: 500,
            searching: true,
            serverSide: true,
            ajax: {
                url: '{!! url()->current() !!}',
            },
            columns: [{
                    data: 'general_journal_no',
                    name: 'tx_general_journals.general_journal_no',
                    orderable: false,
                },
                {
                    data: 'general_journal_date',
                    name: 'tx_general_journals.general_journal_date',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'total_debit',
                    name: 'tx_general_journals.total_debit',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'total_kredit',
                    name: 'tx_general_journals.total_kredit',
                    searchable: true,
                    orderable: true,
                },
                {
                    data: 'initial',
                    name: 'usr.initial'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
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
                    targets: [1],
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return moment(data).format('DD/MM/YYYY');
                    },
                },
                {
                    targets: [2,3],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [4,5,6],
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
            $('#date_begin').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#date_ending').bootstrapMaterialDatePicker({
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
