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
                            <span class="col-sm-3 col-form-label">Entity Type</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->entity_type->title_ind }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Courier Name</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Office Address</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->office_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Province</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->province->province_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">City</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->city->city_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">District</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->district->district_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ ucwords(strtolower($courier->subdistrict->sub_district_name)) }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Postcode</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->post_code }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Courier Email</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->courier_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->phone1 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 2</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->phone2 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC 1 Name</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->pic1_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->pic1_phone }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Email 1</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->pic1_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP no</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->npwp_no }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Address</span>
                            <span class="col-sm-9 col-form-label">{{ $courier->npwp_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Province</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($courier->npwp_province)?$courier->npwp_province->province_name:'') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP City</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($courier->npwp_city)?$courier->npwp_city->city_name:'') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP District</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($courier->npwp_district)?$courier->npwp_district->district_name:'') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($courier->npwp_subdistrict)?ucwords(strtolower($courier->npwp_subdistrict->sub_district_name)):'') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-0 text-uppercase">Courier Bank Information</h6>
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
                                    <th scope="col">Currency</th>
                                    <th scope="col">Swift Code</th>
                                    <th scope="col">BSB Code</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @if (old('totalRow'))
                                    {{-- empty --}}
                                @else
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($queryBank as $qB)
                                        <tr id="row{{ $i }}">
                                            <th scope="row">
                                                <span class="col-form-label">{{ $i + 1 }}.</span>
                                                <input type="hidden" name="bank_id_{{ $i }}" id="bank_id_{{ $i }}" value="{{ $qB->id }}" />
                                            </th>
                                            <td>
                                                <span class="col-form-label">{{ $qB->bank_name }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ $qB->bank_address }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ $qB->account_name }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ $qB->account_no }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ (!is_null($qB->currency)?$qB->currency->title_ind:'') }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ $qB->swift_code }}</span>
                                            </td>
                                            <td>
                                                <span class="col-form-label">{{ $qB->bsb_code }}</span>
                                            </td>
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

<!-- Full screen modal -->
<div class="modal fade" id="cust-info" aria-hidden="true" aria-labelledby="cust-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="cust-info">Courier Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                The following are similar courier names:<br />
                <span id="msg-modal"></span><br />
                Make sure the courier name entered does not match the name of an existing courier.<br /><br />
                <span id="msg-modal-info" style="font-weight: bold;"></span>
            </div>
            <div class="modal-footer">
                {{-- <button class="btn btn-primary" data-bs-target="#exampleModalToggle" data-bs-toggle="modal">Back to
                    first</button> --}}
            </div>
        </div>
    </div>
</div>

@endsection

@php
    $currencyHtml = '';
@endphp
@foreach($currency as $p)
    @php
        $currencyHtml .= '<option value="'.$p->id.'">'.$p->title_ind.'</option>';
    @endphp
@endforeach

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
