@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
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
        <form name="search_ro" id="search_ro" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="orderId" id="orderId">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <label for="supplier_id" class="col-sm-1 col-form-label">Supplier</label>
                        <div class="col-sm-5">
                            <select class="form-select single-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                <option value="">Choose...</option>
                                @foreach ($suppliers as $qS)
                                    <option @if ($supplier_id==$qS->id){{ 'selected' }}@endif value="{{ $qS->id }}">{{ (!is_null($qS->entity_type)?$qS->entity_type->title_ind:'').' '.$qS->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="start_date" class="col-sm-1 col-form-label">Start Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('start_date') is-invalid @enderror"
                                maxlength="10" id="start_date" name="start_date" placeholder="start date" value="{{ $start_date }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="end_date" class="col-sm-1 col-form-label">End Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('end_date') is-invalid @enderror"
                                maxlength="10" id="end_date" name="end_date" placeholder="end date" value="{{ $end_date }}">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="status_id" class="col-sm-1 col-form-label">Status</label>
                        <div class="col-sm-2">
                            <select class="form-select single-select @error('status_id') is-invalid @enderror" id="status_id" name="status_id">
                                <option value="">Choose...</option>
                                <option @if($st=='draft'){{ 'selected' }}@endif value="draft">Draft</option>
                                <option @if($st=='created'){{ 'selected' }}@endif value="created">Created</option>
                                <option @if($st=='ts'){{ 'selected' }}@endif value="ts">TS</option>
                                <option @if($st=='paid'){{ 'selected' }}@endif value="paid">Paid</option>
                                <option @if($st=='partial'){{ 'selected' }}@endif value="partial">Partial</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <button id="searchBtn" class="btn btn-primary px-5" style="margin-bottom: 15px;">Search</button>
                            <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        {{-- <div class="col-12">            
            <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Cancel</a>
        </div> --}}
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
                    <table class="table table-striped table-bordered" id="receipt-order" style="width:105%">
                        <thead>
                            <tr>
                                <th>RO No</th>
                                <th>Inv No</th>
                                <th>PO/MO No</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th style="text-align:left;">Total Price ({{ $qCurrency->string_val }})</th>
                                <th>PR No</th>
                                <th>Created by</th>
                                <th>Action</th>
                                <th>Status</th>
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
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#searchBtn").click(function() {
            event.preventDefault();
            let param = $("#supplier_id").val()+'::'+$("#start_date").val()+'::'+$("#end_date").val()+'::'+$("#status_id").val();
            // let url = '{{ url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/search-ro') }}/'+param;
            location.href = '{{ route('ro.index') }}/'+param;
            // $("#search_ro").action = '{{ route('ro.index') }}/'+param;
            // $("#search_ro").submit();
            // alert(url);
        });

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

        $('#receipt-order').DataTable({
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
                    data: 'receipt_no',
                    name: 'receipt_no'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'po_mo_no',
                    name: 'po_mo_no'
                },
                {
                    data: 'receipt_date',
                    name: 'receipt_date'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'total_price',
                    name: 'total_price',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'purchase_retur_info',
                    name: 'purchase_retur_info'
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
                },
            ],
            columnDefs: [
                {
                    targets: [5],
                    className: 'text-right',
                    // render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [8],
                    className: 'text-center'
                }
            ],
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
