@extends('layouts.app')

@section('style')
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.time.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/plugins/datetimepicker/css/classic.date.css') }}" rel="stylesheet" />
<link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap-material-datetimepicker/css/bootstrap-material-datetimepicker.min.css') }}">
<link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

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
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="border p-4 rounded">
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Sales Quotation No</label>
                                <label for="" class="col-sm-9 col-form-label part-id">{{ $salesQuo->sales_quotation_no }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Date</label>
                                <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($salesQuo->sales_quotation_date), 'd/m/Y') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer</label>
                                <label for="" class="col-sm-9 col-form-label">{{ !is_null($salesQuo->customer)?$salesQuo->customer->entity_type->title_ind.' '.$salesQuo->customer->name:'' }}</label>
                            </div>
                            <div id="customer_data" class="row mb-3">
                                <label for="customer_data" class="col-sm-3 col-form-label">Information</label>
                                <div id="customer_info" class="col-sm-9">
                                    @isset($customerPics[0])
                                        {!!
                                        (!is_null($customerPics[0]->customer_type)?$customerPics[0]->customer_type->title_ind:'').$customerPics[0]->name.
                                        '<br />Address: '.$customerPics[0]->office_address.
                                        ($customerPics[0]->subdistrict->sub_district_name=='Other'?'':
                                        ', '.ucwords(strtolower($customerPics[0]->subdistrict->sub_district_name))).
                                        ($customerPics[0]->district->district_name=='Other'?'':
                                        ', '.$customerPics[0]->district->district_name).
                                        ($customerPics[0]->city->city_name=='Other'?'':
                                        '<br />'.($customerPics[0]->city->city_type=='Luar
                                        Negeri'?'':$customerPics[0]->city->city_type).' '.
                                        $customerPics[0]->city->city_name).
                                        ($customerPics[0]->province->province_name=='Other'?'':
                                        '<br />'.$customerPics[0]->province->province_name).
                                        '<br />'.$customerPics[0]->province->country->country_name.
                                        ($customerPics[0]->subdistrict->post_code=='000000'?'':
                                        ' '.$customerPics[0]->subdistrict->post_code)
                                        !!}
                                    @endisset
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Customer PIC</label>
                                <label for="" class="col-sm-9 col-form-label">
                                    @if ($salesQuo->pic_idx==1)
                                        {{ !is_null($salesQuo->customer)?$salesQuo->customer->pic1_name:'' }}
                                    @else
                                        {{ !is_null($salesQuo->customer)?$salesQuo->customer->pic2_name:'' }}
                                    @endif
                                </label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Branch</label>
                                <label for="" class="col-sm-9 col-form-label">{{ (!is_null($salesQuo->branch)?$salesQuo->branch->name:'') }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Header</label>
                                <label for="" class="col-sm-9 col-form-label">{!! $salesQuo->header !!}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Footer</label>
                                <label for="" class="col-sm-9 col-form-label">{!! $salesQuo->footer !!}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $salesQuo->remark }}</label>
                            </div>
                            <div class="row mb-3">
                                <label for="" class="col-sm-3 col-form-label">Created by</label>
                                <label for="" class="col-sm-9 col-form-label">{{ $salesQuo->createdBy->name }}</label>
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
                        <input type="hidden" id="totalRow" name="totalRow" value="{{ $totRow }}">
                        <table class="table table-bordered mb-0">
                            <thead>
                                <tr style="width: 100%;">
                                    <th scope="col" style="width: 3%;text-align:center;">#</th>
                                    <th scope="col" style="width: 10%;">Part Number</th>
                                    <th scope="col" style="width: 14%;">Part Name</th>
                                    <th scope="col" style="width: 10%;">Part Type</th>
                                    <th scope="col" style="width: 5%;">Qty</th>
                                    <th scope="col" style="width: 12%;">Price ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Total ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 15%;">Description</th>
                                    <th scope="col" style="width: 12%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Final Price ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 12%;">Pricelist ({{ $qCurrency->string_val }})</th>
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @php
                                    $i=0;
                                @endphp
                                @foreach ($querySalesQuoPart as $q)
                                    <tr id="row{{ $i }}">
                                        <th scope="row" style="text-align:right;"><label for="" class="col-form-label">{{ $i + 1 }}.</label></th>
                                        @php
                                            $partNo = \App\Models\Mst_part::where([
                                                'id' => $q->part_id,
                                            ])
                                            ->first();
                                        @endphp
                                        <td>
                                            @php
                                                $partNumber = $q->part->part_number;
                                                if(strlen($partNumber)<11){
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                }else{
                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                }
                                            @endphp
                                            <label for="" class="col-form-label">{{ $partNumber }}</label>
                                        </td>
                                        <td><label for="" class="col-form-label">{{ $q->part->part_name }}</label></td>
                                        <td style="text-align: right;"><label id="part_type-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':$partNo->part_type->title_ind) }}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ $q->qty }}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($q->price_part,0,'.',',') }}</label></td>
                                        <td style="text-align: right;"><label for="" class="col-form-label">{{ number_format($q->qty*$q->price_part,0,'.',',') }}</label></td>
                                        <td><label for="" class="col-form-label">{{ $q->description }}</label></td>
                                        <td style="text-align: right;"><label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->avg_cost,0,'.',',')) }}</label></td>
                                        <td style="text-align: right;"><label id="final-price-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->final_price,0,'.',',')) }}</label></td>
                                        <td style="text-align: right;"><label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':number_format($partNo->price_list,0,'.',',')) }}</label></td>
                                    </tr>
                                    @php
                                        $i += 1;
                                    @endphp
                                @endforeach
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
            history.back();
        });
    });
</script>
@endsection
