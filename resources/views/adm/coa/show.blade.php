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
        @include('adm.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">COA Level</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $coas->coa_level }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">COA Parent</label>
                            <label for="" class="col-sm-9 col-form-label">{{ (!is_null($coas->coaParent)?$coas->coaParent->coa_name:'-') }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">COA Code</label>
                            <label for="" class="col-sm-9 col-form-label">{{ (($coas->is_draft=='Y')?'Draft':'').$coas->coa_code }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">COA Name</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $coas->coa_name }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Master COA?</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $coas->is_master_coa }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Balance Sheet?</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $coas->is_balance_sheet }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Profit Loss?</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $coas->is_profit_loss }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Lokal</label>
                            <label for="" class="col-sm-9 col-form-label">
                                @switch($coas->local)
                                    @case('P')
                                        {{ 'PPN' }}
                                        @break

                                    @case('N')
                                        {{ 'Non PPN' }}
                                        @break

                                    @case('A')
                                        {{ 'PPN & Non PPN' }}
                                        @break

                                    @default
                                        {{ '-' }}
                                @endswitch
                            </label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Branch</label>
                            <label for="" class="col-sm-9 col-form-label">{{ ($coas->branch_id==999?'HO':($coas->branch?$coas->branch->name:'-')) }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Beginning Balance Date</label>
                            <label for="" class="col-sm-9 col-form-label">{{ ($coas->beginning_balance_date!=null?date_format(date_create($coas->beginning_balance_date), 'd/m/Y'):'') }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Beginning Balance Amount</label>
                            <label for="" class="col-sm-9 col-form-label">{{ number_format($coas->beginning_balance_amount,0,'.',',') }}</label>
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
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
