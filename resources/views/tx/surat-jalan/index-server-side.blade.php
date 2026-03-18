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
            {{-- <a id="btn-del-row" class="btn btn-danger px-5" style="margin-bottom: 15px;">Delete</a> --}}
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
                    <table class="table table-striped table-bordered" id="surat-jalan-list" style="width:100%">
                        <thead>
                            <tr>
                                <th>SJ No</th>
                                <th>SQ No</th>
                                <th>Customer Doc No</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th style="text-align: left !important;">Total Price({{ $qCurrency->string_val }})</th>
                                <th>Sales</th>
                                <th style="text-align: left !important;">Action</th>
                                <th style="text-align: left !important;">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end page wrapper -->

<!-- Full screen modal -->
<div class="modal fade" id="print-info" aria-hidden="true" aria-labelledby="print-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            {{-- <div class="modal-header">
                <h1 class="modal-title fs-5" id="print-info-title">Print Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> --}}
            <div class="modal-body">
                <input type="hidden" name="print-id" id="print-id">
                <p id="msg-modal" style="text-align: center"></p>
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to first</button>
            </div> --}}
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    function printDoc(i,print_type){
        $('#print-id').val(i);
        let downloadLInk = 
            '<a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=1a&p='+print_type+'" target="_new" class="btn btn-light">No Number With Price</a>&nbsp;'+
            '<a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=2a&p='+print_type+'" target="_new" class="btn btn-light">No Number No Price</a>'+
            '<a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=1&p='+print_type+'" target="_new" class="btn btn-light">With Price</a>&nbsp;'+
            '<a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=2&p='+print_type+'" target="_new" class="btn btn-light">No Price</a>';
        if(print_type===2){
            dl = 'download=""';
            downloadLInk = 
                '<a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=3a&p='+print_type+'" target="_new" class="btn btn-light">No Number With Price</a>&nbsp;'+
                '<a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=4a&p='+print_type+'" target="_new" class="btn btn-light">No Number No Price</a>'+
                '<a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=3&p='+print_type+'" target="_new" class="btn btn-light">With Price</a>&nbsp;'+
                '<a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=4&p='+print_type+'" target="_new" class="btn btn-light">No Price</a>';
        }
        $("#msg-modal").html(downloadLInk);
        $('#print-info').modal('show');
    }

    $(document).ready(function() {
        $('#surat-jalan-list').DataTable({
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
                    data: 'surat_jalan_no',
                    name: 'tx_surat_jalans.surat_jalan_no'
                },
                {
                    data: 'sales_quotation_no',
                    name: 'sales_quotation_no'
                },
                {
                    data: 'customer_doc_no',
                    name: 'tx_surat_jalans.customer_doc_no'
                },
                {
                    data: 'surat_jalan_date',
                    name: 'surat_jalan_date'
                },
                {
                    data: 'customer_name',
                    name: 'customer_name'
                },
                {
                    data: 'total',
                    name: 'tx_surat_jalans.total'
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
