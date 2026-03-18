@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}
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
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="lastPos" id="lastPos" value="primaryhomeTab">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    @if($errors->any())
                    Error: Please check the entered data again.
                    {{-- {!! implode('', $errors->all('<div>- :message</div>')) !!}<br /> --}}
                    <hr/>
                    @endif
                    <div class="col">
						{{-- <h6 class="mb-0 text-uppercase">Primary Nav Tabs</h6> --}}
						<div class="card">
							<div class="card-body">
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
									<li class="nav-item" role="presentation">
										<a id="primarycontactTab" class="nav-link" data-bs-toggle="tab" href="#primarycontact" role="tab" aria-selected="false">
											<div class="d-flex align-items-center">
												<div class="tab-icon"><i class='bx bx-microphone font-18 me-1'></i></div>
												<div class="tab-title">Menu</div>
											</div>
										</a>
									</li>
								</ul>
								<div class="tab-content py-3">
									<div class="tab-pane fade show active" id="primaryhome" role="tabpanel">
										<div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Name*</span>
                                            <div class="col-sm-9">
                                                <input type="text" id="fullname" name="fullname" class="form-control @error('fullname') is-invalid @enderror"
                                                    maxlength="255" value="@if (old('fullname')){{ old('fullname') }}@endif">
                                                @error('fullname')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Initial*</span>
                                            <div class="col-sm-9">
                                                <input type="text" id="initial" name="initial" class="form-control @error('initial') is-invalid @enderror"
                                                    maxlength="3" value="@if (old('initial')){{ old('initial') }}@endif">
                                                @error('initial')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Profile Picture <span style="font-size:small;font-style:italic;color:rgba(26, 2, 2, 0.7);">(300px*300px)</span></span>
                                            <div class="col-sm-9">
                                                <input name="profile_pic" id="profile_pic" class="form-control @error('profile_pic') is-invalid @enderror" type="file">
                                                @error('profile_pic')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Phone*</span>
                                            <div class="col-sm-9">
                                                <input type="text" id="phone1" name="phone1" class="form-control @error('phone1') is-invalid @enderror"
                                                    maxlength="64" value="@if (old('phone1')){{ old('phone1') }}@endif">
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
                                                    value="@if (old('date_of_birth')){{ old('date_of_birth') }}@endif">
                                                @error('date_of_birth')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Signage Picture <span style="font-size:small;font-style:italic;color:rgba(26, 2, 2, 0.7);">(400px*300px)</span></span>
                                            <div class="col-sm-9">
                                                <input name="signage_pic" id="signage_pic" class="form-control @error('signage_pic') is-invalid @enderror" type="file">
                                                @error('signage_pic')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">NIK</span>
                                            <div class="col-sm-9">
                                                <input type="text" id="nik" name="nik" class="form-control @error('nik') is-invalid @enderror"
                                                    maxlength="255" value="@if (old('nik')){{ old('nik') }}@endif">
                                                @error('nik')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
									</div>
									<div class="tab-pane fade" id="primaryprofile" role="tabpanel">
										<div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Username*</span>
                                            <div class="col-sm-9">
                                                <input type="email" id="uname" name="uname" class="form-control @error('uname') is-invalid @enderror"
                                                    maxlength="255" placeholder="Your email" value="@if (old('uname')){{ old('uname') }}@endif">
                                                @error('uname')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
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
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Section*</span>
                                            <div class="col-sm-9">
                                                <select class="form-select single-select @error('section_id') is-invalid @enderror"
                                                    id="section_id" name="section_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $section_id = old('section_id')?old('section_id'):0;
                                                    @endphp
                                                    @foreach ($sections as $section)
                                                        <option @if ($section_id==$section->id) {{ 'selected' }} @endif value="{{ $section->id }}">
                                                            {{ $section->title_ind }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('section_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Sales</span>
                                            <div class="col-sm-9">
                                                <input type="checkbox" name="is_salesman" id="is_salesman" {{ (old('is_salesman')=='on'?'checked':'') }}>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Director</span>
                                            <div class="col-sm-9">
                                                <input type="checkbox" name="is_director" id="is_director" {{ (old('is_director')=='on'?'checked':'') }}>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Branch Head</span>
                                            <div class="col-sm-9">
                                                <input type="checkbox" name="is_branch_head" id="is_branch_head" {{ (old('is_branch_head')=='on'?'checked':'') }}>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Branch*</span>
                                            <div class="col-sm-9">
                                                <select class="form-select single-select @error('branch_id') is-invalid @enderror"
                                                    id="branch_id" name="branch_id">
                                                    <option value="#">Choose...</option>
                                                    @php
                                                        $branch_id = old('branch_id')?old('branch_id'):0;
                                                    @endphp
                                                    @foreach ($branches as $branch)
                                                        <option @if ($branch_id==$branch->id) {{ 'selected' }} @endif value="{{ $branch->id }}">
                                                            {{ $branch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('branch_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
									</div>
									<div class="tab-pane fade" id="primarycontact" role="tabpanel">
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">
                                                Select All&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="form-check-input" type="checkbox"
                                                id="selectAll" name="selectAll"
                                                @if (old('selectAll'))
                                                    @if (old('selectAll')=='on'){{ 'checked' }}@endif
                                                @endif>
                                            </span>
                                        </div>
                                        <div class="row mb-3" style="padding: 0 15px 15px 15px;">
                                            @php
                                                $inputCount = 0;
                                                $menuCategory = '';
                                            @endphp
                                            @foreach ($menus as $q)
                                                @php
                                                    // $menuChecked = '';
                                                    $menuCategoryArr = explode(" ", $q->name);
                                                @endphp
                                                @if(strtolower($menuCategory)!=strtolower($menuCategoryArr[0]))
                                                    <div class="col-sm-12" style="font-weight: 700;font-size:20px;margin-top: 15px;margin-bottom: 15px;left: -15px;position: relative;">
                                                        <label class="form-check-label" for="">
                                                            @if (strtolower($menuCategoryArr[0])=='admin')
                                                                {{ 'Master' }}
                                                            @else
                                                                {{ ucwords(strtolower($menuCategoryArr[0])) }}
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endif
                                                <div class="col-sm-3 form-check">
                                                    <input type="hidden" name="menu_id_{{ $inputCount }}" id="menu_id_{{ $inputCount }}" value="{{ $q->id }}">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="menuCheck{{ $inputCount }}" name="menuCheck{{ $inputCount }}"
                                                        @if (old('menuCheck'.$inputCount))
                                                            @if (old('menuCheck'.$inputCount)=='on'){{ 'checked' }}@endif
                                                        @endif>
                                                    {{-- <label class="form-check-label" for="menuCheck{{ $inputCount }}">{{ ucwords(strtolower($q->name)) }}</label> --}}
                                                    <label class="form-check-label" for="menuCheck{{ $inputCount }}">
                                                        @php
                                                            $new_menu_name = ucwords(str_replace("admin ","",strtolower($q->name)));
                                                            $new_menu_name = ucwords(str_replace("report ","",strtolower($new_menu_name)));
                                                            $new_menu_name = ucwords(str_replace("transaction ","",strtolower($new_menu_name)));
                                                            if (strpos(strtolower($q->name),"transaction")>0 && strpos(strtolower($q->name),"report")>-1){
                                                                $new_menu_name = ucwords(str_replace("report ","",strtolower(strtolower($q->name))));
                                                            }
                                                        @endphp
                                                        {{ ucwords(strtolower($new_menu_name)) }}
                                                    </label>
                                                </div>
                                                @php
                                                    $inputCount += 1;
                                                    $menuCategory = $menuCategoryArr[0];
                                                @endphp
                                            @endforeach
                                        </div>
									</div>
								</div>
							</div>
						</div>
					</div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Next">
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
                                    <input type="button" id="cancel-btn" class="btn btn-danger px-5" value="Cancel">
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
        $("#initial").keyup(function() {
            let initial = $("#initial").val();
            $("#initial").val(initial.toUpperCase());
        });
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

        $('#selectAll').click(function(){
            if($(this).prop("checked") == true){
                // checked
                for(let i=0;i<{{ $menusCount }};i++){
                    $("#menuCheck"+i).prop("checked",true);
                }
            }
            else if($(this).prop("checked") == false){
                // unchecked
                for(let i=0;i<{{ $menusCount }};i++){
                    $("#menuCheck"+i).prop("checked",false);
                }
            }
        });

        $("#primaryhomeTab").click(function() {
            $('#lastPos').val('primaryhomeTab');
            $("#save").val('Next');
        });
        $("#primaryprofileTab").click(function() {
            $('#lastPos').val('primaryprofileTab');
            $("#save").val('Next');
        });
        $("#primarycontactTab").click(function() {
            $('#lastPos').val('primarycontactTab');
            $("#save").val('Save');
        });
        $("#save").click(function() {
            let lastPos = $('#lastPos').val();
            switch(lastPos) {
                case 'primaryhomeTab':
                    $('#lastPos').val('primaryprofileTab');
                    $("[id=primaryhomeTab]").removeClass("active");
                    $("[id=primaryhome]").removeClass("show active");

                    $("[id=primaryprofileTab]").addClass("active");
                    $("[id=primaryprofile]").addClass("show active");

                    $("#save").val('Next');

                    break;
                case 'primaryprofileTab':
                    $('#lastPos').val('primarycontactTab');
                    $("[id=primaryprofileTab]").removeClass("active");
                    $("[id=primaryprofile]").removeClass("show active");

                    $("[id=primarycontactTab]").addClass("active");
                    $("[id=primarycontact]").addClass("show active");

                    $("#save").val('Save');

                    break;
                case 'primarycontactTab':
                    if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                        event.preventDefault();
                    }else{
                        $("#submit-form").submit();
                    }

                    break;
                default:
                    // default : primaryhomeTab
                    $('#lastPos').val('primaryprofileTab');
                    $("[id=primaryhomeTab]").removeClass("active");
                    $("[id=primaryhome]").removeClass("show active");

                    $("[id=primaryprofileTab]").addClass("active");
                    $("[id=primaryprofile]").addClass("show active");
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });
        $("#cancel-btn").click(function() {
            $("#submit-form").reset();
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
