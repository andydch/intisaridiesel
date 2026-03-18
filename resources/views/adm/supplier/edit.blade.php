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
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$supplier->slug) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                                <span class="col-sm-3 col-form-label">Supplier Type*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('supplierType_id') is-invalid @enderror" id="supplierType_id" name="supplierType_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $supplierTypeId = old('supplierType_id')?old('supplierType_id'):$supplier->supplier_type_id;
                                        @endphp
                                        @foreach ($supplierType as $e)
                                            <option @if ($supplierTypeId==$e->id) {{ 'selected' }} @endif value="{{ $e->id }}">{{ $e->title_ind }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplierType_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @php
                                $disabled = '';
                            @endphp
                            @if (old('supplierType_id')==10)
                                @php
                                    $disabled = 'disabled';
                                @endphp
                            @endif
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Entity Type*</span>
                                <div class="col-sm-9">
                                    <select {{ $disabled }} class="form-select single-select @error('entityType_id') is-invalid @enderror" id="entityType_id" name="entityType_id">
                                        <option value="">Choose...</option>
                                        @php
                                            $entityTypeId = old('entityType_id')?old('entityType_id'):$supplier->entity_type_id;
                                        @endphp
                                        @foreach ($entityType as $e)
                                            <option @if ($entityTypeId==$e->id) {{ 'selected' }} @endif value="{{ $e->id }}">{{ $e->title_ind }}</option>
                                        @endforeach
                                    </select>
                                    @error('entityType_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Supplier Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="supplierName" name="supplierName" class="form-control @error('supplierName') is-invalid @enderror"
                                        maxlength="255" value="@if (old('supplierName')){{ old('supplierName') }}@else{{ $supplier->name }}@endif">
                                    @error('supplierName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Supplier Code*</span>
                                <div class="col-sm-9">
                                    <input readonly type="text" id="supplierCode" name="supplierCode" class="form-control @error('supplierCode') is-invalid @enderror"
                                        maxlength="5" value="@if (old('supplierCode')){{ old('supplierCode') }}@else{{ $supplier->supplier_code }}@endif">
                                    @error('supplierCode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Office Address*</span>
                                <div class="col-sm-9">
                                    <textarea id="office_address" name="office_address" maxlength="1024"
                                        class="form-control @error('office_address') is-invalid @enderror"
                                        rows="3" >@if (old('office_address')){{ old('office_address') }}@else{{ $supplier->office_address }}@endif</textarea>
                                    @error('office_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Country*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('country_id') is-invalid @enderror" id="country_id" name="country_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $countryId = old('country_id')?old('country_id'):$supplier->country_id;
                                        @endphp
                                        @foreach ($country as $p)
                                            <option @if ($countryId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->country_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('country_id')
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
                                            $provinceId = old('province_id')?old('province_id'):$supplier->province_id;
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
                                <span class="col-sm-3 col-form-label">City*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('city_id') is-invalid @enderror" id="city_id" name="city_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $cityId = old('city_id')?old('city_id'):$supplier->city_id;
                                        @endphp
                                        @foreach ($cities as $c)
                                            <option @if ($cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_type.' '.$c->city_name }}</option>
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
                                            $districtId = old('district_id')?old('district_id'):$supplier->district_id;
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
                                <span class="col-sm-3 col-form-label">Sub District*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('subdistrict_id') is-invalid @enderror" id="subdistrict_id" name="subdistrict_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $subdistrictId = old('subdistrict_id')?old('subdistrict_id'):$supplier->sub_district_id;
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
                                <span class="col-sm-3 col-form-label">Postcode</span>
                                <div class="col-sm-9">
                                    <input type="text" id="postcode" name="postcode" class="form-control @error('postcode') is-invalid @enderror"
                                        maxlength="6" value="@if (old('postcode')){{ old('postcode') }}@else{{ $supplier->post_code }}@endif">
                                    @error('postcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Supplier Email</span>
                                <div class="col-sm-9">
                                    <input type="email" id="supplier_email" name="supplier_email" class="form-control @error('supplier_email') is-invalid @enderror"
                                        maxlength="64" value="@if (old('supplier_email')){{ old('supplier_email') }}@else{{ ($supplier->supplier_email=='-')?'':$supplier->supplier_email }}@endif">
                                    @error('supplier_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 1</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone1" name="phone1" class="form-control @error('phone1') is-invalid @enderror"
                                        maxlength="32" value="@if (old('phone1')){{ old('phone1') }}@else{{ $supplier->phone1 }}@endif">
                                    @error('phone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Phone 2</span>
                                <div class="col-sm-9">
                                    <input type="text" id="phone2" name="phone2" class="form-control @error('phone2') is-invalid @enderror"
                                        maxlength="32" value="@if (old('phone2')){{ old('phone2') }}@else{{ $supplier->phone2 }}@endif">
                                    @error('phone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC 1 Name*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="pic1Name" name="pic1Name" class="form-control @error('pic1Name') is-invalid @enderror"
                                        maxlength="255" value="@if(old('pic1Name')){{ old('pic1Name') }}@else{{ $supplier->pic1_name }}@endif">
                                    @error('pic1Name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Phone 1</span>
                                <div class="col-sm-9">
                                    <input type="text" id="picphone1" name="picphone1" class="form-control @error('picphone1') is-invalid @enderror"
                                        maxlength="32" value="@if(old('picphone1')){{ old('picphone1') }}@else{{ $supplier->pic1_phone }}@endif">
                                    @error('picphone1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Email 1</span>
                                <div class="col-sm-9">
                                    <input type="email" id="pic_email1" name="pic_email1" class="form-control @error('pic_email1') is-invalid @enderror"
                                        maxlength="64" value="@if(old('pic_email1')){{ old('pic_email1') }}@else{{ ($supplier->pic1_email=='-')?'':$supplier->pic1_email }}@endif">
                                    @error('pic_email1')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC 2 Name</span>
                                <div class="col-sm-9">
                                    <input type="text" id="pic2Name" name="pic2Name" class="form-control @error('pic2Name') is-invalid @enderror"
                                        maxlength="255" value="@if(old('pic2Name')){{ old('pic2Name') }}@else{{ $supplier->pic2_name }}@endif">
                                    @error('pic2Name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Phone 2</span>
                                <div class="col-sm-9">
                                    <input type="text" id="picphone2" name="picphone2" class="form-control @error('picphone2') is-invalid @enderror"
                                        maxlength="32" value="@if(old('picphone2')){{ old('picphone2') }}@else{{ $supplier->pic2_phone }}@endif">
                                    @error('picphone2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">PIC Email 2</span>
                                <div class="col-sm-9">
                                    <input type="email" id="pic_email2" name="pic_email2" class="form-control @error('pic_email2') is-invalid @enderror"
                                        maxlength="64" value="@if(old('pic_email2')){{ old('pic_email2') }}@else{{ $supplier->pic2_email }}@endif">
                                    @error('pic_email2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <hr />
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">NPWP no</span>
                                <div class="col-sm-9">
                                    <input type="text" id="npwp_no" name="npwp_no" class="form-control @error('npwp_no') is-invalid @enderror"
                                        maxlength="24" value="@if(old('npwp_no')){{ old('npwp_no') }}@else{{ $supplier->npwp_no }}@endif">
                                    @error('npwp_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @php
                                $disabled = '';
                                $npwp_address = old('npwp_address')?old('npwp_address'):$supplier->npwp_address;
                                $npwp_province_id = old('npwp_province_id')?old('npwp_province_id'):$supplier->npwp_province_id;
                                $npwp_city_id = old('npwp_city_id')?old('npwp_city_id'):$supplier->npwp_city_id;
                                $npwp_district_id = old('npwp_district_id')?old('npwp_district_id'):$supplier->npwp_district_id;
                                $npwp_subdistrict_id = old('npwp_subdistrict_id')?old('npwp_subdistrict_id'):$supplier->npwp_sub_district_id;
                            @endphp
                            @if (old('same_as_officeaddress')=='on')
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
                                    <textarea {{ $disabled }} id="npwp_address" name="npwp_address" maxlength="1024" class="form-control @error('npwp_address') is-invalid @enderror"
                                        rows="3" aria-label="npwp address">{{ $npwp_address }}</textarea>
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
                                            <option @if ($npwp_provinceId==$p->id) {{ 'selected' }} @endif value="{{ $p->id }}">{{ $p->province_name }}</option>
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
                                            <option @if ($npwp_cityId==$c->id) {{ 'selected' }} @endif value="{{ $c->id }}">{{ $c->city_type.' '.$c->city_name }}</option>
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
                                            <option @if ($npwp_districtId==$d->id) {{ 'selected' }} @endif value="{{ $d->id }}">{{ $d->district_name }}</option>
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
                            <hr />
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">TOP (day)*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="top_in_day" name="top_in_day"
                                        class="form-control @error('top_in_day') is-invalid @enderror" maxlength="3"
                                        value="@if (old('top_in_day')){{ old('top_in_day') }}@else{{ ($supplier->top>0)?$supplier->top:'' }}@endif">
                                    @error('top_in_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Credit Limit*</span>
                                <div class="col-sm-9">
                                    <input type="text" id="credit_limit" name="credit_limit" onkeyup="formatAmount($(this));"
                                        class="form-control @error('credit_limit') is-invalid @enderror" maxlength="22"
                                        value="@if (old('credit_limit')){{ old('credit_limit') }}@else{{ ($supplier->credit_limit>0)?number_format($supplier->credit_limit,0,'.',','):'' }}@endif">
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
                                        value="@if (old('limit_balance')){{ old('limit_balance') }}@else{{ $supplier->limit_balance }}@endif">
                                    @error('limit_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            {{-- <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Payment From*</span>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('payment_from_id') is-invalid @enderror" id="payment_from_id" name="payment_from_id">
                                        <option value="#">Choose...</option>
                                        @php
                                            $payment_from_id = old('payment_from_id')?old('payment_from_id'):$supplier->payment_from_id;
                                        @endphp
                                        @foreach ($coas as $coa)
                                            <option @if ($payment_from_id==$coa->id) {{ 'selected' }} @endif value="{{ $coa->id }}">
                                                {{ ucwords(strtolower($coa->coa_name)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_from_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="row mb-3">
                                <span class="col-sm-3 col-form-label">Beginning Balance Hutang</span>
                                <div class="col-sm-9">
                                    <input type="text" id="beginning_balance" name="beginning_balance" onkeyup="formatAmount($(this));"
                                        class="form-control @error('beginning_balance') is-invalid @enderror" maxlength="22"
                                        value="@if(old('beginning_balance')){{ old('beginning_balance') }}@else{{ ($supplier->beginning_balance>0)?number_format($supplier->beginning_balance,0,'.',','):'' }}@endif">
                                    @error('beginning_balance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Supplier Bank Information</h6>
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
                                    @if (old('totalRow'))
                                        @for ($i = 0; $i < $totRow; $i++)
                                            <tr id="row{{ $i }}">
                                                <th scope="row">
                                                    {{ $i + 1 }}.
                                                    <input type="hidden" name="bank_id_{{ $i }}" id="bank_id_{{ $i }}" value="0">
                                                </th>
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
                                                        class="form-control @error('bank_address'.$i) is-invalid @enderror"
                                                        rows="3">@if (old('bank_address'.$i)){{ old('bank_address'.$i) }}@endif</textarea>
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
                                                            <option @if ($currencyBankId==$c->id){{ 'selected' }}@endif
                                                                value="{{ $c->id }}">{{ $c->title_ind }}</option>
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
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($queryBank as $qB)
                                            <tr id="row{{ $i }}">
                                                <th scope="row">
                                                    {{ $i + 1 }}.
                                                    <input type="hidden" name="bank_id_{{ $i }}" id="bank_id_{{ $i }}" value="{{ $qB->id }}">
                                                </th>
                                                <td>
                                                    <input type="text" class="form-control @error('bank_name'.$i) is-invalid @enderror"
                                                        id="bank_name{{ $i }}" name="bank_name{{ $i }}" maxlength="255"
                                                        value="{{ $qB->bank_name }}" />
                                                    @error('bank_name'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <textarea id="bank_address{{ $i }}" name="bank_address{{ $i }}" maxlength="1024"
                                                        class="form-control @error('bank_address'.$i) is-invalid @enderror"
                                                        rows="3">{{ $qB->bank_address }}</textarea>
                                                    @error('bank_address'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control @error('account_name'.$i) is-invalid @enderror"
                                                        id="account_name{{ $i }}" name="account_name{{ $i }}" maxlength="255"
                                                        value="{{ $qB->account_name }}" />
                                                    @error('account_name'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('account_no'.$i) is-invalid @enderror"
                                                        id="account_no{{ $i }}" name="account_no{{ $i }}" maxlength="255"
                                                        value="{{ $qB->account_no }}" />
                                                    @error('account_no'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <select class="form-select single-select @error('currency_bank_id'.$i) is-invalid @enderror"
                                                        id="currency_bank_id{{ $i }}" name="currency_bank_id{{ $i }}">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $currencyBankId = old('currency_bank_id'.$i)?old('currency_bank_id'.$i) : $qB->currency_id;
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
                                                    <input type="text" class="form-control @error('swift_code'.$i) is-invalid @enderror"
                                                        id="swift_code{{ $i }}" name="swift_code{{ $i }}" maxlength="255"
                                                        value="{{ $qB->swift_code }}" />
                                                    @error('swift_code'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control @error('bsb_code'.$i) is-invalid @enderror"
                                                        id="bsb_code{{ $i }}" name="bsb_code{{ $i }}" maxlength="255"
                                                        value="{{ $qB->bsb_code }}" />
                                                    @error('bsb_code'.$i)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </td>
                                                <td>
                                                    <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                </td>
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
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
<div class="modal fade" id="supplier-info" aria-hidden="true" aria-labelledby="supplier-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="supplier-info" style="color:#fff;">Supplier Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="color:#fff;">
                The following are similar supplier names:<br />
                <span id="msg-modal"></span><br />
                <span id="msg-warn" style="color:red;font-weight:bold;">Make sure the customer name/code entered does not match the name/code of an existing customer.</span><br /><br />
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

    function SupplierInfo(slug){
        var fd = new FormData();
        fd.append('slug', slug);
        $.ajax({
            url: '{{ url("disp_supplierinfo") }}',
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].suppliers;
                let totCust = o.length;
                if (totCust > 0) {
                    let post_code = o[0].post_code.replace('000000','');
                    let vHtml = 'Detail Info: <br />'+o[0].name+'<br />'+o[0].office_address+','+o[0].sub_district_name.toLowerCase().ucwords()+', '+
                    o[0].district_name+'<br />'+o[0].city_name+'<br />'+o[0].province_name+'<br />'+o[0].country_name+' '+post_code;
                    $('#msg-modal-info').html(vHtml);
                }
            },
        });
    }

    $(document).ready(function() {
        $("#supplierName").keyup(function() {
            let supplierName = $("#supplierName").val();
            $("#supplierName").val(supplierName.toUpperCase());
        });
        $("#supplierCode").keyup(function() {
            let supplierCode = $("#supplierCode").val();
            $("#supplierCode").val(supplierCode.toUpperCase());
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
            
            history.back();
        });

        $('#supplierType_id').change(function () {
            if($('#supplierType_id').val()==10){
                $('#entityType_id').val('').change();
                $("#entityType_id").prop("disabled",true);
            }else{
                $("#entityType_id").prop("disabled",false);
            }
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
                                optionText=o[i].province_name; optionValue=o[i].id;
                                $("#npwp_province_id").append(`<option value="${optionValue}">${optionText}</option>`);
                            }
                        }
                    },
                });
            }
        });

        $('#supplierName').change(function() {
            $('#msg-modal-info').html('&nbsp;');
            var fd = new FormData();
            fd.append('supplierName', $('#supplierName').val());
            $.ajax({
                url: '{{ url("disp_similar_suppliername") }}',
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].suppliers;
                    let totSuppliers = o.length;
                    if (totSuppliers > 0) {
                        let vHtml = '';
                        for (let i = 0; i < totSuppliers; i++) {
                            let supplier_code = o[i].supplier_code;
                            if(supplier_code===null){supplier_code = '-';}
                            vHtml += (i+1)+'. <a href="#" onclick="SupplierInfo(\''+o[i].slug+'\');" '+
                                'style="text-decoration: underline;color:#fff;">'+o[i].name+'</a> - Supplier Code: '+supplier_code+'<br />';
                        }
                        $('#msg-warn').text('');
                        $('#msg-modal').html(vHtml);
                        $('#supplier-info').modal('show');
                    }
                },
            });
        });

        $("#btn-add-row").click(function() {
            let totalRow = $("#totalRow").val();
            let rowNo = (parseInt(totalRow) + 1);
            let vHtml =
            '<tr id="row' + totalRow + '">' +
                '<th scope="row">' + rowNo + '.<input type="hidden" name="bank_id_'+totalRow+'" id="bank_id_'+totalRow+'" value="0"></th>' +
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

        $('#country_id').change(function() {
            $("#province_id").empty();
            $("#city_id").empty();
            $("#district_id").empty();
            $("#subdistrict_id").empty();
            $("#postcode").val('');
            if ($('#country_id option:selected').val() == 9999) {
                // indonesia
                $("#province_id").append(
                    `<option value="#">Choose...</option>`
                );
                dispProvince('country_id', '#country_id option:selected', '{{ url("disp_province") }}', '#province_id');
                $("#city_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#district_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#subdistrict_id").append(
                    `<option value="#">Choose...</option>`
                );
            } else {
                // luar negeri
                $("#province_id").append(`<option value="9999">Other</option>`);
                $("#city_id").append(`<option value="9999">Other</option>`);
                // dispCityByCountry('country_id', '#country_id option:selected', '{{ url("disp_city_by_country") }}', '#city_id');
                $("#district_id").append(`<option value="9999">Other</option>`);
                $("#subdistrict_id").append(`<option value="99999">Other</option>`);
            }
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
            $("#npwp_province_id").val($("#province_id").val()).change();
        });

        $('#city_id').change(function() {
            $("#district_id").empty();
            $("#subdistrict_id").empty();
            if ($('#country_id option:selected').val() == 9999) {
                $("#district_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#subdistrict_id").append(
                    `<option value="#">Choose...</option>`
                );
                $("#postcode").val('');
            }else{
                $("#district_id").append(
                    `<option value="9999">Other</option>`
                );
                $("#subdistrict_id").append(
                    `<option value="99999">Other</option>`
                );
                $("#postcode").val('000000');
            }

            dispDistrict(
                'city_id',
                '#city_id option:selected',
                '{{ url("disp_district") }}',
                '#district_id'
            );
            $("#npwp_city_id").val($("#city_id").val()).change();
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
            $("#npwp_district_id").val($("#district_id").val()).change();
        });

        $('#subdistrict_id').change(function() {
            $("#postcode").val('');

            dispPoscode(
                'subdistrict_id',
                '#subdistrict_id option:selected',
                '{{ url("disp_sub_district_postcode") }}',
                '#postcode'
            );
            $("#npwp_subdistrict_id").val($("#subdistrict_id").val()).change();
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
