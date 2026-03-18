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
            @include('adm.subdistrict.breadcrumb')
            <!--end breadcrumb-->
            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">Sub District</h6>
                    <hr />
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/subdistrict/'.$subdistrict->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        @method('PUT')
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Country Name</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $subdistrict->district->city->country->country_name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">Province Name</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $subdistrict->district->city->province->province_name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">City Name</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ $subdistrict->district->city->city_type.' '.$subdistrict->district->city->city_name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-3 col-form-label" for="district_id">District</label>
                                    <label class="col-sm-9 col-form-label" for="district_id">{{ $subdistrict->district->district_name }}</label>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Sub District Name</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $subdistrict->sub_district_name }}</span>
                                </div>
                                <div class="row mb-3">
                                    <span for="" class="col-sm-3 col-form-label">Postcode</span>
                                    <span for="" class="col-sm-9 col-form-label">{{ $subdistrict->post_code }}</span>
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
    <script>
        $(document).ready(function() {
            $("#back-btn").click(function() {
                history.back();
            });
        });
    </script>
@endsection
