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
        <form name="form_del" id="form-del" action="{{ url('/del_preceipt') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
            </div>
            <div class="card">
                <div class="card-body">
                    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                    @if (session('status-error'))<div class="alert alert-danger">{{ session('status-error') }}</div>@endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="payment-receipt" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%;text-align:left !important;">PC No</th>
                                    <th style="width: 10%;text-align:left !important;">PA No</th>
                                    <th style="width: 10%;text-align:left !important;">Create Date</th>
                                    <th style="width: 10%;text-align:left !important;">Journal No</th>
                                    <th style="width: 10%;text-align:left !important;">Journal Date</th>
                                    <th style="width: 10%;text-align:left !important;">Customer</th>
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
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#payment-receipt').DataTable({
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
                    data: 'payment_receipt_plan_no',
                    name: 'tx_payment_receipts.payment_receipt_plan_no'
                },
                {
                    data: 'payment_receipt_no',
                    name: 'tx_payment_receipts.payment_receipt_no'
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
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'payment_total',
                    name: 'tx_payment_receipts.payment_total'
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
                    targets: [6],
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
