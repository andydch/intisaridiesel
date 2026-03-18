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
                            {{-- @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif --}}
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
                                                        id="customer_id" name="customer_id" onchange="dispSJdate(this.value);">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('customer_id')?old('customer_id'):0);
                                                        @endphp
                                                        @foreach ($qCust as $qC)
                                                            <option @if($p_Id==$qC->id){{ 'selected' }}@endif
                                                                value="{{ $qC->id }}">{{ $qC->customer_unique_code.' - '.(!is_null($qC->entity_type)?$qC->entity_type->title_ind:'').' '.$qC->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('customer_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="surat_jalan_date" class="col-sm-3 col-form-label">SJ Date*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('surat_jalan_date') is-invalid @enderror"
                                                        id="surat_jalan_date" name="surat_jalan_date" onchange="dispSJ();">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $surat_jalan_date = (old('surat_jalan_date')?old('surat_jalan_date'):0);
                                                        @endphp
                                                        @foreach ($get_surat_jalan_date as $wT)
                                                            @php
                                                                $date=date_create($wT->surat_jalan_date);
                                                                // echo date_format($date,"d/m/Y");
                                                            @endphp
                                                            <option @if($surat_jalan_date==$wT->surat_jalan_date){{ 'selected' }}@endif value="{{ $wT->surat_jalan_date }}">{{ date_format($date,"d/m/Y") }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('surat_jalan_date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="surat_jalan_no" class="col-sm-3 col-form-label">Surat Jalan No*</label>
                                                <div class="col-sm-5">
                                                    <select class="form-select single-select @error('surat_jalan_no') is-invalid @enderror" id="surat_jalan_no" name="surat_jalan_no">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $surat_jalan_no = (old('surat_jalan_no')?old('surat_jalan_no'):0);
                                                        @endphp
                                                        @foreach ($get_surat_jalan_no as $wT)
                                                            <option @if($surat_jalan_no==$wT->order_no){{ 'selected' }}@endif value="{{ $wT->id }}">{{ $wT->order_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('surat_jalan_no_all')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <input type="hidden" name="surat_jalan_no_all" id="surat_jalan_no_all" value="@if(old('surat_jalan_no_all')){{ old('surat_jalan_no_all') }}@endif">
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
                                                                        <th scope="col" style="width: 94%;text-align:center;">Surat Jalan No</th>
                                                                        <th scope="col" style="width: 3%;">Delete</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="new-row-so">
                                                                    @if(old('surat_jalan_no_all'))
                                                                        @php
                                                                            $surat_jalan_no_all = explode(',',old('surat_jalan_no_all'));
                                                                            $iRow = 0;
                                                                        @endphp
                                                                        @foreach ($surat_jalan_no_all as $sNo)
                                                                            @if ($sNo!='')
                                                                                @php
                                                                                    $qS = \App\Models\Tx_surat_jalan::where('surat_jalan_no','=',$sNo)
                                                                                    ->first();
                                                                                @endphp
                                                                                <tr id="rowSO{{ $iRow }}">
                                                                                    <th scope="row" style="text-align:right;">
                                                                                        <label for="" id="surat-jalan{{ $iRow }}" class="col-form-label">{{ $iRow+1 }}.</label>
                                                                                        <input type="hidden" name="surat_jalan_idSO{{ $iRow }}" id="surat_jalan_idSO{{ $iRow }}"
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
                                        <th scope="col" style="width: 20%;">Part No</th>
                                        <th scope="col" style="width: 25%;">Part Name</th>
                                        <th scope="col" style="width: 7%;">Qty</th>
                                        <th scope="col" style="width: 5%;">Unit</th>
                                        <th scope="col" style="width: 12%;">Price({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 13%;">Total Price({{ $qCurrency->string_val }})</th>
                                        <th scope="col" style="width: 15%;">SJ No</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @if (old('totalRow'))
                                        @php
                                            $lastTotalAmount = 0;
                                            $iRowSJpart = 0;
                                        @endphp
                                        @for ($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if (old('surat_jalan_part_id'.$lastIdx))
                                                @php
                                                    $qPart = \App\Models\Tx_surat_jalan_part::where('tx_surat_jalan_parts.id','=',old('surat_jalan_part_id'.$lastIdx))
                                                    ->leftJoin('tx_surat_jalans as sj','tx_surat_jalan_parts.surat_jalan_id','=','sj.id')
                                                    ->select(
                                                        'tx_surat_jalan_parts.*',
                                                        'sj.surat_jalan_no')
                                                    ->first();
                                                @endphp
                                                <tr id="row{{ $lastIdx }}">
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="surat-jalan-part{{ $lastIdx }}" class="col-form-label">{{ $iRowSJpart }}.</label>
                                                        <input type="hidden" name="surat_jalan_part_id{{ $lastIdx }}" id="surat_jalan_part_id{{ $lastIdx }}"
                                                            value="@if(old('surat_jalan_part_id'.$lastIdx)){{ old('surat_jalan_part_id'.$lastIdx) }}@endif">
                                                        <input type="hidden" name="surat_jalan_id{{ $lastIdx }}" id="surat_jalan_id{{ $lastIdx }}"
                                                            value="@if(old('surat_jalan_id'.$lastIdx)){{ old('surat_jalan_id'.$lastIdx) }}@endif">
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
                                                    <td style="text-align: left;">
                                                        <label for="" name="weight{{ $lastIdx }}" id="weight{{ $lastIdx }}" class="col-form-label">{{ $qPart->surat_jalan_no }}</label>
                                                    </td>
                                                </tr>
                                                @php
                                                    $lastTotalAmount+= (old('qty'.$lastIdx)*$qPart->price);
                                                    $iRowSJpart++;
                                                @endphp
                                            @endif
                                        @endfor
                                        <tr id="rowTotal">
                                            <td colspan="6" style="text-align: right;">
                                                <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Grand Total</label>
                                            </td>
                                            <td style="text-align: right;">
                                                <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
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
    function dispSJ(){
        if($("#customer_id").val()==='#'){return false;}
        if($("#surat_jalan_date").val()==='#'){return false;}

        $("#surat_jalan_no").empty();
        $("#surat_jalan_no").append(`<option value="#">Choose...</option>`);
        $("#surat_jalan_no_all").val('');
        $("#new-row").empty();
        $("#new-row-so").empty();
        $("#totalRow").val(0);

        var fd = new FormData();
        fd.append('customer_id', $("#customer_id").val());
        fd.append('surat_jalan_date', $("#surat_jalan_date").val());
        $.ajax({
            url: "{{ url('/disp_so_non_tax') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].surat_jalan;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].order_no;
                        optionValue = o[i].id;
                        $("#surat_jalan_no").append(`<option value="${optionValue}">${optionText}</option>`);
                    }
                }
            }
        });
    }

    function dispSJdate(customer_id){
        $("#surat_jalan_date").empty();
        $("#surat_jalan_date").append(`<option value="#">Choose...</option>`);
        $("#surat_jalan_no_all").val('');
        $("#new-row").empty();
        $("#new-row-so").empty();
        $("#totalRow").val(0);

        var fd = new FormData();
        fd.append('customer_id', customer_id);
        $.ajax({
            url: "{{ url('/disp_so_non_tax_date') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].surat_jalan_date;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].surat_jalan_date;
                        optionValue = o[i].surat_jalan_date;

                        const dateArr = optionText.split("-");
                        $("#surat_jalan_date").append(`<option value="${optionValue}">${dateArr[2]+'/'+dateArr[1]+'/'+dateArr[0]}</option>`);
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
            '<label for="" id="surat-jalan-part'+totalRow+'" class="col-form-label">'+rowNo+'.</label>'+
            '<input type="hidden" name="surat_jalan_part_id'+totalRow+'" id="surat_jalan_part_id'+totalRow+'">'+
            '<input type="hidden" name="surat_jalan_id'+totalRow+'" id="surat_jalan_id'+totalRow+'">'+
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
            '<label for="" id="surat-jalan'+totalRowSO+'" class="col-form-label">'+rowNoSO+'.</label>'+
            '<input type="hidden" name="surat_jalan_idSO'+totalRowSO+'" id="surat_jalan_idSO'+totalRowSO+'">'+
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
            }else{
                //
            }
        }

        $('#lblTotalAmount').text('{{ $qCurrency->string_val }}'+(totalAmount).numberFormat(0,'.',','));
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

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#surat-jalan-part"+i).text()){
                    $("#surat-jalan-part"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });
        $("#btn-del-row-so").click(function() {
            for (i = 0; i < $("#totalRowSO").val(); i++) {
                let SO_No = $("#salesorder_no_select"+i).text();
                if ($("#rowCheckSO"+i).is(':checked')) {

                    //delete part
                    for (j = 0; j < $("#totalRow").val(); j++) {
                        if ($("#surat_jalan_id"+j).val()===$("#surat_jalan_idSO"+i).val()) {
                            $("#row"+j).remove();
                        }
                    }
                    recalcTotal();
                    //delete part

                    $("#rowSO"+i).remove();
                    $("#surat_jalan_no_all").val($("#surat_jalan_no_all").val().replace(SO_No,''));
                    $("#surat_jalan_no_all").val($("#surat_jalan_no_all").val().replace(',,',','));
                    if($("#surat_jalan_no_all").val()===','){
                        $("#surat_jalan_no_all").val('');
                    }
                }
            }

            // reset penomoran
            j = 1;
            for (i = 0; i < $("#totalRowSO").val(); i++) {
                if($("#surat-jalan"+i).text()){
                    $("#surat-jalan"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#gen_part").click(function() {
            if($('#surat_jalan_no option:selected').val()=='#'){
                alert('Please select Surat Jalan No!');
                $('#surat_jalan_no').focus();
                return false;
            }
            if($("#surat_jalan_no_all").val().indexOf($('#surat_jalan_no option:selected').text())===-1){
                // agar no order tidak duplikat
                let orderNo = $("#surat_jalan_no_all").val()+','+$('#surat_jalan_no option:selected').text();
                $("#surat_jalan_no_all").val(orderNo);

                addSO();
                let totalRowSO = $("#totalRowSO").val();
                $('#surat_jalan_idSO'+(totalRowSO-1)).val($('#surat_jalan_no option:selected').val());
                $('#salesorder_no_select'+(totalRowSO-1)).text($('#surat_jalan_no option:selected').text());

                var fd = new FormData();
                fd.append('order_id', $('#surat_jalan_no option:selected').val());
                $.ajax({
                    url: "{{ url('/disp_so_part_non_tax') }}",
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

                            let TotalAmount = 0;
                            for (let i = 0; i < totOrder; i++) {
                                dispPart();

                                let totalRow = $("#totalRow").val();
                                $("#surat_jalan_part_id"+(totalRow-1)).val(o[i].surat_jalan_part_id);
                                $("#surat_jalan_id"+(totalRow-1)).val(o[i].surat_jalan_id);

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
                                $("#weight"+(totalRow-1)).text(o[i].surat_jalan_no);
                            }

                            let lastTotalAmount = parseFloat(TotalAmount).numberFormat(0,'.',',');
                            let vHtmlTotal =
                                '<tr id="rowTotal">'+
                                '<td colspan="6" style="text-align: right;"><label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label></td>'+
                                '<td style="text-align: right;"><label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}'+lastTotalAmount+'</label></td>'+
                                '<td colspan="2"></td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlTotal);
                        }

                        // reset penomoran
                        j = 1;
                        for (i = 0; i < $("#totalRowSO").val(); i++) {
                            if($("#surat-jalan"+i).text()){
                                $("#surat-jalan"+i).text(j+'. ');
                                j++;
                            }
                        }

                        j = 1;
                        for (i = 0; i < $("#totalRow").val(); i++) {
                            if($("#surat-jalan-part"+i).text()){
                                $("#surat-jalan-part"+i).text(j+'. ');
                                j++;
                            }
                        }
                        // reset penomoran - end
                    }
                });
            }else{
                alert('The Surat Jalan number already exists!');
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
