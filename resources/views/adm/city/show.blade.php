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
        @include('adm.city.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">City</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Country Name</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $city->country->country_name }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Province Name</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $city->province->province_name }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">{{ $title }} Name</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $city->city_name }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">City Type</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $city->city_type }}</label>
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
        $('#country_id').change(function() {
            $("#province_id").empty();
            var fd = new FormData();
            fd.append('country_id', $('#country_id option:selected').val());
            $.ajax({
                url: '{{ url("disp_province") }}',
                type: 'POST',
                enctype: 'application/x-www-form-urlencoded',
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    let o = res[0].province;
                    let totProvince = o.length;
                    if (totProvince > 0) {
                        $("#province_id").append(
                            `<option value="#">Choose...</option>`
                        );
                        for (let i = 0; i < totProvince; i++) {
                            optionText = o[i].province_name;
                            optionValue = o[i].id;
                            $("#province_id").append(
                                `<option value="${optionValue}">${optionText}</option>`
                            );
                        }
                    } else {
                        $("#province_id").append(
                            `<option selected value="9999">Other</option>`
                        );
                        $("#city_type").empty();
                        $("#city_type").append(
                            `<option selected value="Luar Negeri">Luar Negeri</option>`
                        );
                    }
                },
            });
        });

        $("#save").click(function() {
            if(!confirm("Data will be saved to database. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            history.back();
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' :
                'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
