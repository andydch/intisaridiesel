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
                                <th>PO No</th>
                                <th>PQ No</th>
                                <th>Order Date</th>
                                <th>Supplier</th>
                                <th>Total Price</th>
                                <th>Created by</th>
                                <th>Approval Status</th>
                                {{-- <th>Active</th>
                                <th>Last Updated At</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $o)
                            <tr>
                                <td>
                                    @if (!is_null($o->approved_status))
                                        {{ $o->purchase_no }}
                                    @else
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">{{ $o->purchase_no }}</a>
                                    @endif
                                </td>
                                <td>
                                    {{-- {{ $o->quotation?$o->quotation->quotation_no:'' }} --}}
                                    @if (!is_null($o->quotation))
                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/quotation/'.$o->quotation->id) }}" target="_new" style="text-decoration: underline;">{{ $o->quotation->quotation_no }}</a>
                                    @endif
                                </td>
                                <td>{{ date_format(date_create($o->purchase_date), 'd/m/Y') }}</td>
                                <td>{{ !is_null($o->supplier)?$o->supplier->name:'' }}</td>
                                <td style="text-align: right;">{{ (!is_null($o->currency)?$o->currency->string_val:'').number_format($o->total_price,0,".",",") }}</td>
                                <td>{{ $o->createdBy->userDetail->initial }}</td>
                                <td>
                                    @if (is_null($o->approved_status))
                                        {{ 'Waiting for approval' }}
                                    @else
                                        @if ($o->approved_status=='A')
                                            {{ 'Approved' }}
                                            @if (!is_null($o->approved_by_info))
                                                {!! ' by '.$o->approved_by_info->name.'<br />at '.date_format(date_create($o->approved_at), 'd M Y H:i:s') !!}
                                            @endif
                                        @else
                                            {{ 'Rejected' }}
                                            @if (!is_null($o->cancelBy))
                                                {!! ' by '.$o->cancelBy->name.'<br />at '.date_format(date_create($o->canceled_at), 'd M Y H:i:s') !!}
                                            @endif
                                        @endif

                                    @endif
                                </td>
                                {{-- <td>{{ $o->active }}</td>
                                <td>{{ date_format(date_create($o->updated_at), 'd M Y H:i:s') }}</td> --}}
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>PO No</th>
                                <th>PQ No</th>
                                <th>Order Date</th>
                                <th>Supplier</th>
                                <th>Total Price</th>
                                <th>Created by</th>
                                <th>Approval Status</th>
                                {{-- <th>Active</th>
                                <th>Last Updated At</th> --}}
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
        $('#district').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
