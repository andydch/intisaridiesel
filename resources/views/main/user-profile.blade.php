@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
</style>
@endsection

@section('wrapper')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">User Profile</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">User Profile</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    {{-- <div class="btn-group">
                        <button type="button" class="btn btn-light">Settings</button>
                        <button type="button" class="btn btn-light dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
                        </div>
                    </div> --}}
                </div>
            </div>
            <!--end breadcrumb-->
            <div class="container">
                <div class="main-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex flex-column align-items-center text-center">
                                        @php
                                            $profilePic = asset('assets/images/face.png');
                                            if (!is_null($user->userDetail)) {
                                                if ($user->userDetail->profile_pic != '') {
                                                    $profilePic = asset('upl/employees/'.$user->userDetail->profile_pic);
                                                }
                                            }
                                        @endphp
                                        <img src="{{ $profilePic }}" alt="{{ $user->name }}"
                                            class="rounded-circle p-1 bg-primary" width="110">
                                        <div class="mt-3">
                                            <h4>{{ $user->name }}</h4>
                                            <p class="mb-1">
                                                @if ($user->id == 1)
                                                    {{ 'Administrator' }}
                                                @else
                                                    @if (!is_null($user->userDetail))
                                                        {{ $user->userDetail->position }}
                                                    @else
                                                        {{ 'not specified' }}
                                                    @endif
                                                @endif
                                            </p>
                                            <p class="font-size-sm">
                                                @if (!is_null($user->userDetail))
                                                    {!! $user->userDetail->address .
                                                        (!is_null($user->userDetail->sub_district)? ', '.ucwords(strtolower($user->userDetail->sub_district->sub_district_name)) : '') .
                                                        (!is_null($user->userDetail->district) ? ', '.$user->userDetail->district->district_name : '') .
                                                        (!is_null($user->userDetail->city) ? '<br/>'.$user->userDetail->city->city_name : '') .
                                                        (!is_null($user->userDetail->province) ? '<br/>'.$user->userDetail->province->province_name : '') .
                                                        (!is_null($user->userDetail->country) ? '<br/>'.$user->userDetail->country->country_name : '') .
                                                        (!is_null($user->userDetail->sub_district) ? '&nbsp;'.$user->userDetail->sub_district->post_code : '') !!}
                                                @endif
                                            </p>
                                            {{-- <button class="btn btn-light">Follow</button>
                                            <button class="btn btn-light">Message</button> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    @if (session('status'))
                                        <div class="alert alert-success">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    @if (session('status-error'))
                                        <div class="alert alert-danger">
                                            {{ session('status-error') }}
                                        </div>
                                    @endif
                                    <ul class="nav nav-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a id="primaryhomeTab" class="nav-link active" data-bs-toggle="tab" href="#primaryhome" role="tab" aria-selected="true">
                                                <div class="d-flex align-items-center">
                                                    <div class="tab-icon"><i class='bx bx-home font-18 me-1'></i></div>
                                                    <div class="tab-title">Profile</div>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a id="primaryprofileTab" class="nav-link" data-bs-toggle="tab" href="#primaryprofile" role="tab" aria-selected="false">
                                                <div class="d-flex align-items-center">
                                                    <div class="tab-icon"><i class='bx bx-user-pin font-18 me-1'></i></div>
                                                    <div class="tab-title">Access</div>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content py-3">
                                        <div class="tab-pane fade show active" id="primaryhome" role="tabpanel">
                                            <form id="submit-form-profile" action="{{ url('user-profile/'.$user->slug.'?p=profile') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PUT')
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">Name*</span>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror"
                                                            maxlength="255" value="@if (old('fullname')){{ old('fullname') }}@else{{ $user->name }}@endif">
                                                        @error('fullname')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <input type="hidden" name="profile_pic_tmp" id="profile_pic_tmp" value="{{ $userdetail->profile_pic }}">
                                                    <span class="col-sm-3 col-form-label">Profile Picture* <span style="font-size:small;font-style:italic;color:whitesmoke;">(300px*300px)</span></span>
                                                    <div class="col-sm-9">
                                                        <input name="profile_pic" id="profile_pic" class="form-control @error('profile_pic') is-invalid @enderror" type="file">
                                                        @error('profile_pic')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">&nbsp;</span>
                                                    <div class="col-sm-9">
                                                        <img src="{{ url('/upl/employees/'.$userdetail->profile_pic) }}" alt="{{ $userdetail->profile_pic }}">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">Phone*</span>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="phone1" name="phone1" class="form-control @error('phone1') is-invalid @enderror"
                                                            maxlength="64" value="@if (old('phone1')){{ old('phone1') }}@else{{ $userdetail->phone1 }}@endif">
                                                        @error('phone1')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="" class="col-sm-3 col-form-label">Date of Birth*</label>
                                                    <div class="col-sm-3">
                                                        <input readonly type="text" class="form-control @error('date_of_birth') is-invalid @enderror"
                                                            maxlength="10" id="date_of_birth" name="date_of_birth" placeholder="Date of Birth"
                                                            value="@if (old('date_of_birth')){{ old('date_of_birth') }}@else{{ $userdetail->date_of_birth }}@endif">
                                                        @error('date_of_birth')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">NIK*</span>
                                                    <div class="col-sm-9">
                                                        <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror"
                                                            maxlength="255" value="@if (old('nik')){{ old('nik') }}@else{{ $userdetail->id_no }}@endif">
                                                        @error('nik')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-3">&nbsp;</div>
                                                    <div class="col-sm-9">
                                                        <input type="button" id="save-profile" class="btn btn-light px-5" value="Save">
                                                        <input type="button" id="back-btn-profile" class="btn btn-light px-5" value="Back">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="tab-pane fade" id="primaryprofile" role="tabpanel">
                                            <form id="submit-form-access" action="{{ url('user-profile/'.$user->slug.'?p=access') }}" method="POST" enctype="application/x-www-form-urlencoded">
                                                @csrf
                                                @method('PUT')
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">Password*</span>
                                                    <div class="col-sm-9">
                                                        <input type="password" id="pwd" name="pwd" class="form-control @error('pwd') is-invalid @enderror"
                                                            maxlength="255" placeholder="Your password" value="@if (old('pwd')){{ old('pwd') }}@endif">
                                                        @error('pwd')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <span class="col-sm-3 col-form-label">Confirmation Password*</span>
                                                    <div class="col-sm-9">
                                                        <input type="password" id="c_pwd" name="c_pwd" class="form-control @error('c_pwd') is-invalid @enderror"
                                                            maxlength="255" placeholder="Confirm your password" value="@if (old('c_pwd')){{ old('c_pwd') }}@endif">
                                                        @error('c_pwd')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-sm-3">&nbsp;</div>
                                                    <div class="col-sm-9">
                                                        <input type="button" id="save-access" class="btn btn-light px-5" value="Save">
                                                        <input type="button" id="back-btn-access" class="btn btn-light px-5" value="Back">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
    <script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
    <script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
    <script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-custom.js') }}"></script>
    <script>
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
                $('#date_of_birth').bootstrapMaterialDatePicker({
                    time: false
                });
                $('#time').bootstrapMaterialDatePicker({
                    date: false,
                    format: 'HH:mm'
                });
            });

            $('.single-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });
            $('.multiple-select').select2({
                theme: 'bootstrap4',
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                    'style',
                placeholder: $(this).data('placeholder'),
                allowClear: Boolean($(this).data('allow-clear')),
            });

            $("#save-profile").click(function() {
                if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                    event.preventDefault();
                }else{
                    $("#submit-form-profile").submit();
                }
            });
            $("#back-btn-profile").click(function() {
                history.back();
            });
            $("#save-access").click(function() {
                if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                    event.preventDefault();
                }else{
                    $("#submit-form-access").submit();
                }
            });
            $("#back-btn-access").click(function() {
                history.back();
            });
        });
    </script>
@endsection
