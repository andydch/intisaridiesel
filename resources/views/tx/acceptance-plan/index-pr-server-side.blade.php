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
        <h6 class="mb-0 text-uppercase">{{ $title.' - '.date_format(date_create($qPlans->acceptance_month),"F Y").' ('.$bank_name.')' }}</h6>
        <hr />
        {{-- <div class="col-12">
            <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
        </div> --}}
        <div class="card">
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('status-error'))
                    <div class="alert alert-danger">{{ session('status-error') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="payment-plan-pr-list" style="width:100%">
                        <thead>
                            <tr>
                                {{-- <th style="text-align: center !important;">Due Date</th> --}}
                                <th style="text-align: center !important;">INV/KW No</th>
                                <th style="text-align: center !important;">Customer</th>
                                <th style="text-align: center !important;">Tagihan ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;">Plan Date</th>
                                <th style="text-align: center !important;">Plan Terima ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;">Paid Date</th>
                                <th style="text-align: center !important;">Terima ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;">PAU No</th>
                                <th style="text-align: center !important;">Action</th>
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
        // {
        //     data: 'due_date_acceptance',
        //     name: 'due_date_acceptance',
        //     orderable: false,
        //     searchable: true,
        // },
        $('#payment-plan-pr-list').DataTable({
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
                    data: 'invoice_no',
                    name: 'v_invoices.invoice_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'customer_identity',
                    name: 'customer_identity',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'tagihan',
                    name: 'v_invoices.tagihan',
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
                    data: 'rencana_bayar_tagihan',
                    name: 'rencana_bayar_tagihan',
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
                    data: 'bayar_tagihan',
                    name: 'bayar_tagihan',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'payment_receipt_no',
                    name: 'payment_receipt_no',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                }
            ],
            columnDefs: [
                {
                    targets: [0,3,5,7,8],
                    className: 'text-center',
                    // render: function(data, type, row, meta) {
                    //     return moment(data).format('DD/MM/YYYY');
                    // },
                },
                {
                    targets: [1],
                    className: 'text-left',
                },
                {
                    targets: [2,4,6],
                    className: 'text-right',
                    // render: $.fn.dataTable.render.number(',','.',0,''),
                }
            ],
        });

        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
