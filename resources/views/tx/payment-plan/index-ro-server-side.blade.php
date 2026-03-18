@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
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
        <h6 class="mb-0 text-uppercase">{{ $title.' - '.date_format(date_create($qPlans->payment_month),"F Y").' ('.$bank_name.')' }}</h6>
        <hr />
        <div class="card">
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('status-error'))
                    <div class="alert alert-danger">{{ session('status-error') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="payment-plan-ro-list" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center !important;">TS No</th>
                                <th style="text-align: center !important;">Inv No</th>
                                <th style="text-align: center !important;">RO No</th>
                                <th style="text-align: center !important;">Supplier</th>
                                <th style="text-align: center !important;">Tagihan ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;">Plan Date</th>
                                <th style="text-align: center !important;">Paid Date</th>
                                <th style="text-align: center !important;">Bayar ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;">PV No</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <hr>
        <div class="card" style="margin-top: 15px;">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-12">
                        <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                    </div>
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
        $('#payment-plan-ro-list').DataTable({
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
                {
                    data: 'tagihan_supplier_no',
                    name: 'tx_tagihan_suppliers.tagihan_supplier_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'receipt_orders_invoices',
                    name: 'receipt_orders_invoices',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'receipt_orders_no',
                    name: 'receipt_orders_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'supplier_name',
                    name: 'supplier_name',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'grandtotal_price',
                    name: 'tx_tagihan_suppliers.grandtotal_price',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'plan_date',
                    name: 'plan_date',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'paid_date',
                    name: 'paid_date',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'paid_value',
                    name: 'paid_value',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'pv_no',
                    name: 'pv_no',
                    orderable: false,
                }
            ],
            columnDefs: [
                {
                    targets: [0,2,5,6,8],
                    className: 'text-center',
                },
                {
                    targets: [1,3],
                    className: 'text-left',
                },
                {
                    targets: [7],
                    className: 'text-right',
                },
                {
                    targets: [4],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                }
            ],
        });

        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
