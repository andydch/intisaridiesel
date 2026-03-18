@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
    .part-id {
        font-size: large !important;
        font-weight: 700;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.$queryDelivery->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">FK No</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label part-id">{{ $queryDelivery->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="fp_no" class="col-sm-3 col-form-label">FP No*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('fp_no') is-invalid @enderror" id="fp_no" name="fp_no">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $fp_no = (old('fp_no')?old('fp_no'):$queryDelivery->tax_invoice_id);
                                                        @endphp
                                                        @foreach ($qFP as $wT)
                                                            <option @if($fp_no==$wT->id){{ 'selected' }}@endif value="{{ $wT->id }}">{{ $wT->fp_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('fp_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">FP Date</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ date_format(date_create($queryDelivery->delivery_order_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Total</label>
                                                <label for="" class="col-sm-3 col-form-label">{{ $qCurrency->string_val.number_format($queryFK->total_price,0,'.',',') }}</label>
                                            </div>
                                            @if ($queryFK->is_vat=='Y')
                                                <div class="row mb-3">
                                                    <label for="" class="col-sm-3 col-form-label">VAT</label>
                                                    <label for="" class="col-sm-3 col-form-label">{{ $qCurrency->string_val.number_format($queryFK->total_price*$vat/100,0,'.',',') }}</label>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="" class="col-sm-3 col-form-label">Grand Total</label>
                                                    <label for="" class="col-sm-3 col-form-label">{{ $qCurrency->string_val.number_format($queryFK->total_price+($queryFK->total_price*$vat/100),0,'.',',') }}</label>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="sales_order_no" class="col-sm-3 col-form-label">Sales Order No</label>
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 47%;text-align:center;">Sales Order No</th>
                                                                        <th scope="col" style="width: 47%;text-align:center;">Cust Doc No</th>
                                                                    </tr>
                                                                    @php
                                                                        $sales_orders_no = explode(',',$queryDelivery->sales_order_no_all);
                                                                        $iRow = 0;
                                                                    @endphp
                                                                    @foreach ($sales_orders_no as $row_so)
                                                                        @if($row_so!='')
                                                                            @php
                                                                                $qSO = \App\Models\Tx_sales_order::where('sales_order_no','=',$row_so)
                                                                                ->first();
                                                                            @endphp
                                                                            <tr>
                                                                                <td style="text-align: right;">{{ $iRow+1 }}</td>
                                                                                <td>{{ $row_so }}</td>
                                                                                <td>{{ (($qSO)?$qSO->customer_doc_no:'') }}</td>
                                                                            </tr>
                                                                            @php
                                                                                $iRow += 1;
                                                                            @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </thead>
                                                                <tbody id="new-row-so"></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save" class="btn btn-light px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-light px-5" value="Cancel">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uriOld) }}";
        });
        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
