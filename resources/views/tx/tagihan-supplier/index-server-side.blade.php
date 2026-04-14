@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}

<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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

    .mt-custom {
        margin-top: 0;
    }

    @media only screen and (max-width: 430px) {
        .mt-custom {
            margin-top: 10px !important;
        }
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
        <form name="form_del" id="form-del" action="{{ url('/tx/tagihan-supplier/download-rpt') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="branch_id" class="col-sm-1 col-form-label">Branch</label>
                        <div class="col-sm-2">
                            <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id" onchange="dispPoPm('');">
                                <option value="#">Choose...</option>
                                @php
                                    $p_Id = (old('branch_id')?old('branch_id'):0);
                                @endphp
                                @foreach ($branches as $branch)
                                    <option @if ($p_Id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="start_date" class="col-sm-1 col-form-label">Start Date*</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('start_date') is-invalid @enderror"
                                maxlength="10" id="start_date" name="start_date" placeholder="start date"
                                value="@if(old('start_date')){{ old('start_date') }}@endif">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="end_date" class="col-sm-1 col-form-label">End Date*</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('end_date') is-invalid @enderror"
                                maxlength="10" id="end_date" name="end_date" placeholder="end date"
                                value="@if(old('end_date')){{ old('end_date') }}@endif">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-3 mt-custom">
                            <input type="submit" id="download-btn" class="btn btn-primary px-5" value="Download">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/create') }}" style="margin-bottom: 15px;">Add New</a>
            </div>
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">
                            {{ session('status-error') }}
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="tagihan-supplier-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="text-align: left !important;">TS No</th>
                                    <th style="text-align: left !important;">RO No</th>
                                    <th style="text-align: left !important;">Inv No</th>
                                    <th style="text-align: left !important;">PR No</th>
                                    <th style="text-align: left !important;">Plan Date</th>
                                    <th style="text-align: left !important;">Supplier</th>
                                    <th style="text-align: left !important;">Total Price VAT ({{ $qCurrency->string_val }})</th>
                                    <th style="text-align: left !important;">Created By</th>
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
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>

<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $(function() {
            $('#start_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD-MM-YYYY',
                time: false
            });
            $('#end_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                format: 'DD-MM-YYYY',
                time: false
            });
        });

        $('#tagihan-supplier-list').DataTable({
            processing: true,
            ordering: false,
            // scroller: true,
            // scrollY: 500,
            searching: true,
            serverSide: true,
            ajax: {
                url: '{!! url()->current() !!}',
            },
            columns: [{ // mengambil & menampilkan kolom sesuai tabel database
                    data: 'tagihan_supplier_no',
                    name: 'tx_tagihan_suppliers.tagihan_supplier_no'
                },
                {
                    data: 'receipt_orders_no',
                    name: 'receipt_orders_no'
                },
                {
                    data: 'receipt_orders_invoices',
                    name: 'receipt_orders_invoices',
                },
                {
                    data: 'purchase_returs_no',
                    name: 'purchase_returs_no'
                },
                {
                    data: 'tagihan_supplier_date',
                    name: 'tagihan_supplier_date'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'grandtotal_price',
                    name: 'grandtotal_price',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'createdBy',
                    name: 'userdetails.initial'
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
                    targets: [6],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [0,1,3,4,7,8,9],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
