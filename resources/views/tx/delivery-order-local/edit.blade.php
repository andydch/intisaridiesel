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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$uri.'/'.$queryDelivery->id) }}" method="POST" enctype="application/x-www-form-urlencoded">
                @csrf
                @method('PUT')
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
                                                <label for="customer_id" class="col-sm-3 col-form-label">NP No</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label part-id">{{ $queryDelivery->delivery_order_no }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">Customer</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ $queryDelivery->customer->name }}</label>
                                                <input type="hidden" name="customer_id" id="customer_id"
                                                    value="@if (old('customer_id')){{ old('customer_id') }}@else{{ $queryDelivery->customer_id }}@endif">
                                            </div>
                                            <div class="row mb-3">
                                                <label for="customer_id" class="col-sm-3 col-form-label">SJ Date</label>
                                                <label for="customer_id" class="col-sm-9 col-form-label">{{ date_format(date_create($queryDelivery->delivery_order_date), 'd/m/Y') }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="sales_order_no" class="col-sm-3 col-form-label">Surat Jalan No</label>
                                                <div class="col-sm-9">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <table class="table table-bordered mb-0">
                                                                <thead>
                                                                    <tr style="width: 100%;">
                                                                        <th scope="col" style="width: 3%;text-align:center;">#</th>
                                                                        <th scope="col" style="width: 94%;text-align:center;">Surat Jalan No</th>
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
                                                            <input type="hidden" name="surat_jalan_no_all" id="surat_jalan_no_all" class="@error('surat_jalan_no_all') is-invalid @enderror" 
                                                                value="{{ $queryDelivery->sales_order_no_all }}">
                                                            @error('surat_jalan_no_all')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="remark" class="col-sm-3 col-form-label">Remark</label>
                                                <div class="col-sm-9">
                                                    <textarea name="remark" id="remark" rows="3" class="form-control @error('remark') is-invalid @enderror"
                                                        style="width: 100%;">@if (old('remark')){{ old('remark') }}@else{{ $queryDelivery->remark }}@endif</textarea>
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
                                    @php
                                        $lastIdx = 0;
                                        $lastTotalAmount = 0;
                                    @endphp
                                    @foreach ($parts as $part)
                                        @php
                                            // tampilan referensi ke surat jalan
                                            $sj_no = '';
                                            $sj = \App\Models\Tx_surat_jalan::where('id','=',$part->sales_order_id)
                                            ->first();
                                            if($sj){
                                                $sj_no = $sj->surat_jalan_no;
                                            }
                                        @endphp
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
                                            <td style="text-align: left;">
                                                <label for="" name="weight{{ $lastIdx }}" id="weight{{ $lastIdx }}" class="col-form-label">{{ $sj_no }}</label>
                                            </td>
                                        </tr>
                                        @php
                                            $lastIdx += 1;
                                            $lastTotalAmount += ($part->qty*$part->final_price);
                                        @endphp
                                    @endforeach
                                    <tr id="rowTotal">
                                        <td colspan="6" style="text-align: right;">
                                            <label for="" name="lblTotal" id="lblTotal" class="col-form-label">Grand Total</label>
                                        </td>
                                        <td style="text-align: right;">
                                            <label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val.number_format($lastTotalAmount,0,'.',',') }}</label>
                                        </td>
                                        <td>&nbsp;</td>
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
                                    <input type="hidden" name="is_draft" id="is_draft" value="Y">
                                    @if ($queryDelivery->is_draft=='Y')
                                        <input type="button" id="save-as-draft" class="btn btn-secondary px-5" value="Save as Draft">
                                        <input type="button" id="save" class="btn btn-primary px-5" value="Save">
                                    @endif
                                    <input type="hidden" name="orderId" id="orderId">
                                    <input type="button" id="del-btn" class="btn btn-danger px-5" value="Delete">
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
<script src="{{ asset('assets/plugins/datetimepicker/js/legacy.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.time.js') }}"></script>
<script src="{{ asset('assets/plugins/datetimepicker/js/picker.date.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/moment.min.js') }}"></script>
<script src="{{ asset('assets/plugins/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('assets/js/my-custom.js') }}"></script>
<script>
    function dispSO(customer_id){
        $("#sales_order_no").empty();
        $("#sales_order_no").append(`<option value="#">Choose...</option>`);
        $("#sales_order_no_all").val('');
        $("#new-row").empty();
        $("#new-row-so").empty();
        $("#totalRow").val(0);

        var fd = new FormData();
        fd.append('customer_id', customer_id);
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
                let o = res[0].sales_order;
                let totOrder = o.length;
                if (totOrder > 0) {
                    for (let i = 0; i < totOrder; i++) {
                        optionText = o[i].order_no;
                        optionValue = o[i].id;
                        $("#sales_order_no").append(
                            `<option value="${optionValue}">${optionText}</option>`
                        );
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

    function dispPoCurr(po_no){
        $("#currency_id").val('');
        $("#currency_name").val('');
        $("#shipto_id").val('');
        $("#shipto_name").val('');

        var fd = new FormData();
        fd.append('po_no', po_no);
        $.ajax({
            url: "{{ url('/disp_po_curr') }}",
            type: "POST",
            enctype: "application/x-www-form-urlencoded",
            data: fd,
            cache: false,
            contentType: false,
            processData: false,
            dataType: "json",
            success: function (res) {
                let o = res[0].curr;
                $("#currency_id").val(o.curr_id);
                $("#currency_name").val(o.title_ind);
                $("#shipto_id").val(o.shipto_id);
                $("#shipto_name").val(o.shipto_name);
                $("#courier_id").val(o.courier_id).change();
            }
        });
    }

    function dispPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow) + 1);
        let vHtml =
            '<tr id="row' + totalRow + '">' +
            '<th scope="row" style="text-align:right;">' +
            '<label for="" class="col-form-label">' + rowNo + '.</label>' +
            '<input type="hidden" name="sales_order_part_id' + totalRow + '" id="sales_order_part_id' + totalRow + '">' +
            '<input type="hidden" name="sales_order_id' + totalRow + '" id="sales_order_id' + totalRow + '">' +
            '</th>' +
            '<td><label for="" name="part_no' + totalRow + '" id="part_no' + totalRow + '" class="col-form-label"></label></td>' +
            '<td><label for="" name="part_name' + totalRow + '" id="part_name' + totalRow + '" class="col-form-label"></label></td>' +
            '<td style="text-align: right;">'+
            // '<input type="text" name="qty' + totalRow + '" id="qty' + totalRow + '" class="form-control" style="text-align: right;width:100%;">'+
            '<label for="" name="qtylbl' + totalRow + '" id="qtylbl' + totalRow + '" class="col-form-label"></label>'+
            '<input type="hidden" name="qty' + totalRow + '" id="qty' + totalRow + '">'+
            '<input type="hidden" name="qty_on_so' + totalRow + '" id="qty_on_so' + totalRow + '">'+
            '</td>' +
            '<td><label for="" name="part_unit' + totalRow + '" id="part_unit' + totalRow + '" class="col-form-label"></label></td>' +
            '<td style="text-align: right;"><label for="" name="price' + totalRow + '" id="price' + totalRow + '" class="col-form-label"></label></td>' +
            '<td style="text-align: right;"><label for="" name="total' + totalRow + '" id="total' + totalRow + '" class="col-form-label"></label></td>' +
            '<td style="text-align: right;"><label for="" name="weight' + totalRow + '" id="weight' + totalRow + '" class="col-form-label"></label></td>' +
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck' + totalRow + '" value="' + totalRow + '"></td>' +
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);
    }

    function addSO(){
        let totalRowSO = $("#totalRowSO").val();
        let rowNoSO = (parseInt(totalRowSO) + 1);
        let vHtml =
            '<tr id="rowSO' + totalRowSO + '">' +
            '<th scope="row" style="text-align:right;">' +
            '<label for="" class="col-form-label">' + rowNoSO + '.</label>' +
            '<input type="hidden" name="sales_order_idSO' + totalRowSO + '" id="sales_order_idSO' + totalRowSO + '">' +
            '</th>' +
            '<td><label for="" name="salesorder_no_select' + totalRowSO + '" id="salesorder_no_select' + totalRowSO + '" class="col-form-label"></label></td>' +
            '<td style="text-align:center;"><input type="checkbox" id="rowCheckSO' + totalRowSO + '" value="' + totalRowSO + '"></td>' +
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
                totalAmount += parseFloat(totalAmountPerRow);
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
        $("#del-btn").click(function() {
            let msg = 'The following FK Numbers will be canceled.\n{{ $queryDelivery->delivery_order_no }}\nContinue?';
            if(!confirm(msg)){
                event.preventDefault();
            }else{
                $("#orderId").val('{{ $queryDelivery->id }}');
                $("input[name='_method']").val('POST');
                $('#submit-form').attr('method', "POST");
                $('#submit-form').attr('action', "{{ url('/del_deliveryorder_nontax') }}");
                $("#submit-form").submit();
            }
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
                                $("#part_no"+(totalRow-1)).text(o[i].part_no);
                                $("#part_name"+(totalRow-1)).text(o[i].part_name);
                                $("#qty"+(totalRow-1)).text(o[i].qty);
                                $("#qty"+(totalRow-1)).val(o[i].qty);
                                $("#qty_on_so"+(totalRow-1)).val(o[i].qty);
                                $("#price"+(totalRow-1)).text('{{ $qCurrency->string_val }}'+o[i].price);
                                $("#part_unit"+(totalRow-1)).text(o[i].part_unit);
                                $("#total"+(totalRow-1)).text('{{ $qCurrency->string_val }}'+parseFloat(o[i].qty*o[i].price).numberFormat(0,'.',','));
                                $("#weight"+(totalRow-1)).text(o[i].weight+' '+o[i].weight_unit);
                            }
                            for(iTot=0;iTot<$("#totalRow").val();iTot++){
                                TotalAmount += parseFloat($("#total"+iTot).text().replaceAll('.','').replaceAll(',','.'));
                            }

                            let lastTotalAmount = parseFloat(TotalAmount).numberFormat(0,'.',',');
                            let vHtmlTotal =
                                '<tr id="rowTotal">' +
                                '<td colspan="6" style="text-align: right;"><label for="" name="lblTotal" id="lblTotal" class="col-form-label">Total</label></td>' +
                                '<td style="text-align: right;"><label for="" name="lblTotalAmount" id="lblTotalAmount" class="col-form-label">{{ $qCurrency->string_val }}'+lastTotalAmount+'</label></td>' +
                                '<td colspan="2"></td>'+
                                '</tr>';
                            $("#new-row").append(vHtmlTotal);
                        }
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
