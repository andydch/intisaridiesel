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
        <form name="form_del" id="form-del" action="{{ url('/del_preceipt') }}" method="POST" enctype="application/x-www-form-urlencoded">
            @csrf
            {{-- <input type="hidden" name="payment_receiptId" id="payment_receiptId"> --}}
            <div class="col-12">
                <a class="btn btn-light px-5" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/create') }}" style="margin-bottom: 15px;">Add New</a>
                {{-- <a id="btn-del-row" class="btn btn-light px-5" style="margin-bottom: 15px;">Cancel</a> --}}
            </div>
            <div class="card">
                <div class="card-body">
                    @if (session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
                    @if (session('status-error'))<div class="alert alert-danger">{{ session('status-error') }}</div>@endif
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="PaymentVoucherList" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 10%;">PA No</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 25%;">Customer</th>
                                    <th style="width: 10%;">Total({{ $qCurrency->string_val }})</th>
                                    <th style="width: 10%;">Created By</th>
                                    <th style="width: 10%;">Action</th>
                                    <th style="width: 10%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($paymentReceipts as $pV)
                                    <tr>
                                        <td>
                                            {{ $pV->payment_receipt_no }}
                                            <input type="hidden" name="payment_receipt_no{{ $i }}" id="payment_receipt_no{{ $i }}" value="{{ $pV->payment_receipt_no }}">
                                            <input type="hidden" name="payment_receipt_id_{{ $i }}" id="payment_receipt_id_{{ $i }}" value="{{ $pV->tx_payment_receipts_id }}">
                                        </td>
                                        <td>{{ date_format(date_create($pV->payment_date), 'd/m/Y') }}</td>
                                        <td>{{ $pV->customer_name }}</td>
                                        <td>{{ number_format($pV->payment_total,0,".",",") }}</td>
                                        <td>{{ $pV->created_by_name }}</td>
                                        <td>
                                            @if (($pV->created_by==Auth::user()->id || $is_director_now=='Y' || $is_branch_head_now=='Y') &&
                                                $pV->active=='Y' && is_null($pV->approved_by))
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($pV->payment_receipt_no).'/edit') }}" style="text-decoration: underline;">Edit</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($pV->payment_receipt_no)) }}" style="text-decoration: underline;">View</a> |
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-payment_receipt?pq='.urlencode($pV->payment_receipt_no)) }}" style="text-decoration: underline;">Print</a> |
                                                <a download="" href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/print-sales-payment_receipt?pq='.urlencode($pV->payment_receipt_no)) }}" style="text-decoration: underline;">Download</a>
                                            @else
                                                <a href="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.urlencode($pV->payment_receipt_no)) }}" style="text-decoration: underline;">View</a>
                                            @endif
                                        </td>
                                        <td>
                                            {{-- @if ($pV->created_by==Auth::user()->id && $pV->active=='Y' && is_null($pV->approved_by))
                                                <input type="checkbox" name="delPv{{ $i }}" id="delPv{{ $i }}">
                                            @else
                                                @if (strpos($pV->payment_receipt_no,"Draft")>0 && $pV->active=='Y')
                                                    {{ 'Draft' }}
                                                @endif
                                                @if ($pV->active=='Y' && is_null($pV->approved_by) && is_null($pV->canceled_by))
                                                    {{ 'Created' }}
                                                @endif
                                                @if ($pV->active=='Y' && !is_null($pV->approved_by))
                                                    {{ 'Approved' }}
                                                @endif
                                                @if ($pV->active=='N' || !is_null($pV->canceled_by))
                                                    {{ 'Canceled' }}
                                                @endif
                                                <input type="hidden" name="delPv{{ $i }}" id="delPv{{ $i }}">
                                            @endif --}}
                                            @if (strpos($pV->payment_receipt_no,"Draft")>0 && $pV->active=='Y')
                                                {{ 'Draft' }}
                                            @endif
                                            @if ($pV->active=='Y' && is_null($pV->approved_by) && is_null($pV->canceled_by))
                                                {{ 'Created' }}
                                            @endif
                                            @if ($pV->active=='Y' && !is_null($pV->approved_by))
                                                {{ 'Approved' }}
                                            @endif
                                            @if ($pV->active=='N' || !is_null($pV->canceled_by))
                                                {{ 'Canceled' }}
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
                                    <th>PA No</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Created By</th>
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
        $('#PaymentVoucherList').DataTable({
            'ordering': false,
        });
    });
</script>
@endsection
