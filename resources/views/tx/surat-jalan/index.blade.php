@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
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
        {{-- <form name="form_del" id="form-del" action="{{ url('/del_salesorder') }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
            @csrf
            {{-- <input type="hidden" name="orderId" id="orderId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Delete</a> --}}
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
                        <table class="table table-striped table-bordered" id="tx-sales-order" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SJ No</th>
                                    <th>SQ No</th>
                                    <th>Customer Doc No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total Price({{ $qCurrency->string_val }})</th>
                                    <th>Sales</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    {{-- <th>Cancel</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($sjs as $o)
                                    <tr>
                                        <td>{{ $o->surat_jalan_no }}</td>
                                        <td>{{ is_null($o->sales_quotation)?'':$o->sales_quotation->sales_quotation_no }}</td>
                                        <td>{{ $o->customer_doc_no }}</td>
                                        <td>{{ date_format(date_create($o->surat_jalan_date), 'd/m/Y') }}</td>
                                        <td>{{ $o->cust_name }}</td>
                                        <td style="text-align: right;">{{ number_format($o->total,0,'.',',') }}</td>
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @php
                                                $FKonly = false;
                                            @endphp
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') &&
                                                $o->active=='Y' && is_null($o->delivery_order) &&
                                                ($o->need_approval=='N' || strpos($o->surat_jalan_no,"Draft")>0))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="#"style="text-decoration: underline;" onclick="printDoc({{ $o->id }},1);">Print</a> |
                                                    <a href="#"style="text-decoration: underline;" onclick="printDoc({{ $o->id }},2);">Download</a> ({{ $o->number_of_prints }})
                                                    {{-- <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj='.$o->id) }}" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj='.$o->id) }}" style="text-decoration: underline;">Download</a> | --}}
                                            @else
                                                @if ($o->need_approval=='Y')
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                                @else
                                                    @php
                                                        $FKonly = true;
                                                    @endphp
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="#"style="text-decoration: underline;" onclick="printDoc({{ $o->id }},1);">Print</a> |
                                                    <a href="#"style="text-decoration: underline;" onclick="printDoc({{ $o->id }},2);">Download</a> ({{ $o->number_of_prints }})
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($o->active=='N')
                                                Cancel
                                            @endif
                                            @if ($o->active=='Y' && strpos($o->surat_jalan_no,"Draft")>0)
                                                Draft
                                            @endif
                                            @if ($o->active=='Y' && !strpos($o->surat_jalan_no,"Draft") && $o->need_approval=='N' && is_null($o->approved_by)
                                                && is_null($o->delivery_order) && $o->number_of_prints==0)
                                                Create
                                            @endif
                                            @if ($o->active=='Y' && !is_null($o->delivery_order))
                                                NP
                                            @endif
                                            @if ($o->active=='Y' && !strpos($o->surat_jalan_no,"Draft") && $o->need_approval=='Y')
                                                Waiting for Approval
                                            @endif
                                            @if ($o->active=='Y' && !is_null($o->approved_by) && $o->number_of_prints==0)
                                                Approved
                                            @endif
                                            @if ($o->active=='Y' && $o->number_of_prints>0 && !$FKonly)
                                                Deliver
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if ($o->created_by==Auth::user()->id && $o->active=='Y' && is_null($o->delivery_order))
                                                <input type="checkbox" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
                                            @else
                                                @if ($o->active=='N')
                                                    Cancel
                                                @endif
                                                <input type="hidden" name="delSalesOrd{{ $i }}" id="delSalesOrd{{ $i }}">
                                            @endif
                                        </td> --}}
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>SJ No</th>
                                    <th>SQ No</th>
                                    <th>Customer Doc No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total Price</th>
                                    <th>Sales</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    {{-- <th>Cancel</th> --}}
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        {{-- </form> --}}
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
        let dl = '';
        if(print_type===2){
            dl = 'download=""';
        }
        $('#print-id').val(i);
        let downloadLInk = '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=1&p='+print_type+'" target="_new" class="btn btn-light">With Price</a>&nbsp;'+
            '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-surat-jalan?sj=') }}'+$('#print-id').val()+'&doc=2&p='+print_type+'" target="_new" class="btn btn-light">No Price</a>';
        console.log(downloadLInk);
        $("#msg-modal").html(downloadLInk);
        $('#print-info').modal('show');
    }

    $(document).ready(function() {
        $('#tx-sales-order').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
