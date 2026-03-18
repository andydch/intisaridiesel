@extends('layouts.app')

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.country.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                @if (session('status-error'))
                    <div class="alert alert-danger">
                        {{ session('status-error') }}
                    </div>
                @endif
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Country</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/country') }}" method="POST" enctype="application/x-www-form-urlencoded">
                        <div class="card">
                            <div class="card-body">
                                @csrf
                                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                                <div class="row mb-3">
                                    <label for="countryName" class="col-sm-3 col-form-label">{{ $title }} Name*</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="countryName" name="countryName" class="form-control @error('countryName') is-invalid @enderror" maxlength="128"
                                            aria-label="Title (Ind)" value="@if(old('countryName')){{ old('countryName') }}@endif">
                                        @error('countryName')
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
