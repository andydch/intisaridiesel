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
        <form name="form_del" id="form-del" action="{{ url('/del_stockdisassembly') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="orderId" id="orderId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Delete</a> --}}
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
                        <table class="table table-striped table-bordered" id="tx-stock-assembly" style="width:100%">
                            <thead>
                                <tr>
                                    <th>SD No</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    {{-- <th>Cancel</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($stocks as $o)
                                <tr>
                                    <td>
                                        {{ $o->stock_disassembly_no }}
                                        <input type="hidden" name="stock_no{{ $i }}" id="stock_no{{ $i }}" value="{{ $o->stock_disassembly_no }}">
                                    </td>
                                    <td>{{ date_format(date_create($o->stock_disassembly_date), 'd/m/Y') }}</td>
                                    <td>{{ $o->createdBy->userDetail->initial }}</td>
                                    <td>
                                        @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y'))
                                            @if ($o->active=='Y' && strpos($o->stock_disassembly_no,'Draft')>0)
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                            @endif
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @if ($o->active=='Y' && strpos($o->stock_disassembly_no,'Draft')==0)
                                                | <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly-print/'.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/stock-disassembly-print/'.$o->id) }}" target="_new" style="text-decoration: underline;">Download</a>
                                            @endif
                                        @else
                                            <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                        @endif
                                    </td>
                                    {{-- <td>
                                        @if ($o->created_by==Auth::user()->id && $o->active=='Y' && strpos($o->stock_disassembly_no,'Draft')>0)
                                            <input type="checkbox" name="delQuotation{{ $i }}" id="delQuotation{{ $i }}">
                                        @else
                                            @if ($o->active=='N')
                                                Cancel
                                            @endif
                                            <input type="hidden" name="delStockAsm{{ $i }}" id="delStockAsm{{ $i }}">
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
                                    <th>SD No</th>
                                    <th>Date</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    {{-- <th>Cancel</th> --}}
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
        $('#tx-stock-assembly').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
