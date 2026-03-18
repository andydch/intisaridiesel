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
            @if (session('status-error'))
                <div class="alert alert-danger">
                    {{ session('status-error') }}
                </div>
            @endif
            <form id="submitApproval" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder.'/'.$orders->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Sales Order No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $orders->sales_order_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Sales Order Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->sales_order_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $orders->customer->name }}</label>
                            </div>
                            <div id="customer_data" class="row mb-3">
                                <label for="customer_data" class="col-sm-3 col-form-label">Information</label>
                                <div id="customer_info" class="col-sm-9">
                                    @if (!is_null($custInfo))
                                    {!!
                                    'Address:<br />'.$custInfo->office_address.
                                    ($custInfo->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($custInfo->subdistrict->sub_district_name))).
                                    ($custInfo->district->district_name=='Other'?'':', '.$custInfo->district->district_name).
                                    ($custInfo->city->city_name=='Other'?'':'<br />'.($custInfo->city->city_type=='Luar Negeri'?'':$custInfo->city->city_type).' '.
                                    $custInfo->city->city_name).
                                    ($custInfo->province->province_name=='Other'?'':'<br />'.$custInfo->province->province_name).'<br />'.$custInfo->province->country->country_name.
                                    ($custInfo->subdistrict->post_code=='000000'?'':' '.$custInfo->subdistrict->post_code)
                                    !!}
                                    @endif
                                </div>
                            </div>
                            @if ($userLogin->is_director=='Y')
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($orders->branch)?$orders->branch->name:'') }}</label>
                            </div>
                            @endif
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">SQ No</label>
                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($orders->sales_quotation)?$orders->sales_quotation->sales_quotation_no:'' }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer Doc No</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $orders->customer_doc_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer Unit No</label>
                                <label for="" class="col-sm-3 col-form-label">{{ $orders->cust_unit_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="cust_shipment_address" class="col-sm-3 col-form-label">Customer Shipment Address*</label>
                                @if (!is_null($b))
                                    @php
                                        $address = $b->address.' '.
                                        ($b->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($b->subdistrict->sub_district_name))).
                                        ($b->district->district_name=='Other'?'':', '.$b->district->district_name).
                                        ($b->city->city_name=='Other'?'':' '.($b->city->city_type=='Luar Negeri'?'':$b->city->city_type).' '.$b->city->city_name).
                                        ($b->province->province_name=='Other'?'':' '.$b->province->province_name).' '.$b->province->country->country_name.
                                        ($b->subdistrict->post_code=='000000'?'':' '.$b->subdistrict->post_code);
                                    @endphp
                                @else
                                    @php
                                        $address = '';
                                    @endphp
                                @endif
                                <label for="cust_shipment_address" class="col-sm-9 col-form-label">{{ $address }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer PIC</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @if ($orders->pic_id==1){{ $custInfo->pic1_name }}@endif
                                    @if ($orders->pic_id==2){{ $custInfo->pic2_name }}@endif
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">VAT</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $orders->is_vat }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $orders->remark }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Created by</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $orders->createdBy->name }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="active" class="col-sm-3 col-form-label">Approval Status</label>
                                <div class="col-sm-9">
                                    <select class="form-select single-select @error('order_appr') is-invalid @enderror" id="order_appr" name="order_appr">
                                        <option @if (old('order_appr')=='A'){{ 'selected' }}@endif value="A">Approve</option>
                                        {{-- <option @if (old('order_appr')=='R'){{ 'selected' }}@endif value="R">Reject</option> --}}
                                    </select><br />
                                    {{-- Reason:<br />
                                    <textarea id="reason" name="reason" maxlength="2048" class="form-control @error('reason') is-invalid @enderror" rows="3" aria-label="reason">@if (old('reason')){{ old('reason') }}@endif</textarea> --}}
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            {{-- <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="submit" class="btn btn-primary px-5" style="margin-top: 15px;" value="Submit">
                                </div>
                            </div> --}}
                        </div>
                    </div>
                    <hr>
                    <h6 class="mb-0 text-uppercase">Part Detail</h6>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            @php
                            $totRow = $totalRow;
                            @endphp
                            <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                            <table class="table table-bordered mb-0">
                                <thead>
                                    <tr style="width: 100%;">
                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                        <th scope="col" style="width: 10%;">Part Number</th>
                                        <th scope="col" style="width: 15%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">Description</th>
                                        <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Final Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Pricelist ({{ $qCurrency->string_val }})</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($order_parts as $op)

                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;"><label id="" for="" class="col-form-label">{{ $i + 1 }}.</label></th>
                                        <input type="hidden" name="order_part_id{{ $i }}" id="order_part_id{{ $i }}" value="{{ $op->id }}">
                                        @php
                                            $partNo = \App\Models\Mst_part::where([
                                                'id' => $op->part_id,
                                            ])
                                            ->first();
                                        @endphp
                                        <td>
                                            @php
                                                $partNumber = $op->part->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <label id="" for="" class="col-form-label">{{ $partNumber }}</label>
                                        </td>
                                        <td><label id="" for="" class="col-form-label">{{ $op->part->part_name }}</label></td>
                                        <td style="text-align: right;"><label id="" for="" class="col-form-label">{{ $op->qty }}</label></td>
                                        @php
                                            $bgColor = '#151313';
                                            $color = '#fff';
                                        @endphp
                                        @if ($op->price<$partNo->avg_cost)
                                            @php
                                                $bgColor = '#fff';
                                                $color = 'red';
                                            @endphp
                                        @endif
                                        <td style="text-align: right;background-color:{{ $bgColor }};color:{{ $color }};">
                                            <label id="" for="" class="col-form-label">{{ number_format($op->price,0,'.',',') }}</label>
                                        </td>
                                        <td style="text-align: right;"><label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format($op->qty*$op->price,0,'.',',') }}</label></td>
                                        <td><label id="" for="" class="col-form-label">{{ $op->desc }}</label></td>
                                        <td style="text-align: right;"><label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->avg_cost,0,'.',',')) }}</label></td>
                                        <td style="text-align: right;"><label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($op)?'':number_format($op->final_price,0,'.',',')) }}</label></td>
                                        <input type="hidden" name="final_price_{{ $i }}_db" id="final-price-{{ $i }}-db" value="{{ (is_null($op)?0:$op->final_price) }}">
                                        <td style="text-align: right;"><label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->price_list,0,'.',',')) }}</label></td>
                                    </tr>

                                    @php
                                        $i += 1;
                                    @endphp
                                    @endforeach

                                    <tr>
                                        <td colspan="5" style="text-align: right;">TOTAL before VAT</td>
                                        <td style="text-align: right;">{{ $qCurrency->string_val.number_format($orders->total_before_vat,0,'.',',') }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right;">VAT</td>
                                        <td style="text-align: right;">{{ $qCurrency->string_val.number_format($orders->total_after_vat-$orders->total_before_vat,0,'.',',') }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" style="text-align: right;">Grand Total</td>
                                        <td style="text-align: right;">{{ $qCurrency->string_val.number_format($orders->total_after_vat,0,'.',',') }}</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="submit" class="btn btn-primary px-5" style="" value="Approve">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Back">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <!--end row-->
    </div>
</div>
{{-- </div>
</div> --}}
<!--end page wrapper -->
@endsection

@section('script')
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}">
</script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#submitApproval').submit(function(event){
            if(!confirm("The approval status becomes "+ $("#order_appr option:selected").text() +", after this it cannot be changed!\nContinue?")){
                event.preventDefault();
            }
        });

        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });
    });
</script>
@endsection
