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
            <form id="submit-form" action="{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}" method="POST" enctype="application/x-www-form-urlencoded">
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
                                <div class="col-xl-12">
                                    <div class="row">
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="supplier_id" class="col-sm-3 col-form-label">Supplier*</label>
                                                <div class="col-sm-9">
                                                    <select class="form-select single-select @error('supplier_id') is-invalid @enderror"
                                                        id="supplier_id" name="supplier_id" onchange="dispInvoiceNo(this.value,'');">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $p_Id = (old('supplier_id')?old('supplier_id'):0);
                                                        @endphp
                                                        @foreach ($querySupplier as $qS)
                                                        <option @if ($p_Id==$qS->id) {{ 'selected' }} @endif
                                                            value="{{ $qS->id }}">{{ (!is_null($qS->entity_type)?$qS->entity_type->title_ind:'').' '.$qS->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="ro_id" class="col-sm-3 col-form-label">Invoice No*</label>
                                                <div class="col-sm-9">
                                                    <select onchange="dispReceiptOrderInfo(this.value);" class="form-select single-select @error('ro_id') is-invalid @enderror"
                                                        id="ro_id" name="ro_id">
                                                        <option value="#">Choose...</option>
                                                        @php
                                                            $ro_Id = (old('ro_id')?old('ro_id'):0);
                                                        @endphp
                                                        @foreach ($invoice_no as $qS)
                                                        <option @if ($ro_Id==$qS->id) {{ 'selected' }} @endif
                                                            value="{{ $qS->id }}">{{ $qS->invoice_no }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('ro_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="courier_id" class="col-sm-3 col-form-label">Ship By*</label>
                                                <div class="col-sm-3">
                                                    <select class="form-select" name="courier_type" id="courier_type">
                                                        <option @if(old('courier_type')==env('AMBIL_SENDIRI')){{ 'selected' }}@endif value="{{ env('AMBIL_SENDIRI') }}">{{ env('AMBIL_SENDIRI_STR') }}</option>
                                                        <option @if(old('courier_type')==env('DIANTAR')){{ 'selected' }}@endif value="{{ env('DIANTAR') }}">{{ env('DIANTAR_STR') }}</option>
                                                        <option @if(old('courier_type')==env('COURIER')){{ 'selected' }}@endif value="{{ env('COURIER') }}">{{ env('COURIER_STR') }}</option>
                                                    </select>
                                                </div>
                                                <div id="courier-list" class="col-sm-6" style="@if(old('courier_type')==env('COURIER')){{ 'display: block;' }}@else{{ 'display: none;' }}@endif">
                                                    <select class="form-select single-select @error('courier_id') is-invalid @enderror" id="courier_id" name="courier_id">
                                                        <option value="">Choose...</option>
                                                        @php
                                                            $p_Id = (old('courier_id')?old('courier_id'):0);
                                                        @endphp
                                                        @foreach ($couriers as $c)
                                                        <option @if ($p_Id==$c->id) {{ 'selected' }} @endif
                                                            value="{{ $c->id }}">{{ $c->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('courier_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
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
                                        <div class="col-xl-6">
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">&nbsp;</label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" style="visibility: hidden;">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="receipt_order_no" class="col-sm-3 col-form-label">Receipt Order No</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('receipt_order_no') is-invalid @enderror"
                                                        maxlength="255" id="receipt_order_no" name="receipt_order_no"
                                                        value="@if (old('receipt_order_no')){{ old('receipt_order_no') }}@endif">
                                                    @error('receipt_order_no')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="" class="col-sm-3 col-form-label">Journal Type</label>
                                                @php
                                                    $journal_type_id = '';
                                                @endphp
                                                @if (old('receipt_order_no'))
                                                    @php
                                                        $qRo = \App\Models\Tx_receipt_order::where([
                                                            'receipt_no'=>old('receipt_order_no'),
                                                        ])
                                                        ->first();
                                                        if ($qRo){
                                                            $journal_type_id = $qRo->journal_type_id;
                                                        }
                                                    @endphp
                                                @endif
                                                <label for="" id="journal_type_id" class="col-sm-9 col-form-label">{{ $journal_type_id }}</label>
                                            </div>
                                            <div class="row mb-3">
                                                <label for="branch_name" class="col-sm-3 col-form-label">Branch</label>
                                                <div class="col-sm-9">
                                                    <input readonly type="text" class="form-control @error('branch_name') is-invalid @enderror"
                                                        maxlength="255" id="branch_name" name="branch_name"
                                                        value="@if (old('branch_name')){{ old('branch_name') }}@endif">
                                                    @error('branch_name')
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
                                        <th scope="col" style="width: 35%;">Part Name</th>
                                        <th scope="col" style="width: 10%;">Qty</th>
                                        <th scope="col" style="width: 10%;">Qty Retur</th>
                                        <th scope="col" style="width: 10%;">Price</th>
                                        <th scope="col" style="width: 25%;">Description</th>
                                        <th scope="col" style="width: 3%;">Del</th>
                                    </tr>
                                </thead>
                                <tbody id="new-row">
                                    @php
                                        $iRow = 1;
                                    @endphp
                                    @if (old('totalRow'))
                                        @for($lastIdx=0;$lastIdx<old('totalRow');$lastIdx++)
                                            @if(old('part_id'.$lastIdx))
                                                {{-- cek apakah ada hapus baris sebelum di-submit --}}
                                                <tr id="row{{ $lastIdx }}">
                                                    @php
                                                        $query = \App\Models\Mst_part::where('id','=',old('part_id'.$lastIdx))
                                                        ->first();
                                                    @endphp
                                                    <th scope="row" style="text-align:right;">
                                                        <label for="" id="purchase_retur_row_number{{ $lastIdx }}" class="col-form-label">{{ $iRow }}.</label>
                                                        <input type="hidden" name="po_mo_no{{ $lastIdx }}" id="po_mo_no{{ $lastIdx }}" value="@if (old('po_mo_no'.$lastIdx)){{ old('po_mo_no'.$lastIdx) }}@endif">
                                                        <input type="hidden" name="part_id{{ $lastIdx }}" id="part_id{{ $lastIdx }}" value="@if (old('part_id'.$lastIdx)){{ old('part_id'.$lastIdx) }}@endif">
                                                    </th>
                                                    {{-- <td><label for="" name="part_no{{ $lastIdx }}" id="part_no{{ $lastIdx }}" class="col-form-label">{{ $query->part_number }}</label></td> --}}
                                                    <td>
                                                        @php
                                                            $partNumber = $query->part_number;
                                                        if(strlen($partNumber)<11){
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,strlen($partNumber));
                                                        }else{
                                                            $partNumber = substr($partNumber,0,5).'-'.substr($partNumber,5,5).'-'.substr($partNumber,10,strlen($partNumber));
                                                        }
                                                        @endphp
                                                        <label for="" name="part_name{{ $lastIdx }}" id="part_name{{ $lastIdx }}"
                                                            class="col-form-label">{{ $partNumber.' : '.$query->part_name }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <input type="hidden" name="qty{{ $lastIdx }}" id="qty{{ $lastIdx }}" value="@if (old('qty'.$lastIdx)){{ old('qty'.$lastIdx) }}@endif">
                                                        <label for="" name="qtyLbl{{ $lastIdx }}" id="qtyLbl{{ $lastIdx }}" class="col-form-label">{{ (old('qty'.$lastIdx)?old('qty'.$lastIdx):0) }}</label>
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <input type="text" name="qty_retur{{ $lastIdx }}" id="qty_retur{{ $lastIdx }}"
                                                            class="form-control @error('qty_retur'.$lastIdx) is-invalid @enderror"
                                                            style="text-align: right;width:100%;" value="@if (old('qty_retur'.$lastIdx)){{ old('qty_retur'.$lastIdx) }}@endif">
                                                        @error('qty_retur'.$lastIdx)
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </td>
                                                    <td style="text-align: right;">
                                                        <input type="hidden" name="price{{ $lastIdx }}" id="price{{ $lastIdx }}" value="@if (old('price'.$lastIdx)){{ old('price'.$lastIdx) }}@endif">
                                                        <label for="" name="priceLbl{{ $lastIdx }}" id="priceLbl{{ $lastIdx }}" class="col-form-label">{{ $qCurrency->string_val.number_format((old('price'.$lastIdx)?old('price'.$lastIdx):0),0,'.',',') }}</label>
                                                    </td>
                                                    <td><textarea name="desc_part{{ $lastIdx }}" id="desc_part{{ $lastIdx }}" class="form-control" maxlength="1024" rows="3" style="width: 100%;">@if (old('desc_part'.$lastIdx)){{ old('desc_part'.$lastIdx) }}@endif</textarea></td>
                                                    <td style="text-align:center;"><input type="checkbox" id="rowCheck{{ $lastIdx }}" value="{{ $lastIdx }}"></td>
                                                </tr>
                                                @php
                                                    $iRow++;
                                                @endphp
                                            @endif
                                        @endfor
                                    @endif
                                </tbody>
                            </table>
                            <div class="input-group">
                                <input type="button" id="btn-del-row" class="btn btn-danger px-5" style="margin-top: 15px;" value="Remove Row">
                            </div>
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
                <input type="hidden" name="lastTotalAmountTmp" id="lastTotalAmountTmp" value="@if (old('lastTotalAmountTmp')){{ old('lastTotalAmountTmp') }}@else{{ 0 }}@endif">
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
    function dispInvoiceNo(supplier_id, old_inv_no){
        $("#new-row").empty();
        $("#ro_id").empty();
        $("#ro_id").append(`<option value="#">Choose...</option>`);
        $("#receipt_order_no").empty();
        $("#branch_name").empty();

        if(supplier_id!=='#'){
            var fd = new FormData();
            fd.append('supplier_id', supplier_id);
            fd.append('old_inv_no', old_inv_no);
            $.ajax({
                url: "{{ url('/disp_inv_no') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].invoice_no;
                    let totInv = o.length;
                    if (totInv > 0) {
                        for (let i = 0; i < totInv; i++) {
                            optionText = o[i].invoice_no;
                            optionValue = o[i].id;
                            $("#ro_id").append(`<option value="${optionValue}">${optionText}</option>`);
                        }
                    }
                }
            });
        }
    }

    function dispReceiptOrderInfo(ro_id){
        $("#new-row").empty();
        $("#receipt_order_no").empty();
        $("#branch_name").empty();
        $("#totalRow").val(0);

        if(ro_id!=='#'){
            var fd = new FormData();
            fd.append('ro_id', ro_id);
            $.ajax({
                url: "{{ url('/disp_ro') }}",
                type: "POST",
                enctype: "application/x-www-form-urlencoded",
                data: fd,
                cache: false,
                contentType: false,
                processData: false,
                dataType: "json",
                success: function (res) {
                    let o = res[0].ro_info;
                    $("#receipt_order_no").val(o.receipt_no);
                    $("#journal_type_id").text(o.journal_type_id);
                    $("#branch_name").val(o.branch_name);

                    let p = res[0].ro_part_info;
                    let totPart = p.length;
                    if(totPart>0){
                        for (let i = 0; i < totPart; i++) {
                            dispPart();

                            $('#po_mo_no'+i).val(p[i].po_mo_no);
                            $('#part_id'+i).val(p[i].part_id);

                            let part_no_tmp = p[i].part_number;
                            if(part_no_tmp.length<11){
                                part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, part_no_tmp.length);
                            }else{
                                part_no_tmp = part_no_tmp.substring(0, 5)+"-"+part_no_tmp.substring(5, 10)+"-"+part_no_tmp.substring(10, part_no_tmp.length);
                            }
                            $('#part_name'+i).text(part_no_tmp+' : '+p[i].part_name);

                            $('#qty'+i).val(p[i].qty);
                            $('#qtyLbl'+i).text(p[i].qty);
                            $('#price'+i).val(p[i].final_cost);
                            $('#priceLbl'+i).text('{{ $qCurrency->string_val }}'+parseFloat(p[i].final_cost).numberFormat(0,'.',','));
                        }
                    }

                    // reset penomoran
                    let j = 1;
                    for (i = 0; i < $("#totalRow").val(); i++) {
                        if($("#purchase_retur_row_number"+i).text()){
                            $("#purchase_retur_row_number"+i).text(j+'. ');
                            j++;
                        }
                    }
                    // reset penomoran - end
                }
            });
        }
    }

    function dispPart(){
        let totalRow = $("#totalRow").val();
        let rowNo = (parseInt(totalRow)+1);
        let vHtml =
            '<tr id="row'+totalRow+'">' +
            '<th scope="row" style="text-align:right;">'+
            '<label for="" id="purchase_retur_row_number'+totalRow+'" class="col-form-label">'+rowNo+'.</label>'+
            '<input type="hidden" name="po_mo_no'+totalRow+'" id="po_mo_no'+totalRow+'">'+
            '<input type="hidden" name="part_id'+totalRow+'" id="part_id'+totalRow+'">'+
            '</th>' +
            '<td><label for="" name="part_name'+totalRow+'" id="part_name'+totalRow+'" class="col-form-label"></label></td>' +
            '<td style="text-align: right;">'+
            '<input type="hidden" name="qty'+totalRow+'" id="qty'+totalRow+'">'+
            '<label for="" name="qtyLbl'+totalRow+'" id="qtyLbl'+totalRow+'" class="col-form-label"></label>'+
            '</td>' +
            '<td style="text-align: right;">'+
            '<input type="text" name="qty_retur'+totalRow+'" id="qty_retur'+totalRow+'" value="" class="form-control" style="text-align: right;width:100%;">'+
            '</td>' +
            '<td style="text-align: right;">'+
            '<input type="hidden" name="price'+totalRow+'" id="price'+totalRow+'">'+
            '<label for="" name="priceLbl'+totalRow+'" id="priceLbl'+totalRow+'" class="col-form-label"></label>'+
            '</td>' +
            '<td><textarea name="desc_part'+totalRow+'" id="desc_part'+totalRow+'" class="form-control" rows="3" style="width: 100%;"></textarea></td>' +
            '<td style="text-align:center;"><input type="checkbox" id="rowCheck'+totalRow+'" value="'+totalRow+'"></td>' +
            '</tr>';
        $("#new-row").append(vHtml);
        $("#totalRow").val(rowNo);

        $('.single-select').select2({
            theme: 'bootstrap4',
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            allowClear: Boolean($(this).data('allow-clear')),
        });
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
            
            // history.back();
            location.href = "{{ url(ENV('TRANSACTION_FOLDER_NAME').'/'.$folder) }}";
        });

        $("#btn-del-row").click(function() {
            for (i = 0; i < $("#totalRow").val(); i++) {
                if ($("#rowCheck"+i).is(':checked')) {
                    $("#row"+i).remove();
                }
            }

            // reset penomoran
            let j = 1;
            for (i = 0; i < $("#totalRow").val(); i++) {
                if($("#purchase_retur_row_number"+i).text()){
                    $("#purchase_retur_row_number"+i).text(j+'. ');
                    j++;
                }
            }
            // reset penomoran - end
        });

        $("#courier_type").change(function() {
            if(parseInt($("#courier_type").val())===parseInt({{ env('COURIER') }})){
                $("#courier-list").css("display","block");
            }else{
                $("#courier-list").css("display","none");
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
