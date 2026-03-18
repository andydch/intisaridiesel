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
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="companyName" class="col-sm-3 col-form-label">Company Name*</label>
                                <div class="col-sm-9">
                                    <input type="text" id="companyName" name="companyName"
                                        class="form-control @error('companyName') is-invalid @enderror" maxlength="255"
                                        value="@if (old('companyName')){{ old('companyName') }}@endif">
                                    @error('companyName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="office_address" class="col-sm-3 col-form-label">Office Address*</label>
                                <div class="col-sm-9">
                                    <textarea id="office_address" name="office_address" maxlength="1024"
                                        class="form-control @error('office_address') is-invalid @enderror"
                                        rows="3">@if (old('office_address')){{ old('office_address') }}@endif</textarea>
                                    @error('office_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="province_id" class="col-sm-3 col-form-label">Province*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('province_id') is-invalid @enderror"
                                        id="province_id" name="province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                        $provinceId = old('province_id')?old('province_id'):0;
                                        @endphp
                                        @foreach ($province as $p)
                                            <option @if ($provinceId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="city_id" class="col-sm-3 col-form-label">City*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $cityId = old('city_id')?old('city_id'):0;
                                        @endphp
                                        @foreach ($cities as $c)
                                            <option @if ($cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="district_id" class="col-sm-3 col-form-label">District*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $districtId = old('district_id')?old('district_id'):0;
                                        @endphp
                                        @foreach ($districts as $d)
                                            <option @if ($districtId==$d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="subdistrict_id" class="col-sm-3 col-form-label">Sub District*</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('subdistrict_id') is-invalid @enderror" id="subdistrict_id" name="subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $subdistrictId = old('subdistrict_id')?old('subdistrict_id'):0;
                                        @endphp
                                        @foreach ($subdistricts as $sd)
                                            <option @if ($subdistrictId==$sd->id) {{ 'selected' }} @endif value="{{ $sd->id }}">
                                                {{ ucwords(strtolower($sd->sub_district_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('subdistrict_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="postcode" class="col-sm-3 col-form-label">Postcode*</label>
                                <div class="col-sm-9">
                                    <input type="text" id="postcode" name="postcode"
                                        class="form-control @error('postcode') is-invalid @enderror" maxlength="6"
                                        value="@if (old('postcode')) {{ old('postcode') }} @endif">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="company_email" class="col-sm-3 col-form-label">Company Email*</label>
                                <div class="col-sm-9">
                                    <input type="email" id="company_email" name="company_email"
                                        class="form-control @error('company_email') is-invalid @enderror" maxlength="64"
                                        value="@if (old('company_email')) {{ old('company_email') }} @endif">
                                    @error('company_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="phone1" class="col-sm-3 col-form-label">Phone 1*</label>
                                <div class="col-sm-9">
                                    <input type="text" id="phone1" name="phone1"
                                        class="form-control @error('phone1') is-invalid @enderror" maxlength="32"
                                        value="@if (old('phone1')) {{ old('phone1') }} @endif">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="phone2" class="col-sm-3 col-form-label">Phone 2</label>
                                <div class="col-sm-9">
                                    <input type="text" id="phone2" name="phone2"
                                        class="form-control @error('phone2') is-invalid @enderror" maxlength="32"
                                        value="@if (old('phone2')) {{ old('phone2') }} @endif">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @if ($companyCount==0)

                                <div class="row mb-3">
                                    <label for="npwp_no" class="col-sm-3 col-form-label">NPWP no</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="npwp_no" name="npwp_no"
                                            class="form-control @error('npwp_no') is-invalid @enderror" maxlength="24"
                                            value="@if (old('npwp_no')){{ old('npwp_no') }}@endif">
                                        @error('npwp_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="npwp_address" class="col-sm-3 col-form-label">NPWP Address</label>
                                    <div class="col-sm-9">
                                        <textarea id="npwp_address" name="npwp_address" maxlength="1024"
                                            class="form-control @error('npwp_address') is-invalid @enderror" rows="3"
                                            aria-label="npwp address">@if (old('npwp_address')){{ old('npwp_address') }}@endif</textarea>
                                        @error('npwp_address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="npwp_province_id" class="col-sm-3 col-form-label">NPWP Province</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('npwp_province_id') is-invalid @enderror" id="npwp_province_id" name="npwp_province_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $npwp_provinceId = old('npwp_province_id')?old('npwp_province_id'):0;
                                            @endphp
                                            @foreach ($province as $p)
                                                <option @if ($npwp_provinceId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('npwp_province_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="npwp_city_id" class="col-sm-3 col-form-label">NPWP City</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('npwp_city_id') is-invalid @enderror" id="npwp_city_id" name="npwp_city_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $npwp_cityId = old('npwp_city_id')?old('npwp_city_id'):0;
                                            @endphp
                                            @foreach ($citiesNPWP as $c)
                                                <option @if ($npwp_cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('npwp_city_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="npwp_district_id" class="col-sm-3 col-form-label">NPWP District</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('npwp_district_id') is-invalid @enderror" id="npwp_district_id" name="npwp_district_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $npwp_districtId = old('npwp_district_id')?old('npwp_district_id'):0;
                                            @endphp
                                            @foreach ($districtsNPWP as $d)
                                                <option @if ($npwp_districtId==$d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                            @endforeach
                                        </select>
                                        @error('npwp_district_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="npwp_subdistrict_id" class="col-sm-3 col-form-label">NPWP Sub District</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('npwp_subdistrict_id') is-invalid @enderror" id="npwp_subdistrict_id" name="npwp_subdistrict_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $npwp_subdistrictId = old('npwp_subdistrict_id')?old('npwp_subdistrict_id'):0;
                                            @endphp
                                            @foreach ($subdistrictsNPWP as $sd)
                                                <option @if ($npwp_subdistrictId==$sd->id) {{ 'selected' }} @endif value="{{ $sd->id }}">
                                                    {{ ucwords(strtolower($sd->sub_district_name)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('npwp_subdistrict_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Company Bank Information</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                            $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Bank Name</th>
                                        <th scope="col">Address</th>
                                        <th scope="col">Account Name</th>
                                        <th scope="col">Account Number</th>
                                        <th scope="col">COA</th>
                                        <th scope="col">Currency</th>
                                        <th scope="col">Swift Code</th>
                                        <th scope="col">BSB Code</th>
                                        <th scope="col">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++) <tr id="row{{ $i }}">
                                        <th scope="row">{{ $i + 1 }}</th>
                                        <td>
                                            <input type="text"
                                                class="form-control @error('bank_name'.$i) is-invalid @enderror"
                                                id="bank_name{{ $i }}" name="bank_name{{ $i }}" maxlength="255"
                                                value="@if (old('bank_name'.$i)){{ old('bank_name'.$i) }}@endif" />
                                            @error('bank_name'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <textarea id="bank_address{{ $i }}" name="bank_address{{ $i }}"
                                                maxlength="1024"
                                                class="form-control @error('bank_address'.$i) is-invalid @enderror"
                                                rows="3"
                                                aria-label="address">@if (old('bank_address'.$i)){{ old('bank_address'.$i) }}@endif</textarea>
                                            @error('bank_address'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control @error('account_name'.$i) is-invalid @enderror"
                                                id="account_name{{ $i }}" name="account_name{{ $i }}" maxlength="255"
                                                value="@if (old('account_name'.$i)){{ old('account_name'.$i) }}@endif" />
                                            @error('account_name'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control @error('account_no'.$i) is-invalid @enderror"
                                                id="account_no{{ $i }}" name="account_no{{ $i }}" maxlength="255"
                                                value="@if (old('account_no'.$i)){{ old('account_no'.$i) }}@endif" />
                                            @error('account_no'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <select class="form-select single-select @error('coa_id'.$i) is-invalid @enderror"
                                                id="coa_id{{ $i }}" name="coa_id{{ $i }}">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $coaId = old('coa_id'.$i)?old('coa_id'.$i) : 0;
                                                @endphp
                                                @foreach ($coas as $c)
                                                    <option @if ($coaId==$c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->coa_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('coa_id'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <select class="form-select single-select @error('currency_bank_id'.$i) is-invalid @enderror"
                                                id="currency_bank_id{{ $i }}" name="currency_bank_id{{ $i }}">
                                                <option value="#">Choose...</option>
                                                @php
                                                    $currencyBankId = old('currency_bank_id'.$i)?old('currency_bank_id'.$i) : 0;
                                                @endphp
                                                @foreach ($currency as $c)
                                                    <option @if ($currencyBankId==$c->id){{ 'selected' }}@endif
                                                            value="{{ $c->id }}">{{ $c->title_ind }}</option>
                                                @endforeach
                                            </select>
                                            @error('currency_bank_id'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control @error('swift_code'.$i) is-invalid @enderror"
                                                id="swift_code{{ $i }}" name="swift_code{{ $i }}" maxlength="255"
                                                value="@if (old('swift_code'.$i)){{ old('swift_code'.$i) }}@endif" />
                                            @error('swift_code'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                class="form-control @error('bsb_code'.$i) is-invalid @enderror"
                                                id="bsb_code{{ $i }}" name="bsb_code{{ $i }}" maxlength="255"
                                                value="@if (old('bsb_code'.$i)){{ old('bsb_code'.$i) }}@endif" />
                                            @error('bsb_code'.$i)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                        </td>
                                        </tr>
                                        @endfor
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-add-row" class="btn btn-primary px-5" style="margin-top: 15px;" value="Add Row">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
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
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@php
    $currencyHtml = '';
    $coaHtml = '';
@endphp
@foreach($currency as $p)
    @php
        $currencyHtml .= '<option value="'.$p->id.'">'.$p->title_ind.'</option>';
    @endphp
@endforeach
@foreach($coas as $coa)
    @php
        $coaHtml .= '<option value="'.$coa->id.'">'.$coa->coa_name.'</option>';
    @endphp
@endforeach

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
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

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
            '<tr id="row' + totalRow + '">' +
                '<th scope="row">' + rowNo + '</th>' +
                '<td>'+
                '<input type="text" class="form-control" id="bank_name'+totalRow+'" name="bank_name'+totalRow+'" maxlength="255" />'+
                '</td>'+
                '<td>'+
                '<textarea id="bank_address'+totalRow+'" name="bank_address'+totalRow+'" maxlength="1024" class="form-control" rows="3" aria-label="address"></textarea>'+
                '</td>'+
                '<td>'+
                '<input type="text" class="form-control" id="account_name'+totalRow+'" name="account_name'+totalRow+'" maxlength="255" />'+
                '</td>'+
                '<td>'+
                '<input type="text" class="form-control" id="account_no'+totalRow+'" name="account_no'+totalRow+'" maxlength="255" />'+
                '</td>'+
                '<td>'+
                '<select class="form-select single-select" id="coa_id'+totalRow+'" name="coa_id'+totalRow+'">'+
                '<option value="#">Choose...</option>{!! $coaHtml !!}'+
                '</select>'+
                '</td>'+
                '<td>'+
                '<select class="form-select single-select" id="currency_bank_id'+totalRow+'" name="currency_bank_id'+totalRow+'">'+
                '<option value="#">Choose...</option>{!! $currencyHtml !!}'+
                '</select>'+
                '</td>'+
                '<td>'+
                '<input type="text" class="form-control" id="swift_code'+totalRow+'" name="swift_code'+totalRow+'" maxlength="255" />'+
                '</td>'+
                '<td><input type="text" class="form-control" id="bsb_code'+totalRow+'" name="bsb_code'+totalRow+'" maxlength="255" /></td>'+
                '<td><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
                '</tr>';
            $("#new-row").append(vHtml);
            $("#totalRow").val(rowNo);

            $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass(
            'w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
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
            $("#postcode").val('');

            dispCity('province_id', '#province_id option:selected', '{{ url("disp_city") }}', '#city_id');
        });

        $('#city_id').change(function() {
            $("#district_id").empty();
            $("#subdistrict_id").empty();
            $("#district_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#subdistrict_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#postcode").val('');

            dispDistrict(
                'city_id',
                '#city_id option:selected',
                '{{ url("disp_district") }}',
                '#district_id'
            );
        });

        $('#district_id').change(function() {
            $("#subdistrict_id").empty();
            $("#subdistrict_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#postcode").val('');

            dispSubDistrict(
                'district_id',
                '#district_id option:selected',
                '{{ url("disp_sub_district") }}',
                '#subdistrict_id'
            );
        });

        $('#subdistrict_id').change(function() {
            $("#postcode").val('');

            dispPoscode(
                'subdistrict_id',
                '#subdistrict_id option:selected',
                '{{ url("disp_sub_district_postcode") }}',
                '#postcode'
            );
        });

        $('#npwp_province_id').change(function() {
            $("#npwp_city_id").empty();
            $("#npwp_city_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#npwp_district_id").empty();
            $("#npwp_district_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#npwp_subdistrict_id").empty();
            $("#npwp_subdistrict_id").append(
                `<option value="#">Choose...</option>`
            );

            dispCity(
                'province_id',
                '#npwp_province_id option:selected',
                '{{ url("disp_city") }}',
                '#npwp_city_id'
            );
        });

        $('#npwp_city_id').change(function() {
            $("#npwp_district_id").empty();
            $("#npwp_district_id").append(
                `<option value="#">Choose...</option>`
            );
            $("#npwp_subdistrict_id").empty();
            $("#npwp_subdistrict_id").append(
                `<option value="#">Choose...</option>`
            );

            dispDistrict(
                'city_id',
                '#npwp_city_id option:selected',
                '{{ url("disp_district") }}',
                '#npwp_district_id'
            );
        });

        $('#npwp_district_id').change(function() {
            $("#npwp_subdistrict_id").empty();
            $("#npwp_subdistrict_id").append(
                `<option value="#">Choose...</option>`
            );

            dispSubDistrict(
                'district_id',
                '#npwp_district_id option:selected',
                '{{ url("disp_sub_district") }}',
                '#npwp_subdistrict_id'
            );
        });
    });
</script>
@endsection
