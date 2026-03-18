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
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.$fp_nos->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    {{-- @if($errors->any())
                    Error:
                    {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                    @endif --}}
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <label for="fp_no" class="col-sm-3 col-form-label">FP No</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control @error('prefiks_code') is-invalid @enderror"
                                        maxlength="3" id="prefiks_code" name="prefiks_code" placeholder=""
                                        value="{{ $fp_nos->prefiks_code }}">
                                    @error('prefiks_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-sm-8">
                                    <input readonly type="text" class="form-control @error('fp_no') is-invalid @enderror"
                                        maxlength="255" id="fp_no" name="fp_no" placeholder="FP No"
                                        value="{{ $fp_nos->fp_no }}">
                                    @error('fp_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    @php
                                        $isApplied = \App\Models\Tx_delivery_order::where([
                                            'tax_invoice_id'=>$fp_nos->id,
                                            'active'=>'Y',
                                        ])
                                        ->first();
                                    @endphp
                                    @if (!$isApplied)
                                        <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
                                    @endif
                                    <input type="button" id="save-btn" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-secondary px-5" value="Cancel">
                                    <input type="hidden" id="ope" name="ope">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end row-->
<!--end page wrapper -->
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $("#del-btn").click(function() {
            if(!confirm("Data will be deleted.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#ope").val('rm');
                $("#submit-form").submit();
            }
        });
        $("#save-btn").click(function() {
            if(!confirm("Data will be saved.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#ope").val('sv');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            location.href = '{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder) }}';
        });
    });
</script>
@endsection
