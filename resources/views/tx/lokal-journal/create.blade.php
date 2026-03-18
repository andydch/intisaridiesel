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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="journal_date" class="col-sm-3 col-form-label">Journal Date*</label>
                                <div class="col-sm-9">
                                    <input readonly type="text" class="form-control @error('journal_date') is-invalid @enderror" maxlength="10"
                                        id="journal_date" name="journal_date" placeholder="Enter General Journal Date" value="@if (old('journal_date')){{ old('journal_date') }}@endif">
                                    @error('journal_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <input type="hidden" class="form-control @error('totalDebet') is-invalid @enderror"
                                        name="totalDebet" id="totalDebet" value="@if (old('totalDebet')){{ str_replace(".00","",old('totalDebet')) }}@endif">
                                    <input type="hidden" class="form-control @error('totalCredit') is-invalid @enderror"
                                        name="totalCredit" id="totalCredit" value="@if (old('totalCredit')){{ str_replace(".00","",old('totalCredit')) }}@endif">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    {{-- <h6 class="mb-0 text-uppercase">Part Detail</h6> --}}
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">COA Code</th>
                                        {{-- <th scope="col" style="width: 15%;">COA Name</th> --}}
                                        <th scope="col" style="width: 25%;">Description</th>
                                        <th scope="col" style="width: 15%;">Debet</th>
                                        <th scope="col" style="width: 15%;">Credit</th>
                                        <th scope="col" style="width: 3%;text-align:center;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++)
                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i+1 }}.</label></th>
                                        <td>
                                            <select class="form-select single-select @error('coa_code_'.$i) is-invalid @enderror"
                                                id="coa_code_{{ $i }}" name="coa_code_{{ $i }}">
                                                {{-- onchange="syncCoa(this.value,{{ $i }});" --}}
                                                <option value="#">Choose...</option>
                                                @php
                                                    $coa_code_Id = old('coa_code_'.$i) ? old('coa_code_'.$i) : 0;
                                                @endphp
                                                @foreach ($coas as $pr)
                                                    <option @if ($coa_code_Id==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $pr->coa_code_complete.' - '.$pr->coa_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('coa_code_'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        {{-- <td>
                                            <select class="form-select single-select @error('coa_name_'.$i) is-invalid @enderror"
                                                id="coa_name_{{ $i }}" name="coa_name_{{ $i }}" onchange="dispPriceRef(this.value, {{ $i }});">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $coa_name_ = old('coa_name_'.$i) ? old('coa_name_'.$i) : 0;
                                                @endphp
                                                @foreach ($coas as $pr)
                                                    <option @if ($coa_name_==$pr->id){{ 'selected' }}@endif value="{{ $pr->id }}">{{ $pr->coa_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('coa_name_'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td> --}}
                                        <td>
                                            <textarea class="form-control" name="desc_part{{ $i }}" id="desc_part{{ $i }}" rows="3"
                                                style="width: 100%;">@if (old('desc_part'.$i)){{ old('desc_part'.$i) }}@endif</textarea>
                                            @error('desc_part'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input onchange="sumDebet();" onkeyup="formatPartPrice($(this));" type="text" style="text-align: right;"
                                                class="form-control @error('debet_amount'.$i) is-invalid @enderror"
                                                id="debet_amount{{ $i }}" name="debet_amount{{ $i }}" maxlength="22"
                                                value="@if (old('debet_amount'.$i)){{ old('debet_amount'.$i) }}@endif" />
                                            @error('debet_amount'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input onchange="sumCredit();" onkeyup="formatPartPrice($(this));" type="text" style="text-align: right;"
                                                class="form-control @error('credit_amount'.$i) is-invalid @enderror"
                                                id="credit_amount{{ $i }}" name="credit_amount{{ $i }}" maxlength="22"
                                                value="@if (old('credit_amount'.$i)){{ old('credit_amount'.$i) }}@endif" />
                                            @error('credit_amount'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td style="text-align: center;">
                                            <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                        </td>
                                    </tr>
                                    @endfor
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align: right;"><label for="">Total</label></td>
                                        <td style="text-align: right;">
                                            <label for="" id="lbl-total-debet">@if (old('totalDebet')){{ $qCurrency->string_val.number_format(old('totalDebet'),0,'.',',') }}@endif</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" id="lbl-total-credit">@if (old('totalCredit')){{ $qCurrency->string_val.number_format(old('totalCredit'),0,'.',',') }}@endif</label>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @error('totalCredit')
                                        <tr>
                                            <td colspan="7" style="margin-top: .25rem;color: #f41127;font-size: .875em;">{{ $message }}</td>
                                        </tr>
                                    @enderror
                                </tfoot>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
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

@php
    $coa_code_Html = '';
    $coa_name_Html = '';
@endphp
@foreach($coas as $p)
    @php
        $coa_code_Html .= '<option value="'.$p->id.'">'.$p->coa_code_complete.' - '.$p->coa_name.'</option>';
        // $coa_name_Html .= '<option value="'.$p->id.'">'.$p->coa_name.'</option>';
    @endphp
@endforeach

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
    function syncCoa(coaId,idx){
        $("#coa_name_"+idx).val(coaId).change();
    }
    function sumDebet(){
        let totalRow = $("#totalRow").val();
        let totalDebet = 0;
        for(let iRow=0;iRow<totalRow;iRow++){
            if ($("#debet_amount"+iRow).val()){
                let debet_amount = $("#debet_amount"+iRow).val().replaceAll(',','').replaceAll('{{ $qCurrency->string_val }}','');
                if($.isNumeric(debet_amount)){
                    totalDebet += parseFloat(debet_amount);
                }
            }
        }
        $("#lbl-total-debet").text('{{ $qCurrency->string_val }}'+parseFloat(totalDebet).numberFormat(0,'.',','));
        $("#totalDebet").val(totalDebet);
    }
    function sumCredit(){
        let totalRow = $("#totalRow").val();
        let totalCredit = 0;
        for(let iRow=0;iRow<totalRow;iRow++){
            if ($("#credit_amount"+iRow).val()){
                let credit_amount = $("#credit_amount"+iRow).val().replaceAll(',','').replaceAll('{{ $qCurrency->string_val }}','');
                if($.isNumeric(credit_amount)){
                    totalCredit += parseFloat(credit_amount);
                }
            }
        }
        $("#lbl-total-credit").text('{{ $qCurrency->string_val }}'+parseFloat(totalCredit).numberFormat(0,'.',','));
        $("#totalCredit").val(totalCredit);
    }

    function addPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml = '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;"><label for="" class="col-form-label">'+rowNo+'.</label></th>'+
            '<td>'+
            '<select class="form-select single-select" id="coa_code_'+totalRow+'" name="coa_code_'+totalRow+'">'+
            '<option value="#">Choose...</option>{!! $coa_code_Html !!}'+
            '</select>'+
            '</td>'+
            // '<td>'+
            // '<select class="form-select single-select" id="coa_name_'+totalRow+'" name="coa_name_'+totalRow+'">'+
            // '<option value="#">Choose...</option>{!! $coa_name_Html !!}'+
            // '</select>'+
            // '</td>'+
            '<td><textarea name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" class="form-control" rows="3" style="width: 100%;"></textarea></td>'+
            '<td>'+
            '<input onchange="sumDebet();" onkeyup="formatPartPrice($(this));" type="text" style="text-align: right;" class="form-control" id="debet_amount'+totalRow+'" '+
                'name="debet_amount'+totalRow+'" maxlength="22" value="" />'+
            '</td>'+
            '<td>'+
            '<input onchange="sumCredit();" onkeyup="formatPartPrice($(this));" type="text" style="text-align: right;" class="form-control" id="credit_amount'+totalRow+'" '+
                'name="credit_amount'+totalRow+'" maxlength="22" value="" />'+
            '</td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>'+
            '</tr>';
        // onchange="syncCoa(this.value,'+totalRow+');"
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    }

    $(document).ready(function() {
        $("#save-as-draft").click(function() {
            if(!confirm("Data will be saved to database with DRAFT status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);

                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();
        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#journal_date').bootstrapMaterialDatePicker({
                format: 'DD/MM/YYYY',
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $("#btn-add-row").click(function() {
            addPart();
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }
            sumDebet();
            sumCredit();
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
