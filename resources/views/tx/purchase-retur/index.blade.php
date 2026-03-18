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
        {{-- <form name="form_del" id="form-del" action="{{ url('/del_receiptretur') }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
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
                        <table class="table table-striped table-bordered" id="purchase-retur" style="width:100%">
                            <thead>
                                <tr>
                                    <th>PR No</th>
                                    <th>Inv No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Retur</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    {{-- <th>Del</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($returs as $o)
                                <tr>
                                    <td>
                                        {{ $o->purchase_retur_no }}
                                        <input type="hidden" name="retur_no{{ $i }}" id="retur_no{{ $i }}" value="{{ $o->purchase_retur_no }}">
                                        <input type="hidden" name="retur_id_{{ $i }}" id="retur_id_{{ $i }}" value="{{ $o->id }}">
                                    </td>
                                    <td>{{ (!is_null($o->receipt_order)?$o->receipt_order->invoice_no:'') }}</td>
                                    <td>
                                        {{-- {{ (!is_null($o->receipt_order)?$o->receipt_order->receipt_no:'') }} --}}
                                        @if (!is_null($o->receipt_order))
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$o->receipt_order->id) }}" target="_new" style="text-decoration: underline;">{{ $o->receipt_order->receipt_no }}</a>
                                        @endif
                                    </td>
                                    <td>{{ date_format(date_create($o->purchase_retur_date), 'd/m/Y') }}</td>
                                    <td>{{ !is_null($o->supplier)?$o->supplier->name:'' }}</td>
                                    <td style="text-align: right;">{{ $qCurrency->string_val.number_format($o->total_retur,0,'.',',') }}</td>
                                    <td>{{ $o->createdBy->userDetail->initial }}</td>
                                    <td>
                                        @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                            @if(is_null($o->approved_by))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-retur/'.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-purchase-retur/'.$o->id) }}" style="text-decoration: underline;">Download</a>
                                            @endif
                                        @else
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!is_null($o->approved_by) && $o->active=='Y')
                                            {{ 'Approved' }}
                                        @endif
                                        @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->purchase_retur_no,'Draft')==0)
                                            {{ 'Waiting for Approval' }}
                                            {{-- {{ 'Created' }} --}}
                                        @endif
                                        @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->purchase_retur_no,'Draft')>0)
                                            {{ 'Draft' }}
                                        @endif
                                        @if ($o->active=='N')
                                            Canceled
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if ($o->created_by==Auth::user()->id && $o->active=='Y' && is_null($o->approved_by))
                                            <input type="checkbox" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
                                        @else
                                            @if(!is_null($o->approved_by) && $o->active=='Y')
                                                {{ 'Approved' }}
                                            @endif
                                            @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->purchase_retur_no,'Draft')==0)
                                                {{ 'Created' }}
                                            @endif
                                            @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->purchase_retur_no,'Draft')>0)
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
                                    <th>PR No</th>
                                    <th>Inv No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Retur</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                    {{-- <th>Del</th> --}}
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
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#purchase-retur').DataTable({
            "ordering": false,
        });

        // $("#btn-del-row").click(function() {
        //     let returNo = '';
        //     for (i = 0; i < {{ $retursCount }}; i++) {
        //         if ($("#delOrder" + i).is(':checked')) {
        //             returNo += '- '+$("#retur_no" + i).val()+'\n';
        //         }
        //     }
        //     if(returNo!=''){
        //         let msg = 'The following Order Numbers will be canceled.\n'+returNo+'\nContinue?';
        //         if(!confirm(msg)){
        //             event.preventDefault();
        //         }else{
        //             let aId = '';
        //             for (i = 0; i < {{ $retursCount }}; i++) {
        //                 if ($("#delOrder" + i).is(':checked')) {
        //                     aId += $("#retur_id_" + i).val()+',';
        //                 }
        //             }
        //             if(aId!==''){
        //                 $("#returId").val(aId);
        //                 $("#form-del").submit();
        //             }
        //         }
        //     }
        // });
    });
</script>
@endsection
