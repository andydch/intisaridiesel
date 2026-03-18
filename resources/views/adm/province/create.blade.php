@extends('layouts.app')

@section('style')
    <link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
    <style>
        .select2-selection {
            height: 38px !important;
            font-size: 1rem;
        }
    </style>
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.province.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Province</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/province') }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="provinceName" class="col-sm-3 col-form-label">{{ $title }} Name*</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="provinceName" name="provinceName" class="form-control @error('provinceName') is-invalid @enderror" maxlength="128"
                                            aria-label="Title (Ind)" value="@if(old('provinceName')){{ old('provinceName') }}@endif">
                                        @error('provinceName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="country_id" class="col-sm-3 col-form-label">Country Name*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('country_id') is-invalid @enderror"
                                            id="country_id" name="country_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $countryId = old('country_id')?old('country_id'):0;
                                            @endphp
                                            @foreach ($country as $c)
                                                <option @if ($countryId == $c->id) {{ 'selected' }} @endif
                                                    value="{{ $c->id }}">{{ $c->country_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('country_id')
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
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

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
