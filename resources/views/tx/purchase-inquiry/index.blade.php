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
                    <table class="table table-striped table-bordered" id="PurchaseInquiryList" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 15%;">PI No</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 40%;">Supplier</th>
                                <th style="width: 15%;">Created By</th>
                                <th style="width: 10%;">Action</th>
                                <th style="width: 10%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($inquiries as $m)
                                <tr>
                                    <td>
                                        {{ $m->purchase_inquiry_no }}
                                        <input type="hidden" name="purchase_inquiry_no{{ $i }}" id="purchase_inquiry_no{{ $i }}" value="{{ $m->purchase_inquiry_no }}">
                                        <input type="hidden" name="purchase_inquiry_id_{{ $i }}" id="purchase_inquiry_id_{{ $i }}" value="{{ urlencode($m->slug) }}">
                                    </td>
                                    <td>{{ date_format(date_create($m->purchase_inquiry_date), 'd/m/Y') }}</td>
                                    <td>{{ !is_null($m->supplier)?$m->supplier->name:'' }}</td>
                                    <td>{{ $m->createdBy->userDetail->initial }}</td>
                                    <td>
                                        @if (($m->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $m->active=='Y')
                                            @if (strpos($m->purchase_inquiry_no,"Draft")==0 && $m->active=='Y')
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($m->slug).'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($m->slug)) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-inquiry/'.urlencode($m->slug)) }}" style="text-decoration: underline;" target="_new">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-inquiry/'.urlencode($m->slug)) }}" style="text-decoration: underline;">Download</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($m->slug).'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($m->slug)) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        @else
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($m->slug)) }}" style="text-decoration: underline;">View</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if (strpos($m->purchase_inquiry_no,"Draft")>0 && $m->active=='Y')
                                            {{ 'Draft' }}
                                        @endif
                                        @if (strpos($m->purchase_inquiry_no,"Draft")==0 && $m->active=='Y')
                                            {{ 'Created' }}
                                        @endif
                                        @if ($m->active=='N')
                                            {{ 'Cancel' }}
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
                                <th>PI No</th>
                                <th>Date</th>
                                <th>Supplier</th>
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
        $('#PurchaseInquiryList').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
