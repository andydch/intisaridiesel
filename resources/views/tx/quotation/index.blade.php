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
        <form name="form_del" id="form-del" action="{{ url('/del_quotation') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="quotationId" id="quotationId">
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
                        <table class="table table-striped table-bordered" id="QuotationPart" style="width:100%">
                        {{-- data-order='[[ 0, "desc" ]]' data-page-length='25' --}}
                            <thead>
                                <tr>
                                    <th style="width: 10%;">PQ No</th>
                                    <th style="width: 10%;">PO No</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 45%;">Supplier</th>
                                    {{-- <th style="width: 15%;">Total</th> --}}
                                    <th style="width: 15%;">Created By</th>
                                    <th style="width: 15%;">Action</th>
                                    <th style="width: 5%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($quotations as $m)
                                    <tr>
                                        <td>
                                            {{ $m->quotation_no }}
                                            <input type="hidden" name="quotation_no{{ $i }}" id="quotation_no{{ $i }}" value="{{ $m->quotation_no }}">
                                            <input type="hidden" name="quotation_id_{{ $i }}" id="quotation_id_{{ $i }}" value="{{ $m->tx_id }}">
                                        </td>
                                        <td>
                                            {{-- {{ !is_null($m->purchase_order)?$m->purchase_order->purchase_no:'' }} --}}
                                            @if (!is_null($m->purchase_order))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/order/'.$m->purchase_order->id) }}" target="_new" style="text-decoration: underline;">{{ $m->purchase_order->purchase_no }}</a>
                                            @endif
                                        </td>
                                        <td>{{ date_format(date_create($m->quotation_date), 'd/m/Y') }}</td>
                                        <td>{{ $m->supplier_entity_type->title_ind.' '.$m->supplier->name }}</td>
                                        {{-- <td style="text-align: right;">{{ number_format($m->total_price,2,",",".") }}</td> --}}
                                        <td>{{ $m->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @if (($m->created_by==Auth::user()->id || $m->is_branch_head=='Y' || $m->is_director=='Y' ||
                                                $is_director_now=='Y' || $is_branch_head_now=='Y') && $m->active=='Y')
                                                @if(is_null($m->purchase_order))
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->tx_id.'/edit') }}"
                                                        style="text-decoration: underline;">Edit</a> | <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->tx_id) }}"
                                                        style="text-decoration: underline;">View</a> | <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-quotation?pq='.$m->tx_id) }}"
                                                        style="text-decoration: underline;" target="_new">Print</a> | <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-quotation?pq='.$m->tx_id) }}"
                                                        style="text-decoration: underline;">Download</a>
                                                @else
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->tx_id) }}"
                                                        style="text-decoration: underline;">View</a> | <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-quotation?pq='.$m->tx_id) }}"
                                                        style="text-decoration: underline;" target="_new">Print</a> | <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-quotation?pq='.$m->tx_id) }}"
                                                        style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->tx_id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if (strpos($m->quotation_no,"Draft")>0 && $m->active=='Y')
                                                {{ 'Draft' }}
                                            @endif
                                            @if (strpos($m->quotation_no,"Draft")==0 && $m->active=='Y')
                                                {{ 'Created' }}
                                            @endif
                                            @if ($m->active=='N')
                                                {{ 'Cancel' }}
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if (is_null($m->purchase_order) && $m->active=='Y')
                                                <input type="checkbox" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
                                            @else
                                            <input type="hidden" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
                                                @if ($m->active=='N')
                                                    Canceled
                                                @endif
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
                                    <th>PQ No</th>
                                    <th>PO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    {{-- <th>Total</th> --}}
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
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#QuotationPart').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
