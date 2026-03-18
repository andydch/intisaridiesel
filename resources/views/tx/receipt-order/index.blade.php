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
        <form name="form_del" id="form-del" action="{{ url('/del_receiptorder') }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                        <table class="table table-striped table-bordered" id="receipt-order" style="width:100%">
                            <thead>
                                <tr>
                                    <th>RO No</th>
                                    <th>Inv No</th>
                                    <th>PO/MO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Price ({{ $qCurrency->string_val }})</th>
                                    <th>Created by</th>
                                    <th style="text-align: left !important;">Action</th>
                                    {{-- <th>Status</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($orders as $o)
                                    <tr>
                                        <td>
                                            {{ $o->receipt_no }}
                                            <input type="hidden" name="order_no{{ $i }}" id="order_no{{ $i }}" value="{{ $o->receipt_no }}">
                                            <input type="hidden" name="order_id_{{ $i }}" id="order_id_{{ $i }}" value="{{ $o->id }}">
                                        </td>
                                        <td>{{ $o->invoice_no }}</td>
                                        <td>
                                            @php
                                                $po_mo_all = explode(",",$o->po_or_pm_no);
                                            @endphp
                                            @foreach ($po_mo_all as $po_mo)
                                                @php
                                                    if($po_mo!=''){
                                                        if (strpos('x'.$po_mo,'MO')>=0){
                                                            $qMo = \App\Models\Tx_purchase_memo::where('memo_no','=',$po_mo)
                                                            ->first();
                                                            if($qMo){
                                                                echo '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/memo/'.$qMo->id).'" target="_new" style="text-decoration: underline;">'.$po_mo.'</a><br/>';
                                                            }
                                                        }
                                                        if (strpos('x'.$po_mo,'PO')>=0){
                                                            $qPo = \App\Models\Tx_purchase_order::where('purchase_no','=',$po_mo)
                                                            ->first();
                                                            if($qPo){
                                                                echo '<a href="'.url(ENV('TRANSACTION_FOLDER_NAME').'/order/'.$qPo->id).'" target="_new" style="text-decoration: underline;">'.$po_mo.'</a><br/>';
                                                            }
                                                        }
                                                    }
                                                @endphp
                                            @endforeach
                                        </td>
                                        <td>{{ date_format(date_create($o->receipt_date), 'd/m/Y') }}</td>
                                        <td>{{ $o->supplier->name }}</td>
                                        <td style="text-align: right;">{{ number_format($o->total_price,0,'.',',') }}</td>
                                        <td>{{ $o->createdBy->userDetail->initial }}</td>
                                        <td>
                                            @if (($o->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') && $o->active=='Y')
                                                @if(strpos("$o->receipt_no","Draft"))
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id.'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                                @else
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a> |
                                                    <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-receipt-order/'.$o->id) }}" target="_new" style="text-decoration: underline;">Print</a> |
                                                    <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-receipt-order/'.$o->id) }}" style="text-decoration: underline;">Download</a>
                                                @endif
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$o->id) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        {{-- <td>
                                            @php
                                                $part = \App\Models\Tx_receipt_order_part::where('receipt_order_id','=',$o->id)
                                                // ->where('qty','!=','qty_on_po')
                                                ->where('active','=','Y')
                                                ->first();
                                            @endphp
                                            @if($o->active=='Y' && strpos($o->receipt_no,'Draft')==0)
                                                @if ($part->qty!=$part->qty_on_po)
                                                    {{ 'Partial' }}
                                                @else
                                                    {{ 'Received' }}
                                                @endif
                                            @endif
                                            @if(is_null($o->approved_by) && $o->active=='Y' && strpos($o->receipt_no,'Draft')>0)
                                                {{ 'Draft' }}
                                            @endif
                                            @if ($o->active=='N')
                                                Canceled
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
                                    <th>RO No</th>
                                    <th>Inv No</th>
                                    <th>PO/MO No</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Total Price</th>
                                    <th>Created by</th>
                                    <th>Action</th>
                                    {{-- <th>Status</th> --}}
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
        $('#receipt-order').DataTable({
            "ordering": false,
        });
    });
</script>
@endsection
