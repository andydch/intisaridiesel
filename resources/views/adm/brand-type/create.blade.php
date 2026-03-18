@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
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
                    <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$uri) }}" method="POST" enctype="application/x-www-form-urlencoded">
                        @csrf
                        <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <label for="brand_id" class="col-sm-3 col-form-label">Brand*</label>
                                    <div class="col-sm-9">
                                        <select class="form-select single-select @error('brand_id') is-invalid @enderror"
                                            id="brand_id" name="brand_id">
                                            <option value="#">Choose...</option>
                                            @php
                                                $p_Id = (old('brand_id')?old('brand_id'):0);
                                            @endphp
                                            @foreach ($brands as $qB)
                                            <option @if ($p_Id==$qB->id) {{ 'selected' }} @endif
                                                value="{{ $qB->id }}">{{ $qB->title_ind }}</option>
                                            @endforeach
                                        </select>
                                        @error('brand_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="brand_type_name" class="col-sm-3 col-form-label">{{ $title }} Name*</label>
                                    <div class="col-sm-9">
                                        <input type="text" id="brand_type_name" name="brand_type_name" class="form-control @error('brand_type_name') is-invalid @enderror" maxlength="512"
                                        aria-label="Title (Ind)" value="@if(old('brand_type_name')){{ old('brand_type_name') }}@endif">
                                        @error('brand_type_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="card" style="margin-top: 15px;">
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-sm-12">
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                        <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
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
        $("#brand_type_name").keyup(function() {
            let brand_type_name = $("#brand_type_name").val();
            $("#brand_type_name").val(brand_type_name.toUpperCase());
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
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
