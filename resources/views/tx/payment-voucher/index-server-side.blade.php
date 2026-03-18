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
        </div>
        <div class="card">
            <div class="card-body">
                @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                @if (session('status-error'))<div class="alert alert-danger">{{ session('status-error') }}</div>@endif
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="PaymentVoucherList" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%;text-align:left !important;">PS No</th>
                                <th style="width: 10%;text-align:left !important;">PV No</th>
                                <th style="width: 10%;text-align:left !important;">TS No</th>
                                <th style="width: 10%;text-align:left !important;">Create Date</th>
                                <th style="width: 10%;text-align:left !important;">Journal No</th>
                                <th style="width: 10%;text-align:left !important;">Journal Date</th>
                                <th style="width: 10%;text-align:left !important;">Supplier</th>
                                <th style="width: 10%;text-align:left !important;">Total ({{ $qCurrency->string_val }})</th>
                                <th style="width: 10%;text-align:left !important;">Created By</th>
                                <th style="width: 10%;text-align:left !important;">Action</th>
                                <th style="width: 10%;text-align:left !important;">Status</th>
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
        $('#PaymentVoucherList').DataTable({
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
                    data: 'payment_voucher_plan_no',
                    name: 'tx_payment_vouchers.payment_voucher_plan_no'
                },
                {
                    data: 'payment_voucher_no',
                    name: 'tx_payment_vouchers.payment_voucher_no'
                },
                {
                    data: 'tagihan_supplier_no',
                    name: 'cts.tagihan_supplier_no'
                },
                {
                    data: 'doc_created_at',
                    name: 'doc_created_at'
                },
                {
                    data: 'journal_no',
                    name: 'journal_no'
                },
                {
                    data: 'journal_date_at',
                    name: 'journal_date_at'
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name'
                },
                {
                    data: 'payment_total',
                    name: 'payment_total'
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
                    searchable: true,
                }
            ],
            columnDefs: [
                {
                    targets: [7],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [0,1,2,4,5,8,9,10],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
