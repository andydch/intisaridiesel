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
        @include('tx.' . $folder . '.breadcrumb')
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                <hr />
                {{-- @if($errors->any())
                Error:
                {!! implode('', $errors->all('<div>- :message</div>')) !!}<br />
                @endif --}}
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Surat Jalan No</label>
                            <label for="" class="col-sm-9 col-form-label part-id">{{ $orders->surat_jalan_no }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Surat Jalan Date</label>
                            <label for="" class="col-sm-9 col-form-label">{{ date_format(date_create($orders->surat_jalan_date), 'd/m/Y') }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Customer</label>
                            <label for="" class="col-sm-9 col-form-label">{{ !is_null($orders->customer)?$orders->customer->name:'' }}</label>
                        </div>
                        <div id="customer_data" class="row mb-3">
                            <label for="customer_data" class="col-sm-3 col-form-label">Information</label>
                            <div id="customer_info" class="col-sm-9">
                                @isset($custInfo)
                                    {!!
                                    'Address:<br />'.$custInfo->office_address.
                                    ($custInfo->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($custInfo->subdistrict->sub_district_name))).
                                    ($custInfo->district->district_name=='Other'?'':', '.$custInfo->district->district_name).
                                    ($custInfo->city->city_name=='Other'?'':'<br />'.($custInfo->city->city_type=='Luar Negeri'?'':$custInfo->city->city_type).' '.
                                    $custInfo->city->city_name).
                                    ($custInfo->province->province_name=='Other'?'':'<br />'.$custInfo->province->province_name).'<br />'.$custInfo->province->country->country_name.
                                    ($custInfo->subdistrict->post_code=='000000'?'':' '.$custInfo->subdistrict->post_code)
                                    !!}
                                @endisset
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
                            <label for="" class="col-sm-9 col-form-label part-id">{{ !is_null($orders->sales_quotation)?$orders->sales_quotation->sales_quotation_no:'' }}</label>
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
                            <label for="" class="col-sm-3 col-form-label">Customer Shipment Address</label>
                            <label for="" class="col-sm-9 col-form-label">
                                @foreach ($custShipmentAddressInfo as $b)
                                    @php
                                    $address = $b->address.' '.
                                        ($b->subdistrict->sub_district_name=='Other'?'':','.ucwords(strtolower($b->subdistrict->sub_district_name))).
                                        ($b->district->district_name=='Other'?'':', '.$b->district->district_name).
                                        ($b->city->city_name=='Other'?'':' '.($b->city->city_type=='Luar Negeri'?'':$b->city->city_type).' '.$b->city->city_name).
                                        ($b->province->province_name=='Other'?'':' '.$b->province->province_name).' '.$b->province->country->country_name.
                                        ($b->subdistrict->post_code=='000000'?'':' '.$b->subdistrict->post_code);
                                    @endphp
                                    @if ((int)$orders->cust_shipment_address==(int)$b->id){{ $address }}@endif
                                @endforeach
                            </label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Customer PIC</label>
                            <label for="" class="col-sm-9 col-form-label">
                                @if ($orders->pic_id==1) {{ !is_null($custInfo)?$custInfo->pic1_name:'' }} @endif
                                @if ($orders->pic_id==2) {{ !is_null($custInfo)?$custInfo->pic2_name:'' }} @endif
                            </label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Courier</label>
                            <label for="" class="col-sm-9 col-form-label">
                                @switch($orders->courier_type)
                                    @case(env('AMBIL_SENDIRI'))
                                        {{ env('AMBIL_SENDIRI_STR') }}
                                        @break

                                    @case(env('DIANTAR'))
                                        {{ env('DIANTAR_STR') }}
                                        @break

                                    @case(env('COURIER'))
                                        {{ env('COURIER_STR').(!is_null($orders->courier)?' - '.$orders->courier->name:'') }}
                                        @break

                                    @default
                                        {{ '' }}
                                @endswitch
                                {{-- {{ (!is_null($orders->courier)?$orders->courier->name:'') }} --}}
                            </label>
                        </div>
                        {{-- <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">VAT</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $orders->is_vat }}</label>
                        </div> --}}
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Remark</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $orders->remark }}</label>
                        </div>
                        <div class="row mb-3">
                            <label for="" class="col-sm-3 col-form-label">Created by</label>
                            <label for="" class="col-sm-9 col-form-label">{{ $orders->createdBy->name }}</label>
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
                                    <th scope="col" style="width: 15%;">Part Name</th>
                                    <th scope="col" style="width: 5%;">Qty</th>
                                    <th scope="col" style="width: 10%;">Price ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 10%;">Total ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 10%;">AVG Cost ({{ $qCurrency->string_val }})</th>
                                    <th scope="col" style="width: 10%;">Final Price ({{ $qCurrency->string_val }})</th>
                                    {{-- <th scope="col" style="width: 5%;">OH</th>
                                    <th scope="col" style="width: 5%;">SO</th> --}}
                                    <th scope="col" style="width: 30%;">Description</th>
                                    {{-- <th scope="col" style="width: 10%;">Pricelist</th> --}}
                                    {{-- <th scope="col" style="width: 5%;text-align:center;">Del</th> --}}
                                </tr>
                            </thead>
                            <tbody id="new-row">
                                @if(old('totalRow'))
                                    {{-- empty --}}
                                @else

                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($order_parts as $op)

                                        <tr id="row{{ $i }}">
                                            <th scope="row" style="text-align:right;"><label id="" for="" class="col-form-label">{{ $i + 1 }}.</label></th>
                                            <input type="hidden" name="order_part_id{{ $i }}" id="order_part_id{{ $i }}" value="{{ $op->id }}">
                                            @php
                                                $partNo = \App\Models\Mst_part::leftJoin('tx_qty_parts AS tx_qty','mst_parts.id','=','tx_qty.part_id')
                                                ->select(
                                                    'mst_parts.*',
                                                    'tx_qty.qty AS qty_oh'
                                                )
                                                ->where([
                                                    'mst_parts.id' => $op->part_id,
                                                    'tx_qty.branch_id' => $userLogin->branch_id
                                                ])
                                                ->first();

                                                $qtySO = 0;
                                                $so = \App\Models\Tx_surat_jalan_part::leftJoin('tx_surat_jalans AS txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                ->leftJoin('userdetails AS usr','txso.created_by','=','usr.user_id')
                                                ->select(
                                                    'tx_surat_jalan_parts.qty',
                                                    'txso.surat_jalan_no',
                                                )
                                                ->where('tx_surat_jalan_parts.part_id','=',$op->part_id)
                                                ->where('tx_surat_jalan_parts.active','=','Y')
                                                ->where('txso.surat_jalan_no','NOT LIKE','%Draft%')
                                                ->where('txso.active','=','Y')
                                                ->where('usr.branch_id','=',$userLogin->branch_id)
                                                ->get();
                                                foreach ($so as $so_part) {
                                                    $qDO = \App\Models\Tx_delivery_order_non_tax::where('delivery_order_no','NOT LIKE','%Draft%')
                                                    ->where('sales_order_no_all','LIKE','%'.$so_part->surat_jalan_no.'%')
                                                    ->where('active','=','Y')
                                                    ->first();
                                                    if(!$qDO){
                                                        $qtySO += $so_part->qty;
                                                    }
                                                }
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
                                            <td style="text-align: right;"><label id="" for="" class="col-form-label">{{ number_format($op->price,0,'.',',') }}</label></td>
                                            <td style="text-align: right;"><label id="total-price-{{ $i }}" for="" class="col-form-label">{{ number_format($op->qty*$op->price,0,'.',',') }}</label></td>
                                            <td style="text-align: right;"><label id="final-cost-{{ $i }}" for="" class="col-form-label">{{ number_format($op->last_avg_cost,0,'.',',') }}</label></td>
                                            @php
                                                $qLastFinalPrice = \App\Models\Tx_surat_jalan_part::selectRaw('IFNULL(tx_surat_jalan_parts.price,0) AS last_final_price')
                                                ->leftJoin('tx_surat_jalans as txso','tx_surat_jalan_parts.surat_jalan_id','=','txso.id')
                                                ->leftJoin('userdetails as usr','txso.created_by','=','usr.user_id')
                                                ->where('tx_surat_jalan_parts.part_id','=',$op->part_id)
                                                ->where('usr.branch_id','=',$userLogin->branch_id)
                                                ->where('tx_surat_jalan_parts.active','=','Y')
                                                ->where('txso.active','=','Y')
                                                ->orderBy('txso.created_at','DESC')
                                                ->limit(1)
                                                ->first();
                                            @endphp
                                            <td style="text-align: right;"><label id="final-price-{{ $i }}" for="" class="col-form-label">{{ $qLastFinalPrice?number_format($qLastFinalPrice->last_final_price,0,'.',','):0 }}</label></td>
                                            <input type="hidden" name="final_price_{{ $i }}_db" id="final-price-{{ $i }}-db" value="{{ $qLastFinalPrice?$qLastFinalPrice->last_final_price:0 }}">
                                            {{-- <td style="text-align: right;"><label id="oh-{{ $i }}" for="" class="col-form-label">{{ number_format($partNo->qty_oh,0,'.',',') }}</label></td>
                                            <td style="text-align: right;"><label id="so-{{ $i }}" for="" class="col-form-label">{{ number_format($qtySO,0,'.',',') }}</label></td> --}}
                                            <td><label id="" for="" class="col-form-label">{{ $op->desc }}</label></td>
                                            {{-- <td style="text-align: right;"><label id="price_list-{{ $i }}" for="" class="col-form-label">{{ (is_null($partNo)?'':$qCurrency->string_val.number_format($partNo->price_list,0,'.',',')) }}</label></td> --}}
                                            {{-- <td style="text-align: center;">
                                                <input type="checkbox" id="rowCheck{{ $i }}" value="{{ $i }}">
                                            </td> --}}
                                        </tr>

                                        @php
                                            $i += 1;
                                        @endphp
                                    @endforeach

                                @endif
                                <tr>
                                    <td colspan="5" style="text-align: right;">TOTAL</td>
                                    <td style="text-align: right;">{{ $qCurrency->string_val.number_format($orders->total,0,'.',',') }}</td>
                                    <td colspan="5"></td>
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
            location.href='{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}';
        });
    });
</script>
@endsection
