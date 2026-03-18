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
                    <table class="table table-striped table-bordered" id="purchase-retur-approval" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">PR No</th>
                                <th style="text-align: center;">Inv No</th>
                                <th style="text-align: center;">RO No</th>
                                <th style="text-align: center;">Date</th>
                                <th style="text-align: center;">Supplier</th>
                                <th style="text-align: center;">Total Retur ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center;">Grand Total Retur ({{ $qCurrency->string_val }})</th>
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
        // $('#purchase-retur-approval').DataTable();

        $('#purchase-retur-approval').DataTable({
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
                    data: 'purchase_retur_no',
                    name: 'tx_purchase_returs.purchase_retur_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'invoice_no',
                    name: 'tx_receipt_orders.invoice_no'
                },
                {
                    data: 'receipt_no',
                    name: 'tx_receipt_orders.receipt_no'
                },
                {
                    data: 'purchase_retur_date',
                    name: 'tx_purchase_returs.purchase_retur_date'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'total_before_vat',
                    name: 'tx_purchase_returs.total_before_vat',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'total_after_vat',
                    name: 'tx_purchase_returs.total_after_vat',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'initial',
                    name: 'usr.initial'
                },
                {
                    data: 'purchase_retur_no_wlink',
                    name: 'purchase_retur_no_wlink',
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
                    targets: [5,6],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [7,8,9],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
