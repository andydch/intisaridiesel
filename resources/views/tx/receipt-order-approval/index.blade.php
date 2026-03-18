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
                                <th>Order No</th>
                                <th>Inv No</th>
                                <th>Order Date</th>
                                <th>Supplier</th>
                                <th>Total Price</th>
                                <th>Created by</th>
                                <th>Approvel Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($orders as $o)
                            <tr>
                                @php
                                    $waiting_for_approval = 'N';
                                    $queryApproval = \App\Models\Tx_receipt_order_part::where([
                                        'receipt_order_id' => $o->id,
                                        'is_partial_received' => 'Y',
                                        'active' => 'Y',
                                    ])
                                    ->get();
                                    if($queryApproval){
                                        $waiting_for_approval = 'Y';
                                    }
                                @endphp
                                <td>
                                    @if(count($queryApproval)==0 && is_null($o->approved_by) && is_null($o->canceled_by))
                                        {{ $o->receipt_no }}
                                    @else
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">{{ $o->receipt_no }}</a>
                                    @endif
                                </td>
                                <td>{{ $o->invoice_no }}</td>
                                <td>{{ date_format(date_create($o->receipt_date), 'd/m/Y') }}</td>
                                <td>{{ $o->supplier->name }}</td>
                                <td style="text-align: right;">{{ number_format($o->total_price,0,'.',',') }}</td>
                                <td>{{ $o->createdBy->name }}</td>
                                <td>
                                    @if(count($queryApproval)>0 && is_null($o->approved_by))
                                        {{ 'Waiting for Approval' }}
                                    @endif
                                    @if(count($queryApproval)==0 && is_null($o->approved_by) && is_null($o->canceled_by))
                                        {{ 'Received' }}
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
                                <th>Order No</th>
                                <th>Inv No</th>
                                <th>Order Date</th>
                                <th>Supplier</th>
                                <th>Total Price</th>
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
