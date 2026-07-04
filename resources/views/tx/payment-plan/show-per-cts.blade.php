@extends('layouts.app')

@section('style')
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            {{-- <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder_per_inv.'/'.urlencode($ap)) }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
                @csrf
                @method('PUT')
                <input type="hidden" name="tagihan_supplier_no" id="tagihan_supplier_no" value="{{ $qCts->tagihan_supplier_no }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Tagihan Supplier No: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qCts->tagihan_supplier_no }}</label>
                                <label for="" class="col-sm-2 col-form-label">Due Date: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ date_format(date_create($qCts->plan_date),"d/m/Y") }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Supplier: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qCts->supplier->supplier_code.' - '.$qCts->supplier->entity_type->title_ind.' '.$qCts->supplier->name }}</label>
                                <label for="" class="col-sm-2 col-form-label">Tagihan: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qCurrency->string_val.' '.number_format($qCts->plan_pay,0,",",".") }}</label>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">&nbsp;</label>
                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                <label for="" class="col-sm-2 col-form-label">Terima</label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qCts->is_pv_approved=='Y'>0?$qCurrency->string_val.' '.number_format($qCts->actual_payment,0,",","."):'' }}</label>
                            </div> --}}
                            @php
                                $qCtsD = \App\Models\Tx_payment_plan_per_rc_order::where('tagihan_supplier_id', $qCts->tagihan_supplier_id)
                                ->where('active', 'Y')
                                ->orderBy('id', 'ASC')
                                ->get();
                            @endphp
                            <div class="row mb-3">
                                <div class="col-sm-8">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                            <tr style="width: 100%;">
                                                <th scope="col" style="width: 15%;">Plan Date</th>
                                                <th scope="col" style="width: 25%;">Plan Bayar ({{ $qCurrency->string_val }})</th>
                                                <th scope="col" style="width: 15%;">Actual Date</th>
                                                <th scope="col" style="width: 30%;">Actual Bayar ({{ $qCurrency->string_val }})</th>
                                                <th scope="col" style="width: 15%;">PV No</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-payment-plan">
                                            @foreach ($qCtsD as $qCD)
                                                <tr>
                                                    <td style="text-align: center;">
                                                        <label for="" class="col-form-label">{{ date_format(date_create($qCD->plan_date),"d/m/Y") }}</label>
                                                    </td>
                                                    <td style="text-align: right">
                                                        <label for="" class="col-form-label">{{ number_format($qCD->plan_pay,0,",",".") }}</label>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <label for="" class="col-form-label">{{ $qCD->is_pv_approved=='Y'?($qCD->actual_date?date_format(date_create($qCD->actual_date),"d/m/Y"):''):'' }}</label>
                                                    </td>
                                                    <td style="text-align: right">
                                                        <label for="" class="col-form-label">{{ $qCD->is_pv_approved=='Y'?($qCD->actual_payment?number_format($qCD->actual_payment,0,",","."):''):'' }}</label>
                                                    </td>
                                                    <td style="text-align: right">
                                                        <label for="" class="col-form-label">{{ $qCD->payment_voucher_no }}</label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            {{-- </form> --}}
        </div>
    </div>
</div>
<!--end row-->
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$ap) }}";
        });
    });
</script>
@endsection
