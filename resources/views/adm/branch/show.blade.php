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
            @include('adm.branch.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Branch</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/branch/'.urlencode($branch->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <span class="col-sm-3 col-form-label">Initial</span>
                                    <span class="col-sm-9 col-form-label">{{ $branch->initial }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Branch Name</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Address</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->address }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Province</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->province->province_name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">City</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->city->city_name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">District</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->district->district_name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Sub District</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->subdistrict->sub_district_name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Postcode</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->post_code }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Phone 1</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->phone1 }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Phone 2</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $branch->phone2 }}</span>
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
                    </form>
                </div>
            </div>
            <!--end row-->
        </div>
    </div>
    <!--end page wrapper -->
@endsection

@section('script')
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/my-custom.js') }}"></script>
    <script>
        $(document).ready(function() {
            $("#back-btn").click(function() {
                history.back();
            });
        });
    </script>
@endsection
