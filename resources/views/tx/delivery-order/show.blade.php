@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons"> --}}

<link href="{{ asset('assets/plugins/select2/css/select2.min.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/select2/css/select2-bootstrap4.css') }}" rel="stylesheet" />
<style>
    .select2-selection {
        height: 38px !important;
        font-size: 1rem;
    }
    .dtp-btn-ok, .dtp-btn-cancel {
        color: white !important;
    }
    .part-id {
        font-size: large !important;
        font-weight: 700;
    }
</style>
@endsection

@section('wrapper')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        @include('tx.'.$folder.'.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            {{-- <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$queryDelivery->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT') --}}
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">FK No</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label part-id">{{ $queryDelivery->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ $queryDelivery->customer->name }}</label>
                                                <input type="hidden" name="customer_id" id="customer_id"
                                                    value="@if (old('customer_id')){{ old('customer_id') }}@else{{ $queryDelivery->customer_id }}@endif">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">SO Date</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ date_format(date_create($queryDelivery->delivery_order_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="sales_order_no" class="col-sm-3 col-form-label">Sales Order No</label>
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">Sales Order No</th>
                                                                    </tr>
                                                                    @php
                                                                        $sales_orders_no = explode(',',$queryDelivery->sales_order_no_all);
                                                                        $iRow = 0;
                                                                    @endphp
                                                                    @foreach ($sales_orders_no as $row_so)
                                                                        @if($row_so!='')
                                                                            <tr>
                                                                                <td style="text-align: right;">{{ $iRow+1 }}</td>
                                                                                <td>{{ $row_so }}</td>
                                                                            </tr>
                                                                            @php
                                                                                $iRow += 1;
                                                                            @endphp
                                                                        @endif
                                                                    @endforeach
                                                                </thead>
                                                                <tbody id="new-row-so"></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Ship To</label>
                                                @foreach ($ship_to as $wT)
                                                    @php
                                                        $addr = $wT->address.(!is_null($wT->subdistrict)?', '.$wT->subdistrict->sub_district_name:'').
                                                            (!is_null($wT->district)?', '.ucwords(strtolower($wT->district->district_name)):'').
                                                            (!is_null($wT->city)?', '.($wT->city->city_type=='Luar Negeri'?'':$wT->city->city_type.' ').$wT->city->city_name:'').
                                                            (!is_null($wT->province)?', '.$wT->province->province_name.' '.$wT->province->country->country_name:'')
                                                    @endphp
                                                    @if((int)$queryDelivery->c_shipment_addr_id==(int)$wT->id)
                                                        <label for="" class="col-sm-9 col-form-label">{{ $addr }}</label>
                                                    @endif
                                                @endforeach
                                            </div> --}}
                                            {{-- <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Ship By</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->courier->name }}</label>
                                            </div> --}}
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->remark }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created by</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->createdBy->name }}</label>
                                            </div>
                                            @php
                                                $date = date_create($queryDelivery->draft_to_created_at!=null?$queryDelivery->draft_to_created_at:$queryDelivery->createdat);
                                                date_add($date, date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours"));
                                            @endphp
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Created at</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format($date,"d/m/Y H:i:s") }}</label>
                                            </div>
                                            {{-- @php
                                                $date = date_create($queryDelivery->updated_at);
                                                date_add($date, date_interval_create_from_date_string((env("WAKTU_ID")??7)." hours"));
                                            @endphp
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Updated at</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ date_format($date,"d/m/Y H:i:s") }}</label>
                                            </div> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                            $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="@if(old('totalRow')){{ old('totalRow') }}@else{{ $totRow }}@endif">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 25%;">Part No</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 4%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 6%;">Weight</th>
                                        {{-- <th scope="col" style="width: 3%;">Delete</th> --}}
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $lastIdx = 0;
                                        $lastTotalAmount = 0;
                                    @endphp
                                    @foreach ($parts as $part)
                                        <tr id="row{{ $lastIdx }}">
                                            <th scope="row" style="text-align:right;">
                                                <label for="" class="col-form-label">{{ $lastIdx+1 }}.</label>
                                            </th>
                                            <td>
                                                @php
                                                    $partNumber = $part->part->part_number;
                                                    if(strlen($partNumber)<11){
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                    }else{
                                                        $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                    }
                                                @endphp
                                                <label for="" name="part_no{{ $lastIdx }}" id="part_no{{ $lastIdx }}" class="col-form-label">{{ $partNumber }}</label>
                                            </td>
                                            <td><label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}" class="col-form-label">{{ $part->part->part_name }}</label></td>
                                            <td style="text-align: right;">
                                                <label for="" name="qtylbl{{ $lastIdx }}" id="qtylbl{{ $lastIdx }}" class="col-form-label">{{ $part->qty }}</label>
                                            </td>
                                            <td>
                                                <label for="" name="part_unit{{ $lastIdx }}" id="part_unit{{ $lastIdx }}"
                                                    class="col-form-label">{{ $part->part->quantity_type->string_val }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="price{{ $lastIdx }}" id="price{{ $lastIdx }}"
                                                    class="col-form-label">{{ number_format($part->final_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}"
                                                    class="col-form-label">{{ number_format($part->qty*$part->final_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="weight{{ $lastIdx }}" id="weight{{ $lastIdx }}"
                                                    class="col-form-label">{{ $part->part->weight.' '.(!is_null($part->part->weight_unit)?$part->part->weight_unit->string_val:'') }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $lastIdx += 1;
                                            $lastTotalAmount += ($part->qty*$part->final_price);
                                        @endphp
                                    @endforeach
                                    <tr id="rowTotal">
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
                                    </tr>
                                    @if ($queryDelivery->is_vat=='Y')
                                        <tr id="rowVAT">
                                            <td colspan="6" style="text-align: right;">
                                                <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount*$vat/100,0,'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <tr id="rowGrandTotal">
                                            <td colspan="6" style="text-align: right;">
                                                <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastTotalAmount*$vat/100),0,'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
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
            {{-- </form> --}}
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $("#back-btn").click(function() {
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri) }}";
        });
    });
</script>
@endsection
