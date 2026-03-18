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
                @if($errors->any())
                Error:
                {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                @endif
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Supplier Type</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->supplier_type->title_ind }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Entity Type</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->entity_type)?$supplier->entity_type->title_ind:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Supplier Name</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Office Address</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->office_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Country</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->country->country_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Province</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->province->province_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">City</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->city->city_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">District</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->district->district_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->subdistrict->sub_district_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Postcode</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->post_code }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Supplier Email</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->supplier_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->phone1 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 2</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->phone2 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Currency 1</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->currencies1)?$supplier->currencies1->title_ind:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Currency 2</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($supplier->currencies2)?$supplier->currencies2->title_ind:'') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC 1 Name</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic1_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic1_phone }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Email 1</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic1_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC 2 Name</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic2_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Phone 2</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic2_phone }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Email 2</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->pic2_email }}</span>
                        </div>
                        <hr />
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP no</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->npwp_no }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Address</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->npwp_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Province</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->npwp_province)?$supplier->npwp_province->province_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP City</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->npwp_city)?$supplier->npwp_city->city_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP District</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->npwp_district)?$supplier->npwp_district->district_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($supplier->npwp_subdistrict)?$supplier->npwp_subdistrict->sub_district_name:'' }}</span>
                        </div>
                        <hr />
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">TOP (day)</span>
                            <span class="col-sm-9 col-form-label">{{ $supplier->top }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Credit Limit</span>
                            <span class="col-sm-9 col-form-label">{{ number_format($supplier->credit_limit,0,",",".") }}</span>
                        </div>
                        {{-- <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Limit Balance</span>
                            <span class="col-sm-9 col-form-label">{{ number_format($supplier->limit_balance,0,",",".") }}</span>
                            </div>
                        </div> --}}
                        {{-- <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Payment From</span>
                            <span class="col-sm-9 col-form-label">{{ ($supplier->coa?$supplier->coa->coa_name:'') }}</span>
                        </div> --}}
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Beginning Balance Hutang</span>
                            <span class="col-sm-9 col-form-label">{{ number_format($supplier->beginning_balance,0,",",".") }}</span>
                        </div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-0 text-uppercase">Supplier Bank Information</h6>
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
                                                {{ $i + 1 }}.
                                                <input type="hidden" name="bank_id_{{ $i }}" id="bank_id_{{ $i }}" value="{{ $qB->id }}">
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
                                                <span class="col-form-label">{{ $qB->currency->title_ind }}</span>
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
<div class="modal fade" id="supplier-info" aria-hidden="true" aria-labelledby="supplier-info" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="supplier-info">Supplier Info</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                The following are similar supplier names:<br />
                <span id="msg-modal"></span><br />
                Make sure the supplier name entered does not match the name of an existing supplier.<br /><br />
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

@section('script')
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            history.back();
        });
    });
</script>
@endsection
