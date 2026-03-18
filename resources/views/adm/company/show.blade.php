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
            <form id="submit-form" action="{{ url(ENV('ADMIN_FOLDER_NAME').'/'.$folder.'/'.urlencode($company->slug)) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Company Name</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Office Address</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->office_address }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Province</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->province->province_name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">City</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->city->city_name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">District</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->district->district_name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Sub District</label>
                                <label for="" class="col-sm-9 col-form-label">{{ ucwords(strtolower($company->subdistrict->sub_district_name)) }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Postcode</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->post_code }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Company Email</label>
                                <label for="" class="col-sm-3 col-form-label">{{ $company->company_email }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Phone 1</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $company->phone1 }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Phone 2</label>
                                <label for="" class="col-sm-3 col-form-label">{{ $company->phone2 }}</label>
                            </div>
                            @if ($company->id==1)
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP no</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_no)?$company->npwp_no:'') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP Address</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_address)?$company->npwp_address:'') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP Province</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_province)?$company->npwp_province->province_name:'') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP City</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_city)?$company->npwp_city->city_name:'') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP District</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_district)?$company->npwp_district->district_name:'') }}</label>
                                </div>
                                <div class="row mb-3">
                                    <label for="" class="col-sm-3 col-form-label">NPWP Sub District</label>
                                    <label for="" class="col-sm-9 col-form-label">{{ (!is_null($company->npwp_subdistrict)?ucwords(strtolower($company->npwp_subdistrict->sub_district_name)):'') }}</label>
                                </div>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Company Bank Information</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                                $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Bank Name</th>
                                        <th scope="col">Address</th>
                                        <th scope="col">Account Name</th>
                                        <th scope="col">Account Number</th>
                                        <th scope="col">COA</th>
                                        <th scope="col">Currency</th>
                                        <th scope="col">Swift Code</th>
                                        <th scope="col">BSB Code</th>
                                        {{-- <th scope="col">Del</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if(old('totalRow'))
                                    {{-- empty --}}
                                    @else
                                        @php
                                            $i = 0;
                                        @endphp
                                        @foreach ($queryBank as $b)
                                            <tr id="row{{ $i }}">
                                                <th scope="row" style="vertical-align: middle;">
                                                    {{ $i + 1 }}
                                                    <input type="hidden" name="bank_id_{{ $i }}" id="bank_id_{{ $i }}" value="{{ $b->id }}">
                                                </th>
                                                <td><label for="" class="col-form-label">{{ $b->bank_name }}</label></td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->bank_address }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->account_name }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->account_no }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->coa?$b->coa->coa_name:'' }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->currency->title_ind }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->swift_code }}</label>
                                                </td>
                                                <td>
                                                    <label for="" class="col-form-label">{{ $b->bsb_code }}</label>
                                                </td>
                                                {{-- <td>
                                                    <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                                </td> --}}
                                            </tr>
                                            @php
                                                $i += 1;
                                            @endphp
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
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
    });
</script>
@endsection
