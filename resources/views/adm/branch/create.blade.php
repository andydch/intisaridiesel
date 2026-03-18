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
            @include('adm.branch.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Branch</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch') }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <span class="col-sm-3 col-form-label">Initial*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="initial" name="initial" class="form-control @error('initial') is-invalid @enderror" maxlength="12"
                                            value="@if (old('initial')){{ old('initial') }}@endif">
                                        @error('initial')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="branchName" class="col-sm-3 col-form-label">Branch Name*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="branchName" name="branchName" class="form-control @error('branchName') is-invalid @enderror"
                                            maxlength="255" value="@if (old('branchName')){{ old('branchName') }}@endif">
                                        @error('branchName')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="address" class="col-sm-3 col-form-label">Address*</span>
                                    <div class="col-sm-9">
                                        <textarea id="address" name="address" maxlength="1024" class="form-control @error('address') is-invalid @enderror"
                                            rows="3" aria-label="address">@if (old('address')){{ old('address') }}@endif</textarea>
                                        @error('address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="province_id" class="col-sm-3 col-form-label">Province*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $provinceId = old('province_id')?old('province_id'):0;
                                            @endphp
                                            @foreach ($province as $p)
                                                <option @if ($provinceId == $p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('province_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="city_id" class="col-sm-3 col-form-label">City*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $cityId = old('city_id')?old('city_id'):0;
                                            @endphp
                                            @foreach ($cities as $c)
                                                <option @if ($cityId == $c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="city_id" class="col-sm-3 col-form-label">District*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $districtId = old('district_id')?old('district_id'):0;
                                            @endphp
                                            @foreach ($districts as $d)
                                                <option @if ($districtId == $d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('district_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="subdistrict_id" class="col-sm-3 col-form-label">Sub District*</span>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('subdistrict_id') is-invalid @enderror" id="subdistrict_id" name="subdistrict_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $subdistrictId = old('subdistrict_id')?old('subdistrict_id'):0;
                                            @endphp
                                            @foreach ($subdistricts as $sd)
                                                <option @if ($subdistrictId == $sd->id){{ 'selected' }}@endif
                                                    value="{{ $sd->id }}">{{ ucwords(strtolower($sd->sub_district_name)) }}</option>
                                            @endforeach
                                        </select>
                                        @error('subdistrict_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="postcode" class="col-sm-3 col-form-label">Postcode*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="postcode" name="postcode" class="form-control @error('postcode') is-invalid @enderror"
                                            maxlength="6" value="@if(old('postcode')){{ old('postcode') }}@endif">
                                        @error('postcode')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="phone1" class="col-sm-3 col-form-label">Phone 1*</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="phone1" name="phone1" class="form-control @error('phone1') is-invalid @enderror"
                                            maxlength="32" value="@if(old('phone1')){{ old('phone1') }}@endif">
                                        @error('phone1')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <span for="phone2" class="col-sm-3 col-form-label">Phone 2</span>
                                    <div class="col-sm-9">
                                        <input type="text" id="phone2" name="phone2" class="form-control @error('phone2') is-invalid @enderror"
                                            maxlength="32" value="@if(old('phone2')){{ old('phone2') }}@endif">
                                        @error('phone2')
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
    <script src="{{ asset('assets/js/my-custom.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#initial').keyup(function() {
                let initial = $("#initial").val();
                $("#initial").val(initial.toUpperCase());
            });
            $('#branchName').keyup(function() {
                let branchName = $("#branchName").val();
                $("#branchName").val(branchName.toUpperCase());
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

            $('#province_id').change(function() {
                $("#city_id").empty();
                $("#district_id").empty();
                $("#subdistrict_id").empty();
                $("#postcode").val('');
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
                        $("#subdistrict_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totCity > 0) {
                            for (let i = 0; i < totCity; i++) {
                                optionText = o[i].city_name;
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
                $("#subdistrict_id").empty();
                $("#postcode").val('');
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
                        $("#subdistrict_id").append(
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

            $('#district_id').change(function() {
                $("#subdistrict_id").empty();
                $("#postcode").val('');
                var fd = new FormData();
                fd.append('district_id', $('#district_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_sub_district') }}',
                    type: 'POST',
                    enctype: 'application/x-www-form-urlencoded',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        let o = res[0].sub_district;
                        let totSubDistrict = o.length;
                        $("#subdistrict_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        if (totSubDistrict > 0) {
                            for (let i = 0; i < totSubDistrict; i++) {
                                optionText = o[i].sub_district_name.toLowerCase().ucwords();
                                optionValue = o[i].id;
                                $("#subdistrict_id").append(
                                    `<option value="${optionValue}">${optionText}</option>`
                                );
                            }
                        }
                    },
                });
            });

            $('#subdistrict_id').change(function() {
                $("#postcode").val('');
                var fd = new FormData();
                fd.append('subdistrict_id', $('#subdistrict_id option:selected').val());
                $.ajax({
                    url: '{{ url('disp_sub_district_postcode') }}',
                    type: 'POST',
                    enctype: 'application/x-www-form-urlencoded',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        let o = res[0].sub_district;
                        $("#postcode").val(o[0].post_code);
                    },
                });
            });
        });
    </script>
@endsection
