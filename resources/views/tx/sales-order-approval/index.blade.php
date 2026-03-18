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
                    <table class="table table-striped table-bordered" id="district" style="width:100%">
                        <thead>
                            <tr>
                                <th>SO No</th>
                                <th>SQ No</th>
                                <th>Customer Doc No</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total Before VAT ({{ $qCurrency->string_val }})</th>
                                <th>Sales</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($salesOrders as $o)
                                <tr>
                                    <td>{{ $o->sales_order_no }}</td>
                                    <td>{{ is_null($o->sales_quotation)?'':$o->sales_quotation->sales_quotation_no }}</td>
                                    <td>{{ $o->customer_doc_no }}</td>
                                    <td>{{ date_format(date_create($o->sales_order_date), 'd/m/Y') }}</td>
                                    <td>{{ $o->cust_name }}</td>
                                    <td style="text-align: right;">{{ number_format($o->total_before_vat,0,'.',',') }}</td>
                                    <td>{{ $o->createdBy->name }}</td>
                                    <td>
                                        @if ($o->active=='Y' && !strpos($o->sales_order_no,"Draft") && $o->need_approval=='Y')
                                            Need Approval
                                        @else
                                            {{ '---' }}
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->sales_order_id.'/edit') }}" style="text-decoration: underline;">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>SO No</th>
                                <th>SQ No</th>
                                <th>Customer Doc No</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Total Before VAT ({{ $qCurrency->string_val }})</th>
                                <th>Sales</th>
                                <th>Status</th>
                                <th>Action</th>
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
        $('#district').DataTable();
    });
</script>
@endsection
