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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order-adjustment/'.$queryDelivery->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                                                <label for="customer_id" class="col-sm-3 col-form-label">Delivery Order No</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label part-id">{{ $queryDelivery->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">Delivery Order Date</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ date_format(date_create($queryDelivery->delivery_order_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ !is_null($queryDelivery->customer)?$queryDelivery->customer->name:'' }}</label>
                                                <input type="hidden" name="customer_id" id="customer_id"
                                                    value="@if (old('customer_id')){{ old('customer_id') }}@else{{ $queryDelivery->customer_id }}@endif">
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
                                            <div class="row mb-3">
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
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Ship By</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->courier->name }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Remark</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->remark }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">VAT</label>
                                                <label for="" class="col-sm-9 col-form-label">{{ $queryDelivery->is_vat }}</label>
                                            </div>
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
                                        <th scope="col" style="width: 20%;">Part No</th>
                                        <th scope="col" style="width: 20%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Qty Adjustment</th>
                                        <th scope="col" style="width: 4%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Price</th>
                                        <th scope="col" style="width: 10%;">Total Price</th>
                                        <th scope="col" style="width: 6%;">Weight</th>
                                        <th scope="col" style="width: 3%;">Delete</th>
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
                                                <input type="hidden" name="delv_order_part_id{{ $lastIdx }}" id="delv_order_part_id{{ $lastIdx }}" value="{{ $part->id }}">
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
                                            <td style="text-align: right;">
                                                <input type="text" class="form-control @error('qtyAdjustment'.$lastIdx) is-invalid @enderror" style="text-align: right;" id="qtyAdjustment{{ $lastIdx }}"
                                                    name="qtyAdjustment{{ $lastIdx }}" maxlength="3" onchange="reCalc(this.value,{{ $lastIdx }});"
                                                    value="@if (old('qtyAdjustment'.$lastIdx)){{ old('qtyAdjustment'.$lastIdx) }}@else{{ $part->qty }}@endif" />
                                                @error('qtyAdjustment'.$lastIdx)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td>
                                                <label for="" name="part_unit{{ $lastIdx }}" id="part_unit{{ $lastIdx }}"
                                                    class="col-form-label">{{ $part->part->quantity_type->string_val }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="price{{ $lastIdx }}" id="price{{ $lastIdx }}"
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($part->final_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}"
                                                    class="col-form-label">{{ $qCurrency->string_val.number_format($part->qty*$part->final_price,0,'.',',') }}</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="weight{{ $lastIdx }}" id="weight{{ $lastIdx }}"
                                                    class="col-form-label">{{ $part->part->weight.' '.(!is_null($part->part->weight_unit)?$part->part->weight_unit->string_val:'') }}</label>
                                            </td>
                                            <td style="text-align:center;"><input type="checkbox" id="rowCheck{{ $lastIdx }}" value="{{ $lastIdx }}"></td>
                                        </tr>
                                        @php
                                            $lastIdx += 1;
                                            $lastTotalAmount += ($part->qty*$part->final_price);
                                        @endphp
                                    @endforeach
                                    <tr id="rowTotal">
                                        <td colspan="7" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr id="rowVAT">
                                        <td colspan="7" style="text-align: right;">
                                            <label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount*$vat/100,0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr id="rowGrandTotal">
                                        <td colspan="7" style="text-align: right;">
                                            <label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount+($lastTotalAmount*$vat/100),0,'.',',') }}</label>
                                        </td>
                                        <td colspan="2">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="btn-del-row" class="btn btn-light px-5" value="Remove Row">
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    {{-- @if ($queryDelivery->is_draft=='Y')
                                    <input type="button" id="save-as-draft" class="btn btn-light px-5" value="Save as Draft">
                                    @endif --}}
                                    <input type="button" id="save" class="btn btn-light px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-light px-5" value="Cancel">
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
    function reCalc(val,idx){
        // if(!isNaN(val)){
        let price = $("#price"+idx).text().replaceAll(',','').replaceAll('{{ $qCurrency->string_val }}','');
        // let price = $("#price"+idx).text().replaceAll('.','').replaceAll(',','.');
        $("#total"+idx).text('{{ $qCurrency->string_val }}'+parseFloat(price*val).numberFormat(0,'.',','));
        recalcTotal();
        // }
    }

    function recalcTotal(){
        let totalRow = $("#totalRow").val();
        let totalAmount = 0;
        for(iRow=0;iRow<totalRow;iRow++){
            if($('#total'+iRow).length){
                let totalAmountPerRow = $('#total'+iRow).text().replaceAll(',','').replaceAll('{{ $qCurrency->string_val }}','');
                // let totalAmountPerRow = $('#total'+iRow).text().replaceAll('.','').replaceAll(',','.');
                totalAmount += parseFloat(totalAmountPerRow);
            }else{
                //
            }
        }

        $('#lblTotalAmount').text('{{ $qCurrency->string_val }}'+(totalAmount).numberFormat(0,'.',','));
        $('#lblVATAmount').text('{{ $qCurrency->string_val }}'+(totalAmount*{{ $vat }}/100).numberFormat(0,'.',','));
        $('#lblGrandTotalAmount').text('{{ $qCurrency->string_val }}'+(totalAmount+(totalAmount*{{ $vat }}/100)).numberFormat(0,'.',','));
    }

    $(document).ready(function() {
        $("#save-as-draft").click(function() {
            if(!confirm("Data will be saved to database with DRAFT status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/delivery-order') }}";
        });
        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck" + i).is(':checked')) {
                    $("#row" + i).remove();
                }
            }
            recalcTotal();
        });
        $("#btn-del-row-so").click(function() {
            for (i = 0; i < $("#totalRowSO").val(); i++) {
                let SO_No = $("#salesorder_no_select" + i).text();
                if ($("#rowCheckSO" + i).is(':checked')) {

                    //delete part
                    for (j = 0; j < $("#totalRow").val(); j++) {
                        if ($("#sales_order_id" + j).val()===$("#sales_order_idSO" + i).val()) {
                            $("#row" + j).remove();
                        }
                    }
                    recalcTotal();
                    //delete part

                    $("#rowSO" + i).remove();
                    $("#sales_order_no_all").val($("#sales_order_no_all").val().replace(SO_No,''));
                    $("#sales_order_no_all").val($("#sales_order_no_all").val().replace(',,',','));
                    if($("#sales_order_no_all").val()===','){
                        $("#sales_order_no_all").val('');
                    }
                }
            }
        });
    });
</script>
@endsection
