@extends('layouts.app')

@section('style')
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
                            @if(session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="month_id" class="col-sm-2 col-form-label">Month*</label>
                                <div class="col-sm-3">
                                    <select class="form-select single-select @error('month_id') is-invalid @enderror" id="month_id" name="month_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $monthId = (old('month_id')?old('month_id'):0);
                                        @endphp
                                        @for ($iMonth=1;$iMonth<=12;$iMonth++)
                                            <option @if($monthId==$iMonth){{ 'selected' }}@endif value="{{ $iMonth }}">{{ $monthList[$iMonth-1] }}</option>
                                        @endfor
                                    </select>
                                    @error('month_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <label for="year_id" class="col-sm-1 col-form-label">Year*</label>
                                <div class="col-sm-3">
                                    <select class="form-select single-select @error('year_id') is-invalid @enderror" id="year_id" name="year_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $yearNow = date_format(now(),"Y");
                                            $yearId = (old('year_id')?old('year_id'):0);
                                        @endphp
                                        @for ($iYear=(int)$yearNow-5;$iYear<=(int)$yearNow+5;$iYear++)
                                            <option @if($yearId==$iYear){{ 'selected' }}@endif value="{{ $iYear }}">{{ $iYear }}</option>
                                        @endfor
                                    </select>
                                    @error('year_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="saldo_awal" class="col-sm-2 col-form-label">Saldo Awal ({{ $qCurrency->string_val }})*</label>
                                <div class="col-sm-7">
                                    <input onkeyup="formatPartPrice($(this));" type="text" class="form-control @error('saldo_awal') is-invalid @enderror"
                                        maxlength="20" id="saldo_awal" name="saldo_awal" placeholder="saldo awal"
                                        value="@if(old('saldo_awal')){{ old('saldo_awal') }}@endif">
                                    @error('saldo_awal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="bank_id" class="col-sm-2 col-form-label">Bank*</label>
                                <div class="col-sm-3">
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

@section('script')
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

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
