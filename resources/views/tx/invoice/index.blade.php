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
        <form name="form_del" id="form-del" action="{{ url('/del_invoice') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="orderId" id="orderId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Cancel</a> --}}
            </div>
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="inventory-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>INV No</th>
                                    {{-- <th>Tax INV No</th>
                                    <th>DO No</th> --}}
                                    <th>Date</th>
                                    <th>Customer</th>
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
                                @foreach ($invoices as $o)
                                    <tr>
                                        <td>
                                            {{ $o->invoice_no }}
                                            <input type="hidden" name="invoice_no{{ $i }}" id="invoice_no{{ $i }}" value="{{ $o->invoice_no }}">
                                            <input type="hidden" name="invoice_id_{{ $i }}" id="invoice_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        {{-- <td>{{ $o->tax_invoice_no }}</td>
                                        <td>{{ (!is_null($o->delivery_order)?$o->delivery_order->delivery_order_no:'') }}</td> --}}
                                        <td>{{ date_format(date_create($o->invoice_date), 'd/m/Y') }}</td>
                                        <td>{{ !is_null($o->customer)?$o->customer->name:'' }}</td>
                                        <td style="text-align: right;">{{ number_format($o->do_total,0,'.',',') }}</td>
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                                @if(is_null($o->approved_by) && is_null($o->tax_invoice_no))
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="#" style="text-decoration: underline;" onclick="printDoc({{ $o->id }},1);">Print</a> |
                                                    <a download="" href="#" style="text-decoration: underline;" onclick="printDoc({{ $o->id }},2);">Download</a>
                                                @else
                                                    @if (!is_null($o->approved_by) && $is_director_now=='Y')
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/tax-inv/'.urlencode($o->invoice_no).'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/tax-inv/'.urlencode($o->invoice_no)) }}" style="text-decoration: underline;">View</a>
                                                    @else
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                                    @endif
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- @if ($o->created_by==Auth::user()->id && $o->active=='Y' && strpos($o->invoice_no,'Draft')>0)
                                                <input type="checkbox" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
                                            @else
                                            @endif --}}
                                            @if($o->active=='Y' && strpos($o->invoice_no,'Draft')==0 && is_null($o->approved_by) && is_null($o->canceled_by))
                                                {{ 'Created' }}
                                            @endif
                                            @if($o->active=='Y' && strpos($o->invoice_no,'Draft')>0)
                                                {{ 'Draft' }}
                                            @endif
                                            @if($o->active=='Y' && !is_null($o->approved_by))
                                                {{ 'Approved' }}
                                            @endif
                                            {{-- @if($o->active=='Y' && !is_null($o->approved_by) && is_null($o->tax_invoice_no))
                                                {{ 'Approved' }}
                                            @endif --}}
                                            {{-- @if($o->active=='Y' && !is_null($o->approved_by) && !is_null($o->tax_invoice_no))
                                                {{ 'Tax Invoice' }}
                                            @endif --}}
                                            @if($o->active=='Y' && !is_null($o->canceled_by))
                                                {{ 'Canceled' }}
                                            @endif
                                            @if ($o->active=='N')
                                                Canceled
                                            @endif
                                            <input type="hidden" name="delOrder{{ $i }}" id="delOrder{{ $i }}">
                                        </td>
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>INV No</th>
                                    {{-- <th>Tax INV No</th>
                                    <th>DO No</th> --}}
                                    <th>Date</th>
                                    <th>Customer</th>
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
        </form>
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
        let downloadLInk = '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=1&p='+print_type+'" target="_new" class="btn btn-light">Permohonan Pembayaran</a>&nbsp;'+
            '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=2&p='+print_type+'" target="_new" class="btn btn-light">Tanda Terima</a>&nbsp;'+
            '<a '+dl+' href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/invoice-print?inv=') }}'+$('#print-id').val()+'&doc=3&p='+print_type+'" target="_new" class="btn btn-light">Kwitansi</a>';
        console.log(downloadLInk);
        $("#msg-modal").html(downloadLInk);
        $('#print-info').modal('show');
    }

    $(document).ready(function() {
        $('#inventory-list').DataTable({
            'ordering':false,
        });
    });
</script>
@endsection
