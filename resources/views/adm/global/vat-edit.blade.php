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
                                    <label for="title_ind" class="col-sm-3 col-form-label">VAT Title*</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="title_ind" name="title_ind"
                                            class="form-control @error('title_ind') is-invalid @enderror" maxlength="64"
                                            value="@if(old('title_ind')){{ old('title_ind') }}@else{{ $globals->title_ind }}@endif">
                                        @error('title_ind')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="value_numeric" class="col-sm-3 col-form-label">VAT Value*</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="value_numeric" name="value_numeric"
                                            class="form-control @error('title_ind') is-invalid @enderror" maxlength="32"
                                            value="@if(old('value_numeric')){{ old('value_numeric') }}@else{{ $globals->numeric_val }}@endif">
                                        @error('value_numeric')
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
            history.back();
        });
    });
</script>
@endsection
