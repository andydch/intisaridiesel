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
        <form name="form_del" id="form-del" action="{{ url('/del_deliveryorder') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/dl-faktur-pajak/create') }}" style="margin-bottom: 15px;">Download List Faktur</a>
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/upl-faktur-pajak/create') }}" style="margin-bottom: 15px;">Upload Faktur Pajak</a>
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
                        <table class="table table-striped table-bordered" id="faktur-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>FK No</th>
                                    <th>Created at</th>
                                    <th>FP No</th>
                                    <th>SO No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th style="text-align:left !important;">Total Price ({{ $qCurrency->string_val }})</th>
                                    <th>Sales</th>
                                    <th style="text-align:left !important;">Action</th>
                                    <th style="text-align:left !important;">Status</th>
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
        $('#faktur-list').DataTable({
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
                    data: 'delivery_order_no',
                    name: 'delivery_order_no'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'fp_no_w_prefiks',
                    name: 'tx_tax_invoices.fp_no',
                    orderable: false,
                    // searchable: false,
                },
                {
                    data: 'sales_order_no_all',
                    name: 'sales_order_no_all'
                },
                {
                    data: 'delivery_order_date',
                    name: 'delivery_order_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'total_price',
                    name: 'total_price',
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
