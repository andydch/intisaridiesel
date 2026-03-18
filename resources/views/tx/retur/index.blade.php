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
        <form name="form_del" id="form-del" action="{{ url('/del_notaretur') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="returId" id="returId"> --}}
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
                        <table class="table table-striped table-bordered" id="nota-retur" style="width:100%">
                            <thead>
                                <tr>
                                    <th>RE No</th>
                                    <th>NP No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total ({{ $qCurrency->string_val }})</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($returs as $o)
                                    <tr>
                                        <td>
                                            {{ $o->nota_retur_no }}
                                            <input type="hidden" name="retur_no{{ $i }}" id="retur_no{{ $i }}" value="{{ $o->nota_retur_no }}">
                                            <input type="hidden" name="retur_id_{{ $i }}" id="retur_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        {{-- <td>{{ $o->invoice->invoice_no }}</td> --}}
                                        <td>{{ $o->delivery_order->delivery_order_no }}</td>
                                        <td>{{ date_format(date_create($o->nota_retur_date), 'd/m/Y') }}</td>
                                        <td>{{ !is_null($o->customer)?$o->customer->name:'' }}</td>
                                        <td style="text-align: right;">{{ number_format($o->total_retur,0,'.',',') }}</td>
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @if(!is_null($o->approved_by) && $o->active=='Y')
                                                {{ 'Approved' }}
                                            @endif
                                            @if(!is_null($o->canceled_by) && $o->active=='Y')
                                                {{ 'Rejected' }}
                                            @endif
                                            @if(is_null($o->approved_by) && is_null($o->canceled_by) && $o->active=='Y' && strpos($o->nota_retur_no,'Draft')==0)
                                                {{ 'Waiting for Approval' }}
                                            @endif
                                            @if(is_null($o->approved_by) && is_null($o->canceled_by) && $o->active=='Y' && strpos($o->nota_retur_no,'Draft')>0)
                                                {{ 'Draft' }}
                                            @endif
                                            @if ($o->active=='N')
                                                Canceled
                                            @endif
                                        </td>
                                        <td>
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                                @if(is_null($o->approved_by))
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($o->nota_retur_no).'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($o->nota_retur_no)) }}" style="text-decoration: underline;">View</a>
                                                @else
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($o->nota_retur_no)) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-retur/'.urlencode($o->nota_retur_no)) }}" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-retur/'.urlencode($o->nota_retur_no)) }}" style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($o->nota_retur_no)) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if ($o->created_by==Auth::user()->id && $o->active=='Y' && is_null($o->approved_by))
                                                <input type="checkbox" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
                                            @else
                                                @if(!is_null($o->approved_by) && $o->active=='Y')
                                                    {{ 'Approved' }}
                                                @endif
                                                @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->nota_retur_no,'Draft')==0)
                                                    {{ 'Created' }}
                                                @endif
                                                @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->nota_retur_no,'Draft')>0)
                                                    {{ 'Draft' }}
                                                @endif
                                                @if ($o->active=='N')
                                                    Cancel
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
                                    <th>RE No</th>
                                    {{-- <th>Inv No</th> --}}
                                    <th>NP No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total ({{ $qCurrency->string_val }})</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                    {{-- <th>Del</th> --}}
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
        $('#nota-retur').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
