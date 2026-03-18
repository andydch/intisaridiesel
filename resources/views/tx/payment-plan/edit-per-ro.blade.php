@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}
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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder_per_ro.'/'.urlencode($pp)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <input type="hidden" name="receipt_no" id="receipt_no" value="{{ $qRO->receipt_no }}">
                <input type="hidden" name="b_i" id="b_i" value="{{ $b_i }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif
                    <div class="card">
                        <div class="card-body">
                            @if(session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Inv No: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qRO->invoice_no }}</label>
                                <label for="" class="col-sm-2 col-form-label">Due Date: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ date_format(date_create($qRO->due_date_payment),"d/m/Y") }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">RO No: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qRO->receipt_no }}</label>
                                <label for="" class="col-sm-2 col-form-label">Tagihan: </label>
                                <label for="" class="col-sm-3 col-form-label">
                                    {{ $qCurrency->string_val.' '.($qRO->supplier_type_id==10?
                                        number_format($qRO->total_after_vat*$qRO->exchange_rate,0,",","."):
                                        number_format($qRO->total_after_vat,0,",",".")) }}
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-2 col-form-label">Supplier: </label>
                                <label for="" class="col-sm-3 col-form-label">{{ $qRO->supplier_identity }}</label>
                                <label for="" class="col-sm-2 col-form-label">Bayar</label>
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
                                                <th scope="col" style="width: 59%;">Plan Bayar</th>
                                                <th scope="col" style="width: 2%;">Del</th>
                                            </tr>
                                        </thead>
                                        <tbody id="body-payment-plan">
                                            @if (old('plan_rows_total'))
                                                @for ($i=0;$i<old('plan_rows_total');$i++)
                                                    @if (old('plan_id_'.$i)!=null)
                                                        <tr id="row-{{ $i }}">
                                                            <td>
                                                                <input type="hidden" name="plan_id_{{ $i }}" id="plan_id_{{ $i }}"
                                                                    value="@if(old('plan_id_'.$i)){{ old('plan_id_'.$i) }}@else{{ 0 }}@endif">
                                                                <input type="text" class="form-control @error('plan_date_'.$i) is-invalid @enderror"
                                                                    id="plan_date_{{ $i }}" name="plan_date_{{ $i }}"
                                                                    value="{{ old('plan_date_'.$i) }}" placeholder="Plan Date" />
                                                                @error('plan_date_'.$i)
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control @error('plan_payment_'.$i) is-invalid @enderror"
                                                                    id="plan_payment_{{ $i }}" name="plan_payment_{{ $i }}"
                                                                    value="{{ old('plan_payment_'.$i) }}" placeholder="Plan Payment"
                                                                    onkeyup="formatPartPrice(this);" style="text-align: right;" />
                                                                @error('plan_payment_'.$i)
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </td>
                                                            <td style="text-align:center;">
                                                                <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endfor
                                            @else
                                                @php
                                                    $i = 0;
                                                @endphp
                                                @foreach ($qPaymentPlans as $qPP)
                                                    <tr id="row-{{ $i }}">
                                                        <td>
                                                            <input type="hidden" name="plan_id_{{ $i }}" id="plan_id_{{ $i }}" value="{{ $qPP->id }}">
                                                            <input type="text" class="form-control @error('plan_date_'.$i) is-invalid @enderror"
                                                                id="plan_date_{{ $i }}" name="plan_date_{{ $i }}"
                                                                value="{{ date_format(date_create($qPP->plan_date),"d/m/Y") }}" placeholder="Plan Date" />
                                                            @error('plan_date_'.$i)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control @error('plan_payment_'.$i) is-invalid @enderror" id="plan_payment_{{ $i }}"
                                                                name="plan_payment_{{ $i }}" value="{{ number_format($qPP->plan_pay,0,"",",") }}"
                                                                placeholder="Plan Payment" onkeyup="formatPartPrice(this);" style="text-align: right;" />
                                                            @error('plan_payment_'.$i)
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </td>
                                                        <td style="text-align:center;">
                                                            <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $i += 1;
                                                    @endphp
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    <div class="input-group">
                                        <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                        <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
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
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
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
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function formatPartPrice(elm){
        let priceList = $(elm).val().replaceAll(',','');
        if(priceList===''){$(elm).val('');return false;}
        if(isNaN(priceList)){$(elm).val('');return false;}
        priceList = parseFloat(priceList).numberFormat(0,'.',',');    // without decimal
        $(elm).val(priceList);
    }

    function addRow(){
        let totalRow = isNaN($("#plan_rows_total").val())?0:$("#plan_rows_total").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="row-'+totalRow+'">'+
            '<td><input type="hidden" name="plan_id_'+totalRow+'" id="plan_id_'+totalRow+'" value="0">'+
            '<input type="text" class="form-control" id="plan_date_'+totalRow+'" name="plan_date_'+totalRow+'" readonly="" /></td>'+
            '<td><input type="text" class="form-control" style="text-align: right;" id="plan_payment_'+totalRow+'" name="plan_payment_'+totalRow+'" '+
            'onkeyup="formatPartPrice(this);" /></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
            '</tr>';
        $("#body-payment-plan").append(vHtml);
        $("#plan_rows_total").val(rowNo);

        $(function() {
            $('#plan_date_'+totalRow).bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
        });
    }

    $(document).ready(function() {
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$pp) }}";
        });
        $("#btn-add-row").click(function() {
            addRow();
        });
        $("#btn-del-row").click(function() {
            for (i=0;i<$("#plan_rows_total").val();i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row-"+i).remove();
                }
            }
        });

        $(function() {
            @if (old('plan_rows_total'))
                @for ($i=0;$i<old('plan_rows_total');$i++)
                    $('#plan_date_{{ $i }}').bootstrapMaterialDatePicker({
                        format: 'DD/MM/YYYY',
                        time: false
                    });
                @endfor
            @else
                @php
                    $i = 0;
                @endphp
                @foreach ($qPaymentPlans as $qPP)
                    $('#plan_date_{{ $i }}').bootstrapMaterialDatePicker({
                        format: 'DD/MM/YYYY',
                        time: false
                    });
                    @php
                        $i += 1;
                    @endphp
                @endforeach
            @endif
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
