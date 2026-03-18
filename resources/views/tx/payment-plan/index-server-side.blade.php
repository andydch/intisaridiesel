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
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <div class="col-12">
            <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
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
                    <table class="table table-striped table-bordered" id="payment-plan-list" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center !important;width:10%">Period</th>
                                <th style="text-align: center !important;width:10%">Bank</th>
                                <th style="text-align: center !important;width:60%">Saldo Awal ({{ $qCurrency->string_val }})</th>
                                <th style="text-align: center !important;width:10%">Action</th>
                                <th style="text-align: center !important;width:10%">Status</th>
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
        $('#payment-plan-list').DataTable({
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
                    data: 'payment_month_f',
                    name: 'payment_month_f',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'coa_name',
                    name: 'coa_name',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'beginning_balance_num',
                    name: 'tx_payment_plans.beginning_balance',
                    orderable: false,
                    searchable: true,
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
                    targets: [0,1,3,4],
                    className: 'text-center',
                },
                {
                    targets: [2],
                    className: 'text-right',
                }
            ],
        });
    });
</script>
@endsection
