@extends('layouts.app')

@section('style')
    {{--  --}}
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.global.breadcrumb-per-category')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri.'/'.urlencode($globals->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="memo_limit_val" class="col-sm-3 col-form-label">{{ $title }}*</label>
                                    <div class="col-sm-9">
                                        <input onkeyup="formatPartPrice($(this));" type="text" id="memo_limit_val" name="memo_limit_val"
                                            class="form-control @error('memo_limit_val') is-invalid @enderror" maxlength="25"
                                            value="@if(old('memo_limit_val')){{ old('memo_limit_val') }}@else{{ number_format($globals->numeric_val,0,'.',',') }}@endif">
                                        @error('memo_limit_val')
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
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                        <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
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
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri) }}";
        });
    });
</script>
@endsection
