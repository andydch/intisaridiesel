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
                                <span class="col-sm-3 col-form-label">Entity Type*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('entityType_id') is-invalid @enderror" id="entityType_id" name="entityType_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $entityTypeId = old('entityType_id')?old('entityType_id'):0;
                                        @endphp
                                        @foreach ($entityType as $e)
                                            <option @if ($entityTypeId==$e->id){{ 'selected' }}@endif value="{{ $e->id }}">{{ $e->title_ind }}</option>
                                        @endforeach
                                    </select>
                                    @error('entityType_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Courier Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="courierName" name="courierName"
                                        class="form-control @error('courierName') is-invalid @enderror" maxlength="255"
                                        value="@if (old('courierName')){{ old('courierName') }}@endif">
                                    @error('courierName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Office Address*</span>
                                <div class="col-sm-9">
                                    <textarea id="office_address" name="office_address" maxlength="1024" class="form-control @error('office_address') is-invalid @enderror"
                                        rows="3">@if (old('office_address')){{ old('office_address') }}@endif</textarea>
                                    @error('office_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Province*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('province_id') is-invalid @enderror" id="province_id" name="province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $provinceId = old('province_id')?old('province_id'):0;
                                        @endphp
                                        @foreach ($province as $p)
                                            <option @if ($provinceId==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">City*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $cityId = old('city_id')?old('city_id'):0;
                                        @endphp
                                        @foreach ($cities as $c)
                                            <option @if ($cityId==$c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">District*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('district_id') is-invalid @enderror" id="district_id" name="district_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $districtId = old('district_id')?old('district_id'):0;
                                        @endphp
                                        @foreach ($districts as $d)
                                            <option @if ($districtId==$d->id){{ 'selected' }}@endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Sub District*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('subdistrict_id') is-invalid @enderror" id="subdistrict_id" name="subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $subdistrictId = old('subdistrict_id')?old('subdistrict_id'):0;
                                        @endphp
                                        @foreach ($subdistricts as $sd)
                                            <option @if ($subdistrictId==$sd->id){{ 'selected' }}@endif value="{{ $sd->id }}">
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
                                <span class="col-sm-3 col-form-label">Postcode</span>
                                <div class="col-sm-9">
                                    <input type="text" id="postcode" name="postcode"
                                        class="form-control @error('postcode') is-invalid @enderror" maxlength="6"
                                        value="@if (old('postcode')){{ old('postcode') }}@endif">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Courier Email</span>
                                <div class="col-sm-9">
                                    <input type="email" id="courier_email" name="courier_email"
                                        class="form-control @error('courier_email') is-invalid @enderror" maxlength="64"
                                        value="@if (old('courier_email')){{ old('courier_email') }}@endif">
                                    @error('courier_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 1</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone1" name="phone1"
                                        class="form-control @error('phone1') is-invalid @enderror" maxlength="32"
                                        value="@if (old('phone1')){{ old('phone1') }}@endif">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 2</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone2" name="phone2"
                                        class="form-control @error('phone2') is-invalid @enderror" maxlength="32"
                                        value="@if (old('phone2')){{ old('phone2') }}@endif">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="pic1Name" name="pic1Name"
                                        class="form-control @error('pic1Name') is-invalid @enderror" maxlength="255"
                                        value="@if (old('pic1Name')){{ old('pic1Name') }}@endif">
                                    @error('pic1Name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Phone</span>
                                <div class="col-sm-9">
                                    <input type="text" id="picphone1" name="picphone1"
                                        class="form-control @error('picphone1') is-invalid @enderror" maxlength="32"
                                        value="@if (old('picphone1')){{ old('picphone1') }}@endif">
                                    @error('picphone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Email</span>
                                <div class="col-sm-9">
                                    <input type="email" id="pic_email1" name="pic_email1"
                                        class="form-control @error('pic_email1') is-invalid @enderror" maxlength="64"
                                        value="@if (old('pic_email1')){{ old('pic_email1') }}@endif">
                                    @error('pic_email1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP no</span>
                                <div class="col-sm-9">
                                    <input type="text" id="npwp_no" name="npwp_no"
                                        class="form-control @error('npwp_no') is-invalid @enderror" maxlength="24"
                                        value="@if (old('npwp_no')){{ old('npwp_no') }}@endif">
                                    @error('npwp_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @php
                                $disabled = '';
                                $npwp_address = old('npwp_address');
                                $npwp_province_id = old('npwp_province_id');
                                $npwp_city_id = old('npwp_city_id');
                                $npwp_district_id = old('npwp_district_id');
                                $npwp_subdistrict_id = old('npwp_subdistrict_id');
                            @endphp
                            @if(old('same_as_officeaddress')=='on')
                                @php
                                    $disabled = 'disabled';
                                    // $npwp_address = old('office_address');
                                    // $npwp_province_id = old('province_id');
                                    // $npwp_city_id = old('city_id');
                                    // $npwp_district_id = old('district_id');
                                    // $npwp_subdistrict_id = old('subdistrict_id');
                                @endphp
                            @endif
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Same as office address?</span>
                                <div class="col-sm-9">
                                    <input class="form-check-input" type="checkbox" id="same_as_officeaddress"
                                        name="same_as_officeaddress" @if(old('same_as_officeaddress')=='on'){{ 'checked' }}@endif>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP Address</span>
                                <div class="col-sm-9">
                                    <textarea {{ $disabled }} id="npwp_address" name="npwp_address" maxlength="1024"
                                        class="form-control @error('npwp_address') is-invalid @enderror" rows="3">{{ $npwp_address }}</textarea>
                                    @error('npwp_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP Province</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_province_id') is-invalid @enderror" id="npwp_province_id" name="npwp_province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_provinceId = $npwp_province_id;
                                        @endphp
                                        @foreach ($province as $p)
                                            <option @if ($npwp_provinceId==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('npwp_province_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP City</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_city_id') is-invalid @enderror" id="npwp_city_id" name="npwp_city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_cityId = $npwp_city_id;
                                        @endphp
                                        @foreach ($citiesNPWP as $c)
                                            <option @if ($npwp_cityId==$c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('npwp_city_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP District</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_district_id') is-invalid @enderror" id="npwp_district_id" name="npwp_district_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_districtId = $npwp_district_id;
                                        @endphp
                                        @foreach ($districtsNPWP as $d)
                                            <option @if ($npwp_districtId==$d->id){{ 'selected' }}@endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('npwp_district_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP Sub District</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_subdistrict_id') is-invalid @enderror" id="npwp_subdistrict_id" name="npwp_subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_subdistrictId = $npwp_subdistrict_id;
                                        @endphp
                                        @foreach ($subdistrictsNPWP as $sd)
                                            <option @if ($npwp_subdistrictId==$sd->id){{ 'selected' }}@endif value="{{ $sd->id }}">
                                                {{ ucwords(strtolower($sd->sub_district_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('npwp_subdistrict_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Courier Bank Information</h6>
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
                                        <th scope="col">Currency</th>
                                        <th scope="col">Swift Code</th>
                                        <th scope="col">BSB Code</th>
                                        <th scope="col">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++)
                                        <tr id="row{{ $i }}">
                                            <th scope="row">{{ $i + 1 }}.</th>
                                            <td>
                                                <input type="text" class="form-control @error('bank_name'.$i) is-invalid @enderror"
                                                    id="bank_name{{ $i }}" name="bank_name{{ $i }}" maxlength="255"
                                                    value="@if (old('bank_name'.$i)){{ old('bank_name'.$i) }}@endif" />
                                                @error('bank_name'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <textarea id="bank_address{{ $i }}" name="bank_address{{ $i }}" maxlength="1024"
                                                    class="form-control @error('bank_address'.$i) is-invalid @enderror" rows="3"
                                                    >@if (old('bank_address'.$i)){{ old('bank_address'.$i) }}@endif</textarea>
                                                @error('bank_address'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control @error('account_name'.$i) is-invalid @enderror"
                                                    id="account_name{{ $i }}" name="account_name{{ $i }}" maxlength="255"
                                                    value="@if (old('account_name'.$i)){{ old('account_name'.$i) }}@endif" />
                                                @error('account_name'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control @error('account_no'.$i) is-invalid @enderror"
                                                    id="account_no{{ $i }}" name="account_no{{ $i }}" maxlength="255"
                                                    value="@if (old('account_no'.$i)){{ old('account_no'.$i) }}@endif" />
                                                @error('account_no'.$i)
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
                                                        <option @if ($currencyBankId==$c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->title_ind }}</option>
                                                    @endforeach
                                                </select>
                                                @error('currency_bank_id'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control @error('swift_code'.$i) is-invalid @enderror"
                                                    id="swift_code{{ $i }}" name="swift_code{{ $i }}" maxlength="255"
                                                    value="@if (old('swift_code'.$i)){{ old('swift_code'.$i) }}@endif" />
                                                @error('swift_code'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control @error('bsb_code'.$i) is-invalid @enderror"
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
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->

<!-- Full screen modal -->
<div class="modal fade" id="cust-info" aria-hidden="true" aria-labelledby="cust-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="cust-info" style="color: #fff;">Courier Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color: #fff;">
                The following are similar courier names:<br />
                <span id="msg-modal"></span><br />
                Make sure the courier name entered does not match the name of an existing courier.<br /><br />
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

@php
    $currencyHtml = '';
@endphp
@foreach($currency as $p)
    @php
        $currencyHtml .= '<option value="'.$p->id.'">'.$p->title_ind.'</option>';
    @endphp
@endforeach

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function CourierInfo(slug){
        var fd = new FormData();
        fd.append('slug', slug);
        $.ajax({
            url: '{{ url("disp_courierinfo") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].couriers;
                let totCouriers = o.length;
                if (totCouriers > 0) {
                    let post_code = o[0].post_code.replace('000000','');
                    let vHtml = 'Detail Info: <br />'+o[0].name+'<br />'+o[0].office_address+', '+
                    o[0].sub_district_name.toLowerCase().ucwords()+', '+
                    o[0].district_name+'<br />'+o[0].city_name+'<br />'+
                    o[0].province_name+'<br />'+o[0].country_name+' '+post_code;
                    $('#msg-modal-info').html(vHtml);
                }
            },
        });
    }

    $(document).ready(function() {
        $("#courierName").keyup(function() {
            let courierName = $("#courierName").val();
            $("#courierName").val(courierName.toUpperCase());
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            location.href = '{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}';
        });

        // $('#office_address').keyup(function () {
        //     $('#npwp_address').val($('#office_address').val());
        // });

        $('#same_as_officeaddress').change(function () {
            if (this.checked){
                $('#npwp_address').val('');
                $('#npwp_address').prop("disabled",true);

                $("#npwp_province_id").val('#').change();
                $("#select2-npwp_province_id-container").css("color","black");
                $("#select2-npwp_city_id-container").css("color","black");
                $("#select2-npwp_district_id-container").css("color","black");
                $("#select2-npwp_subdistrict_id-container").css("color","black");

                $("#npwp_province_id").prop("disabled",true);
                $("#npwp_city_id").prop("disabled",true);
                $("#npwp_district_id").prop("disabled",true);
                $("#npwp_subdistrict_id").prop("disabled",true);
            }else{
                $('#npwp_address').prop("disabled",false);
                $("#npwp_province_id").prop("disabled",false);
                $("#npwp_city_id").prop("disabled",false);
                $("#npwp_district_id").prop("disabled",false);
                $("#npwp_subdistrict_id").prop("disabled",false);

                $("#select2-npwp_province_id-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-npwp_city_id-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-npwp_district_id-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-npwp_subdistrict_id-container").css("color","rgb(255 255 255 / 70%)");
            }
        });

        $('#courierName').change(function() {
            $('#msg-modal-info').html('&nbsp;');
            var fd = new FormData();
            fd.append('courierName', $('#courierName').val());
            $.ajax({
                url: '{{ url("disp_similar_couriername") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].couriers;
                    let totCourier = o.length;
                    if (totCourier > 0) {
                        let vHtml = '';
                        for (let i = 0; i < totCourier; i++) {
                            vHtml +=(i+1)+'. <a href="#" onclick="CourierInfo(\''+o[i].slug+'\');" style="text-decoration: underline;color:#fff;">'+
                            o[i].name+'</a> - No NPWP: '+o[i].npwp_no+'<br />';
                        }
                        $('#msg-modal').html(vHtml);
                        $('#cust-info').modal('show');
                    }
                },
            });
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
            '<tr id="row' + totalRow + '">' +
                '<th scope="row"><span class="col-form-label">' + rowNo + '.</span></th>' +
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
            $("#city_id").append(`<option value="#">Choose...</option>`);
            $("#district_id").empty();
            $("#district_id").append(`<option value="#">Choose...</option>`);
            $("#subdistrict_id").empty();
            $("#subdistrict_id").append(`<option value="#">Choose...</option>`);
            $("#postcode").val('');
            dispCity('province_id', '#province_id option:selected', '{{ url("disp_city") }}', '#city_id');
            // $('#npwp_province_id').val($('#province_id').val()).change();
        });

        $('#city_id').change(function() {
            $("#district_id").empty();
            $("#subdistrict_id").empty();
            $("#district_id").append(`<option value="#">Choose...</option>`);
            $("#subdistrict_id").append(`<option value="#">Choose...</option>`);
            $("#postcode").val('');
            dispDistrict('city_id','#city_id option:selected','{{ url("disp_district") }}','#district_id');
            // $('#npwp_city_id').val($('#city_id').val()).change();
        });

        $('#district_id').change(function() {
            $("#subdistrict_id").empty();
            $("#subdistrict_id").append(`<option value="#">Choose...</option>`);
            $("#postcode").val('');
            dispSubDistrict('district_id','#district_id option:selected','{{ url("disp_sub_district") }}','#subdistrict_id');
            // $('#npwp_district_id').val($('#district_id').val()).change();
        });

        $('#subdistrict_id').change(function() {
            $("#postcode").val('');
            dispPoscode('subdistrict_id','#subdistrict_id option:selected','{{ url("disp_sub_district_postcode") }}','#postcode');
            // $('#npwp_subdistrict_id').val($('#subdistrict_id').val()).change();
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
