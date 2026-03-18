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
        <div class="col-12">
            <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
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
                                <th>ADJ No</th>
                                <th>Date</th>
                                <th>Total ({{ $qCurrency->string_val }})</th>
                                <th>Created By</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($qStockAdj as $o)
                                <tr>
                                    <td>
                                        {{ $o->stock_adj_no }}
                                        <input type="hidden" name="stock_adj_no{{ $i }}" id="stock_adj_no{{ $i }}" value="{{ $o->stock_adj_no }}">
                                        <input type="hidden" name="stock_adj_id_{{ $i }}" id="stock_adj_id_{{ $i }}" value="{{ $o->id }}">
                                    </td>
                                    <td>{{ date_format(date_create($o->stock_adj_date), 'd M Y') }}</td>
                                    <td style="text-align: right;">{{ number_format($o->total,0,'.',',') }}</td>
                                    <td>{{ $o->createdBy->userDetail->initial }}</td>
                                    <td>
                                        @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                            @if (strpos($o->stock_adj_no,'Draft')>0)
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment-print/'.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-adjustment-print/'.$o->id) }}" target="_new" style="text-decoration: underline;">Download</a>
                                            @endif
                                        @else
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if($o->active=='Y' && strpos($o->stock_adj_no,'Draft')==0)
                                            {{ 'Created' }}
                                        @endif
                                        @if($o->active=='Y' && strpos($o->stock_adj_no,'Draft')>0)
                                            {{ 'Draft' }}
                                        @endif
                                        @if ($o->active=='N')
                                            Canceled
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $i += 1;
                                @endphp
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ADJ No</th>
                                <th>Date</th>
                                <th>Total ({{ $qCurrency->string_val }})</th>
                                <th>Created By</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
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
        $('#recipt-order').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
