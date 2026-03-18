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
        <form name="form_del" id="form-del" action="{{ url('/del_sales_quotation') }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                            <thead>
                                <tr>
                                    <th style="width: 10%;">SQ No</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 25%;">Customer</th>
                                    <th style="width: 10%;">Sales</th>
                                    <th style="width: 10%;">Action</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($quotations as $m)
                                    <tr>
                                        <td>
                                            {{ $m->sales_quotation_no }}
                                            <input type="hidden" name="quotation_no{{ $i }}" id="quotation_no{{ $i }}" value="{{ $m->sales_quotation_no }}">
                                            <input type="hidden" name="quotation_id_{{ $i }}" id="quotation_id_{{ $i }}" value="{{ $m->sales_quo_id }}">
                                        </td>
                                        <td>{{ date_format(date_create($m->sales_quotation_date), 'd/m/Y') }}</td>
                                        <td>{{ (!is_null($m->customer)?$m->customer->entity_type->title_ind.' '.$m->customer->name:'') }}</td>
                                        <td>{{ $m->createdBy->name }}</td>
                                        <td>
                                            @if (($m->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') &&
                                                $m->active=='Y' && is_null($m->sales_order))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->sales_quo_id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->sales_quo_id) }}" style="text-decoration: underline;">View</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$m->sales_quo_id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-quotation?pq='.$m->sales_quo_id) }}" style="text-decoration: underline;">Download</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->sales_quo_id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($m->active=='N')
                                                Canceled
                                            @endif
                                            @if ($m->active=='Y' && strpos($m->sales_quotation_no,"Draft")>0)
                                                Draft
                                            @endif
                                            @if ($m->active=='Y' && !strpos($m->sales_quotation_no,"Draft"))
                                                Created
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if ($m->created_by==Auth::user()->id && $m->active=='Y' && is_null($m->sales_order))
                                                <input type="checkbox" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
                                            @else
                                                @if ($m->active=='N')
                                                    Cancel
                                                @endif
                                                <input type="hidden" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
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
                                    <th>SQ No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Sales</th>
                                    <th>Action</th>
                                    <th>Status</th>
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
        $('#QuotationPart').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
