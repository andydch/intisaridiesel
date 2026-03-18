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
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include(ENV('REPORT_FOLDER_NAME').'.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
        <hr />
        <form name="submit_form" id="submit-form" action="{{ url('/dbg/rpt-'.$folder.'-2026') }}" method="POST" enctype="application/x-www-form-urlencoded">
        {{-- <form name="submit_form" id="submit-form" action="{{ url('/'.ENV('REPORT_FOLDER_NAME').'/rpt-'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded"> --}}
            @csrf
            <div class="card">
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif
                    @if (session('status-error'))
                        <div class="alert alert-danger">{{ session('status-error') }}</div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-xl-6">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Bulan</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('period_month') is-invalid @enderror" id="period_month" name="period_month">
                                                @php
                                                    $period_month = (old('period_month')?old('period_month'):(isset($reqs)?$reqs->period_month:0));
                                                @endphp
                                                @for ($iMonth=1;$iMonth<=12;$iMonth++)
                                                    <option @if($period_month==$iMonth){{ 'selected' }}@endif value="{{ $iMonth }}">{{ $monthList[$iMonth-1] }}</option>
                                                @endfor
                                            </select>
                                            @error('period_month')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="date_start" class="col-sm-3 col-form-label">Tahun</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('period_year') is-invalid @enderror" id="period_year" name="period_year">
                                                @php
                                                    $period_year = (old('period_year')?old('period_year'):date_format(now(),"Y"));
                                                @endphp
                                                @for ($year=2023;$year<=date_format(now(),"Y");$year++)
                                                    <option @if($period_year==$year){{ 'selected' }}@endif value="{{ $year }}">{{ $year }}</option>
                                                @endfor
                                            </select>
                                            @error('period_year')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label for="bank_id" class="col-sm-3 col-form-label">Bank*</label>
                                        <div class="col-sm-9">
                                            <select class="form-select single-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $bank_id = (old('bank_id')?old('bank_id'):0);
                                                @endphp
                                                @foreach ($coas as $coa)
                                                    <option @if ($bank_id==$coa->id) {{ 'selected' }} @endif value="{{ $coa->id }}">
                                                        {{ ucwords(strtolower($coa->coa_name)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('bank_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-12">
                            <input type="button" id="download-report" class="btn btn-primary px-5" value="Download Report">
                            <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="view_mode" id="view_mode">
        </form>
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script>
    function dispBankAccountNo(monthId, yearId){
        let vMonthId = isNaN(monthId)?0:monthId;
        let vYearId = isNaN(monthId)?0:yearId;

        $("#bank_id").empty();
        $("#bank_id").append(`<option value="">Choose...</option>`);
        var fd = new FormData();
        fd.append('month_id', vMonthId);
        fd.append('year_id', vYearId);
        $.ajax({
            url: "{{ url('/disp_bankaccno_forcashflow') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].bankaccno;
                let totRo = o.length;
                if (totRo > 0) {
                    for (let i = 0; i < totRo; i++) {
                        optionText = o[i].coa_name;
                        optionValue = o[i].id;
                        $("#bank_id").append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
                    }
                }
            },
        });
    }

    $(document).ready(function() {
        $("#download-report").click(function() {
            if(!confirm("Data for Report will be saved as Excel.\nContinue?")){
                event.preventDefault();
            }else{
                $("#view_mode").val('P');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });
        $("#period_month").change(function() {
            dispBankAccountNo($("#period_month option:selected").val(), $("#period_year option:selected").val());
        });
        $("#period_year").change(function() {
            dispBankAccountNo($("#period_month option:selected").val(), $("#period_year option:selected").val());
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
