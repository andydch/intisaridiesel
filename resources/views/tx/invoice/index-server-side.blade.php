@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
{{-- <link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" /> --}}
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
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
        <hr />
        <div class="card">
            <div class="card-body">
                <form name="form_search" id="form-search" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/rpt') }}" method="POST" enctype="application/x-www-form-urlencoded">
                    @csrf
                    <div class="row mb-3">
                        <label for="start_date" class="col-sm-1 col-form-label">Start Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('start_date') is-invalid @enderror" maxlength="10"
                                id="start_date" name="start_date" placeholder="start date" value="@if(old('start_date')){{ old('start_date') }}@endif">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <label for="end_date" class="col-sm-1 col-form-label">End Date</label>
                        <div class="col-sm-2">
                            <input readonly type="text" class="form-control @error('end_date') is-invalid @enderror" maxlength="10"
                                id="end_date" name="end_date" placeholder="end date" value="@if(old('end_date')){{ old('end_date') }}@endif">
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-1">&nbsp;</div>
                        <div class="col-sm-5">
                            <input type="submit" class="btn btn-primary px-5" value="Download">
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-12">
            <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
            {{-- <a id="btn-del-danger" class="btn btn-light px-5" style="margin-bottom: 15px;">Cancel</a> --}}
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
                    <table class="table table-striped table-bordered" id="invoice-list" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align:left !important;">INV No</th>
                                <th style="text-align:left !important;">Create Date</th>
                                <th style="text-align:left !important;">Plan Date</th>
                                <th style="text-align:left !important;">Customer</th>
                                <th style="text-align:left !important;">Total ({{ $qCurrency->string_val }})</th>
                                <th style="text-align:left !important;">Sales</th>
                                <th style="text-align:left !important;">Action</th>
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

<!-- Full screen modal -->
<div class="modal fade" id="print-info" aria-hidden="true" aria-labelledby="print-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            {{-- <div class="modal-header">
                <h1 class="modal-title fs-5" id="print-info-title">Print Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> --}}
            <div class="modal-body">
                <input type="hidden" name="print-id" id="print-id">
                <p id="msg-modal" style="text-align: center"></p>
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to first</button>
            </div> --}}
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
{{-- <script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script> --}}
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script>
    function printDoc(i,print_type){
        let dl = '';
        if(print_type===2){
            dl = 'download=""';
        }
        $('#print-id').val(i);
        let downloadLInk = '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=1&p='+print_type+'" target="_new" class="btn btn-primary">Permohonan Pembayaran</a>&nbsp;'+
            '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=2&p='+print_type+'" target="_new" class="btn btn-primary">Tanda Terima</a>&nbsp;'+
            '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=3&p='+print_type+'" target="_new" class="btn btn-primary">Kwitansi</a>';
        console.log(downloadLInk);
        $("#msg-modal").html(downloadLInk);
        $('#print-info').modal('show');
    }

    $(document).ready(function() {
        $(function() {
            $('#start_date').bootstrapMaterialDatePicker({
                format: 'DD-MM-YYYY',
                time: false
            });
            $('#end_date').bootstrapMaterialDatePicker({
                format: 'DD-MM-YYYY',
                time: false
            });
        });

        $('#invoice-list').DataTable({
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
                    data: 'invoice_no',
                    name: 'tx_invoices.invoice_no'
                },
                {
                    data: 'createdat',
                    name: 'createdat'
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'total_with_retur_if_any',
                    name: 'total_with_retur_if_any'
                },
                // {
                //     data: 'do_grandtotal_vat',
                //     name: 'tx_invoices.do_grandtotal_vat'
                // },
                {
                    data: 'sales_initial',
                    name: 'usr_sales.initial'
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
                    targets: [4],
                    className: 'text-right',
                    // render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [1,2,5,6,7],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
