@extends('layouts.app')

@section('style')
    {{--  --}}
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.global.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h6 class="mb-0 text-uppercase">Parameter</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/mst-global/' . $globals->id) }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                @csrf
                                @method('PUT')
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Data Category*</span>
                                    <input type="text" id="dataCategory" name="dataCategory"
                                        class="form-control @error('dataCategory') is-invalid @enderror" maxlength="32"
                                        aria-label="Data Category" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('dataCategory')) {{ old('dataCategory') }}@else{{ $globals->data_cat }} @endif">
                                    @error('dataCategory')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Title (Ind)*</span>
                                    <input type="text" id="title_ind" name="title_ind"
                                        class="form-control @error('title_ind') is-invalid @enderror" maxlength="512"
                                        aria-label="Title (Ind)" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('title_ind')) {{ old('title_ind') }}@else{{ $globals->title_ind }} @endif">
                                    @error('title_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Title (Eng)*</span>
                                    <input type="text" id="title_eng" name="title_eng"
                                        class="form-control @error('title_eng') is-invalid @enderror" maxlength="512"
                                        aria-label="Title (Eng)" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('title_eng')) {{ old('title_eng') }}@else{{ $globals->title_eng }} @endif">
                                    @error('title_eng')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Order No*</span>
                                    <input type="text" id="order_no" name="order_no"
                                        class="form-control @error('order_no') is-invalid @enderror" maxlength="3"
                                        aria-label="Order Number" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('order_no')) {{ old('order_no') }}@else{{ $globals->order_no }} @endif">
                                    @error('order_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Notes</span>
                                    <textarea id="notes" name="notes" maxlength="1000" class="form-control @error('notes') is-invalid @enderror"
                                        rows="3" aria-label="Notes">
@if (old('notes'))
{{ old('notes') }}@else{{ $globals->notes }}
@endif
</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Small Description (Ind)</span>
                                    <textarea id="small_desc_ind" name="small_desc_ind" maxlength="1000"
                                        class="form-control @error('small_desc_ind') is-invalid @enderror" rows="3"
                                        aria-label="Small Description (Ind)">
@if (old('small_desc_ind'))
{{ old('small_desc_ind') }}@else{{ $globals->small_desc_ind }}
@endif
</textarea>
                                    @error('small_desc_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Small Description (Eng)</span>
                                    <textarea id="small_desc_eng" name="small_desc_eng" maxlength="1000"
                                        class="form-control @error('small_desc_eng') is-invalid @enderror" rows="3"
                                        aria-label="Small Description (Eng)">
@if (old('small_desc_eng'))
{{ old('small_desc_eng') }}@else{{ $globals->small_desc_eng }}
@endif
</textarea>
                                    @error('small_desc_eng')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Long Description (Ind)</span>
                                    <textarea id="long_desc_ind" name="long_desc_ind" maxlength="8000"
                                        class="form-control @error('long_desc_ind') is-invalid @enderror" rows="3"
                                        aria-label="Long Description (Ind)">
@if (old('long_desc_ind'))
{{ old('long_desc_ind') }}@else{{ $globals->long_desc_ind }}
@endif
</textarea>
                                    @error('long_desc_ind')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Long Description (Eng)</span>
                                    <textarea id="long_desc_eng" name="long_desc_eng" maxlength="8000"
                                        class="form-control @error('long_desc_eng') is-invalid @enderror" rows="3"
                                        aria-label="Long Description (Eng)">
@if (old('long_desc_eng'))
{{ old('long_desc_eng') }}@else{{ $globals->long_desc_eng }}
@endif
</textarea>
                                    @error('long_desc_eng')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Value (string)*</span>
                                    <input type="text" id="value_string" name="value_string"
                                        class="form-control @error('value_string') is-invalid @enderror" maxlength="512"
                                        aria-label="Value (string)" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('value_string')) {{ old('value_string') }}@else{{ $globals->string_val }} @endif">
                                    @error('value_string')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Value (numeric)*</span>
                                    <input type="text" id="value_numeric" name="value_numeric"
                                        class="form-control @error('value_numeric') is-invalid @enderror" maxlength="32"
                                        aria-label="Value (numeric)" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('value_numeric')) {{ old('value_numeric') }}@else{{ $globals->numeric_val }} @endif">
                                    @error('value_numeric')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="input-group mb-3" style="margin-top: 15px;">
                                    @php
                                        $active = $globals->active;
                                    @endphp
                                    @if (old('active') == 'on')
                                        @php
                                            $active = 'Y';
                                        @endphp
                                    @endif
                                    <span class="input-group-text">Active</span>
                                    <div class="input-group-text">
                                        <input class="form-check-input" type="checkbox" id="active" name="active"
                                            aria-label="Active"
                                            @if ($active == 'Y') {{ 'checked' }} @endif>
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
    <script>
        $(document).ready(function() {
            // 
        });
    </script>
@endsection
