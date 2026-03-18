@extends('layouts.app')

@section('style')
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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                <input type="hidden" name="lg_ea" value="{{ Auth::user()->email }}">
                <div class="col-xl-12 mx-auto">
                    <h6 class="mb-0 text-uppercase">{{ $title }}</h6>
                    <hr />
                    <div class="card">
                        <div class="card-body">
                            @if (session('status-error'))
                                <div class="alert alert-danger">
                                    {{ session('status-error') }}
                                </div>
                            @endif
                            <div class="row mb-3">
                                <div class="col-xl-6">
                                    <div class="row">
                                        <div class="col-xl-12">
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">Customer*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('customer_id') is-invalid @enderror"
                                                        id="customer_id" name="customer_id" onchange="dispSOdate(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('customer_id')?old('customer_id'):0);
                                                        @endphp
                                                        @foreach ($qCust as $qC)
                                                            <option @if ($p_Id==$qC->id){{ 'selected' }}@endif
                                                                value="{{ $qC->id }}">{{ $qC->customer_unique_code.' - '.(!is_null($qC->entity_type)?$qC->entity_type->title_ind:'').' '.$qC->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('customer_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="sales_order_date" class="col-sm-3 col-form-label">SO Date*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('sales_order_date') is-invalid @enderror"
                                                        id="sales_order_date" name="sales_order_date" onchange="dispSO();">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $sales_order_date = (old('sales_order_date')?old('sales_order_date'):0);
                                                        @endphp
                                                        @foreach ($get_sales_order_date as $wT)
                                                            <option @if($sales_order_date==$wT->sales_order_date){{ 'selected' }}@endif value="{{ $wT->sales_order_date }}">{{ $wT->sales_order_date }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('sales_order_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="sales_order_no" class="col-sm-3 col-form-label">Sales Order No*</label>
                                                <div class="col-sm-5">
                                                    <select class="form-select single-select @error('sales_order_no') is-invalid @enderror" id="sales_order_no" name="sales_order_no">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $sales_order_no = (old('sales_order_no')?old('sales_order_no'):0);
                                                        @endphp
                                                        @foreach ($get_sales_order_no as $wT)
                                                            <option @if($sales_order_no==$wT->order_no){{ 'selected' }}@endif value="{{ $wT->id }}">{{ $wT->order_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('sales_order_no_all')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <input type="hidden" name="sales_order_no_all" id="sales_order_no_all" value="@if(old('sales_order_no_all')){{ old('sales_order_no_all') }}@endif">
                                                <input type="button" name="gen_part" id="gen_part" class="btn btn-primary px-5 col-sm-4" value="Generate Part">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">Sales Order No</th>
                                                                        <th scope="col" style="width: 3%;">Delete</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="new-row-so">
                                                                    @if(old('sales_order_no_all'))
                                                                        @php
                                                                            $sales_order_no_all = explode(',',old('sales_order_no_all'));
                                                                            $iRow = 0;
                                                                            $iRowNo = 1;
                                                                        @endphp
                                                                        @foreach ($sales_order_no_all as $sNo)
                                                                            @if ($sNo!='')
                                                                                @php
                                                                                    $qS = \App\Models\Tx_sales_order::where('sales_order_no','=',$sNo)
                                                                                    ->first();
                                                                                @endphp
                                                                                <tr id="rowSO{{ $iRow }}">
                                                                                    <th scope="row" style="text-align:right;">
                                                                                        <label for="" id="sales_order_row_number{{ $iRow }}" class="col-form-label">{{ $iRowNo }}.</label>
                                                                                        <input type="hidden" name="sales_order_idSO{{ $iRow }}" id="sales_order_idSO{{ $iRow }}"
                                                                                            value="{{ $qS->id }}">
                                                                                    </th>
                                                                                    <td>
                                                                                        <label for="" name="salesorder_no_select{{ $iRow }}" id="salesorder_no_select{{ $iRow }}"
                                                                                            class="col-form-label">{{ $sNo }}</label>
                                                                                    </td>
                                                                                    <td style="text-align:center;">
                                                                                        <input type="checkbox" id="rowCheckSO{{ $iRow }}" value="{{ $iRow }}">
                                                                                    </td>
                                                                                </tr>
                                                                                @php
                                                                                    $iRow+= 1;
                                                                                    $iRowNo++;
                                                                                @endphp
                                                                            @endif
                                                                        @endforeach
                                                                    @endif
                                                                </tbody>
                                                            </table>
                                                            <input type="hidden" id="totalRowSO" name="totalRowSO" value="@if(old('totalRowSO')){{ $iRow }}@else{{ 0 }}@endif">
                                                            <input type="button" id="btn-del-row-so" class="btn btn-danger px-5 mt-3" value="Remove Row">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@endif</textarea>
                                                    @error('remark')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
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
                                        <th scope="col" style="width: 25%;">Part No</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 5%;">Qty</th>
                                        <th scope="col" style="width: 7%;">Unit</th>
                                        <th scope="col" style="width: 10%;">Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 10%;">Total Price ({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 6%;">Weight</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if (old('totalRow'))
                                        @php
                                            $lastTotalAmount = 0;
                                            $iRowPartNo = 1;
                                        @endphp
                                        @for ($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if (old('sales_order_part_id'.$lastIdx))
                                                @php
                                                    $qPart = \App\Models\Tx_sales_order_part::where('id','=',old('sales_order_part_id'.$lastIdx))
                                                    ->first();
                                                @endphp
                                                @if ($qPart)                                                    
                                                    <tr id="row{{ $lastIdx }}">
                                                        <th scope="row" style="text-align:right;">
                                                            <label for="" id="sales_order_part_row_number{{ $lastIdx }}" class="col-form-label">{{ $iRowPartNo }}.</label>
                                                            <input type="hidden" name="sales_order_part_id{{ $lastIdx }}" id="sales_order_part_id{{ $lastIdx }}"
                                                                value="@if(old('sales_order_part_id'.$lastIdx)){{ old('sales_order_part_id'.$lastIdx) }}@endif">
                                                            <input type="hidden" name="sales_order_id{{ $lastIdx }}" id="sales_order_id{{ $lastIdx }}"
                                                                value="@if(old('sales_order_id'.$lastIdx)){{ old('sales_order_id'.$lastIdx) }}@endif">
                                                        </th>
                                                        <td>
                                                            @php
                                                                $partNumber = $qPart->part->part_number;
                                                                if(strlen($partNumber)<11){
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                                }else{
                                                                    $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                                }
                                                            @endphp
                                                            <label for="" name="part_no{{ $lastIdx }}" id="part_no{{ $lastIdx }}"
                                                                class="col-form-label">{{ $partNumber }}</label>
                                                        </td>
                                                        <td>
                                                            <label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}"
                                                                class="col-form-label">{{ $qPart->part->part_name }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" name="qtylbl{{ $lastIdx }}" id="qtylbl{{ $lastIdx }}"
                                                                class="col-form-label">{{ $qPart->qty }}</label>
                                                            <input type="hidden" name="qty{{ $lastIdx }}" id="qty{{ $lastIdx }}"
                                                                value="@if(old('qty'.$lastIdx)){{ old('qty'.$lastIdx) }}@endif">
                                                            <input type="hidden" name="qty_on_so{{ $lastIdx }}" id="qty_on_so{{ $lastIdx }}"
                                                                value="@if(old('qty_on_so'.$lastIdx)){{ old('qty_on_so'.$lastIdx) }}@endif">
                                                        </td>
                                                        <td>
                                                            <label for="" name="part_unit{{ $lastIdx }}" id="part_unit{{ $lastIdx }}"
                                                                class="col-form-label">{{ $qPart->part->quantity_type->string_val }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" name="price{{ $lastIdx }}" id="price{{ $lastIdx }}"
                                                                class="col-form-label">{{ number_format($qPart->price,0,'.',',') }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" name="total{{ $lastIdx }}" id="total{{ $lastIdx }}"
                                                                class="col-form-label">{{ number_format(old('qty'.$lastIdx)*$qPart->price,0,'.',',') }}</label>
                                                        </td>
                                                        <td style="text-align: right;">
                                                            <label for="" name="weight{{ $lastIdx }}" id="weight{{ $lastIdx }}"
                                                                class="col-form-label">{{ $qPart->part->weight.' '.(!is_null($qPart->part->weight_unit)?$qPart->part->weight_unit->string_val:'') }}</label>
                                                        </td>
                                                    </tr>
                                                    @php
                                                        $lastTotalAmount+= (old('qty'.$lastIdx)*$qPart->price);
                                                        $iRowPartNo++;
                                                    @endphp
                                                @endif
                                            @endif
                                        @endfor
                                        <tr id="rowTotal">
                                            <td colspan="6" style="text-align: right;">
                                                <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
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
                    {{-- <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="button" id="btn-del-row" class="btn btn-danger px-5" value="Remove Row">
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <hr>
                    <div class="card" style="margin-top: 15px;">
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-sm-12">
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                    <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    <input type="button" id="back-btn" class="btn btn-danger px-5" value="Cancel">
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
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function dispSO(){
        if($("#customer_id").val()==='#'){return false;}
        if($("#sales_order_date").val()==='#'){return false;}

        $("#sales_order_no").empty();
        $("#sales_order_no").append(`<option value="#">Choose...</option>`);
        $("#sales_order_no_all").val('');
        $("#new-row").empty();
        $("#new-row-so").empty();
        $("#totalRow").val(0);

        var fd = new FormData();
        fd.append('customer_id', $("#customer_id").val());
        fd.append('sales_order_date', $("#sales_order_date").val());
        fd.append('is_vat', 'Y');
        $.ajax({
            url: "{{ url('/disp_so') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].sales_order;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].order_no;
                        optionValue = o[i].id;
                        $("#sales_order_no").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function dispSOdate(customer_id){
        $("#sales_order_date").empty();
        $("#sales_order_date").append(`<option value="#">Choose...</option>`);
        $("#sales_order_no_all").val('');
        $("#new-row").empty();
        $("#new-row-so").empty();
        $("#totalRow").val(0);

        var fd = new FormData();
        fd.append('customer_id', customer_id);
        $.ajax({
            url: "{{ url('/disp_so_date') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].sales_order_date;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].sales_order_date;
                        optionValue = o[i].sales_order_date;
                        $("#sales_order_date").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function dispShipmentAddress(customer_id){
        $("#ship_to_id").empty();
        $("#ship_to_id").append(`<option value="#">Choose...</option>`);

        var fd = new FormData();
        fd.append('customer_id', customer_id);
        $.ajax({
            url: "{{ url('/disp_shipment_address') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].shipment_addr;
                let totShipment = o.length;
                if (totShipment > 0) {
                    for (let i = 0; i < totShipment; i++) {
                        let sub_district = ', '+o[i].sub_district_name;
                        if(o[i].sub_district_name===null){sub_district = '';}
                        let district = ', '+o[i].district_name;
                        if(o[i].district_name===null){district = '';}
                        let city = ', '+o[i].city_name;
                        if(o[i].city_name===null){
                            city = '';
                        }else{
                            let city_type = o[i].city_type;
                            if(city_type==='Luar neger'){city_type = '';}
                            city = ', '+city_type+' '+o[i].city_name;
                        }
                        let province = ', '+o[i].province_name;
                        if(o[i].province_name===null){province = '';}
                        let shipment_addr = o[i].address+sub_district.toLowerCase().ucwords()+district+city+province+' '+o[i].country_name;

                        optionText = shipment_addr;
                        optionValue = o[i].shipment_address_id;
                        $("#ship_to_id").append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
                    }
                }
            }
        });
    }

    function dispPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml =
            '<tr id="row'+totalRow+'">'+
            '<th scope="row" style="text-align:right;">'+
            '<label for="" id="sales_order_part_row_number'+totalRow+'" class="col-form-label">'+rowNo+'.</label>'+
            '<input type="hidden" name="sales_order_part_id'+totalRow+'" id="sales_order_part_id'+totalRow+'">'+
            '<input type="hidden" name="sales_order_id'+totalRow+'" id="sales_order_id'+totalRow+'">'+
            '</th>'+
            '<td><label for="" name="part_no'+totalRow+'" id="part_no'+totalRow+'" class="col-form-label"></label></td>'+
            '<td><label for="" name="part_name'+totalRow+'" id="part_name'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align: right;">'+
            '<label for="" name="qtylbl'+totalRow+'" id="qtylbl'+totalRow+'" class="col-form-label"></label>'+
            '<input type="hidden" name="qty'+totalRow+'" id="qty'+totalRow+'">'+
            '<input type="hidden" name="qty_on_so'+totalRow+'" id="qty_on_so'+totalRow+'">'+
            '</td>'+
            '<td><label for="" name="part_unit'+totalRow+'" id="part_unit'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align: right;"><label for="" name="price'+totalRow+'" id="price'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align: right;"><label for="" name="total'+totalRow+'" id="total'+totalRow+'" class="col-form-label"></label></td>'+
            '<td style="text-align: right;"><label for="" name="weight'+totalRow+'" id="weight'+totalRow+'" class="col-form-label"></label></td>'+
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);
    }

    function addSO(){
        let totalRowSO = $("#totalRowSO").val();
        let rowNoSO = (parseInt(totalRowSO)+1);
        let vHtml =
            '<tr id="rowSO'+totalRowSO+'">'+
            '<th scope="row" style="text-align:right;">'+
            '<label for="" id="sales_order_row_number'+totalRowSO+'" class="col-form-label">'+rowNoSO+'.</label>'+
            '<input type="hidden" name="sales_order_idSO'+totalRowSO+'" id="sales_order_idSO'+totalRowSO+'">'+
            '</th>'+
            '<td><label for="" name="salesorder_no_select'+totalRowSO+'" id="salesorder_no_select'+totalRowSO+'" class="col-form-label"></label></td>'+
            '<td style="text-align:center;"><input type="checkbox" id="rowCheckSO'+totalRowSO+'" value="'+totalRowSO+'"></td>'+
            '</tr>';
        $("#new-row-so").append(vHtml);
        $("#totalRowSO").val(rowNoSO);
    }

    function recalcTotal(){
        let totalRow = $("#totalRow").val();
        let totalAmount = 0;
        for(iRow=0;iRow<totalRow;iRow++){
            if($('#total'+iRow).length){
                let totalAmountPerRow = $('#total'+iRow).text().replaceAll(',','');
                totalAmount+= parseFloat(totalAmountPerRow);
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
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('Y');
                $("#submit-form").submit();
            }
        });
        $("#save").click(function() {
            if(!confirm("Data will be saved to database with CREATED status. Make sure the data entered is correct.\nContinue?")){
                event.preventDefault();
            }else{
                $(':input[type="button"]').prop('disabled', true);
                
                $("#is_draft").val('N');
                $("#submit-form").submit();
            }
        });
        $("#back-btn").click(function() {
            $(':input[type="button"]').prop('disabled', true);
            
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri) }}";
        });
        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }
            recalcTotal();
        });
        $("#btn-del-row-so").click(function() {
            for (i = 0; i < $("#totalRowSO").val(); i++) {
                let SO_No = $("#salesorder_no_select"+i).text();
                if ($("#rowCheckSO"+i).is(':checked')) {

                    //delete part
                    for (j = 0; j < $("#totalRow").val(); j++) {
                        if ($("#sales_order_id"+j).val()===$("#sales_order_idSO"+i).val()) {
                            $("#row"+j).remove();
                        }
                    }
                    recalcTotal();
                    //delete part

                    $("#rowSO"+i).remove();
                    $("#sales_order_no_all").val($("#sales_order_no_all").val().replace(SO_No,''));
                    $("#sales_order_no_all").val($("#sales_order_no_all").val().replace(',,',','));
                    if($("#sales_order_no_all").val()===','){
                        $("#sales_order_no_all").val('');
                    }
                }
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totalRowSO").val(); i++) {
                if($("#sales_order_row_number"+i).text()){
                    $("#sales_order_row_number"+i).text(j+'. ');
                    j++;
                }
            }

            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#sales_order_part_row_number"+i).text()){
                    $("#sales_order_part_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#gen_part").click(function() {
            if($('#sales_order_no option:selected').val()=='#'){
                alert('Please select Sales Order No!');
                $('#sales_order_no').focus();
                return false;
            }
            if($("#sales_order_no_all").val().indexOf($('#sales_order_no option:selected').text())===-1){
                // agar no order tidak duplikat
                let orderNo = $("#sales_order_no_all").val()+','+$('#sales_order_no option:selected').text();
                $("#sales_order_no_all").val(orderNo);

                addSO();
                let totalRowSO = $("#totalRowSO").val();
                $('#sales_order_idSO'+(totalRowSO-1)).val($('#sales_order_no option:selected').val());
                $('#salesorder_no_select'+(totalRowSO-1)).text($('#sales_order_no option:selected').text());

                var fd = new FormData();
                fd.append('order_id', $('#sales_order_no option:selected').val());
                $.ajax({
                    url: "{{ url('/disp_so_part') }}",
                    type: "POST",
                    enctype: "application/x-www-form-urlencoded",
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (res) {
                        let o = res[0].parts;
                        let totOrder = o.length;
                        if (totOrder > 0) {
                            $("#rowTotal").remove();
                            $("#rowVAT").remove();
                            $("#rowGrandTotal").remove();

                            let TotalAmount = 0;
                            for (let i = 0; i < totOrder; i++) {
                                dispPart();

                                let totalRow = $("#totalRow").val();
                                $("#sales_order_part_id"+(totalRow-1)).val(o[i].sales_order_part_id);
                                $("#sales_order_id"+(totalRow-1)).val(o[i].sales_order_id);

                                let part_no_tmp = o[i].part_no;
                                if(part_no_tmp.length<11){
                                    part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, part_no_tmp.length);
                                }else{
                                    part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, 10)+"-"+part_no_tmp.substring(10, part_no_tmp.length);
                                }
                                $("#part_no"+(totalRow-1)).text(part_no_tmp);

                                $("#part_name"+(totalRow-1)).text(o[i].part_name);
                                $("#qtylbl"+(totalRow-1)).text(o[i].qty);
                                $("#qty"+(totalRow-1)).val(o[i].qty);
                                $("#qty_on_so"+(totalRow-1)).val(o[i].qty);
                                $("#price"+(totalRow-1)).text(parseFloat(o[i].price).numberFormat(0,'.',','));
                                $("#part_unit"+(totalRow-1)).text(o[i].part_unit);
                                $("#total"+(totalRow-1)).text(parseFloat(o[i].qty*o[i].price).numberFormat(0,'.',','));

                                TotalAmount += parseFloat(o[i].qty*o[i].price);

                                let weight = 0;
                                let weight_unit = '';
                                if(o[i].weight!==null){weight = o[i].weight;}
                                if(o[i].weight_unit!==null){weight_unit = o[i].weight_unit;}
                                $("#weight"+(totalRow-1)).text(weight+' '+weight_unit);
                            }

                            TotalAmount = 0;
                            for(iTot=0;iTot<$("#totalRow").val();iTot++){
                                TotalAmount += parseFloat($("#total"+iTot).text().replaceAll(',',''));
                            }

                            let lastTotalAmount = parseFloat(TotalAmount).numberFormat(0,'.',',');
                            let lastVAT = parseFloat(TotalAmount*{{ $vat }}/100).numberFormat(0,'.',',');
                            let lastgrandTotalAmount = parseFloat(TotalAmount+(TotalAmount*{{ $vat }}/100)).numberFormat(0,'.',',');
                            let vHtmlTotal =
                                '<tr id="rowTotal">'+
                                '<td colspan="6" style="text-align: right;"><label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}'+lastTotalAmount+'</label></td>'+
                                '<td colspan="2"></td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlTotal);
                            let vHtmlVAT =
                                '<tr id="rowVAT">'+
                                '<td colspan="6" style="text-align: right;"><label for="" name="lblVAT" id="lblVAT" class="col-form-label">VAT</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblVATAmount" id="lblVATAmount" class="col-form-label">{{ $qCurrency->string_val }}'+lastVAT+'</label></td>'+
                                '<td colspan="2"></td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlVAT);
                            let vHtmlGrandTotal =
                                '<tr id="rowGrandTotal">'+
                                '<td colspan="6" style="text-align: right;"><label for="" name="lblGrandTotal" id="lblGrandTotal" class="col-form-label">Grand Total</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblGrandTotalAmount" id="lblGrandTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}'+lastgrandTotalAmount+'</label></td>'+
                                '<td colspan="2"></td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlGrandTotal);
                        }

                        // reset penomoran
                        let j = 1;
                        for (i = 0; i < $("#totalRowSO").val(); i++) {
                            if($("#sales_order_row_number"+i).text()){
                                $("#sales_order_row_number"+i).text(j+'. ');
                                j++;
                            }
                        }

                        j = 1;
                        for (i = 0; i < $("#totalRow").val(); i++) {
                            if($("#sales_order_part_row_number"+i).text()){
                                $("#sales_order_part_row_number"+i).text(j+'. ');
                                j++;
                            }
                        }
                        // reset penomoran - end
                    }
                });
            }else{
                alert('The Sales Order number already exists!');
            }
        });

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });
</script>
@endsection
