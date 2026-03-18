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
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
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
                                            <option @if($entityTypeId==$e->id) {{ 'selected' }} @endif value="{{ $e->id }}">{{ $e->title_ind }}</option>
                                        @endforeach
                                    </select>
                                    @error('entityType_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Customer Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="customerName" name="customerName" class="form-control @error('customerName') is-invalid @enderror"
                                        maxlength="255" value="@if(old('customerName')){{ old('customerName') }}@endif">
                                    @error('customerName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Customer Code*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="cust_unique_code" name="cust_unique_code"
                                        class="form-control @error('cust_unique_code') is-invalid @enderror" maxlength="5"
                                        value="@if(old('cust_unique_code')){{ old('cust_unique_code') }}@endif">
                                    @error('cust_unique_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="cust_code_warn"></div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Office Address*</span>
                                <div class="col-sm-9">
                                    <textarea id="office_address" name="office_address" maxlength="1024"
                                        class="form-control @error('office_address') is-invalid @enderror"
                                        rows="3">@if(old('office_address')){{ old('office_address') }}@endif</textarea>
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
                                            <option @if($provinceId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
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
                                            <option @if($cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_name }}</option>
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
                                        <option @if($districtId==$d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
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
                                            <option @if($subdistrictId==$sd->id) {{ 'selected' }} @endif value="{{ $sd->id }}">
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
                                <span class="col-sm-3 col-form-label">Postcode*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="postcode" name="postcode" class="form-control @error('postcode') is-invalid @enderror"
                                        maxlength="6" value="@if(old('postcode')){{ old('postcode') }}@endif">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Customer Email</span>
                                <div class="col-sm-9">
                                    <input type="email" id="customer_email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror"
                                        maxlength="64" value="@if(old('customer_email')){{ old('customer_email') }}@endif">
                                    @error('customer_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 1</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone1" name="phone1" class="form-control @error('phone1') is-invalid @enderror"
                                        maxlength="32" value="@if(old('phone1')){{ old('phone1') }}@endif">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 2</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone2" name="phone2" class="form-control @error('phone2') is-invalid @enderror"
                                        maxlength="32" value="@if(old('phone2')){{ old('phone2') }}@endif">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC 1 Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="pic1Name" name="pic1Name"
                                        class="form-control @error('pic1Name') is-invalid @enderror" maxlength="255"
                                        value="@if(old('pic1Name')){{ old('pic1Name') }}@endif">
                                    @error('pic1Name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Phone 1</span>
                                <div class="col-sm-9">
                                    <input type="text" id="picphone1" name="picphone1"
                                        class="form-control @error('picphone1') is-invalid @enderror" maxlength="32"
                                        value="@if(old('picphone1')){{ old('picphone1') }}@endif">
                                    @error('picphone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Email 1</span>
                                <div class="col-sm-9">
                                    <input type="email" id="pic_email1" name="pic_email1"
                                        class="form-control @error('pic_email1') is-invalid @enderror" maxlength="64"
                                        value="@if(old('pic_email1')){{ old('pic_email1') }}@endif">
                                    @error('pic_email1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC 2 Name</span>
                                <div class="col-sm-9">
                                    <input type="text" id="pic2Name" name="pic2Name"
                                        class="form-control @error('pic2Name') is-invalid @enderror" maxlength="255"
                                        value="@if(old('pic2Name')){{ old('pic2Name') }}@endif">
                                    @error('pic2Name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Phone 2</span>
                                <div class="col-sm-9">
                                    <input type="text" id="picphone2" name="picphone2"
                                        class="form-control @error('picphone2') is-invalid @enderror" maxlength="32"
                                        value="@if(old('picphone2')) {{ old('picphone2') }} @endif">
                                    @error('picphone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Email 2</span>
                                <div class="col-sm-9">
                                    <input type="email" id="pic_email2" name="pic_email2"
                                        class="form-control @error('pic_email2') is-invalid @enderror" maxlength="64"
                                        value="@if(old('pic_email2')) {{ old('pic_email2') }} @endif">
                                    @error('pic_email2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <hr />
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP no</span>
                                <div class="col-sm-9">
                                    <input type="text" id="npwp_no" name="npwp_no"
                                        class="form-control @error('npwp_no') is-invalid @enderror" maxlength="24"
                                        value="@if(old('npwp_no')){{ old('npwp_no') }} @endif">
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
                                        class="form-control @error('npwp_address') is-invalid @enderror"
                                        rows="3">{{ $npwp_address }}</textarea>
                                    @error('npwp_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP Province</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_province_id') is-invalid @enderror"
                                        id="npwp_province_id" name="npwp_province_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_provinceId = $npwp_province_id;
                                        @endphp
                                        @foreach ($province as $p)
                                            <option @if($npwp_provinceId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
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
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_city_id') is-invalid @enderror"
                                        id="npwp_city_id" name="npwp_city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_cityId = $npwp_city_id;
                                        @endphp
                                        @foreach ($citiesNPWP as $c)
                                            <option @if($npwp_cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_name }}</option>
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
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_district_id') is-invalid @enderror"
                                        id="npwp_district_id" name="npwp_district_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_districtId = $npwp_district_id;
                                        @endphp
                                        @foreach ($districtsNPWP as $d)
                                            <option @if($npwp_districtId==$d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
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
                                    <select {{ $disabled }} class="form-select single-select @error('npwp_subdistrict_id') is-invalid @enderror"
                                        id="npwp_subdistrict_id" name="npwp_subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $npwp_subdistrictId = $npwp_subdistrict_id;
                                        @endphp
                                        @foreach ($subdistrictsNPWP as $sd)
                                            <option @if($npwp_subdistrictId==$sd->id) {{ 'selected' }} @endif value="{{ $sd->id }}">
                                                {{ ucwords(strtolower($sd->sub_district_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('npwp_subdistrict_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <hr />
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Credit Limit*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="credit_limit" name="credit_limit" onkeyup="formatAmount($(this));"
                                        class="form-control @error('credit_limit') is-invalid @enderror" maxlength="22"
                                        value="@if(old('credit_limit')){{ old('credit_limit') }}@endif">
                                    @error('credit_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Limit Balance*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="limit_balance" name="limit_balance"
                                        class="form-control @error('limit_balance') is-invalid @enderror" maxlength="22"
                                        value="@if(old('limit_balance')){{ old('limit_balance') }}@endif">
                                    @error('limit_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">TOP (day)*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="top_in_day" name="top_in_day"
                                        class="form-control @error('top_in_day') is-invalid @enderror" maxlength="3"
                                        value="@if(old('top_in_day')){{ old('top_in_day') }}@endif">
                                    @error('top_in_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span for="branch_id" class="col-sm-3 col-form-label">Branch*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('branch_id') is-invalid @enderror" id="branch_id" name="branch_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $branch_id = old('branch_id');
                                        @endphp
                                        @foreach ($branches as $branch)
                                            <option @if($branch_id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Salesman*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('salesman_id') is-invalid @enderror" id="salesman_id" name="salesman_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $salesmanId = old('salesman_id')?old('salesman_id'):0;
                                        @endphp
                                        @foreach ($salesman as $s)
                                            <option @if($salesmanId==$s->user_id) {{ 'selected' }} @endif value="{{ $s->user_id }}">
                                                {{ ucwords(strtolower($s->salesman_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('salesman_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Salesman 2</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('salesman_id2') is-invalid @enderror" id="salesman_id2" name="salesman_id2">
                                        <option value="#">Choose...</option>
                                        @php
                                            $salesmanId2 = old('salesman_id2')?old('salesman_id2'):0;
                                        @endphp
                                        @foreach ($salesman as $s)
                                            <option @if($salesmanId2==$s->user_id) {{ 'selected' }} @endif value="{{ $s->user_id }}">
                                                {{ ucwords(strtolower($s->salesman_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('salesman_id2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Customer Status*</span>
                                <div class="col-sm-9">
                                    <select class="form-select @error('cust_status') is-invalid @enderror" id="cust_status" name="cust_status">
                                        @php
                                            $cust_status = old('cust_status')?old('cust_status'):'Y';
                                        @endphp
                                        <option @if($cust_status=='Y') {{ 'selected' }} @endif value="Y">Active</option>
                                        <option @if($cust_status=='N') {{ 'selected' }} @endif value="N">Not Active</option>
                                    </select>
                                    @error('cust_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Payment Status*</span>
                                <div class="col-sm-9">
                                    <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status">
                                        @php
                                            $payment_status = old('payment_status')?old('payment_status'):'Y';
                                        @endphp
                                        <option @if($payment_status=='Y') {{ 'selected' }} @endif value="Y">{{ ENV('PAYMENT_STATUS_LANCAR') }}</option>
                                        <option @if($payment_status=='N') {{ 'selected' }} @endif value="N">{{ ENV('PAYMENT_STATUS_TIDAK_LANCAR') }}</option>
                                    </select>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Beginning Balance Piutang</span>
                                <div class="col-sm-9">
                                    <input type="text" id="beginning_balance" name="beginning_balance" onkeyup="formatAmount($(this));"
                                        class="form-control @error('beginning_balance') is-invalid @enderror" maxlength="25"
                                        value="@if(old('beginning_balance')){{ !is_null(old('beginning_balance'))?old('beginning_balance'):'' }}@endif">
                                    @error('beginning_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>     
                            </div>
                            {{-- <div class="input-group">
                                <input type="submit" class="btn btn-light px-5" style="margin-top: 15px;" value="Submit">
                            </div> --}}
                        </div>
                    </div>
                    <hr />
                    <h6 class="mb-0 text-uppercase">Shipment Address</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = old('totalRow')?old('totalRow'):$totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 3%;">#</th>
                                        <th scope="col" style="width: 20%;">Address</th>
                                        <th scope="col">Province</th>
                                        <th scope="col">City</th>
                                        <th scope="col">District</th>
                                        <th scope="col">Sub District</th>
                                        <th scope="col" style="width: 7%;">Postcode</th>
                                        <th scope="col" style="width: 10%;">Phone</th>
                                        <th scope="col" style="width: 3%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @for ($i = 0; $i < $totRow; $i++)
                                        <tr id="row{{ $i }}">
                                            <th scope="row">{{ $i+1 }}</th>
                                            <td>
                                                @php
                                                    $disabled = '';
                                                    $npwp_address = old('address_addr'.$i)?old('address_addr'.$i):'';
                                                    $npwp_province_id = old('province_id_addr'.$i)?old('province_id_addr'.$i):0;
                                                    $npwp_city_id = old('city_id_addr'.$i)?old('city_id_addr'.$i):0;
                                                    $npwp_district_id = old('district_id_addr'.$i)?old('district_id_addr'.$i):0;
                                                    $npwp_subdistrict_id = old('sub_district_id_addr'.$i)?old('sub_district_id_addr'.$i):0;
                                                    $postcode = old('post_code_shipment'.$i)?old('post_code_shipment'.$i):'';
                                                    $phone = old('phone_addr'.$i)?old('phone_addr'.$i):'';
                                                @endphp
                                                @if(old('same_as_officeaddress_shipment'.$i)=='on')
                                                    @php
                                                        $disabled = 'disabled';
                                                        // $npwp_address = old('office_address');
                                                        // $npwp_province_id = old('province_id');
                                                        // $npwp_city_id = old('city_id');
                                                        // $npwp_district_id = old('district_id');
                                                        // $npwp_subdistrict_id = old('subdistrict_id');
                                                        // $postcode = old('postcode');
                                                        // $phone = old('phone1');
                                                    @endphp
                                                @endif
                                                <input type="checkbox" id="same_as_officeaddress_shipment{{ $i }}" onclick="sameAsAddressShipment({{ $i }});"
                                                    name="same_as_officeaddress_shipment{{ $i }}"
                                                    @if(old('same_as_officeaddress_shipment'.$i)=='on'){{ 'checked' }}@endif> same as office address?<br />
                                                <textarea {{ $disabled }} id="address_addr{{ $i }}" name="address_addr{{ $i }}" maxlength="1024"
                                                    class="form-control @error('address_addr'.$i) is-invalid @enderror" rows="3">{{ $npwp_address }}</textarea>
                                                @error('address_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <select {{ $disabled }} class="form-select single-select @error('province_id_addr'.$i) is-invalid @enderror"
                                                    id="province_id_addr{{ $i }}" name="province_id_addr{{ $i }}" onchange="prepCity(this.value, {{ $i }})">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $provinceId = $npwp_province_id;
                                                    @endphp
                                                    @foreach ($province as $p)
                                                        <option @if($provinceId==$p->id){{ 'selected' }}@endif value="{{ $p->id }}">{{ $p->province_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('province_id_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <select {{ $disabled }} class="form-select single-select @error('city_id_addr'.$i) is-invalid @enderror"
                                                    id="city_id_addr{{ $i }}" name="city_id_addr{{ $i }}" onchange="prepDistrict(this.value,{{ $i }})">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $cityId = $npwp_city_id;
                                                        $qCity = \App\Models\Mst_city::where('province_id','=',$npwp_province_id)
                                                        ->where('active','=','Y')
                                                        ->get();
                                                    @endphp
                                                    @foreach ($qCity as $c)
                                                        <option @if($cityId==$c->id){{ 'selected' }}@endif value="{{ $c->id }}">{{ $c->city_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('city_id_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <select {{ $disabled }} class="form-select single-select @error('district_id_addr'.$i) is-invalid @enderror"
                                                    id="district_id_addr{{ $i }}" name="district_id_addr{{ $i }}" onchange="prepSubDistrict(this.value,{{ $i }})">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $districtId = $npwp_district_id;
                                                        $qDistrict = \App\Models\Mst_district::where('city_id','=',$npwp_city_id)
                                                        ->where('active','=','Y')
                                                        ->get();
                                                    @endphp
                                                    @foreach ($qDistrict as $d)
                                                        <option @if($districtId==$d->id){{ 'selected' }}@endif value="{{ $d->id }}">{{ $d->district_name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('district_id_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <select {{ $disabled }} class="form-select single-select @error('sub_district_id_addr'.$i) is-invalid @enderror"
                                                    id="sub_district_id_addr{{ $i }}" name="sub_district_id_addr{{ $i  }}" onchange="prepPostCode(this.value,{{ $i }})">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $subdistrictId = $npwp_subdistrict_id;
                                                        $qSubDistrict = \App\Models\Mst_sub_district::where('district_id','=',$npwp_district_id)
                                                        ->where('active','=','Y')
                                                        ->get();
                                                    @endphp
                                                    @foreach ($qSubDistrict as $d)
                                                        <option @if($subdistrictId==$d->id){{ 'selected' }}@endif value="{{ $d->id }}">{{ ucwords(strtolower($d->sub_district_name)) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('sub_district_id_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input {{ $disabled }} type="text" class="form-control @error('post_code_shipment'.$i) is-invalid @enderror"
                                                    id="post_code_shipment{{ $i }}" name="post_code_shipment{{ $i }}" maxlength="6" value="{{ $postcode }}" />
                                                @error('post_code_shipment'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input {{ $disabled }} type="text" class="form-control @error('phone_addr'.$i) is-invalid @enderror"
                                                    id="phone_addr{{ $i }}" name="phone_addr{{ $i }}" maxlength="32" value="{{ $phone }}" />
                                                @error('phone_addr'.$i)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                            </td>
                                        </tr>
                                        {{-- @endif --}}
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
                <h1 class="modal-title fs-5" id="cust-info-title" style="color: #fff;">Customer Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color: #fff;">
                The following are similar customer names/codes:<br />
                <span id="msg-modal" style="color: #fff;"></span><br />
                <span id="msg-warn" style="color:red;font-weight:bold;">Make sure the customer name/code entered does not match the name/code of an existing customer.</span><br /><br />
                <span id="msg-modal-info" style="font-weight: bold;"></span>
            </div>
            {{-- <div class="modal-footer">
                <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to first</button>
            </div> --}}
        </div>
    </div>
</div>

@endsection

@section('script')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function formatAmount(elm){
        let amount = elm.val().replaceAll(',','');
        if(amount===''){elm.val('');return false;}
        if(isNaN(amount)){elm.val('');return false;}
        amount = parseFloat(amount).numberFormat(0,'.',',');    // without decimal
        elm.val(amount);

        // set cursor position
        console.log(elm.val().length);
        // if(elm.val().length>=3){
        //     elm.selectRange(elm.val().length-3); // set cursor position
        // }
    }

    function prepCity(val, idx) {
        $('#city_id_addr'+idx).empty();
        $('#city_id_addr'+idx).append(
        `<option value="#">Choose...</option>`
        );
        $("#district_id_addr"+idx).empty();
        $("#district_id_addr"+idx).append(
        `<option value="#">Choose...</option>`
        );
        $("#sub_district_id_addr"+idx).empty();
        $("#sub_district_id_addr"+idx).append(
        `<option value="#">Choose...</option>`
        );
        dispCity(
            'province_id',
            '#province_id_addr'+idx+' option:selected',
            '{{ url('disp_city') }}',
            '#city_id_addr'+idx
        );
    }

    function prepDistrict(val, idx) {
        $("#district_id_addr"+idx).empty();
        $("#district_id_addr"+idx).append(
        `<option value="#">Choose...</option>`
        );
        $("#sub_district_id_addr"+idx).empty();
        $("#sub_district_id_addr"+idx).append(
        `<option value="#">Choose...</option>`
        );
        dispDistrict(
            'city_id',
            '#city_id_addr'+idx+' option:selected',
            '{{ url('disp_district') }}',
            '#district_id_addr'+idx
        );
    }

    function prepSubDistrict(val, idx) {
        $("#sub_district_id_addr"+idx).empty();
        $("#sub_district_id_addr"+idx).append(
        `<option value="#">Choose...</option>`
        );
        dispSubDistrict(
            'district_id',
            '#district_id_addr'+idx+' option:selected',
            '{{ url('disp_sub_district') }}',
            '#sub_district_id_addr'+idx
        );
    }

    function prepPostCode(val, idx) {
        dispPoscode(
            'subdistrict_id',
            '#sub_district_id_addr'+idx+' option:selected',
            '{{ url('disp_sub_district_postcode') }}',
            '#post_code_shipment'+idx
        );
    }

    function CustInfo(slug){
        var fd = new FormData();
        fd.append('slug', slug);
        $.ajax({
            url: '{{ url("disp_custinfo") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].customers;
                let totCust = o.length;
                if (totCust > 0) {
                    let post_code = o[0].post_code.replace('000000','');
                    let vHtml = 'Detail Info: <br/>'+o[0].custName+'<br/>'+o[0].office_address+', '+o[0].sub_district_name.toLowerCase().ucwords()+', '+
                    o[0].district_name+'<br/>'+o[0].city_name+'<br />'+o[0].province_name+'<br/>'+o[0].country_name+' '+post_code;
                    $('#msg-modal-info').html(vHtml);
                }
            },
        });
    }

    function sameAsAddressShipment(idx){
        $('#same_as_officeaddress_shipment'+idx).change(function () {
            if (this.checked){
                $('#address_addr'+idx).val('');
                $('#address_addr'+idx).prop("disabled",true);
                $("#province_id_addr"+idx).val('#').change();
                $("#select2-province_id_addr"+idx+"-container").css("color","black");
                $("#select2-city_id_addr"+idx+"-container").css("color","black");
                $("#select2-district_id_addr"+idx+"-container").css("color","black");
                $("#select2-sub_district_id_addr"+idx+"-container").css("color","black");
                $('#post_code_shipment'+idx).val($('#postcode').val());
                $('#phone_addr'+idx).val($('#phone1').val());

                $("#province_id_addr"+idx).prop("disabled",true);
                $("#city_id_addr"+idx).prop("disabled",true);
                $("#district_id_addr"+idx).prop("disabled",true);
                $("#sub_district_id_addr"+idx).prop("disabled",true);
                $('#post_code_shipment'+idx).prop("disabled",true);
                $('#phone_addr'+idx).prop("disabled",true);
            }else{
                $('#address_addr'+idx).prop("disabled",false);
                $("#province_id_addr"+idx).prop("disabled",false);
                $("#city_id_addr"+idx).prop("disabled",false);
                $("#district_id_addr"+idx).prop("disabled",false);
                $("#sub_district_id_addr"+idx).prop("disabled",false);
                $('#post_code_shipment'+idx).prop("disabled",false);
                $('#phone_addr'+idx).prop("disabled",false);

                $("#select2-province_id_addr"+idx+"-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-city_id_addr"+idx+"-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-district_id_addr"+idx+"-container").css("color","rgb(255 255 255 / 70%)");
                $("#select2-sub_district_id_addr"+idx+"-container").css("color","rgb(255 255 255 / 70%)");

                var fd = new FormData();
                fd.append('country_id', 9999);
                $.ajax({
                    url: '{{ url("/disp_province") }}',
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].province;
                        let totProvince = o.length;
                        if (totProvince > 0) {
                            for (let i = 0; i < totProvince; i++) {
                                optionText=o[i].province_name; optionValue=o[i].id;
                                $("#province_id_addr"+idx).append(`<option value="${optionValue}">${optionText}</option>`);
                            }
                        }
                    },
                });
            }
        });
    }

    $(document).ready(function() {
        $("#cust_unique_code").keyup(function() {
            let cust_unique_code = $("#cust_unique_code").val();
            $("#cust_unique_code").val(cust_unique_code.toUpperCase());
        });
        $("#customerName").keyup(function() {
            let customerName = $("#customerName").val();
            $("#customerName").val(customerName.toUpperCase());
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            // history.back();
            location.href='{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}';
        });

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

                var fd = new FormData();
                fd.append('country_id', 9999);
                $.ajax({
                    url: '{{ url("/disp_province") }}',
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].province;
                        let totProvince = o.length;
                        if (totProvince > 0) {
                            for (let i = 0; i < totProvince; i++) {
                                optionText=o[i].province_name;
                                optionValue=o[i].id;
                                $("#npwp_province_id").append(`<option value="${optionValue}">${optionText}</option>`);
                            }
                        }
                    },
                });
            }
        });

        $('#customerName').change(function() {
            $('#msg-modal-info').html('&nbsp;');
            var fd = new FormData();
            fd.append('custName', $('#customerName').val());
            $.ajax({
                url: '{{ url("disp_similar_custname") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].customers;
                    let totCust = o.length;
                    if (totCust > 0) {
                        let vHtml = '';
                        for (let i = 0; i < totCust; i++) {
                            let customer_unique_code = o[i].customer_unique_code;
                            if(customer_unique_code===null){customer_unique_code = '-';}
                            vHtml+= (i+1)+'. <a href="#" onclick="CustInfo(\''+o[i].slug+'\');" '+
                            'style="text-decoration: underline;color:#fff;">'+o[i].custName+'</a> - Cust Code: '+customer_unique_code+'<br/>';
                        }
                        $('#msg-warn').text('');
                        $('#msg-modal').html(vHtml);
                        $('#cust-info').modal('show');
                    }
                },
            });
        });

        $('#cust_unique_code').change(function() {
            if($('#cust_unique_code').val().length<5){
                $("#save").attr("disabled","disabled");
            }else{
                $("#save").removeAttr("disabled");
            }

            $('#msg-modal-info').html('&nbsp;');
            var fd = new FormData();
            fd.append('custCode', $('#cust_unique_code').val());
            $.ajax({
                url: '{{ url("disp_similar_custcode") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].customers;
                    let totCust = o.length;
                    if (totCust > 0) {
                        $("#cust_unique_code").addClass("is-invalid");
                        $("#cust_code_warn").addClass("invalid-feedback");
                        $("#cust_code_warn").text('Customer Code already Exist.');
                        $("#customerName").focus();
                        $("#cust_code_warn").focus();

                        let vHtml = '';
                        for (let i = 0; i < totCust; i++) {
                            let customer_unique_code = o[i].customer_unique_code;
                            if(customer_unique_code===null){customer_unique_code = '-';}
                            vHtml+= (i+1)+'. <a href="#" onclick="CustInfo(\''+o[i].slug+'\');" '+
                            'style="text-decoration: underline;color:#fff;">'+o[i].custName+'</a> - Cust Code: '+customer_unique_code+'<br/>';
                        }
                        $('#msg-warn').text('Customer Code already Exist.');
                        $('#msg-modal').html(vHtml);
                        $('#cust-info').modal('show');

                        for (let i = 0; i < totCust; i++) {
                            let customer_unique_code = o[i].customer_unique_code;
                            if($('#cust_unique_code').val()===customer_unique_code){
                                $("#save").attr("disabled","disabled");
                                break;
                            }
                        }
                    }else{
                        $("#cust_unique_code").removeClass("is-invalid");
                        $("#cust_code_warn").removeClass("invalid-feedback");
                        $("#cust_code_warn").text('');
                        $("#cust_code_warn").focus();
                    }
                },
            });
        });

        $('#branch_id').change(function() {
            $("#salesman_id").empty();
            $("#salesman_id").append(`<option value="#">Choose</option>`);
            var fd = new FormData();
            fd.append('branch_id', $('#branch_id').val());
            $.ajax({
                url: '{{ url("disp_salesmanbybranchinfo") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].salesmans;
                    let totCust = o.length;
                    if (totCust > 0) {
                        let vHtml = '';
                        for (let i = 0; i < totCust; i++) {
                            optionText=o[i].salesman_name;
                            optionValue=o[i].user_id;
                            $("#salesman_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                },
            });
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            @php
                $provinceHtml = '';
            @endphp
            @foreach($province as $p)
                @php
                    $provinceHtml .= '<option value="'.$p->id.'">'.$p->province_name.'</option>';
                @endphp
            @endforeach

            let rowNo = (parseInt(totalRow)+1);
            let vHtml =
                '<tr id="row'+totalRow+'">'+
                '<th scope="row">'+rowNo+'</th>'+
                '<td>'+
                '<input class="" type="checkbox" id="same_as_officeaddress_shipment'+totalRow+'" onclick="sameAsAddressShipment('+totalRow+')" '+
                    'name="same_as_officeaddress_shipment'+totalRow+'"> same as office address?<br/>'+
                '<textarea id="address_addr'+totalRow+'" name="address_addr'+totalRow+'" maxlength="1024" class="form-control" rows="3" aria-label="address"></textarea>'+
                '</td>'+
                '<td><select class="form-select single-select" id="province_id_addr'+totalRow+'" '+
                    'name="province_id_addr'+totalRow+'" '+'onchange="javascript:prepCity(this.value,'+totalRow+');">'+
                    '<option value="#">Choose...</option>{!! $provinceHtml !!}</select></td>'+
                '<td><select class="form-select single-select" id="city_id_addr'+totalRow+'" '+
                    'name="city_id_addr'+totalRow+'" onchange="javascript:prepDistrict(this.value,'+totalRow+');">'+
                    '<option value="#">Choose...</option></select></td>'+
                '<td><select class="form-select single-select" id="district_id_addr'+totalRow+'" name="district_id_addr'+totalRow+'" '+
                    'onchange="javascript:prepSubDistrict(this.value,'+totalRow+');"><option value="#">Choose...</option></select></td>'+
                '<td><select class="form-select single-select" id="sub_district_id_addr'+totalRow+'" name="sub_district_id_addr'+totalRow+'" '+
                    'onchange="javascript:prepPostCode(this.value,'+totalRow+');">'+
                    '<option value="#">Choose...</option></select></td>'+
                '<td><input type="text" class="form-control" maxlength="6" id="post_code_shipment'+totalRow+'" name="post_code_shipment'+totalRow+'" /></td>'+
                '<td><input type="text" class="form-control" maxlength="32" id="phone_addr'+totalRow+'" name="phone_addr'+totalRow+'" /></td>'+
                '<td><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'">'+
                '</td>'+
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
                if ($("#rowCheck"+i).is(':checked')) {
                    // alert($("#row"+i).val());
                    $("#row"+i).remove();
                } else {
                    // alert(2);
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
            dispCity(
                'province_id',
                '#province_id option:selected',
                '{{ url('disp_city') }}',
                '#city_id'
            );
            // $("#npwp_province_id").val($('#province_id').val()).change();
        });

        $('#city_id').change(function() {
            $("#district_id").empty();
            $("#district_id").append(`<option value="#">Choose...</option>`);
            $("#subdistrict_id").empty();
            $("#subdistrict_id").append(`<option value="#">Choose...</option>`);
            $("#postcode").val('');
            dispDistrict(
                'city_id',
                '#city_id option:selected',
                '{{ url('disp_district') }}',
                '#district_id'
            );
            // $("#npwp_city_id").val($('#city_id').val()).change();
        });

        $('#district_id').change(function() {
            console.log($('#district_id').val());
            $("#subdistrict_id").empty();
            $("#subdistrict_id").append(`<option value="#">Choose...</option>`);
            $("#postcode").val('');
            dispSubDistrict(
                'district_id',
                '#district_id option:selected',
                '{{ url('disp_sub_district') }}',
                '#subdistrict_id'
            );
            // $("#npwp_district_id").val($('#district_id').val()).change();
        });

        $('#subdistrict_id').change(function() {
            $("#postcode").val('');
            dispPoscode(
                'subdistrict_id',
                '#subdistrict_id option:selected',
                '{{ url('disp_sub_district_postcode') }}',
                '#postcode'
            );
            // $("#npwp_subdistrict_id").val($('#subdistrict_id').val()).change();
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
                '{{ url('disp_city') }}',
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
                '{{ url('disp_district') }}',
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
                '{{ url('disp_sub_district') }}',
                '#npwp_subdistrict_id'
            );
        });
    });
</script>
@endsection
