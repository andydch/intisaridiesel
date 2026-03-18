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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder_per_inv.'/'.urlencode($ap)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="invoice_no" id="invoice_no" value="{{ $qInv->invoice_no }}">
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
                                <label for="" class="col-sm-2 col-form-label">INV/KW No: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qInv->invoice_no }}</label>
                                <label for="" class="col-sm-2 col-form-label">Due Date: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ date_format(date_create($qInv->due_date_payment),"d/m/Y") }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Customer: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qInv->invoice_no }}</label>
                                <label for="" class="col-sm-2 col-form-label">Tagihan: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qCurrency->string_val.' '.number_format($qInv->tagihan,0,",",".") }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Supplier: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qInv->supplier_identity }}</label>
                                <label for="" class="col-sm-2 col-form-label">Terima</label>
                                <label for="" class="col-sm-3 col-form-label">{{ $paid_val>0?$qCurrency->string_val.' '.number_format($paid_val,0,",","."):'' }}</label>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-5">
                                    <input type="hidden" name="plan_rows_total" id="plan_rows_total"
                                        value="@if(old('plan_rows_total')){{ old('plan_rows_total') }}@else{{ $qPaymentPlansRows }}@endif">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                            <tr style="width: 100%;">
                                                <th scope="col" style="width: 39%;">Plan Date</th>
                                                <th scope="col" style="width: 61%;">Plan Bayar</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-payment-plan">
                                            @if (old('plan_rows_total'))

                                            @else
                                                @php
                                                    $i = 0;
                                                @endphp
                                                @foreach ($qPaymentPlans as $qPP)
                                                    <tr id="row-{{ $i }}">
                                                        <td style="text-align: center;">
                                                            <label for="" class="col-form-label">{{ date_format(date_create($qPP->plan_date),"d/m/Y") }}</label>
                                                        </td>
                                                        <td style="text-align: right">
                                                            <label for="" class="col-form-label">{{ number_format($qPP->plan_accept,0,",",".") }}</label>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $i += 1;
                                                    @endphp
                                                @endforeach
                                            @endif
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
            </form>
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
