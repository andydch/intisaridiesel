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
        @include('adm.city.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">City</h6>
                <hr />
                <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/city/'.$city->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="country_id" class="col-sm-3 col-form-label">Country Name*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('country_id') is-invalid @enderror"
                                        id="country_id" name="country_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $countryId = old('country_id')?old('country_id'):$city->country_id;
                                        @endphp
                                        @foreach ($country as $c)
                                            <option @if ($countryId == $c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->country_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="province_id" class="col-sm-3 col-form-label">Province Name*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('province_id') is-invalid @enderror"
                                        id="province_id" name="province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $provinceId = old('province_id')?old('province_id'):$city->province_id;
                                        @endphp
                                        @foreach ($province as $c)
                                            <option @if ($provinceId == $c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="cityName" class="col-sm-3 col-form-label">{{ $title }} Name*</label>
                                <div class="col-sm-9">
                                    <input type="text" id="cityName" name="cityName" class="form-control @error('cityName') is-invalid @enderror" maxlength="128"
                                        aria-label="Title (Ind)" value="@if(old('cityName')){{ old('cityName') }}@else{{ $city->city_name }}@endif">
                                    @error('cityName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="city_type" class="col-sm-3 col-form-label">City Type*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('city_type') is-invalid @enderror" id="city_type" name="city_type">
                                        <option value="#">Choose...</option>
                                        @php
                                            $city_type = old('city_type')?old('city_type'):$city->city_type;
                                        @endphp
                                        @if ($city_type != 'Luar Negeri')
                                            <option @if ($city_type=='Kota' ) {{ 'selected' }} @endif value="Kota">Kota</option>
                                            <option @if ($city_type=='Kabupaten' ) {{ 'selected' }} @endif value="Kabupaten">Kabupaten</option>
                                        @else
                                            <option selected value="Luar Negeri">Luar Negeri</option>
                                        @endif
                                    </select>
                                    @error('city_type')
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
        $('#country_id').change(function() {
            $("#province_id").empty();
            var fd = new FormData();
            fd.append('country_id', $('#country_id option:selected').val());
            $.ajax({
                url: '{{ url("disp_province") }}',
                type: 'POST',
                enctype: 'application/x-www-form-urlencoded',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    let o = res[0].province;
                    let totProvince = o.length;
                    if (totProvince > 0) {
                        $("#province_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        for (let i = 0; i < totProvince; i++) {
                            optionText = o[i].province_name;
                            optionValue = o[i].id;
                            $("#province_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    } else {
                        $("#province_id").append(
                            `<option selected value="9999">Other</option>`
                        );
                        $("#city_type").empty();
                        $("#city_type").append(
                            `<option selected value="Luar Negeri">Luar Negeri</option>`
                        );
                    }
                },
            });
        });

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
