@extends('layouts.app')

@section('style')
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
                            @csrf
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">{{ $title }}</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $globals->title_ind }}</label>
                            </div>
                            {{-- <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Symbol</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $globals->string_val }}</label>
                            </div> --}}
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
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
