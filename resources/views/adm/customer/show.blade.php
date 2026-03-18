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
                            <span class="col-sm-9 col-form-label">{{ $cust->entity_type->title_ind }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Customer Name</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Customer Code</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->customer_unique_code }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Office Address</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->office_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Province</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->province->province_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">City</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->city->city_type.' '.$cust->city->city_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">District</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->district->district_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ ucwords(strtolower($cust->subdistrict->sub_district_name)) }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Postcode</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->post_code }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Customer Email</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->cust_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->phone1 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Phone 2</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->phone2 }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC 1 Name</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic1_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Phone 1</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic1_phone }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Email 1</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic1_email }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC 2 Name</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic2_name }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Phone 2</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic2_phone }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">PIC Email 2</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->pic2_email }}</span>
                        </div>
                        <hr />
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP no</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->npwp_no }}</span>
                        </div>
                        @php
                            $disabled = '';
                        @endphp
                        @if (old('same_as_officeaddress')=='on')
                            @php
                                $disabled = 'disabled';
                            @endphp
                        @endif
                        {{-- <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Same as office address?</span>
                            <div class="col-sm-9">
                                <input class="form-check-input" type="checkbox" id="same_as_officeaddress"
                                    name="same_as_officeaddress" @if(old('same_as_officeaddress')=='on' ){{ 'checked' }}@endif>
                            </div>
                        </div> --}}
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Address</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->npwp_address }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Province</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($cust->npwp_province)?$cust->npwp_province->province_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP City</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($cust->npwp_city)?$cust->npwp_city->city_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP District</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($cust->npwp_district)?$cust->npwp_district->district_name:'' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">NPWP Sub District</span>
                            <span class="col-sm-9 col-form-label">{{ !is_null($cust->npwp_subdistrict)?ucwords(strtolower($cust->npwp_subdistrict->sub_district_name)):'' }}</span>
                        </div>
                        <hr />
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Credit Limit</span>
                            <span class="col-sm-9 col-form-label">{{ number_format($cust->credit_limit,0,",",".") }}</span>
                        </div>
                        {{-- <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Limit Balance</span>
                            <span class="col-sm-9 col-form-label">{{ ($cust->limit_balance!=0)?number_format($cust->limit_balance,0,",","."):'' }}</span>
                        </div> --}}
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">TOP (day)</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->top }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Branch</span>
                            <span class="col-sm-4 col-form-label">{{ (!is_null($cust->branch)?$cust->branch->name:'') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Salesman</span>
                            <span class="col-sm-4 col-form-label">{{ $cust->salesman01?$cust->salesman01->user->name:'-' }}</span>
                        </div>
                        {{-- <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Salesman 2</span>
                            <span class="col-sm-9 col-form-label">{{ (!is_null($cust->salesman02)?$cust->salesman02->name:'') }}</span>
                        </div> --}}
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Customer Status</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->customer_status=='Y'?'Active':'Not Active' }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Payment Status</span>
                            <span class="col-sm-9 col-form-label">{{ $cust->payment_status=='Y'?ENV('PAYMENT_STATUS_LANCAR'):ENV('PAYMENT_STATUS_TIDAK_LANCAR') }}</span>
                        </div>
                        <div class="row mb-3">
                            <span class="col-sm-3 col-form-label">Beginning Balance Piutang</span>
                            <span class="col-sm-9 col-form-label">{{ ($cust->beginning_balance!=0)?number_format($cust->beginning_balance,0,",","."):'' }}</span>
                        </div>
                    </div>
                </div>
                <hr />
                <h6 class="mb-0 text-uppercase">Shipment Address</h6>
                <div class="card" style="margin-top: 15px;">
                    <div class="card-body">
                        @php
                            $totRow = old('totalRow')?old('totalRow'):$totalRow;
                        @endphp
                        <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr>
                                    <th scope="col">#{{ old('address_addr') }}</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Province</th>
                                    <th scope="col">City</th>
                                    <th scope="col">District</th>
                                    <th scope="col">Sub District</th>
                                    <th scope="col">Postcode</th>
                                    <th scope="col">Phone</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @if (old('totalRow'))
                                    {{-- empty --}}
                                @else
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($queryShipment as $qS)

                                        <tr id="row{{ $i }}">
                                            <th scope="row">{{ $i + 1 }}</th>
                                            <td><span class="col-form-label">{{ $qS->address }}</span></td>
                                            <td><span class="col-form-label">{{ $qS->province->province_name }}</span></td>
                                            <td><span class="col-form-label">{{ $qS->city->city_name }}</span></td>
                                            <td><span class="col-form-label">{{ $qS->district->district_name }}</span></td>
                                            <td><span class="col-form-label">{{ ucwords(strtolower($qS->subdistrict->sub_district_name)) }}</span></td>
                                            <td><span class="col-form-label">{{ $qS->post_code }}</span></td>
                                            <td><span class="col-form-label">{{ $qS->phone }}</span></td>
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
