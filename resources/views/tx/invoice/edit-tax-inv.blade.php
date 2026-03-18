@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/tax-inv/'.urlencode($qInv->invoice_no)) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                                <label for="" class="col-sm-3 col-form-label">Invoice No</label>
                                                <label for="" class="col-sm-9 col-form-label part-id">{{ $qInv->invoice_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="tax_invoice_no" class="col-sm-3 col-form-label">Tax Invoice No*</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control @error('tax_invoice_no') is-invalid @enderror"
                                                        maxlength="64" id="tax_invoice_no" name="tax_invoice_no" placeholder="Tax Invoice No"
                                                        value="@if(old('tax_invoice_no')){{ old('tax_invoice_no') }}@else{{ $qInv->tax_invoice_no }}@endif">
                                                    @error('tax_invoice_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="tax_invoice_date" class="col-sm-3 col-form-label">Tax Invoice Date*</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('tax_invoice_date') is-invalid @enderror"
                                                        maxlength="10" id="tax_invoice_date" name="tax_invoice_date" placeholder="Tax Invoice Date"
                                                        value="@if(old('tax_invoice_date')){{ old('tax_invoice_date') }}@else{{ $qInv->tax_invoice_date }}@endif">
                                                    @error('tax_invoice_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            {{-- <div class="row mb-3">
                                                <label for="invoice_date" class="col-sm-3 col-form-label">Date*</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('invoice_date') is-invalid @enderror"
                                                        maxlength="10" id="invoice_date" name="invoice_date" placeholder="Date"
                                                        value="@if(old('invoice_date')){{ old('invoice_date') }}@else{{ $qInv->invoice_date }}@endif">
                                                    @error('invoice_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="expired_invoice_date" class="col-sm-3 col-form-label">Expired Date*</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('expired_invoice_date') is-invalid @enderror"
                                                        maxlength="10" id="expired_invoice_date" name="expired_invoice_date" placeholder="Expired Date"
                                                        value="@if(old('expired_invoice_date')){{ old('expired_invoice_date') }}@else{{ $qInv->invoice_expired_date }}@endif">
                                                    @error('expired_invoice_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@else{{ $qInv->remark }}@endif</textarea>
                                                    @error('remark')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            {{-- <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->customer->name }}</label>
                                            </div> --}}
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">DO No</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qInv->delivery_order->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                {{-- <label for="" class="col-sm-3 col-form-label">SO No</label> --}}
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">SO No</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="new-row-so">
                                                                    @if (old('delivery_order_id'))
                                                                        @php
                                                                            $delivery_order_per_id = explode(",",$delivery_order_per_id->sales_order_no_all);
                                                                            $i = 1;
                                                                        @endphp
                                                                        @foreach ($delivery_order_per_id as $do_perId)
                                                                            @if ($do_perId!='')
                                                                                <tr id="row{{ $i }}">
                                                                                    <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i }}.</label></th>
                                                                                    <td>
                                                                                        <label for="" name="so_no{{ $i }}" id="so_no{{ $i }}"
                                                                                            class="col-form-label">{{ $do_perId }}</label>
                                                                                    </td>
                                                                                </tr>
                                                                                @php
                                                                                    $i += 1;
                                                                                @endphp
                                                                            @endif
                                                                        @endforeach
                                                                    @else
                                                                        @php
                                                                            $delivery_order_per_id = explode(",",$delivery_order_per_id->sales_order_no_all);
                                                                            $i = 1;
                                                                        @endphp
                                                                        @foreach ($delivery_order_per_id as $do_perId)
                                                                            @if ($do_perId!='')
                                                                                <tr id="row{{ $i }}">
                                                                                    <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i }}.</label></th>
                                                                                    <td>
                                                                                        <label for="" name="so_no{{ $i }}" id="so_no{{ $i }}"
                                                                                            class="col-form-label">{{ $do_perId }}</label>
                                                                                    </td>
                                                                                </tr>
                                                                                @php
                                                                                    $i += 1;
                                                                                @endphp
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Total</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($qInv->do_total,0,'.',',') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">VAT</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($qInv->do_vat,0,'.',',') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Grand Total</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $qCurrency->string_val.number_format($qInv->do_grandtotal_vat,0,'.',',') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    {{-- <h6 class="mb-0 text-uppercase">Part Detail</h6> --}}
                    {{-- <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part No</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 4%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Price (Rp)</th>
                                        <th scope="col" style="width: 10%;">Total Price (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $i = 0;
                                        $lastTotalAmount = 0;
                                    @endphp
                                    @if (old('delivery_order_id'))
                                        @foreach ($delivery_order_part as $do_part)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i+1 }}.</label></th>
                                                <td><label for="" name="part_no{{ $i }}" id="part_no{{ $i }}" class="col-form-label">{{ $do_part->part->part_number }}</label></td>
                                                <td><label for="" name="part_name{{ $i }}" id="part_name{{ $i }}" class="col-form-label">{{ $do_part->part->part_name }}</label></td>
                                                <td style="text-align: right;"><label for="" name="qty{{ $i }}" id="qty{{ $i }}" class="col-form-label">{{ $do_part->qty }}</label></td>
                                                <td>
                                                    <label for="" name="part_unit{{ $i }}" id="part_unit{{ $i }}"
                                                        class="col-form-label">{{ $do_part->part->quantity_type->title_ind }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" name="price{{ $i }}" id="price{{ $i }}"
                                                        class="col-form-label">{{ number_format($do_part->final_price,0,'.',',') }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" name="price{{ $i }}" id="price{{ $i }}"
                                                        class="col-form-label">{{ number_format($do_part->qty*$do_part->final_price,0,'.',',') }}</label>
                                                </td>
                                            </tr>
                                            @php
                                                $i += 1;
                                                $lastTotalAmount += ($do_part->qty*$do_part->final_price);
                                            @endphp
                                        @endforeach

                                    @else
                                        @foreach ($delivery_order_part as $do_part)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i+1 }}.</label></th>
                                                <td><label for="" name="part_no{{ $i }}" id="part_no{{ $i }}" class="col-form-label">{{ $do_part->part->part_number }}</label></td>
                                                <td><label for="" name="part_name{{ $i }}" id="part_name{{ $i }}" class="col-form-label">{{ $do_part->part->part_name }}</label></td>
                                                <td style="text-align: right;"><label for="" name="qty{{ $i }}" id="qty{{ $i }}" class="col-form-label">{{ $do_part->qty }}</label></td>
                                                <td>
                                                    <label for="" name="part_unit{{ $i }}" id="part_unit{{ $i }}"
                                                        class="col-form-label">{{ $do_part->part->quantity_type->title_ind }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" name="price{{ $i }}" id="price{{ $i }}"
                                                        class="col-form-label">{{ number_format($do_part->final_price,0,'.',',') }}</label>
                                                </td>
                                                <td style="text-align: right;">
                                                    <label for="" name="price{{ $i }}" id="price{{ $i }}"
                                                        class="col-form-label">{{ number_format($do_part->qty*$do_part->final_price,0,'.',',') }}</label>
                                                </td>
                                            </tr>
                                            @php
                                                $i += 1;
                                                $lastTotalAmount += ($do_part->qty*$do_part->final_price);
                                            @endphp
                                        @endforeach

                                    @endif
                                    <tr id="rowTotal">
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total before VAT</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                    </tr>
                                    <tr id="rowVAT">
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ number_format($lastTotalAmount*$vat/100,0,'.',',') }}</label>
                                        </td>
                                    </tr>
                                    <tr id="rowGrandTotal">
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ number_format($lastTotalAmount+($lastTotalAmount*$vat/100),0,'.',',') }}</label>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr> --}}
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save" class="btn btn-light px-5" value="Save">
                                    @if ($qInv->created_by==Auth::user()->id && $qInv->active=='Y' && $qInv->approved_by==null && strpos($qInv->invoice_no,'Draft')>0)
                                        <input type="hidden" name="orderId" id="orderId">
                                        <input type="button" id="del-btn" class="btn btn-light px-5" value="Delete">
                                    @endif
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
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            $('#tax_invoice_date').bootstrapMaterialDatePicker({
                // format: 'YYYY-MM-DD HH:mm',
                time: false
            });
            // $('#expired_invoice_date').bootstrapMaterialDatePicker({
            //     time: false
            // });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
        @if ($qInv->created_by==Auth::user()->id && $qInv->active=='Y' && strpos($qInv->invoice_no,'Draft')>0)
            $("#del-btn").click(function() {
                let msg = 'The following Invoice Numbers will be canceled.\n{{ $qInv->invoice_no }}\nContinue?';
                if(!confirm(msg)){
                    event.preventDefault();
                }else{
                    $("#orderId").val('{{ $qInv->id }}');
                    $("input[name='_method']").val('POST');
                    $('#submit-form').attr('method', "POST");
                    $('#submit-form').attr('action', "{{ url('/del_invoice') }}");
                    $("#submit-form").submit();
                }
            });
        @endif
    });
</script>
@endsection
