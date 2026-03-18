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
        <form name="form_del" id="form-del" action="{{ url('/del_order') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            <input type="hidden" name="orderId" id="orderId">
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
                        <table class="table table-striped table-bordered" id="order-list" style="width:100%">
                            <thead>
                                <tr>
                                    <th>PO No</th>
                                    <th>PQ No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Price</th>
                                    <th>Created by</th>
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
                                            {{ $o->purchase_no }}
                                            <input type="hidden" name="order_no{{ $i }}" id="order_no{{ $i }}" value="{{ $o->purchase_no }}">
                                            <input type="hidden" name="order_id_{{ $i }}" id="order_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        <td>
                                            @if (!is_null($o->quotation))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/quotation/'.$o->quotation->id) }}" target="_new" style="text-decoration: underline;">{{ $o->quotation->quotation_no }}</a>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $allRO = '';
                                                $qRO = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->select(
                                                    'tx_ro.id',
                                                    'tx_ro.receipt_no',
                                                )
                                                ->where('tx_receipt_order_parts.po_mo_no','=',$o->purchase_no)
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->orderBy('tx_ro.created_at','ASC')
                                                ->groupBy('tx_ro.id')
                                                ->groupBy('tx_ro.receipt_no')
                                                ->get();
                                                if($qRO){
                                                    foreach ($qRO as $q) {
                                                        $allRO .= '<br/><a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/receipt-order/'.$q->id).'" target="_new" '.
                                                            'style="text-decoration: underline;">'.$q->receipt_no.'</a>';
                                                        // $allRO .= '<br/>'.$q->receipt_no;
                                                    }
                                                    echo substr($allRO,5,strlen($allRO));
                                                }
                                            @endphp
                                        </td>
                                        <td>{{ date_format(date_create($o->purchase_date), 'd/m/Y') }}</td>
                                        <td>{{ !is_null($o->supplier)?$o->supplier->name:'' }}</td>
                                        <td style="text-align: right;">{{ (!is_null($o->currency)?$o->currency->string_val:$qCurrency->string_val).number_format($o->total_price,0,".",",") }}</td>
                                        {{-- <td style="text-align: right;">{{ $qCurrency->string_val.number_format($o->total_price,0,".",",") }}</td> --}}
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @php
                                                $hasRO = false;
                                                $isReceived = false;
                                                $qROreceived = \App\Models\Tx_receipt_order_part::leftJoin('tx_receipt_orders AS tx_ro','tx_receipt_order_parts.receipt_order_id','=','tx_ro.id')
                                                ->select('tx_receipt_order_parts.id','tx_receipt_order_parts.is_partial_received')
                                                ->where('tx_receipt_order_parts.po_mo_no','=',$o->purchase_no)
                                                ->where('tx_receipt_order_parts.active','=','Y')
                                                ->where('tx_ro.receipt_no','NOT LIKE','%Draft%')
                                                ->where('tx_ro.active','=','Y')
                                                ->orderBy('tx_ro.updated_at','DESC')
                                                ->first();
                                                if($qROreceived){
                                                    $hasRO = true;
                                                    if($qROreceived->is_partial_received=='N'){
                                                        $isReceived = true;
                                                    }
                                                }
                                            @endphp
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y' && is_null($o->canceled_by))
                                                @if (($o->active=='Y' && is_null($o->approved_by) && !$hasRO && $is_director_now=='Y') ||
                                                    ($o->active=='Y' && !is_null($o->approved_by) && !$isReceived && $is_director_now=='Y') ||
                                                    (is_null($o->receipt_order) && is_null($o->approved_by) && is_null($o->canceled_by)))
                                                    {{-- hanya direksi dan pemilik dokumen PO yg boleh edit --}}
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                                    @if (!is_null($o->approved_by))
                                                         |
                                                    @endif
                                                @endif
                                                @if (!is_null($o->approved_by))
                                                    {{-- jika sudah approved boleh show & print --}}
                                                    {{-- <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> | --}}
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-order/'.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-order/'.$o->id) }}" style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            @if (strpos($o->purchase_no,"Draft")>0 && $o->active=='Y')
                                                {{ 'Draft' }}
                                            @else
                                                @if ($o->active=='Y' && $isReceived && $hasRO)
                                                    {{ 'Received' }}
                                                @endif
                                                @if ($o->active=='Y' && !$isReceived && $hasRO)
                                                    {{ 'Partial Received' }}
                                                @endif
                                                @if ($o->active=='Y' && is_null($o->approved_by) && !$hasRO)
                                                    {{ 'Created' }}
                                                @endif
                                                @if ($o->active=='Y' && !is_null($o->approved_by) && !$isReceived && !$hasRO)
                                                    {{ 'Approved' }}
                                                @endif
                                                @if ($o->active=='N')
                                                    {{ 'Canceled' }}
                                                @endif
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
                                    <th>PO No</th>
                                    <th>PQ No</th>
                                    <th>RO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Price</th>
                                    <th>Created by</th>
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
        $('#order-list').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
