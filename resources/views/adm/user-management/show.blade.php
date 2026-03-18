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
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($user->slug)) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
                                            <span class="col-sm-3 col-form-label">Name</span>
                                            <span class="col-sm-9 col-form-label">{{ $user->name }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Initial</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->initial }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Profile Picture</span>
                                            <div class="col-sm-9">
                                                @if (!is_null($userdetail->profile_pic))
                                                    <img src="{{ url('/upl/employees/'.$userdetail->profile_pic) }}" alt="{{ $userdetail->profile_pic }}">
                                                @else
                                                    <img src="{{ url('/upl/user-pics.jpg') }}" alt="user-pics.jpg">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Phone</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->phone1 }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="" class="col-sm-3 col-form-label">Date of Birth</label>
                                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($userdetail->date_of_birth),"d M Y") }}</label>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Signage Picture</span>
                                            <div class="col-sm-9">
                                                <img src="{{ url('/upl/employees/'.$userdetail->signage_pic) }}" alt="{{ $userdetail->signage_pic }}">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">NIK</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->id_no }}</span>
                                        </div>
									</div>
									<div class="tab-pane fade" id="primaryprofile" role="tabpanel">
										<div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Username</span>
                                            <span class="col-sm-9 col-form-label">{{ $user->email }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Password</span>
                                            <span class="col-sm-9 col-form-label">****************</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Section</span>
                                            <span class="col-sm-9 col-form-label">{{ (!is_null($userdetail->section_user)?$userdetail->section_user->title_ind:'') }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Sales</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->is_salesman }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Director</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->is_director }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Branch Head</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->is_branch_head }}</span>
                                        </div>
                                        <div class="row mb-3">
                                            <span class="col-sm-3 col-form-label">Branch</span>
                                            <span class="col-sm-9 col-form-label">{{ $userdetail->branch?$userdetail->branch->name:'' }}</span>
                                        </div>
									</div>
									<div class="tab-pane fade" id="primarycontact" role="tabpanel">
                                        <div class="row mb-3" style="padding: 0 15px 15px 15px;">
                                            @php
                                                $inputCount = 0;
                                                $menuCategory = '';
                                            @endphp
                                            @foreach ($menus as $q)
                                                @php
                                                    $menuChecked = '';
                                                    $menuCategoryArr = explode(" ",$q->name);
                                                @endphp
                                                @if (old('menuCheck'.$inputCount))
                                                    @php
                                                        $menuChecked = (old('menuCheck'.$inputCount)=='on')?'checked':'';
                                                    @endphp
                                                @else
                                                    @php
                                                        $qMenu = \App\Models\Mst_menu_user::where([
                                                            'menu_id' => $q->id,
                                                            'user_id' => $user->id,
                                                        ])
                                                        ->first();
                                                    @endphp
                                                    @if ($qMenu)
                                                        @if ($qMenu->user_access_read=='Y')
                                                            @php
                                                                $menuChecked = 'checked';
                                                            @endphp
                                                        @endif
                                                    @endif
                                                @endif
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
                                                    <input disabled class="form-check-input" type="checkbox"
                                                        id="menuCheck{{ $inputCount }}" name="menuCheck{{ $inputCount }}" {{ $menuChecked }}>
                                                    {{-- <label class="form-check-label" for="menuCheck{{ $inputCount }}">{{ ucwords(strtolower($q->name)) }}<i class="fa-solid fa-check"></i></label> --}}
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
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Back">
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
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
