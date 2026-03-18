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
                    <table class="table table-striped table-bordered" id="nota-retur_approval" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">RE No</th>
                                <th style="text-align: center;">NP No</th>
                                <th style="text-align: center;">Date</th>
                                <th style="text-align: center;">Customer</th>
                                <th style="text-align: center;">Total ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center;">Sales</th>
                                <th style="text-align: center;">Action</th>
                                <th style="text-align: center;">Status</th>
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
        $('#nota-retur_approval').DataTable({
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
                    data: 'nota_retur_no',
                    name: 'tx_nota_retur_non_taxes.nota_retur_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'delivery_order_no',
                    name: 'delivery_order_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'nota_retur_date',
                    name: 'nota_retur_date'
                },
                {
                    data: 'cust_name',
                    name: 'mst_customers.name'
                },
                {
                    data: 'total_price',
                    name: 'tx_nota_retur_non_taxes.total_price',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'sales_initial',
                    name: 'usr_sales.initial'
                },
                {
                    data: 'nota_retur_no_wlink',
                    name: 'nota_retur_no_wlink',
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
                    targets: [5,6,7],
                    className: 'text-center',
                }
            ],
        });
    });
</script>
@endsection
