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
        <form name="form_del" id="form-del" action="{{ url('/del_deliveryorder') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="orderId" id="orderId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/create') }}" style="margin-bottom: 15px;">Add New</a>
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
                                    <th>NP No</th>
                                    <th>SJ No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total Price ({{ $qCurrency->string_val }})</th>
                                    <th>Sales</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($orders as $o)
                                    <tr>
                                        <td>
                                            {{ $o->delivery_order_no }}
                                            <input type="hidden" name="order_no{{ $i }}" id="order_no{{ $i }}" value="{{ $o->delivery_order_no }}">
                                            <input type="hidden" name="order_id_{{ $i }}" id="order_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        @php
                                            $sales_order_no_all = '';
                                        @endphp
                                        @if (substr($o->sales_order_no_all,0,1)==',')
                                            @php
                                                $sales_order_no_all = substr($o->sales_order_no_all,1,strlen($o->sales_order_no_all));
                                            @endphp
                                        @endif
                                        {{-- <td>{{ (!is_null($o->tax_invoice)?$o->tax_invoice->fp_no:'') }}</td> --}}
                                        <td>{{ str_replace(",,",",",$sales_order_no_all) }}</td>
                                        <td>{{ date_format(date_create($o->delivery_order_date), 'd M Y') }}</td>
                                        <td>{{ !is_null($o->customer)?$o->customer->name:'' }}</td>
                                        <td style="text-align: right;">{{ number_format($o->total_price,0,'.',',') }}</td>
                                        <td>{{ $o->createdBy->name }}</td>
                                        <td>
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-do?fk='.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-do?fk='.$o->id) }}" target="_new" style="text-decoration: underline;">Download</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if($o->active=='Y' && strpos($o->delivery_order_no,'Draft')==0 && is_null($o->tax_invoice))
                                                {{ 'Created' }}
                                            @endif
                                            @if($o->active=='Y' && strpos($o->delivery_order_no,'Draft')==0 && !is_null($o->tax_invoice))
                                                {{ 'FP' }}
                                            @endif
                                            @if($o->active=='Y' && strpos($o->delivery_order_no,'Draft')>0)
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
                                    <th>NP No</th>
                                    <th>SJ No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total Price ({{ $qCurrency->string_val }})</th>
                                    <th>Sales</th>
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
        $('#recipt-order').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
