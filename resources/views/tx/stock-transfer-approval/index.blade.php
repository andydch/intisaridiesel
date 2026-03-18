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
                                <th>Stock Transfer No</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($qStockTransfer as $o)
                                <tr>
                                    <td>
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->stock_transfer_id) }}" style="text-decoration: underline;">{{ $o->stock_transfer_no }}</a>
                                    </td>
                                    <td>{{ date_format(date_create($o->stock_transfer_date), 'd/m/Y') }}</td>
                                    <td>{{ $o->branch_from->name }}</td>
                                    <td>{{ $o->branch_to->name }}</td>
                                    <td>{{ $o->createdBy->name }}</td>
                                    <td>
                                        @if(!is_null($o->approved_by) && $o->active=='Y')
                                            {{ 'Approved at '.date_format(date_create($o->approved_at), 'd-M-Y H:i:s').' by '.$o->approvedBy->name }}
                                        @endif
                                        @if(!is_null($o->canceled_by) && $o->active=='Y')
                                            {{ 'Rejected at '.date_format(date_create($o->canceled_at), 'd-M-Y H:i:s').' by '.$o->canceledBy->name }}
                                        @endif
                                        @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->stock_transfer_no,'Draft')==0 && is_null($o->approved_by) && is_null($o->canceled_by))
                                            {{ 'Waiting for Approval' }}
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
                                <th>Stock Transfer No</th>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Created By</th>
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
@endsection
