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
        <form name="form_del" id="form-del" action="{{ url('/del_stocktransfer') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="orderId" id="orderId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Cancel</a> --}}
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
                        <table class="table table-striped table-bordered" id="recipt-order" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SM No</th>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Created By</th>
                                    <th>Received by</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                    {{-- <th>Cancel</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($qStockTransfer as $o)
                                    <tr>
                                        <td>
                                            {{ $o->stock_transfer_no }}
                                            <input type="hidden" name="order_no{{ $i }}" id="order_no{{ $i }}" value="{{ $o->stock_transfer_no }}">
                                            <input type="hidden" name="order_id_{{ $i }}" id="order_id_{{ $i }}" value="{{ $o->stock_transfer_id }}">
                                        </td>
                                        <td>{{ date_format(date_create($o->stock_transfer_date), 'd/m/Y') }}</td>
                                        <td>{{ $o->branch_from->name }}</td>
                                        <td>{{ $o->branch_to->name }}</td>
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>{{ (!is_null($o->receivedBy)?$o->receivedBy->userDetail->initial:'') }}</td>
                                        <td>
                                            @if(!is_null($o->approved_by) && is_null($o->received_by) && $o->active=='Y')
                                                {{ 'Approved' }}
                                            @endif
                                            @if(!is_null($o->canceled_by) && is_null($o->received_by) && $o->active=='Y')
                                                {{ 'Rejected' }}
                                            @endif
                                            @if(!is_null($o->approved_by) && !is_null($o->received_by) && $o->active=='Y')
                                                {{ 'Received' }}
                                            @endif
                                            @if(is_null($o->approved_by) && is_null($o->canceled_by) && $o->active=='Y' && strpos($o->stock_transfer_no,'Draft')==0)
                                                {{ 'Waiting for Approval' }}
                                            @endif
                                            @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->stock_transfer_no,'Draft')>0)
                                                {{ 'Draft' }}
                                            @endif
                                            @if ($o->active=='N')
                                                {{ 'Canceled' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                                @if(is_null($o->approved_by))
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->stock_transfer_id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->stock_transfer_id) }}" style="text-decoration: underline;">View</a>
                                                @else
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->stock_transfer_id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-print/'.urlencode($o->stock_transfer_no)) }}" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-transfer-print/'.urlencode($o->stock_transfer_no)) }}" style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->stock_transfer_id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if ($o->created_by==Auth::user()->id && $o->active=='Y' && is_null($o->approved_by))
                                                <input type="checkbox" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
                                            @else
                                                @if ($o->active=='N')
                                                    {{ 'Cancel' }}
                                                @endif
                                                <input type="hidden" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
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
                                    <th>SM No</th>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Created By</th>
                                    <th>Received by</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                    {{-- <th>Cancel</th> --}}
                                </tr>
                            </tfoot>
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
        $('#recipt-order').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
