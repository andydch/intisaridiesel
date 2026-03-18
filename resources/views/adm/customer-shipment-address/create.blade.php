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
            @include('adm.' . $folder . '.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $folder) }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                @csrf
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <label class="input-group-text" for="entityType_id">Customer*</label>
                                    <select class="form-select single-select @error('customer_id') is-invalid @enderror"
                                        id="customer_id" name="customer_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $customerId = 0;
                                        @endphp
                                        @if (old('customer_id'))
                                            @php
                                                $customerId = old('customer_id');
                                            @endphp
                                        @endif
                                        @foreach ($customers as $c)
                                            <option @if ($customerId == $c->id) {{ 'selected' }} @endif
                                                value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Address*</span>
                                    <textarea id="address" name="address" maxlength="1024"
                                        class="form-control @error('address') is-invalid @enderror" rows="3" aria-label="address">
@if (old('address'))
{{ old('address') }}
@endif
</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <label class="input-group-text" for="province_id">Province*</label>
                                    <select class="form-select single-select @error('province_id') is-invalid @enderror"
                                        id="province_id" name="province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $provinceId = 0;
                                        @endphp
                                        @if (old('province_id'))
                                            @php
                                                $provinceId = old('province_id');
                                            @endphp
                                        @endif
                                        @foreach ($province as $p)
                                            <option @if ($provinceId == $p->id) {{ 'selected' }} @endif
                                                value="{{ $p->id }}">{{ $p->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="city_id">City*</label>
                                    <select class="form-select single-select @error('city_id') is-invalid @enderror"
                                        id="city_id" name="city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $cityId = 0;
                                        @endphp
                                        @if (old('city_id'))
                                            @php
                                                $cityId = old('city_id');
                                            @endphp
                                        @endif
                                        @foreach ($cities as $c)
                                            <option @if ($cityId == $c->id) {{ 'selected' }} @endif
                                                value="{{ $c->id }}">{{ $c->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="district_id">District*</label>
                                    <select class="form-select single-select @error('district_id') is-invalid @enderror"
                                        id="district_id" name="district_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $districtId = 0;
                                        @endphp
                                        @if (old('district_id'))
                                            @php
                                                $districtId = old('district_id');
                                            @endphp
                                        @endif
                                        @foreach ($districts as $d)
                                            <option @if ($districtId == $d->id) {{ 'selected' }} @endif
                                                value="{{ $d->id }}">{{ $d->district_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <label class="input-group-text" for="subdistrict_id">Sub District*</label>
                                    <select class="form-select single-select @error('subdistrict_id') is-invalid @enderror"
                                        id="subdistrict_id" name="subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $subdistrictId = 0;
                                        @endphp
                                        @if (old('subdistrict_id'))
                                            @php
                                                $subdistrictId = old('subdistrict_id');
                                            @endphp
                                        @endif
                                        @foreach ($subdistricts as $sd)
                                            <option @if ($subdistrictId == $sd->id) {{ 'selected' }} @endif
                                                value="{{ $sd->id }}">
                                                {{ ucwords(strtolower($sd->sub_district_name)) }}</option>
                                        @endforeach
                                    </select>
                                    @error('subdistrict_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Phone*</span>
                                    <input type="text" id="phone" name="phone"
                                        class="form-control @error('phone') is-invalid @enderror" maxlength="32"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('phone')) {{ old('phone') }} @endif">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <span class="input-group-text">Active</span>
                                    <div class="input-group-text">
                                        <input class="form-check-input" type="checkbox" id="active" name="active"
                                            aria-label="Active"
                                            @if (old('active') == 'on') {{ 'checked' }} @endif>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="submit" class="btn btn-light px-5" style="margin-top: 15px;"
                                        value="Submit">
                                </div>
                            </form>
                        </div>
                    </div>
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
            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });

            $('#province_id').change(function() {
                $("#city_id").empty();
                $("#city_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#district_id").empty();
                $("#district_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#subdistrict_id").empty();
                $("#subdistrict_id").append(
                    `<option value="#">Choose...</option>`
                );

                dispCity(
                    'province_id',
                    '#province_id option:selected',
                    '{{ url('disp_city') }}',
                    '#city_id'
                );
            });

            $('#city_id').change(function() {
                $("#district_id").empty();
                $("#district_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#subdistrict_id").empty();
                $("#subdistrict_id").append(
                    `<option value="#">Choose...</option>`
                );

                dispDistrict(
                    'city_id',
                    '#city_id option:selected',
                    '{{ url('disp_district') }}',
                    '#district_id'
                );
            });

            $('#district_id').change(function() {
                $("#subdistrict_id").empty();
                $("#subdistrict_id").append(
                    `<option value="#">Choose...</option>`
                );

                dispSubDistrict(
                    'district_id',
                    '#district_id option:selected',
                    '{{ url('disp_sub_district') }}',
                    '#subdistrict_id'
                );
            });
        });
    </script>
@endsection
