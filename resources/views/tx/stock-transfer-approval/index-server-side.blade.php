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
                    <table class="table table-striped table-bordered" id="stock-transfer-approval" style="width:100%">
                        <thead>
                            <tr>
                                <th style="text-align: center;">Stock Transfer No</th>
                                <th style="text-align: center;">Date</th>
                                <th style="text-align: center;">From</th>
                                <th style="text-align: center;">To</th>
                                <th style="text-align: center;">Created By</th>
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
        $('#stock-transfer-approval').DataTable({
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
                { // mengambil & menampilkan kolom sesuai tabel database
                    data: 'stock_transfer_no',
                    name: 'tx_stock_transfers.stock_transfer_no',
                    orderable: false,
                    searchable: true,
                },
                {
                    data: 'stock_transfer_date',
                    name: 'stock_transfer_date'
                },
                {
                    data: 'branch_from_name',
                    name: 'msb_from.name'
                },
                {
                    data: 'branch_to_name',
                    name: 'msb_to.name'
                },
                {
                    data: 'initial',
                    name: 'usr.initial'
                },
                {
                    data: 'stock_transfer_no_wlink',
                    name: 'stock_transfer_no_wlink',
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
                    targets: [4,5,6],
                    className: 'text-center',
                }
            ],
        });
    });
</script>
@endsection
