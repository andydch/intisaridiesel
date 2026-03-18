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
        <form name="form_del" id="form-del" action="{{ url('/del_order') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="orderId" id="orderId">
            <div class="col-12">
                <a class="btn btn-primary px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Cancel</a> --}}
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
                        <table class="table table-striped table-bordered" id="order-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>PO No</th>
                                    <th>PQ No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th style="text-align:left;">Total Price ({{ $qCurrency->string_val }})</th>
                                    <th>Created by</th>
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
        // $('#order-list').DataTable({
        //     "ordering": false,
        // });

        $('#order-list').DataTable({
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
                    name: 'purchase_no'
                },
                {
                    data: 'quotation_no',
                    name: 'quotation_no'
                },
                {
                    data: 'receipt_order_no',
                    name: 'receipt_order_no'
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
                    data: 'total_price',
                    name: 'total_price',
                    orderable: false,
                    searchable: false,
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
                    targets: [5],
                    className: 'text-right',
                    render: $.fn.dataTable.render.number(',','.',0,''),
                },
                {
                    targets: [7,8],
                    className: 'text-center'
                }
            ],
        });
    });
</script>
@endsection
