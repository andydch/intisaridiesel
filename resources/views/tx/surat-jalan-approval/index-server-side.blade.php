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
                    <table class="table table-striped table-bordered" id="surat-jalan-approval" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">SJ No</th>
                                <th style="text-align: center;">SQ No</th>
                                <th style="text-align: center;">Customer Doc No</th>
                                <th style="text-align: center;">Date</th>
                                <th style="text-align: center;">Customer</th>
                                <th style="text-align: center;">Total ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center;">Sales</th>
                                <th style="text-align: center;">Status</th>
                                <th style="text-align: center;">Action</th>
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
        $('#surat-jalan-approval').DataTable({
            processing: true,
            ordering: false,
            // scroller: true,
            // scrollY: 500,
            searching: true,
            serverSide: true,
            ajax: {
                url: '{!! url()->current() !!}',
            },
            columns: [
                { // mengambil & menampilkan kolom sesuai tabel database
                    data: 'surat_jalan_no',
                    name: 'tx_surat_jalans.surat_jalan_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'sales_quotation_no',
                    name: 'sales_quotation_no'
                },
                {
                    data: 'customer_doc_no',
                    name: 'tx_surat_jalans.customer_doc_no'
                },
                {
                    data: 'surat_jalan_date',
                    name: 'surat_jalan_date'
                },
                {
                    data: 'cust_name',
                    name: 'cust_name'
                },
                {
                    data: 'total',
                    name: 'tx_surat_jalans.total',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'sales_initial',
                    name: 'usr_sales.initial'
                },
                {
                    data: 'surat_jalan_no_wlink',
                    name: 'surat_jalan_no_wlink',
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
                    targets: [5],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [6,7,8],
                    className: 'text-center',
                }
            ],
        });
    });
</script>
@endsection
