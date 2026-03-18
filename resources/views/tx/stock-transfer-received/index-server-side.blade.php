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
                    <table class="table table-striped table-bordered" id="stock-transfer-received" style="width:100%">
                        <thead>
                            <tr>
                                <th>Stock Transfer No</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Received By</th>
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
        // $('#stock-transfer-received').DataTable({
        //     'ordering': false,
        // });

        $('#stock-transfer-received').DataTable({
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
                    data: 'stock_transfer_link',
                    name: 'tx_stock_transfers.stock_transfer_no'
                },
                {
                    data: 'stock_transfer_date',
                    name: 'stock_transfer_date'
                },
                {
                    data: 'branch_from_name',
                    name: 'branch_from_name'
                },
                {
                    data: 'branch_to_name',
                    name: 'branch_to_name'
                },
                {
                    data: 'initial',
                    name: 'usr.initial'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'received_by_name',
                    name: 'received_by_name'
                }
            ],
        });
    });
</script>
@endsection
