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
            @include('adm.subdistrict.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Sub District</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/subdistrict') }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
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
                                                $countryId = old('country_id')?old('country_id'):0;
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
                                                $provinceId = old('province_id')?old('province_id'):0;
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
                                    <label for="city_id" class="col-sm-3 col-form-label">City Name*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('city_id') is-invalid @enderror"
                                            id="city_id" name="city_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $cityId = old('city_id')?old('city_id'):0;
                                            @endphp
                                            @foreach ($city as $c)
                                                <option @if ($cityId == $c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_type.' '.$c->city_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label" for="district_id">District*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $districtId = old('district_id')?old('district_id'):0;
                                            @endphp
                                            @foreach ($district as $d)
                                                <option @if ($districtId == $d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('district_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="subdistrictName" class="col-sm-3 col-form-label">Sub District Name*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="subdistrictName" name="subdistrictName"
                                            class="form-control @error('subdistrictName') is-invalid @enderror" maxlength="128"
                                            value="@if (old('subdistrictName')) {{ old('subdistrictName') }} @endif">
                                        @error('subdistrictName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="postcode" class="col-sm-3 col-form-label">Postcode*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="postcode" name="postcode"
                                            class="form-control @error('postcode') is-invalid @enderror" maxlength="6"
                                            value="@if (old('postcode')) {{ old('postcode') }} @endif">
                                        @error('postcode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                {{-- <div class="input-group">
                                    <input type="submit" class="btn btn-primary px-5" style="margin-top: 15px;"
                                        value="Submit">
                                </div> --}}
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

            $('#country_id').change(function() {
                $("#province_id").empty();
                $("#city_id").empty();
                $("#district_id").empty();
                var fd = new FormData();
                fd.append('country_id', $('#country_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_province') }}',
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
                        $("#province_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        $("#city_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        $("#district_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totProvince > 0) {
                            for (let i = 0; i < totProvince; i++) {
                                optionText = o[i].province_name;
                                optionValue = o[i].id;
                                $("#province_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            });

            $('#province_id').change(function() {
                $("#city_id").empty();
                $("#district_id").empty();
                var fd = new FormData();
                fd.append('province_id', $('#province_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_city') }}',
                    type: 'POST',
                    enctype: 'application/x-www-form-urlencoded',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        let o = res[0].city;
                        let totCity = o.length;
                        $("#city_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        $("#district_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totCity > 0) {
                            for (let i = 0; i < totCity; i++) {
                                optionText = o[i].city_type+' '+o[i].city_name;
                                optionValue = o[i].id;
                                $("#city_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            });

            $('#city_id').change(function() {
                $("#district_id").empty();
                var fd = new FormData();
                fd.append('city_id', $('#city_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_district') }}',
                    type: 'POST',
                    enctype: 'application/x-www-form-urlencoded',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        let o = res[0].district;
                        let totDistrict = o.length;
                        $("#district_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totDistrict > 0) {
                            for (let i = 0; i < totDistrict; i++) {
                                optionText = o[i].district_name;
                                optionValue = o[i].id;
                                $("#district_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            });
        });
    </script>
@endsection
