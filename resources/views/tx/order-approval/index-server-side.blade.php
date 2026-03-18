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
                    <table class="table table-striped table-bordered" id="purchase-order-approval" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">PO No</th>
                                <th style="text-align: center;">PQ No</th>
                                <th style="text-align: center;">Order Date</th>
                                <th style="text-align: center;">Supplier</th>
                                <th style="text-align: center;">Total Price ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center;">Grand Price ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center;">Created by</th>
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
        // $('#purchase-order-approval').DataTable({
        //     "ordering": false,
        // });

        $('#purchase-order-approval').DataTable({
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
                    data: 'purchase_no',
                    name: 'tx_purchase_orders.purchase_no'
                },
                {
                    data: 'quotation_no',
                    name: 'quotation_no'
                },
                {
                    data: 'purchase_date',
                    name: 'purchase_date'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'total_before_vat',
                    name: 'tx_purchase_orders.total_before_vat',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'total_after_vat',
                    name: 'tx_purchase_orders.total_after_vat',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'initial',
                    name: 'usr.initial'
                },
                {
                    data: 'purchase_no_wlink',
                    name: 'purchase_no_wlink',
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
                    targets: [4,5],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [6,7,8],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
