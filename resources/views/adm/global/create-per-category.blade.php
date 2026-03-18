@extends('layouts.app')

@section('style')
    {{--  --}}
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.global.breadcrumb-per-category')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/' . $uri) }}" method="POST" enctype="application/x-www-form-urlencoded">
                                @csrf
                                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Title*</span>
                                    <input type="text" id="title_ind" name="title_ind"
                                        class="form-control @error('title_ind') is-invalid @enderror" maxlength="512"
                                        aria-label="Title (Ind)" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('title_ind')){{ old('title_ind') }}@endif">
                                    @error('title_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Order No*</span>
                                    <input type="text" id="order_no" name="order_no"
                                        class="form-control @error('order_no') is-invalid @enderror" maxlength="3"
                                        aria-label="Order Number" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('order_no')){{ old('order_no') }}@endif">
                                    @error('order_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Notes</span>
                                    <textarea id="notes" name="notes" maxlength="1000" class="form-control @error('notes') is-invalid @enderror"
                                        rows="3" aria-label="Notes">@if (old('notes')){{ old('notes') }}@endif</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group" style="margin-top: 15px;">
                                    <span class="input-group-text">Small Description</span>
                                    <textarea id="small_desc_ind" name="small_desc_ind" maxlength="1000"
                                        class="form-control @error('small_desc_ind') is-invalid @enderror" rows="3"
                                        aria-label="Small Description (Ind)">@if (old('small_desc_ind')){{ old('small_desc_ind') }}@endif</textarea>
                                    @error('small_desc_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group" style="margin-top: 15px;">
                                    <span class="input-group-text">Long Description</span>
                                    <textarea id="long_desc_ind" name="long_desc_ind" maxlength="8000"
                                        class="form-control @error('long_desc_ind') is-invalid @enderror" rows="3"
                                        aria-label="Long Description (Ind)">@if (old('long_desc_ind')){{ old('long_desc_ind') }}@endif</textarea>
                                    @error('long_desc_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($uri=='brand' || $uri=='currency' || $uri=='gender' || $uri=='entity-type' || $uri=='supplier-type' ||
                                    $uri=='part-type' || $uri=='part-category' || $uri=='weight-type' || $uri=='delivery-type' || $uri=='quantity-type')
                                    <div class="input-group mb-3" style="margin-top: 15px;">
                                        <span class="input-group-text" id="inputGroup-sizing-default">Value (string)</span>
                                        <input type="text" id="value_string" name="value_string"
                                            class="form-control @error('value_string') is-invalid @enderror" maxlength="512"
                                            aria-label="Value (string)" aria-describedby="inputGroup-sizing-default"
                                            value="@if (old('value_string')){{ old('value_string') }}@endif">
                                        @error('value_string')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="value_string" id="value_string">
                                @endif
                                @if($uri=='vat')
                                    <div class="input-group mb-3" style="margin-top: 15px;">
                                        <span class="input-group-text" id="inputGroup-sizing-default">Value (numeric)</span>
                                        <input type="text" id="value_numeric" name="value_numeric"
                                            class="form-control @error('value_numeric') is-invalid @enderror" maxlength="32"
                                            aria-label="Value (numeric)" aria-describedby="inputGroup-sizing-default"
                                            value="@if (old('value_numeric')){{ old('value_numeric') }}@endif">
                                        @error('value_numeric')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @else
                                    <input type="hidden" name="value_numeric" id="value_numeric" value="0">
                                @endif
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <span class="input-group-text">Active</span>
                                    <div class="input-group-text">
                                        <input class="form-check-input" type="checkbox" id="active" name="active"
                                            aria-label="Active"
                                            @if (old('active') == 'on') {{ 'checked' }} @endif>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="submit" class="btn btn-primary px-5" style="margin-top: 15px;"
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
@endsection
