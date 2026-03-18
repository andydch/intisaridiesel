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
                                <th style="width: 10%;">Invoice No</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 25%;">Customer</th>
                                <th style="width: 10%;">Total</th>
                                <th style="width: 10%;">Created By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $i = 0;
                            @endphp
                            @foreach ($query as $pV)
                                <tr>
                                    <td>
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($pV->invoice_no)) }}" style="text-decoration: underline;">{{ $pV->invoice_no }}</a>
                                    </td>
                                    <td>{{ date_format(date_create($pV->invoice_date), 'd/m/Y') }}</td>
                                    <td>{{ !is_null($pV->customer)?$pV->customer->name:'' }}</td>
                                    <td style="text-align: right;">{{ $qCurrency->string_val.number_format($pV->do_total,0,'.',',') }}</td>
                                    <td>{{ $pV->createdBy->name }}</td>
                                    <td>
                                        @if(!is_null($pV->approved_by) && is_null($pV->canceled_by) && $pV->active=='Y')
                                            {{ 'Approved at '.date_format(date_create($pV->approved_at), 'd M Y H:i:s').' by '.$pV->approvedBy->name }}
                                        @endif
                                        @if(!is_null($pV->canceled_by) && is_null($pV->approved_by) && $pV->active=='Y')
                                            {{ 'Rejected at '.date_format(date_create($pV->canceled_at), 'd M Y H:i:s').' by '.$pV->canceledBy->name }}
                                        @endif
                                        @if($pV->active=='Y' && strpos($pV->invoice_no,'Draft')==0 && is_null($pV->approved_by) && is_null($pV->canceled_by))
                                            {{ 'Waiting for Approval' }}
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
                                <th style="width: 10%;">Invoice No</th>
                                <th style="width: 10%;">Date</th>
                                <th style="width: 25%;">Customer</th>
                                <th style="width: 10%;">Total</th>
                                <th style="width: 10%;">Created By</th>
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
        $('#recipt-order').DataTable({
            'ordering':false,
        });
    });
</script>
@endsection
