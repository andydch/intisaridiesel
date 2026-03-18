@extends('layouts.app')

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            @include('adm.country.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h6 class="mb-0 text-uppercase">Menu Access for </h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <form action="{{ url(ENV('ADMIN_FOLDER_NAME') . '/menu/' . $user_id) }}" method="POST"
                                enctype="application/x-www-form-urlencoded">
                                @csrf
                                @method('PUT')
                                @php
                                    $inputCount = 1;
                                @endphp
                                @foreach ($queryIn as $q)
                                    <div class="form-check">
                                        <input type="hidden" name="menu_id{{ $inputCount }}"
                                            id="menu_id{{ $inputCount }}" value="{{ $q->id }}">
                                        <input class="form-check-input" type="checkbox" id="menuCheck{{ $inputCount }}"
                                            name="menuCheck{{ $inputCount }}"
                                            @if ($q->menuUser[0]->user_access_read == 'Y') checked @endif>
                                        <label class="form-check-label"
                                            for="menuCheck{{ $inputCount }}">{{ ucwords(strtolower($q->name)) }}</label>
                                    </div>
                                    @php
                                        $inputCount += 1;
                                    @endphp
                                @endforeach
                                <hr>
                                @foreach ($queryNotIn as $q)
                                    <div class="form-check">
                                        <input type="hidden" name="menu_id{{ $inputCount }}"
                                            id="menu_id{{ $inputCount }}" value="{{ $q->id }}">
                                        <input class="form-check-input" type="checkbox" id="menuCheck{{ $inputCount }}"
                                            name="menuCheck{{ $inputCount }}">
                                        <label class="form-check-label"
                                            for="menuCheck{{ $inputCount }}">{{ ucwords(strtolower($q->name)) }}</label>
                                    </div>
                                    @php
                                        $inputCount += 1;
                                    @endphp
                                @endforeach
                                <input type="hidden" name="inputTot" id="inputTot" value="{{ $inputCount - 1 }}">
                                {{-- <div class="input-group mb-3">
                                    <span class="input-group-text" id="inputGroup-sizing-default">Country Name*</span>
                                    <input type="text" id="countryName" name="countryName"
                                        class="form-control @error('countryName') is-invalid @enderror" maxlength="128"
                                        aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default"
                                        value="@if (old('countryName')) {{ old('countryName') }}
                                                    @else {{ $country->country_name }} @endif">
                                    @error('countryName')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="active" name="active"
                                        @if (old('active') == 'on') {{ 'checked' }} @else 
                                        (@if ($country->active == 'Y')
                                            {{ 'checked' }} @endif)
                                        @endif>
                                    <label class="form-check-label" for="flexCheckDefault">Active</label>
                                </div>
                                 --}}
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
