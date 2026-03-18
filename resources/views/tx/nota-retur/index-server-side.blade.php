@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
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
        <div class="col-12">
            <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
            {{-- <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Cancel</a> --}}
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
                    <table class="table table-striped table-bordered" id="nota-retur" style="width:100%">
                        <thead>
                            <tr>
                                <th>NR No</th>
                                <th>FK No</th>
                                <th>Approved Date</th>
                                <th>Customer</th>
                                <th style="text-align:left !important;">Total ({{ $qCurrency->string_val }})</th>
                                <th>Sales</th>
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
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#nota-retur').DataTable({
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
                    data: 'nota_retur_no',
                    name: 'tx_nota_returs.nota_retur_no'
                },
                {
                    data: 'delivery_order_no',
                    name: 'delivery_order_no'
                },
                {
                    data: 'approved_date',
                    name: 'approved_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'total_retur',
                    name: 'total_retur',
                    orderable: false,
                    searchable: false,
                },
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
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [6,7],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
