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
                                <th>PR No</th>
                                <th>Inv No</th>
                                <th>RO No</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Total Retur ({{ $qCurrency->string_val }})</th>
                                <th>Created by</th>
                                <th>Approvel Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($returs as $o)
                            <tr>
                                <td>
                                    @if (is_null($o->approved_by) && is_null($o->canceled_by))
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">{{ $o->purchase_retur_no }}</a>
                                    @else
                                        {{ $o->purchase_retur_no }}
                                    @endif
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
                                <td style="text-align: right;">{{ number_format($o->total_retur,0,'.',',') }}</td>
                                <td>{{ $o->createdBy->userDetail->initial }}</td>
                                <td>
                                    @if(is_null($o->approved_by) && is_null($o->canceled_by))
                                        {{ 'Waiting for Approval' }}
                                    @endif
                                    @if(!is_null($o->approved_by))
                                        {{ 'Approved by '.$o->approvedBy->name.' at '.date_format(date_create($o->approved_at), 'd M Y H:i:s') }}
                                    @endif
                                    @if(!is_null($o->canceled_by))
                                        {{ 'Rejected by '.$o->canceledBy->name.' at '.date_format(date_create($o->canceled_at), 'd M Y H:i:s') }}
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
                                <th>Purchase Retur No</th>
                                <th>Inv No</th>
                                <th>Receipt Order No</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Total Retur</th>
                                <th>Created by</th>
                                <th>Approvel Status</th>
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
