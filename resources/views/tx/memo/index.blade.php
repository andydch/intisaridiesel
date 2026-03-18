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
        <form name="form_del" id="form-del" action="{{ url('/del_memo') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="memoId" id="memoId">
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
                        <table class="table table-striped table-bordered" id="MemoPart" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">MO No</th>
                                    <th style="width: 10%;">RO No</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 25%;">Supplier</th>
                                    <th style="width: 10%;">Total Price</th>
                                    <th style="width: 15%;">Created By</th>
                                    <th style="width: 10%;">Action</th>
                                    <th style="width: 10%;">Status</th>
                                    {{-- <th style="width: 5%;">Cancel</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($memos as $m)
                                    <tr>
                                        <td>
                                            {{ $m->memo_no }}
                                            <input type="hidden" name="memo_no{{ $i }}" id="memo_no{{ $i }}" value="{{ $m->memo_no }}">
                                            <input type="hidden" name="memo_id_{{ $i }}" id="memo_id_{{ $i }}" value="{{ $m->id }}">
                                        </td>
                                        <td>
                                            @php
                                                $qRO = \App\Models\Tx_receipt_order::where('po_or_pm_no','LIKE','%'.$m->memo_no.'%')
                                                ->where('active','=','Y')
                                                ->first();
                                            @endphp
                                            @if ($qRO)
                                                {{-- {{ $qRO->receipt_no }} --}}
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$qRO->id) }}" target="_new" style="text-decoration: underline;">{{ $qRO->receipt_no }}</a>
                                            @endif
                                        </td>
                                        <td>{{ date_format(date_create($m->memo_date), 'd/m/Y') }}</td>
                                        <td>{{ !is_null($m->supplier)?$m->supplier->name:'' }}</td>
                                        <td style="text-align: right;">{{ $qCurrency->string_val.number_format($m->total_price,0,".",",") }}</td>
                                        <td>{{ $m->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @if (($m->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $m->active=='Y')
                                                @if(!$qRO)
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->id) }}" style="text-decoration: underline;">View</a> |
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$m->id) }}" style="text-decoration: underline;" target="_new">Print</a> |
                                                        <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$m->id) }}" style="text-decoration: underline;">Download</a>
                                                @else
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->id) }}" style="text-decoration: underline;">View</a> |
                                                        <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$m->id) }}" style="text-decoration: underline;" target="_new">Print</a> |
                                                        <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-memo/'.$m->id) }}" style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$m->id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if (strpos($m->memo_no,"Draft")>0 && $m->active=='Y')
                                                {{ 'Draft' }}
                                            @endif
                                            @if (strpos($m->memo_no,"Draft")==0 && $m->active=='Y' && $qRO)
                                                {{ 'Received' }}
                                            @endif
                                            @if (strpos($m->memo_no,"Draft")==0 && $m->active=='Y' && !$qRO)
                                                {{ 'Created' }}
                                            @endif
                                            @if ($m->active=='N')
                                                {{ 'Cancel' }}
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @if ($m->created_by==Auth::user()->id && $m->active=='Y' && is_null($m->receipt_order))
                                                <input type="checkbox" name="delMemo{{ $i }}" id="delMemo{{ $i }}">
                                            @else
                                                <input type="hidden" name="delMemo{{ $i }}" id="delMemo{{ $i }}">
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
                                    <th>MO No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Price</th>
                                    <th>Created By</th>
                                    <th>Action</th>
                                    <th>Status</th>
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
        $('#MemoPart').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
