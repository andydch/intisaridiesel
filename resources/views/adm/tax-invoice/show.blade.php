@extends('layouts.app')

@section('style')
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                {{-- @if($errors->any())
                Error:
                {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                @endif --}}
                <div class="row mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">FP No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $fp_nos->prefiks_code.$fp_nos->fp_no }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="back-btn" class="btn btn-primary px-5" value="Back">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end row-->
<!--end page wrapper -->
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
