@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet"
    href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

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
        @include('adm.salesman.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">Salesman</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/salesman') }}" method="POST"
                            enctype="application/x-www-form-urlencoded">
                            @csrf
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Salesman Name*</span>
                                <input type="text" id="salesmanName" name="salesmanName"
                                    class="form-control @error('salesmanName') is-invalid @enderror" maxlength="255"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('salesmanName')) {{ old('salesmanName') }} @endif">
                                @error('salesmanName')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="branch_id">Branch*</label>
                                <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                    id="branch_id" name="branch_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $branchId = 0;
                                    @endphp
                                    @if (old('branch_id'))
                                    @php
                                    $branchId = old('branch_id');
                                    @endphp
                                    @endif
                                    @foreach ($branch as $b)
                                    <option @if ($branchId==$b->id) {{ 'selected' }} @endif
                                        value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">Address*</span>
                                <textarea id="address" name="address" maxlength="1024"
                                    class="form-control @error('address') is-invalid @enderror" rows="3"
                                    aria-label="address">@if (old('address')){{ old('address') }}@endif</textarea>
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
                                    <option @if ($provinceId==$p->id) {{ 'selected' }} @endif
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
                                    <option @if ($cityId==$c->id) {{ 'selected' }} @endif
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
                                    <option @if ($districtId==$d->id) {{ 'selected' }} @endif
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
                                    <option @if ($subdistrictId==$sd->id) {{ 'selected' }} @endif
                                        value="{{ $sd->id }}">
                                        {{ ucwords(strtolower($sd->sub_district_name)) }}</option>
                                    @endforeach
                                </select>
                                @error('subdistrict_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Postcode*</span>
                                <input type="text" id="postcode" name="postcode"
                                    class="form-control @error('postcode') is-invalid @enderror" maxlength="6"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('postcode')) {{ old('postcode') }} @endif">
                                @error('postcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">NIK*</span>
                                <input type="text" id="idNo" name="idNo"
                                    class="form-control @error('idNo') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('idNo')) {{ old('idNo') }} @endif">
                                @error('idNo')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Email*</span>
                                <input type="email" id="sales_email" name="sales_email"
                                    class="form-control @error('sales_email') is-invalid @enderror" maxlength="64"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('sales_email')) {{ old('sales_email') }} @endif">
                                @error('sales_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Mobilephone*</span>
                                <input type="text" id="phone1" name="phone1"
                                    class="form-control @error('phone1') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('phone1')) {{ old('phone1') }} @endif">
                                @error('phone1')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Birth Date*</span>
                                <input type="text" class="form-control @error('birth_date') is-invalid @enderror"
                                    maxlength="10" id="birth_date" name="birth_date" readonly
                                    value="@if (old('birth_date')) {{ old('birth_date') }} @endif">
                                @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <label class="input-group-text" for="branch_id">Gender*</label>
                                <select class="form-select single-select @error('gender_id') is-invalid @enderror"
                                    id="gender_id" name="gender_id">
                                    <option value="#">Choose...</option>
                                    @php
                                    $genderId = 0;
                                    @endphp
                                    @if (old('gender_id'))
                                    @php
                                    $genderId = old('gender_id');
                                    @endphp
                                    @endif
                                    @foreach ($gender as $g)
                                    <option @if ($genderId==$g->id) {{ 'selected' }} @endif
                                        value="{{ $g->id }}">{{ $g->title_ind }}</option>
                                    @endforeach
                                </select>
                                @error('gender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Join Date*</span>
                                <input type="text" class="form-control @error('join_date') is-invalid @enderror"
                                    maxlength="10" id="join_date" name="join_date" readonly
                                    value="@if (old('join_date')) {{ old('join_date') }} @endif">
                                @error('join_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="inputGroup-sizing-default">Sales Target*</span>
                                <input type="text" id="sales_target" name="sales_target"
                                    class="form-control @error('sales_target') is-invalid @enderror" maxlength="32"
                                    aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                    value="@if (old('sales_target')){{ old('sales_target') }}@endif">
                                @error('sales_target')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="input-group mb-3" style="margin-top: 15px;">
                                <span class="input-group-text">Active</span>
                                <div class="input-group-text">
                                    <input class="form-check-input" type="checkbox" id="active" name="active"
                                        aria-label="Active" @if (old('active')=='on' ) {{ 'checked' }} @endif>
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

<!-- Full screen modal -->
<div class="modal fade" id="supplier-info" aria-hidden="true" aria-labelledby="supplier-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="supplier-info">Salesman Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                The following are similar salesman names:<br />
                <span id="msg-modal"></span><br />
                Make sure the salesman name entered does not match the name of an existing salesman.<br /><br />
                <span id="msg-modal-info" style="font-weight: bold;"></span>
            </div>
            <div class="modal-footer">
                {{-- <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to
                    first</button> --}}
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script
    src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}">
</script>

<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function SalesmanInfo(slug){
        var fd = new FormData();
        fd.append('slug', slug);
        $.ajax({
            url: '{{ url("disp_salesmaninfo") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].salesmans;
                let totSalesman = o.length;
                if (totSalesman > 0) {
                    let post_code = o[0].post_code.replace('000000','');
                    let vHtml = 'Detail Info:<br />'+o[0].name+'<br />'+o[0].address+','+
                    o[0].sub_district_name.toLowerCase().ucwords()+', '+o[0].district_name+'<br />'+o[0].city_name+'<br />'+
                    o[0].province_name+'<br />'+o[0].country_name+' '+post_code;
                    $('#msg-modal-info').html(vHtml);
                }
            },
        });
    }

    $(document).ready(function() {
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: true
        }),
        $('.timepicker').pickatime();

        $(function() {
            $('#date-time').bootstrapMaterialDatePicker({
                format: 'YYYY-MM-DD HH:mm'
            });
            $('#birth_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#join_date').bootstrapMaterialDatePicker({
                time: false
            });
            $('#time').bootstrapMaterialDatePicker({
                date: false,
                format: 'HH:mm'
            });
        });

        $('#salesmanName').change(function() {
            $('#msg-modal-info').html('&nbsp;');
            var fd = new FormData();
            fd.append('salesmanName', $('#salesmanName').val());
            $.ajax({
                url: '{{ url("disp_similar_salesmanname") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].salesmans;
                    let totSalesman = o.length;
                    if (totSalesman > 0) {
                        let vHtml = '';
                        for (let i = 0; i < totSalesman; i++) {
                            vHtml+=(i+1)+'. <a href="#" onclick="SalesmanInfo(\''+o[i].slug+'\');" '+
                            'style="text-decoration: underline;">'+o[i].name+'</a><br />';
                        }
                        $('#msg-modal').html(vHtml);
                        $('#supplier-info').modal('show');
                    }
                },
            });
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
